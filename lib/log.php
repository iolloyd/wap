<?php

class log {
	public static function info($msg){
		$this->addEntry('info', $msg);
	}

	public static function warning($msg){
		$this->addEntry('warning', $msg);
	}

	public static function addEntry($level, $msg){
		$date = date('Y:m:d h:i:s');
		$entry = '['.$date.'] ['.$level.'] ['.$msg.']'.PHP_EOL;
		file_put_contents(LOGDIR.'/info.log', $entry, FILE_APPEND);
	}
}
