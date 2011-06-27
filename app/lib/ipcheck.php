<?php
class ipcheck extends controller {
	public function maxPerTimeLimitExceeded($ip, $secs){
		$last = $this->r->get('time_since_last_entry:'.$ip);
		return ($last < $secs) ? true : false;
	}
}

