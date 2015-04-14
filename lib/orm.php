<?php
class Orm {
	public $database;
	public $table;

	public function __construct($table){
		$this->table = $table;
		$this->init();
	}

	private function init(){
		$query = 'select distinct table_name from information_schema where table_schema=';
	}

	public function getAll(){
		$query = 'select * from ' . $this->table;
		return $this->runQuery($query);
	}


	public function getBy($key, $value){
		$value = (is_string($value))
			? "'$value'"
			: $value;
		$query = 'select * from ' . $this->table . ' where ' . $key . ' = ' . $value;
		return $this->runQuery($query);
	}


	public function getById($id) {
		return $this->getBy('id', $id);
	}

	public function getChildren($table){
		//find tables that have $table_id in their columns
		$query = 'select distinct table_name from information_schema where table_schema='.$table;
	}

	public function remove(){
	}

	public function save(){
		$query = $this->id == 0 
			? $this->buildInsertQuery()
			: $this->buildUpdateQuery();
		return $this->runQuery($query);
	}

	private function buildInsertQuery(){
		$keys  = implode("','", array_keys($this->row_values));
		$vals  = implode("','", array_values($this->row_values));
		$query = 'insert into '.$this->table.'('.$keys.') values ('.$values.')';
		return $query;
	}

	private function buildUpdateQuery(){
		$query = 'update ' . $this->table . ' ';
		$vals = array();
		foreach ($this->row_values as $k => $v) {
			$vals[]= 'set ' . $k . '=' . $v;
		}
		$query .= implode(',', $vals);
		$query .= ' where id=' . $this->id;
		return $query;
	}

	private function runQuery($query, $out=array()){
		$q = @mysql_query($query);
		if (mysql_num_rows($q)) {
			while($out[] = mysql_fetch_assoc($q));
			return $out;
		} else {
			throw new Exception("Nada!");
		}
	}

}

