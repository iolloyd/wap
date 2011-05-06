<?php
class main extends controller {
	var $layout = 'main';

	public function activatePost($request){
		$phone    = helpers::cleanPhoneNumber($_REQUEST['phone']);
		$sms      = new sms();
		$response = $sms->sendSms($phone, config::read('free', 'messages'));
	}

	public function index($request){
		$this->template('main/index', array(
		));
	}

	/**
	 * This is called when the user clicks the link in the email
	 */
	public function playwin($request){
		/*
		$ident = new ident();
		$alias = $ident->getAliasForUser();
		*/
		$out   = $this->chargeUser();
	}

	/**
	 * This is called after the user is redirected to IPX for ident 
	 * and then IPX redirects them to us with the url 
	 * provided by ourselves, which in this case is /main/ok.
	 */
	public function endident($request){
		$details = config::read('defaults', 'ipx');
		$user    = $details['username2'];
		$pwd     = $details['password2'];
		$status  = $this->checkStatus($user, $pwd);
		if ($status->statusMessage == 'Authenticated') {
			$out = $this->finalizeSession($user, $pwd);
			if ($out->responseMessage == 'Success') {
				$this->setConsumerId($out->consumerId);
				echo 'going to attempt create sub and charge user';
				$this->chargeUser();
			}
		} 

	}

	private function chargeUser($tariff_class = 'EUR300ES') {
		$this->chargeuser1();
	}

	private function chargeuser1(){
		$sub = new subscription();
		$out = $this->createSubscriptionSession();
		if ($out->responseMessage == 'Success'){
			header('Location: ' . $out->redirectURL);
			exit();
		}
	}

	public function chargeuser2(){
		$out = $this->finalizeSubscriptionSession();
		echo 'auth finalize response:';
		print_r($out); die;
		$out = $this->authorizePayment($out);
		$out = $this->capturePayment($out);
	}

	private function capturePayment($prev_step){
		if (@$prev_step && $prev_step->responseMessage == 'Success') {
			$sub = new subscription();
			$out = $sub->capturePayment(array(
				'username'  => $this->getSubscriptionUser(),
				'password'  => $this->getSubscriptionPwd(),
				'sessionId' => $this->getSessionId()
			));
			return $out;
		}
	}

	/**
	 * This request is initiated by the user in their terminal, but the actual 
	 * request directly comes from the IPX servers as a GET request.
	 */
	public function terminateSubscription($request){
		$sub      = new subscription();
		$con_id   = helpers::cleanPhoneNumber($_GET['consumerId']);
		$sub_id   = $_GET['subscriptionId'];
		$response = $sub->terminateSubscription(array(
			'consumerId'     => $con_id,
			'subscriptionId' => $sub_id
		));
	}


	/******************************
	 * Private utility methods
	 *****************************/
	private function getConsumerId(){
		return $this->r->get('consumer_id:'.session_id());
	}

	private function getSessionId(){
		return $this->r->get('session:'.session_id());
	}

	private function setConsumerId($consumer_id){
		$this->r->set('consumer_id:'.session_id(), $consumer_id);
	}

	private function setSessionId($session_id) {
		$this->r->set('session:'.session_id(), $session_id);
	}

	private function sendInitialSms($phone) {
		$msg      = config::read('free', 'messages');
		$sms      = new sms();
		$out      = $sms->sendSms($phone, $msg, 'EUR0ES');
		$choice   = $out->responseMessage == 'Success';

		$this->r->recordEvent('send_sms', $phone, $out);

		$headings = array('Rejected', 'Accepted');
		$messages = array('no'      , ''        );
		$this->template('main/activated', array(
			'response' => $out,
			'number'   => $phone,
			'heading'  => $headings[$choice],
			'message'  => $messages[$choice]
		));
	}

	/*******************************
	 * Used by identification API
	 ******************************/
	private function checkStatus($user, $pwd){
		$session_id = $this->r->get('session:'.session_id());
		$ident      = new ident();
		$out        = $ident->checkStatus(array(
			'username'  => $user,
			'password'  => $pwd,
			'sessionId' => $session_id
		));
		return $out;
	}

	private function finalizeSession($user, $pwd){
		$session_id = $this->r->get('session:'.session_id());
		$ident = new ident();
		$out = $ident->finalizeSession(array(
			'username'  => $user,
			'password'  => $pwd,
			'sessionId' => $session_id
		));
		return $out;
	}

	/*******************************
	 * Used by subscription API
	 ******************************/
	private function createSubscriptionSession($tariff_class='EUR300ES'){
		$sub = new subscription();
		$out = $sub->createSubscriptionSession(array(
			'username'    => $this->getSubscriptionUser(),
			'password'    => $this->getSubscriptionPwd(),
			'consumerId'  => $this->getConsumerId(),
			'sessionId'   => $this->getSessionId(),
			'returnURL'   => $this->getSubscriptionSessionUrl(),
			'tariffClass' => $tariff_class
		));
		$this->setSessionId($out->sessionId);
		return $out;
	}

	private function finalizeSubscriptionSession(){ 
		$sub = new subscription();
		$out = $sub->finalizeSubscriptionSession(array(
			'sessionId' => $this->getSessionId(),
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd()
		));
		return $out;
	}

	private function authorizePayment($prev_step){
		if ($prev_step->responseMessage == 'Success'){
			return $sub->authorizePayment(array(
				'user'           => $this->getSubscriptionUser(),
				'password'       => $this->getSubscriptionPwd(),
				'consumerId'     => $this->getConsumerId(),
				'subscriptionId' => $this->getSubscriptionId(),
				'sessionId'      => $this->getSessionId(),
				'tariffClass'    => $tariff_class
			));
		}
	}

	private function getSubscriptionPwd(){
		$details = config::read('defaults', 'ipx');
		return $details['password2'];
	}

	private function getSubscriptionUser(){
		$details = config::read('defaults', 'ipx');
		return $details['username2'];
	}

	private function getSubscriptionSessionUrl(){
		$details = config::read('defaults', 'ipx');
		return $details['subscription_url'];
	}

}
