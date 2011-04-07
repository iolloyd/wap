<?php
function __autoload($class){
	$toks = explode('\\', $class);
	if (count($toks) > 1) {
		$class = $toks[0];
	}
	$class = str_replace('-', '_', $class);
	include($class.'.php');
}

function authorized($ctrl, $meth) {
	// If we are already authorized just return true;

	if (!empty($_SESSION['is_authorized'])
		&& $_SESSION['is_authorized'] == true)
	{
		return true;
	}

	// Let's see if this request is supposed to
	// be secure
	$secured            = config::read('secure_methods', 'auth');
	$secure_controllers = array_keys($secured);
	if (!in_array($ctrl, $secure_controllers)) {
		return true;
	}

	// OK - This is a secured controller, lets see if the method
	// is secure. If the method list is empty, it means that all methods
	// are secure and require auth.
	$secure_methods = $secured[$ctrl];
	if (!$secure_methods || in_array($meth, $secure_methods)) {
		$auth_required = true;
	} 

	$_SESSION['is_authorized'] = false;
	return false;
}

function getPath(){
	$uri  = $_SERVER['REQUEST_URI'];
	$path = str_replace($_SERVER['QUERY_STRING'], '', $uri);
	$path = trim($path, '?');
	return $path;
}

function getAppEnv(){
	if (in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
		return 'dev';
	} else {
		return 'prod';
	}
}

function getControllerAndMethod(){
	$path = trim(getPath(), '/');
	$toks = explode('/', $path, 3);
	$ctrl = (count($toks) > 0 && $toks[0] != '') 
		? $toks[0] : 'main';

	$meth = count($toks) > 1 ? helpers::unCamelize($toks[1]) : 'index';
	$vars = count($toks) > 2 ? explode('/', $toks[2]) : array();

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$vars = $_POST;
		$meth .= 'Post';
	}
	return array($ctrl, $meth, $vars);
}

function logHit($controller, $method){
	$r = new dbredis();
	$c_m = $controller.':'.$method;
	$r->incr($c_m.':'.date('y:m:d:h:i'));
	$r->incr($c_m.':'.date('y:m:d:h'));
	$r->incr($c_m.':'.date('y:m:d'));
	$r->incr($c_m.':'.date('y:m'));
	$r->incr($c_m.':'.date('y'));
	$r->incr($c_m);
}

function showTemplate($c, $m, $vars) {
	$content = call_user_func_array( array(new $c(), $m), array($vars));
	return $content;
}

function runPlugins(){
	$plugins = config::read('plugins', 'plugins');
	foreach ($plugins as $plugin) {
		$class = $plugin['class'];
		$method = $plugin['method'];
		call_user_func_array(
			array(new $class, $method),
			array()
		);
	}
}

function run(){
	list($controller, $method, $vars) = getControllerAndMethod();
	$vars = array('vars' => $vars);

	if (!authorized($controller, $method)) {
		$controller = 'auth';
		$method     = 'login';
	}

	$method = str_replace('-', '_', $method);

	define("CONTROLLER", $controller);
	define("METHOD",     $method);
	runPlugins();
	showTemplate($controller, $method, $vars);
}
