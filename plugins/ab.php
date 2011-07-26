<?php
/**
 * Plugin for AB testing.
 */ 
class ab extends controller {
	var $styles = array('even', 'random');

    public function run(){
		return true;

        $routes = config::read('routes', 'ab');
		foreach($routes as $route){
			if (CONTROLLER == $route['controller'] 
				&& METHOD  == $route['method']
			){
			};
		}
    }
}

