<?php
class admin extends controller{
	var $layout = 'admin';

	public function index($request){
		$this->template('admin/index');
	}

	public function login($request) {
	}

	public function logout($request) {
		$_SESSION['is_authorized'] = false;
		$this->template('admin/login');
	}

	public function loginPost($request) {
		$authorized_users = Config::read('authorized_users');
	}

	public function showgraph($request, $defaults=array()){
    }

    public function showPieChart($datii){
        $url = 'http://chart.apis.google.com/chart?';
        $url .= 'cht='.$cht;
        $url .= 'chds='.$chds;
        $url .= 'chd='.$chd;
    }
}
