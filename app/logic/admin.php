<?php
class admin extends controller{
	var $layout = 'admin';

	public function index($request){
		$this->template('admin/index', array(
		    'views_today'             => $this->viewsToday(),
		    'views_seven_days'        => $this->viewsSeven(),
		    'views_thirty_days'       => $this->viewsThirty(),
		    'conversions_today'       => $this->conversionsToday(),
		    'conversions_seven_days'  => $this->conversionsSeven(),
		    'conversions_thirty_days' => $this->conversionsThirty()
		));
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

	public function viewsToday(){
		return array(
		    0 => 3,
			1 => 4,
			2 => 3,
			3 => 19,
			4 => 29,
			5 => 28,
			6 => 45
		);
	}

	public function viewsSeven(){
		return array(
		);
	}

	public function viewsThirty(){
		return array(
		);
	}

	public function conversionsToday(){
		return array(
		);
	}

	public function conversionsSeven(){
		return array(
		);
	}

	public function conversionsThirty(){
		return array(
		);
	}

	private function prepareTwoLineGoogleChart($axis, $name_of_1, $ys1, $name_of_2, $ys2){
		$out  = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
		$out .= "<script type='text/javascript'>";
		$out .= "var data = new google.visualization.DataTable();";
        $len = count($axis);
		$x = -1;
		while (++$x < $len) {
			$out .= "data.setValue($x, 0, $axis[$x]);";
			$out .= "data.setValue($x, 1, $ys1[$x]); ";
			$out .= "data.setValue($x, 2, $ys2[$x]); ";
		}
		$out .= "
		    var chart = new google.visualization.LineChart(document.getElementById('chart_div');
			chart.draw(data, {width:400, height:240, title:'Stats'});
        ";
	    return $out;	
	}

}
