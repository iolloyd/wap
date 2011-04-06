<?php
class main extends controller {
	var $layout = 'main';

	public function createSubscription($request){
		$phone = $_POST['phone'];

		sub::createSubscription(array(
			'consumerId'  => $phone,
			'referenceId' => $msgid,
			'tariffClass' => 'EUR300'
		));
	}

	public function chargeUser($request){

		sub::finalizeSubscriptionRequest($overrides);

		// Once we have the ALIAS or MSISDN we can
		// authorize and capture payment
		sub::authorizePaymentRequest($overrides);
		sub::capturePaymentRequest($overrides);
	}

}
