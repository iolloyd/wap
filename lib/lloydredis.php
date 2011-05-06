<?php
define('NL', sprintf('%s%s', chr(13), chr(10)));

class RedisException extends \Exception {
}

class lloydredis {

	public $host;
	public $port;

	private $socket;
	private $number_of_gets;

	public function __construct($host=null, $port=null){
		if (!$host || !$port) {
			$conn = config::read('host', 'redis');
		}
		$this->host   = $host ?: $conn['host'];
		$this->port   = $port ?: $conn['port'];
		$this->socket = fsockopen($this->host, $this->port, $errno, $errstr);
		if (!$this->socket) {
			throw new \Exception("{$errno} - {$errstr}");
		}
	}

	public function __destruct() {
		fclose($this->socket);
	}

	/*
	 * The new unified request protocol as of 1.2 is of the general form:
	 * '*'<number of args> CR LF
	 * '$'<length of argument 1> CR LF
	 * <argument 1> CR LF
	 * ...
	 * ...
	 * <length of argument n> CR LF
	 * <argument n data>
	 *
	 * Example: SET xyz fooball becomes ->
	 * '*'3\r\n$3\r\nSET\r\n$3\r\nxyzr\nfooball\r\n 
	 */

	function __call($name, $args) {
		if(count($args) && is_array($args[count($args)-1])) {
			$args = $this->expandArgs($args);
		}
		array_unshift($args, strtoupper($name));
		$number_of_gets = 
		$cmd  = sprintf('*%d%s%s%s', count($args), NL, $this->withLengths($args), NL);
		$done = 0;
		for ($w = 0; $w < strlen($cmd); $w += $done) {
			$done = fwrite($this->socket, substr($cmd, $w));
			if ($done === FALSE) {
				throw new \Exception('Failed to write entire command to stream');
			}
		}

		/**
		 * Parse the response based on the reply identifier 
		 */
		$reply = trim(fgets($this->socket, 512));
		switch (substr($reply, 0, 1)) {

			case '+': $response = substr(trim($reply), 1); break;
			case '-': throw new RedisException(substr(trim($reply), 4)); break;
			case ':': $response = intval(substr(trim($reply), 1)); break;
			case '$':
				$response = null;

				// If the following happens, it means that the key does not exist
				// and so we leave $response as null.
				if ($reply == '$-1') {
					break;
				}
				$read_so_far = 0;
				$size        = substr($reply, 1);
				do {
					$read_ptr     = min(1024, ($size - $read_so_far));
					$response    .= fread($this->socket, $read_ptr);
					$read_so_far += $read_ptr;
				} while ($read_so_far < $size);
				fread($this->socket, 2); 
				break;

			case '*':
				$count = substr($reply, 1);
				if ($count == '-1') {
					return null;
				}
				$response = array();
				for ($i = 0; $i < $count; $i++) {
					$bulk_head = trim(fgets($this->socket, 512));
					$size      = substr($bulk_head, 1);
					if ($size == '-1') {
						$response[] = null;
					} else {
						$read_so_far = 0;
						$block = "";
						do {
							$read_ptr     = min(1024, ($size - $read_so_far));
							$block       .= fread($this->socket, $read_ptr);
							$read_so_far += $read_ptr;
						} while ($read_so_far < $size);
						fread($this->socket, 2); 
						$response[] = $block;
					}
				}
				break;

			default:
				throw new RedisException("server response makes no sense to me: {$reply}");
				break;
		}
		$counts         = array_count_values($args);
		$number_of_gets = $counts['get'];
		$response       = array_chunk($response, $number_of_gets);
		return $response;
	}

	private function expandArgs($args){
		$first = $args[0];
		$rev   = array_reverse($args);
		$out   = array();
		foreach($rev[0] as $k => $v){
			$out[] = $k;
			$out[] = $v;
		}
		array_unshift($out, $args[0]);
		return $out;
	}

	private function withLengths($args){
		$cmd_parts = array_map(function($x){ 
			return sprintf('$%d%s%s%s', strlen($x), NL, $x, NL); }, 
			$args
		);
		return implode(NL, $cmd_parts);
	}

}
