<?php

class log {
	public static function info($msg){
		self::addEntry('info', $msg);
	}

	public static function warning($msg){
		self::addEntry('warning', $msg);
	}

	public static function addEntry($level, $msg){
		return true;
		if (is_array($msg)) {
			$msg = print_r($msg, true);
		}
		$date = date('Y:m:d h:i:s');
		$entry = '['.$date.'] ['.$level.'] ['.$msg.']'.PHP_EOL;
		file_put_contents(LOGDIR.'/'.$level.'.log', $entry, FILE_APPEND);
	}
}
