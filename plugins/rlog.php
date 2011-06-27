<?php
class rlog extends controller{
    public function registerHit(){

        // Register the epoch time for the caller's REMOTEIP address

        $time_now = time();
        $last_entry = $this->r->get('last_entry:'.REMOTEIP);
        $time_since = $time_now - $last_entry; 
        $this->r->hset('times:entries', 'since', $time_since);
        $this->r->hset('times:entries', 'time', $time_since);

        // Register which method was called
        // for statistical info


        $k = CONTROLLER.':'.METHOD;
        $this->r->zadd('calls', 0, $k);
        $this->r->zincrby('hit:'.$k, 1, date('Ymd'));

    }
}

