<?php
class ipcheck extends controller {
	public function maxPerTimeLimitExceeded($ip, $secs){
		$last = $this->r->get('time_since_last_entry:'.$ip);
		$this->r->publish('evt.last_entry.'.$ip, $last);
		return ($last < $secs) ? true : false;
	}
}

