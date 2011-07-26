<?php
class admin extends controller{
    var $layout = 'admin';

    public function index($request){
        $this->template('admin/index', array(
            'sales_yesterday' => $this->salesYesterday(),
            'sales_today'     => $this->salesToday()
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

    public function salesToday(){
        $today = date('Ymd');
        $data = $this->lift($this->r->zrange('signup_by_hour:'.$today, 0, -1, 'withscores'));
        ksort($data, SORT_NUMERIC);
        return $data;
    }

    public function salesYesterday(){
        $today = date('Ymd', time() - 60 * 60 * 24);
        $data = $this->lift($this->r->zrange('signup_by_hour:'.$today, 0, -1, 'withscores'));
        ksort($data, SORT_NUMERIC);
        return $data;
    }

    private function smsForDay($day){
        $options = array(
            'today'     => date('Ymd'),
            'yesterday' => date('Ymd', time() - (60 * 60 * 24))
        );
        return $this->r->zscore('send_sms', $options[$day]);
    }

    public function sms(){
        $sms_responses = $this->r->getAll('sms');
        $this->template('admin/sms', array('sms_responses' => $sms_responses));
    }

    public function today(){
        $data = $this->getDayResults(date('Ymd'));
        $this->template('admin/sales', $data);
    }

    public function yesterday(){
        $data = $this->getDayResults(date('Ymd', time() - (60 * 60 * 24)), 'yesterday');
        $this->template('admin/sales', $data);
    }

    private function getDayResults($day, $which='today'){
		$operators = $this->r->zrange('operator:'.$day, 0, -1, 'withscores');
        $platforms = $this->r->zrange('platform:'.$day, 0, -1, 'withscores');
        $browsers  = $this->r->zrange('browser:'.$day, 0, -1, 'withscores');
        return array(
            'day'                       => $day,
            'visits'                    => $this->r->zscore('hit:main:index', $day), 
            'operators'                 => $operators,
            'vodafone'                  => $this->r->zscore('sent_to_vodafone', $day),
            'browsers'                  => $browsers,
            'platforms'                 => $this->r->zrange('platform:'.$day, 0, -1, 'withscores'),
            'sms'                       => $this->smsForDay($which),
            'sales'                     => $this->r->zscore('signup_by_day:'.date('Ym')     , $day) , 
            'fails_ident_create_session'=> $this->r->zscore('fail:ident_create_session', $day),
            'fails_ident_check_status'  => $this->r->zscore('fail:ident_check_status', $day),
            'fails_subscription_auth'   => $this->r->zscore('fail:subscription_authorize_payment', $day),
            'fails_subscription_final'  => $this->r->zscore('fail:subscription_finalize_session', $day),
            'fails_authorize_payment'   => $this->r->zscore('fail:authorize_payment'        , $day) , 
            'fails_oneshot_checkstatus' => $this->r->zscore('fail:oneshot_check_status'     , $day) , 
            'fails_oneshot_finalize'    => $this->r->zscore('fail:oneshot_finalize_session' , $day) ,
            'browser_fails'             => $this->r->zscore('fail:browser'.date('Ymd'), $day),
            'platform_fails'            => $this->r->zscore('fail:platform'.date('Ymd'), $day),
			'chart_operators'           => $this->getJsonVersion($operators),
			'chart_browsers'            => $this->getJsonVersion($browsers),
			'chart_platforms'           => $this->getJsonVersion($platforms),
        );
    }

	private function getJsonVersion($data){
		$out = array();
		foreach ($data as $k => $v) {
			if ($v[0] == "") $v[0] = 'UNKNOWN';
			$out[] = $v;
		}
		return json_encode($out, JSON_NUMERIC_CHECK); 

	}

    private function lift($array_of_arrays){
        $out = array();
        foreach ($array_of_arrays as $i => $arr) {
            $out[$arr[0]] = $arr[1];
        }
        return $out;
    }

    private function getChartHeader(){
        $hdr = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
        return $hdr;
    }
    private function chartTwoLine($axis, $name_of_1, $ys1, $name_of_2, $ys2){
        $out  = $this->getChartHeader();
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
