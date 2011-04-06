<?php
//require_once('lib/predis/lib/Predis.php');

/**
 * Provides a higher level interface to redis 
 * using the Predis client library.
 * @author Lloyd Moore <lloyd@lloydsays.com>
 * @version 1.3
 * package movic 
 */
class dbredis {
	var $r;

	public function __call($method, $args){
		return call_user_func_array(
			array($this->r, $method),
			$args
		);
	}

	public function __construct(){
		$conn = config::read('host', 'redis');
		$this->r = new Predis\Client($conn);
	}

	/**
	 * @param string $collection
	 * @param array  $ids
	 * @return array $found
	 */
	public function getAll($collection, $ids=array()) {
		$found = array();
		if ($ids == array()) {
			$ids = $this->r->smembers('list:'.$collection);
		}
		foreach ($ids as $id) {
			$entry = $this->getById($collection, $id);
			$entry['id'] = $id;
			$found[] = $entry;
		}

		return $found;
	}


	public function getByPattern($collection, $pattern){
		$collection = $this->r->smembers($collection);
	}

	public function getSorted($collection, $gets, $info) {
		$gs = array();
		foreach ($gets as $g) {
			$gs[] = $collection.':*->'.$g;
		}
		$atts = array_merge($gets, $info);
		return $this->r->sort('list:'.$collection, array(
			'get' => $gs
			//'by' => $by
		));
	}

	public function getByVals($collection, $values){
	}

	/**
	 * @param string $collection
	 * @param  int    $id
	 */
	public function getById($collection, $id){
		return $this->r->hgetall($collection.':'.$id);
	}

	/**
	 * Given a key returns a representation of the value
	 * irrespective of the key type.
	 * @param string $key
	 * @param bool $as_string
	 * @return array $out
	 */
	public function getAny($key, $as_string=false){
		$type  = $this->r->type($key);
		$r     = $this->r;
		$calls = array(
			'string' => array('get'     , array($key)), 
			'hash'   => array('hgetall' , array($key)), 
			'list'   => array('lrange'  , array($key  , 0, -1)), 
			'set'    => array('smembers', array($key)),
			'zset'   => array('zrange'  , array($key  , 0, -1))
		);
		try {
			$call = $calls[$type][0];
			$args = $calls[$type][1];
			$out = call_user_func_array(array($this, $call), $args);
			if ($as_string == true && is_array($out)) { 
				$out = implode(',', $out);
			}
			return $out;
		} catch (exception $e) {
			echo $e->getMessage();
			exit;
		}
	}

	/**
	 * wrapper around redis hset command
	 * @param string $key
	 * @param string $field
	 * @param string $value
     * @return string 
	 */
	public function hset($key, $field, $value){
		return $this->r->hset($key, $field, $value);
	}

	/**
	 * @param string $c
	 * @param string $key
	 * @return string 
	 */
	public function getHashValues($c, $key) {
		$c = preg_replace('/^list:', '', $c);
		$c = 'list:'.$c;
		return $this->r->sort($c, array('get' => "{$collection}:*->{$key}"));
	}

	/**
	 * Records event data for a given key in redis
	 * TODO return boolean OR throw exception?
	 * @param string $event
	 * @param string $key
	 * @param array $data
	 * @return boolean true 
	 */
	public function recordEvent($event, $key, $data){
		$id                = $this->r->incr('idx:'.$event);
		$time              = date('YmdHis');
		$data              = helpers::convertStdToArray($data);
		$data['key']       = $key;
		$data['timestamp'] = $time;

		$this->r->incr('count:'.date('YmdH').':'.$event);
		$this->r->incr('count:'.date('Ymd') .':'.$event);

		$this->r->zadd('zset:'.$event,          $time, $id);
		$this->r->zadd('zset:'.$event.':'.$key, $time, $id);

		$this->r->hmset("evt:$event:$id",  $data);
		$this->r->hmset("evt:$event:$key", $data);

		$this->r->lpush("trk:$event:$key", $id);
	}

	public function eventsByDateRange($evt, $start, $end) {
		$start = helpers::convertDateToInt($start);
		$end   = helpers::convertDateToInt($end);
		$vals = $this->r->zrange('zset:'.$evt, 0, -1, array('withscores' => true));

	}

	public function pullAllEvents($evt, $start=0, $end=-1) {
		$keys = $this->r->zrevrange('zset:'.$evt, 0, -1);
		$evts = array();

		foreach ($keys as $k) {
			$evts[] = $this->r->hgetall('evt:'.$evt.':'.$k);
		}
		return $evts;
	}

	/**
	 * Retrieves all events for a given key
	 * @param string $event
     * @param string $key
	 */
	public function pullEvents($event, $key) {
		$evt  = $event . ':' . $key;
		$ids  = $this->r->lrange($evt, 0, -1);
		$evts = array();
		foreach ($ids as $id) {
			$evts[] = $this->r->hgetall($evt.':'.$id);
		}
		return $evts;
	}

	/**
	 * @deprecated
	 * @param string $collection
	 * @param string $key
	 * @param array $entry
	 */
	public function save($collection, $key, $entry){
		foreach ($entry as $k => $v) {
			if (is_array($v)) {
				$entry[$k] = serialize($v); } }

		$this->r->lpush('list:'.$collection, $key);
		$this->r->hmset($collection.':'.$key, $entry);
	}

	/**
	 * @deprecated
	 * @param string $collection
	 * @param string $key
	 * @param array $data
	 * @return void
	 */
	public function saveHash($collection, $key, $data) {
		$this->r->sadd('list:'.$collection, $key);
		$this->r->hmset($collection.':'.$key, $data);
	}

	/**
	 * @param string $collection
	 * @param string $key
	 * @param array $entry
	 */
	public function saveIncr($collection, $entry, $add_id_to_data=true){
		$id = $this->r->incr('nextid:'.$collection);
		$this->r->lpush('list:'.$collection, $id);

		if ($add_id_to_data) {
			$entry['id'] = $id;
		}
		$this->r->hmset($collection.':'.$id, $entry);
	}

	/**
	 * @param string $key
	 * @param string $entry
	 * @param array $vals
	 */
	protected function saveRev($key, $entry, $vals=array()){
		foreach ($entry as $k => $v) {
			foreach ($vals as $val) {
				$this->r->set($key.':'.$v, $val);
			}
		}
	}

}
