<?php
class Mixin {
    protected $mixins;  
	protected $instances = array();

    public function __call($f, $args) {
        foreach ($this->mixins as $mixer) {

			// Lets see if we have a class with the method we want to use
            if (in_array($f, get_class_methods($mixer))){

				$mixer_to_use = $this->instances[$mixer];
				return call_user_func_array(array($mixer_to_use, $f), $args);
            }
        }
    }

    public function __construct() {
        $this->mixins = func_get_args(); 
		foreach ($this->mixins as $mix){
			$instance = new $mix();
			$this->instances[$mix] = new $mix();
		}
    }

}

