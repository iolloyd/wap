<?php
class main extends controller {
	var $layout = 'main';

	public function activatePost($request){
		$phone    = helpers::cleanPhoneNumber($_REQUEST['phone']);
		$ident    = new ident();
		$ident->initIdentitySession();
	}

	public function index($request){
		$this->template('main/index', array(
		));
	}

	/**
	 * This is called when the user clicks the link in the email
	 */
	public function playwin($request){
		$ident = new ident();
		$alias = $ident->getAliasForUser();
		$sub   = new subscription();
		$out   = $sub->createSubscriptionSession(array(
			'username'          => config::read('subscription_username', 'app'),
			'password'          => config::read('subscription_password', 'app'),
			'returnURL'         => 'http://juganar.com/main/ok',
			'contentName'       => 'weekly subscription',
			'eventCount'        => 500,
			'duration'          => 3285,
			'frequencyInterval' => 3,
			'frequencyCount'    => 1,
			'tariffClass'       => 'EUR290',
			'tariffClassId'     => 'EUR290'
		));
		if ($out->responseMessage == 'Success') {
			$this->r->set('session:'.session_id(), $out->sessionId);
			header("Location: ".$create_response->redirectURL);
			exit();
		} 
	}

	/**
	 * This is called after the user is redirected to IPX 
	 * and then IPX redirects them to us with the url 
	 * provided by ourselves, which in this case is /main/ok.
	 */
	public function ok($request){
		$details = config::read('defaults', 'ipx');
		$user    = $details['username2'];
		$pwd     = $details['password2'];
		$status  = $this->checkStatus($user, $pwd);
		if ($status->responseMessage == 'Activated') {
			$out = $this->finalizeSubscriptionSession($user, $pwd);
			if ($out->responseMessage == 'Success') {
				$this->r->saveReverse($out->consumerId, $out->subscriptionId, 'subscription'); 
				$out = $this->chargeUser($user, $pwd);
			}
			return $out;
		} else {
			return stdClass();
		}
	}

	/**
	 * Charge the user
	 */
	private function chargeUser($user, $pwd){
		$sub = new subscription();
		$out = $sub->authorizePayment(array(
			'username'       => $user,
			'password'       => $pwd,
			'consumerId'     => $this->r->get('session:'.session_id()),
			'subscriptionId' => $subscription_id
		));
		if ($out->responseMessage == 'Success') {
			$out = $sub->capturePayment(array(
				'username'  => $user,
				'password'  => $pwd,
				'sessionId' => $out->sessionId
			));
			echo 'charged';
			return $out;
		} else {
			return new stdClass();
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

	public function sendInitialSms($phone) {
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

	private function finalizeSubscriptionSession($user, $pwd, $status){
		if (!($status && $status->responseMessage == 'Activated')) {
			$session_id = $this->r->get('session:'.session_id());
			$ident = new ident();
			$out = $ident->finalizeSession(array(
				'username'  => $user,
				'password'  => $pwd,
				'sessionId' => $session_id
			));
			return $out;
		} else {
			// Return the same type whenever possible, even 
			// in crappy type-poo php
			return new stdClass();
		}
	}
}
