<?php
class defaults extends controller {
	var $layout = 'default';

	public function index($request){
		$this->template('main/juego', array(
			'last_answered' => 0,
			'questions' => config::read('questions', 'questions')
		));
	}

	public function juego($request){
		require $this->template('main/juego');
	}
}
