<?php
class main extends controller {
	var $layout = 'main';

	public function activatePost($request){
		$phone = helpers::cleanPhoneNumber($_REQUEST['phone']);
		$msg   = config::read('free', 'messages');
		$sms   = new sms();
		$out   = $sms->sendSms($phone, $msg, 'EUR0ES');

		$this->r->recordEvent('send_sms', $phone, $out);

		$messages = array( 'no', '');
		$headings = array('Rejected', 'Accepted');

		$this->template('main/activated', array(
			'response' => $out,
			'number'   => $phone,
			'heading'  => $headings[$out->responseMessage == 'Success'],
			'message'  => $messages[$out->responseMessage == 'Success']
		));
	}

	public function index($request){
		$this->template('main/index', array(
		));
	}

	/**
	 * 1. Browse
	 * Here the user has browsed to us or received an sms with our link.
	 */
	public function playwin($request){
		// 2. Request for subscription, 3. Create session
		$subscription    = new subscription();
		$create_response = $subscription->createSubscriptionSession(array(
			'tariffClass' => 'EUR300'
		));

		// 4. Session Information
		$redirect_url = $create_response->redirectURL;
		$session_id   = $create_response->sessionId;

		if ($create_response->responseMessage == 'Success') {
			// 5. Redirect to service
			echo '<a href='.$redirect_url.'>Redirect</a>'; die;
			header("Location: $redirect_url");
			exit();
		} else {
			echo 'Could not redirect!';
			die;
		}
		$final_response  = $subscription->finalizeSession(array(
			'sessionId' => $create_response->sessionId
		));

		print_r($final_response);
	}

	public function ok($request){
		print_r($_REQUEST);
	}

	private function checkstatus($create_response){
		$subscription = new subscription();
		$status_response = $subscription->checkStatus(array(
			'sessionId' => $create_response->sessionId
		));
		print_r($status_response);
		$this->finalizeSession();
	}

	private function finalizeResponse(){
		$response = $subscription->finalizeResponse(array(
		));
		if ($response->responseMessage == 'Success'){
			//cool
		}
	}
}
