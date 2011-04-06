<?php
class search extends controller {
	function getDaysOfWeek(){
		return array(
			'0' => 'Sunday',
			'1' => 'Tuesday',
			'2' => 'Wednesday',

		);
		//return $this->r->lrange('days_of_week', 0, -1);
	}

	function salutations(){
		return array(
			'mr'  => 'Mr',
			'mrs' => 'Mrs',
			'dr'  => 'Dr',
			'dra' => 'Dra'
		);
		// return $this->r->lrange('salutations', 0, -1);
	}
}
