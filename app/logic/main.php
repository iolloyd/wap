<?php
class main extends controller {
	var $layout = 'main';

	public function activatePost($request){

		$ident = new ident();
		$phone    = helpers::cleanPhoneNumber($_REQUEST['phone']);
		$url      = 'http://mega-quiz.nl/wap/play';
		$text_msg = 'Click this link '. $url;
		$sms      = new sms();
		$out = $sms->sendSms($phone, $text_msg, 'EUR0');
		print_r($out);
	}

	public function index($request){
		$this->template('main/index', array(
		));
	}

	public function chargeUser($request){
		$ident = new ident();

		// return sessionId, redirectURL
		// All values are in config file
		$create_response = $ident->createSession(array(
		));

		// return statusCode, statusMessage
		$status_response = $ident->checkStatus(array(
			'sessionId' => $create_response['sessionId']
		));

		// return transactionId, consumerId 
		$final_response  = $ident->finalizeSession(array(
			'sessionId' => $create_response['sessionId']
		));
	}
}
