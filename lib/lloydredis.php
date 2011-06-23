<?php
define('NL', sprintf('%s%s', chr(13), chr(10)));

class RedisException extends Exception(){
	public function __construct($msg, $code=0, Exception $prev=null){
		$msg = __CLASS__ . ':' . $msg;
        parent::__construct($msg, $code, $prev);
	}

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
			throw new RedisException("{$errno} - {$errstr}");
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
		$cmd = $this->prepareCmdString($name, $args);
		$this->sendCmd($cmd);
		$reply = trim(fgets($this->socket, 512));
		switch (substr($reply, 0, 1)) {
			case '-': $response = $this->responseError($reply)     ; break ; 
			case ':': $response = $this->responseInt($reply)       ; break ; 
			case '$': $response = $this->responseMulti($reply)     ; break ; 
			case '*': $response = $this->responseMultiMany($reply) ; break ; 
			case '+': $response = $this->responseSimple($reply)    ; break ; 
			default:
				throw new RedisException('No entiendo la respuesta del servidor de redis: ' . $reply);
				break;
		}
		$response = $this->prepareResponse($type, $response);
		return $response;
	}

    private function prepareCmdString($name, $args) {
		if(count($args) && is_array($args[count($args)-1])) {
			$args = $this->expandArgs($args);
		}
		array_unshift($args, strtoupper($name));
		$cmd  = sprintf('*%d%s%s%s', count($args), NL, $this->withLengths($args), NL);
		$done = 0;
    }

	private function prepareResponse(array $response_details){
		list($type, $response) = $response_details);

		// Do something with response depending on gets or type

		return $response;
	}

	private function sendCmd($cmd){
		for ($w = 0, $cmdlen = strlen($cmd); $w < $cmdlen; $w += $done) {
			$done = fwrite($this->socket, substr($cmd, $w));
			if ($done === FALSE) {
				throw new RedisException('Failed to write entire command to stream');
			}
		}

	}

	private function responseInt($reply){
		return array('int', intval(substr(trim($reply), 1))); 
	}

	private function responseError($reply){
		throw new RedisException(substr(trim($reply), 4));
	}

	private function responseSimple($reply){
		return array('plus', substr(trim($reply), 1));  
	}

	private function responseMulti($reply){
		$response = null;
		if ($reply == '$-1') { // key does not exist
			break;
		}
		$response = $this->getMultiResponse();
		fread($this->socket, 2); 
        return array('multi', $response);
	}

	private function responseMultiMany($reply){
		$count = substr($reply, 1);
		if ($count == '-1') {
			return null;
		}
		$response = array();
		for ($i = 0; $i < $count; $i++) {
			$bulk_head = trim(fgets($this->socket, 512));
			$size      = substr($bulk_head, 1);
			$response[] = ($size == '-1') ? null : $this->getMultiResponse();
		}
		return array('multimany', $response);
	}

	private function getMultiReponse(){
		$response = '';
		$read_so_far = 0;
		$size = substr($reply, 1);
		do {
			$read_pos     = min(1024, ($size - $read_so_far));
			$response    .= fread($this->socket, $read_pos);
			$read_so_far += $read_pos;
		} while ($read_so_far < $size);
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
