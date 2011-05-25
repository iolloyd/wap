<?php
class events {
	public static function registerEvent($key, $value=false) {
	}
	public static function registerEvents(array $events){
		foreach ($events as $k => $v) {
			self::registerEvent($k, $v);
		}
	}
}

