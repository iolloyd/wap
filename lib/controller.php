<?php
class controller extends mixin{
	var $layout = 'main';
	public function __construct(){
		parent::__construct();
		$this->r = new dbredis();
	}

	public function __call($name, $args){
		echo 'You called ' . CONTROLLER.'::'.METHOD.  ' ?';
	}

	public function call($uri, $args){
		$uri = ltrim(trim($uri), '/');
		list($c, $m) = explode('/', $uri); 
		call_user_func_array(array(new $c(), $m), array($args)); 
	}

	public function redirect($uri){
		header("Location: $uri");
	}

	protected function loadAB($layout, $tpl_name){
		$routes = config::read('ab', 'routes');
		if (!in_array($tpl_name, array_keys($routes))){
			return array(
				'layout' => $layout,
				'template' => $tpl_name 
			);
		}

		$choices = array();
		foreach ($routes as $url => $options) {
			foreach ($options as $opt) {
				for($x=0;$x < $opt['weight'];$x++){
					$choices[] = $opt;
				}
			}
		}

		$choose   = count($choices) - 1;
		$choice   = mt_rand(0, $choose);
		$choice   = $choices[$choice];
		$layout   = $choice['layout'];
		$template = $choice['template'];
		return array(
			'layout'   => $layout,
			'template' => $template
		);
	}

	protected function template($tpl_name, $vars=array()){
		$d = debug_backtrace();
		$o = new $d[1]['object'];
		foreach ($vars as $k => $v) {
			$$k = $v;
		}
		$layout   = $o->layout;
		$ab       = $this->loadAB($layout, $tpl_name);
		$layout   = $ab['layout'];
		$tpl_name = $ab['template'];
		$wrapper  = TEMPLATEDIR.'/'.$layout.'.php';
		$content  = TEMPLATEDIR.'/'.$tpl_name.'.php';
		ob_start();
		require $content;
		$content = ob_get_contents();
		ob_get_clean();

		if (file_exists($wrapper)) {
			require($wrapper);
		}
	}
}
