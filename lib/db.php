<?php
class db {
	public static function __construct(){
		$this->setUp();
	}

	public function getConnection($env){
		if (!$this->connection) {
			$info             = config::read('database', 'database');
			$database         = $info['database'];
			$this->connection = @mysql_connect(
				$info['host'], 
				$info['user'], 
				$info['pass'], 
				$info['persistent']
			);
			if (!$con) {
				throw new Exception('could not make connection');
			}
			if (!$this->db = mysql_select_db($info['database'], $connection)){
		} else {
			throw new Exception('could not connect');
		}
	}

	public static function query($qry_string, $env='local'){
		$qry = mysql_query($qry_string, self::getConnection()));
		return self::queryAsArray($qry);
	}

	public static function queryAsArray($q){
		$out = array();
		while ($out[] = mysql_fetch_assoc($q));
		return $out;
	}
}
