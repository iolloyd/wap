<?php
require_once(dirname(dirname(__FILE__)).'/lib/Spyc.php');

class config {

	public function getCfgDir(){
		return dirname(dirname(__FILE__)).'/config';
	}

	public static function read($section, $file){
		try {
			$env     = getAppEnv();
			$config  = Spyc::YAMLLoad(CONFDIR . '/' . $file.'.yml');
			$all = @$config['all'][$section] ?: array();
			$env = @$config[$env][$section]  ?: array();
			if (count($env)) {
				foreach ($env as $k => $v) {
					$all[$k] = $v;
				}
			}
			return $all;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public static function readForm($section) {
		$config  = Spyc::YAMLLoad(FORMSDIR . '/' . $section.'.yml');
		$section = $config[$section];
		try {
			return $section;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}

