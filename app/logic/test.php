<?php 
class test extends controller {
	var $layout = 'test';

	public function bed($request) {
		$foo = 'FOOVALl';
		$this->template('test/bed', array( 'foo' => $foo));
	}
	
}
