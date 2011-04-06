<?php
class admin extends controller{
	var $layout = 'admin';

	public function authpayment($request) {
		$this->template('admin/authpayment');
	}

	public function capturePayment($request){
		$this->template('admin/capturepayment');
	}

	public function capturePaymentPost($request){
		$this->template('admin/capturepayment_results');
	}

	public function checkStatus($request){
		$this->template('admin/check_status');
	}

	public function debug($request) {
		$this->template('admin/debug');
	}

	public function debugPost($request) {
		$pattern = $_POST['debug_key'];
		$results = array();
		foreach ($this->r->keys($pattern) as $key) {
			$results[] = array(
				'type' => $this->r->type($key),
				'key'  => $key, 
				'val'  => $this->r->getAny($key, true)
			);
		}
		$this->template('admin/debug_results', array(
			'pattern' => $pattern,
			'results' => $results
		));
	}

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


	public function phones($request){
		$results = array(
			'send_sms'   => $this->r->pullAllEvents('send_sms'),
			'subscribed' => $this->r->pullAllEvents('create_sub'),
			'confirm'    => $this->r->pullAllEvents('send_confirm_sms'),
			'billing'    => $this->r->pullAllEvents('send_billing_sms')
		);

		$this->template('admin/show_phones',array(
			'results' => $results
		));
	}

	public function removePhone($request){
		$phone = $request['vars'][0];
		$this->r->smove('list:phone', 'backup:list:phone', $phone);
		$this->call('/admin/phones', $request);
	}

	public function status($request) {
		$this->template('admin/checkstatus');
	}

	public function stats($request){
		$dates = helpers::getDateRange('2011:02:01', '2011:02:28');
		array_unshift($dates, '');
		$pages = array(
			'activate:index',
			'main:index',
			'main:activate',
			'main:activatePost',
			'main:handys',
			'main:login',
			'main:loginPost',
			'main:juegoPost',
			'main:impressum',
			'main:olvide_pass',
			'defaults:juego',
			'defaults:index'
		);
		$keys = array();
		foreach ($pages as $page) {
			$keys[$page] = array();
			foreach ($dates as $date) {
				$keys[$page][] = $this->r->get($date.':'.$page);
			}
		}
		$this->template('admin/stats', array(
			'month'   => 'feb',
			'range'   => $dates,
			'results' => $keys
		));
	}

	public function statsPost($request){
		$from = array_reverse(explode(':', str_replace('/',':',$_POST['date_from'])));
		$to   = str_replace('/',':',$_POST['date_to']);

		$this->template('admin/show_stats');
	}

	public function terminatepayment($request) {
		$this->template('admin/terminatepayment');
	}

	public function search($request) {
		$this->template('admin/search');
	}

	public function searchPost($request){
		$gets = array();
		$collection = $request['vars']['collection'];
		foreach ($request['vars'] as $k => $v) {
			$k = $collection.'*->'.$k;
			$gets[$k] = $v;
		}
		$results = $this->r->sort('list:'.$collection, array(
			'get' => $gets
		));
		$this->template('admin/search_results', array(
			'results'    => $results,
			'collection' => $collection
		));
	}

	public function sendFreeBilling($request) {
		$password = helpers::generatePassword(); 
		$phone = $request['vars'][0];
		$this->sms->sendBillingSms($phone, 'EUR0', $password);
		$this->r->hset('phone:'.$phone, 'password', $password);
		$this->call('/admin/phones', $request);
	}

	public function translations($request){
		$data = $this->r->getAll('translation');
		$this->template('admin/translations', array(
			'results' => $data
		));
	}

	public function addtranslationPost($request){
		unset($_POST['submit']);
		$key = $_POST['label'];
		$this->r->saveHash('translation', $key, $_POST);
		$this->call('/admin/translations', $request);
	}
}
