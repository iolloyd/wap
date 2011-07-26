<?php
class hits extends controller {
	var $layout = 'admin';

	public function __construct(){
		parent::__construct();
	}
	
	public function getPages($req){
		$tree    = $this->buildCallTree();
		$results = array();

		foreach ($tree as $ctrl => $methods) {
			foreach ($methods as $m => $_) {
				$key = $ctrl.':'.$m;
				$out = $this->r->zrange('hit:'.$key, 0, -1, 'withscores');
				$results[] = array($key, $this->lift($out));
			}
        }
	   $this->template('hits/results', array(
		   'results' => $results
	   ));
   }

	private function buildCallTree(){
		$tree = array();
		$pages = $this->r->keys("hit:*");
		foreach ($pages as $page) {
			$tokens = explode(':', $page);
			if (empty($tree[$tokens[1]])) {
				$tree[$tokens[1]] = array();
			}
			$tree[$tokens[1]][$tokens[2]] = 1;
		}
		return $tree;
	}

    private function lift($array_of_arrays){
        $out = array();
        foreach ($array_of_arrays as $i => $arr) {
            $out[$arr[0]] = $arr[1];
        }
        return $out;
    }

}

