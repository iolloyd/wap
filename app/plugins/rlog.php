<?php
class rlog extends controller{
	public function registerHit(){

		// Register the epoch time for the caller's REMOTEIP address

		$time_now = time();
		//$last_entry = $this->r->get('last_entry:'.REMOTEIP);
		//$time_since = $time_now - $last_entry; 
		//$this->r->set('time_since_last_entry:'.REMOTEIP, $time_since);
		//$this->r->set('last_entry:'.REMOTEIP, $time_now);
		// Register which method was called
		// for statistical info

		$k = CONTROLLER.':'.METHOD;

        $this->r->zincrby('hit:'.$k, 1, date("ymd"));

	}
}

