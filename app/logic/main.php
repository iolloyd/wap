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

	public function playwin($request){
		$ident           = new ident();
		$alias           = $ident->getAliasForUser();
		$subscription    = new subscription();
		$create_response = $subscription->createSubscriptionSession(array(
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
		if ($create_response->responseMessage == 'Success') {
			$this->r->set('session:'.session_id(), $create_response->sessionId);
			header("Location: ".$create_response->redirectURL);
			exit();
		} 
	}

	/**
	 * Finalize subscription
	 */
	public function ok($request){
		$this->checkStatus();
		$this->finalizeSession();
		$this->createSubscription();
	}

	public function terminateSubscription($request){
		$subscription    = new subscription();
		$phone           = helpers::cleanPhoneNumber($_GET['consumerId');
		$subscription_id = $_GET['subscription_id'];
		$response        = $subscription->terminateSubscription(array(
			'consumerId'     => $phone,
			'subscriptionId' => $subscription_id
		));
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

	private function checkStatus(){
		$details    = config::read('defaults', 'ipx');
		$user       = $details['username2'];
		$pwd        = $details['password2'];
		$session_id = $this->r->get('session:'.session_id());
		$ident = new ident();
		$out = $ident->checkStatus(array(
			'username'  => $user,
			'password'  => $pwd,
			'sessionId' => $session_id
		));

		if ($out->statusMessage ==  'Authenticated') {
			$out = $ident->finalizeSession(array(
				'username'  => $user,
				'password'  => $pwd,
				'sessionId' => $session_id
			));
		}

		if ($out->responseMessage == 'Success'){
			$this->r->recordEvent('finalize_subscription', $session_id, $out);
		}
	}

}
