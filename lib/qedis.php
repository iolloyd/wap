<?php

require_once "lloydredis.php";

class qedis {

	public function __construct($connection) {
		$this->r = $connection;
	}

	public function add($group, $data) {
		$id     = $this->getNextId($group);
		$member = "$group:$id";
		$this->r->hmset($member, $data);
		foreach ($data as $k => $v) {
			$score = $this->wordInt($v);
			$this->r->zadd($k, $score, $member);
		}
	}

	public function find(array $predicates) {
		$TMPKEY = date('ymdhis');
		$ranges = array();
		foreach ($predicates as $k => $v) {
			if (!is_array($v)) $v = array($v, $v);
			list($a, $b) = array($this->wordInt($v[0], $this->wordInt($v[1])));
			$ranges[]    = $this->r->zrangebyscore($k, $a, $b);
		}
		$keys = array();
		foreach ($ranges as $k => $v){
			$keys[] = "${TMPKEY}${k}";
			array_map(function($x){$this->r->sadd("${TMPKEY}${k}", $x);}, $v);
		}
		$reply = call_user_func_array(array($this->r, 'sinter'), $keys);
		array_map(function($x){$this->r->del($x);}, $keys);
		return $reply;
	}


	private function getNextId($group){
		return $this->r->incr('nextid:'.$group);
	}

	/**
	 * The highest chr score of a printable 
	 * character is 125 so we take off 
	 * 26 to make each character only 2 ints long
	 */
	private function wordInt($word){
		if ($x = $this->r->hget('wordcache', $word)) {
			return $x;
		} else {
			$ints = array_map(function($x){return ord($x)-26;}, str_split($word));
			$num  = implode('', $ints);
			$this->r->hset('wordcache', $word, $num); 
			$this->r->hset('wordcache', $num, $word); 
			return $this->r->hget('wordcache', $word);
		}
	}

	private function wordOfInt($int){
		return $this->r->hget('wordcache', $int);
	}
}
