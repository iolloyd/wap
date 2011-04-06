<?php
class db {
	public static function setUp($env){
		$info       = config::read('database', 'database');
		$info       = $info[$env];
		$database   = $info['database'];
		$connection = $this->getConnection();
		if ($connection) {
			$db_con = mysql_select_db($info['database'], $connection);
		} else {
			throw new Exception('could not connect');
		}
	}

	public static function getConnection(){
		if (empty(self::connection)) {
			$con = @mysql_connect(
				$info['host'], 
				$info['user'], 
				$info['pass'], 
				$info['persistent']
			);
			if (!$con) {
				throw new Exception('could not make connection');
			}
		}
		return self::connection;
	}

	public static function query($qry_string, $env='local'){
		$qry = mysql_query($qry_string, self::getConnection()));
		return self::queryAsArray($qry);
	}

	public static function queryAsArray($q){
		$out = array();
		while($out[] = mysql_fetch_assoc($q))
			;
		return $out;
	}
}
