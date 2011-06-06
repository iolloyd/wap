<?php
require_once('dbredis.php');

class redis_item {
    protected $id;
    protected $data;

    public function __construct(){
        $this->data = array();
        $id = $this->getNextId();
        $this->id = __CLASS__ . ':' . $id;
    }

    public function __call($method, $args){
        if (strpos('my', $method) === true){
            $key = substr($method, 2);
            return $this->r->smembers($key);
    }

    public function __set($key, $value){
        $this->data[$key] = $value;
    }

    public function __get($key){
        return $this->data[$key];
    }

    public function setOwner($owner){
        $this->r->sadd($owner, $this->id);
    }

    public function has($member_name, $value){
        $this->r->sadd($member_name, $value);
    }

    public function save(){
        $this->r->hmset($this->key, $this->data);
    }

    protected function getNextId(){
        $incr_key = 'uniq_id:' . __CLASS__ ; 
        return $this->r->incr($incr_key);
    }

}

