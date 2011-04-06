<?php 
class stop extends controller {

	public function index($request) {

		$phone = $_GET['consumerId'];
		$out = $this->sms->terminateSubscription(array(
			'subscriptionId' => $_GET['subscriptionId'],
			'consumerId'     => $_GET['consumerId']
		));

		$this->sms->sendSms($phone, config::read('stop', 'messages'));

		// Return ack response = true
		$this->template('activate/index');
	}

}
