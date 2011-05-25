<?php
function __autoload($class){
	$toks = explode('\\', $class);
	if (count($toks) > 1) {
		$class = $toks[0];
	}
	$class = str_replace('-', '_', $class);
	include($class.'.php');
}

function secureUri($ctrl, $meth) {
	$list        = config::read('secure_methods', 'auth');
	$controllers = array_keys($list);

	// case 1: controller not in list, therefore no auth required
	if (!in_array($ctrl, $controllers)) {
		return false;
	} 
	
	if (!$methods = $list[$ctrl]) {;
	 $methods = array();
	}

	// case 2: all methods required auth
	if (array() == $methods){
		return true;
	}

	// case 3: only named methods require auth
	if (in_array($meth, $methods)) {
		return true;
	}

	// case 4: controller in list but method is not so no auth required
	return false;
}

function getPath(){
	$uri  = $_SERVER['REQUEST_URI'];
	$path = str_replace($_SERVER['QUERY_STRING'], '', $uri);
	$path = trim($path, '?');
	return $path;
}

function getAppEnv(){
	return (in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) 
		? 'dev' : 'prod';
}

function getControllerAndMethod(){
	$path = trim(getPath(), '/');
	$toks = explode('/', $path, 3);
    $routes = config::read('routes', 'routes');
	if (in_array($path, array_keys($routes))) {
		$ctrl = $routes[$path]['controller'];
		$meth = $routes[$path]['method'];
	} else {
		if (count($toks) == 1) {
			$ctrl = 'main'; 
			$meth = empty($toks[0]) ? 'index' : helpers::unCamelize($toks[0]); 
			$vars = array();
		} else {
			$ctrl = helpers::unCamelize($toks[0]);
			$meth = helpers::unCamelize($toks[1]);
		}
	}
	$vars = count($toks) > 2 ? explode('/', $toks[2]) : array();

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		events::registerEvent('post');
		$vars = $_POST;
		$meth .= 'Post';
	}
	return array($ctrl, $meth, $vars);
}

function showTemplate($c, $m, $vars) {
	$content = call_user_func_array(array(new $c(), $m), array($vars));
	return $content;
}

function isLoggedIn(){
	return $_SESSION['is_authorized'] == true;
}

function logout(){
	$_SESSION['is_authorized'] = false;
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
	events::registerEvents(array(
		'controller' => $controller,
		'method'     => $method,
		'vars'       => $vars
	));

	$vars = array('vars' => $vars);

	if (secureUri($controller, $method)) {
		if (!isLoggedIn()) {
			$controller = 'auth';
			$method     = 'login';
		}
	}

	$method = str_replace('-', '_', $method);

	define("CONTROLLER", $controller);
	define("METHOD",     $method);
	runPlugins();
	showTemplate($controller, $method, $vars);
}
