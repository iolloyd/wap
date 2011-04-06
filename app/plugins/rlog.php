<?php
class rlog extends controller{
	public function registerHit(){

		// Register the epoch time for the caller's IP address

		$time_now = time();
		$last_entry = $this->r->get('last_entry:'.IP);
		$time_since = $time_now - $last_entry; 
		$this->r->set('time_since_last_entry:'.IP, $time_since);
		$this->r->set('last_entry:'.IP, $time_now);

		// Register which method was called
		// for statistical info

		$k = CONTROLLER.':'.METHOD;

		$this->r->incr(date('y:m:d:h:i').':'.$k);
		$this->r->incr(date('y:m:d:h').':'.$k);
		$this->r->incr(date('y:m:d').':'.$k);
		$this->r->incr(date('y:m').':'.$k);

	}
}

