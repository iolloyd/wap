<?php
class status extends controller{
	var $layout = 'admin';
	public function checkPost($request) {
		$phone           = $_POST['number'];
		$details         = $this->r->hgetall('evt:create_sub:'.$phone);
		$subscription_id = $details['subscriptionId'];
		$out = $this->sms->getSubscriptionStatus(array(
			'consumerId'     => $phone,
			'subscriptionId' => $subscription_id));
		$this->template('status/subscriber', array(
			'phone'   => $phone,
			'details' => $out	
		));
	}

	public function authpaymentPost($request) {
		$phone = $_POST['number'];
		$id    = $this->r->hget('phone:'.$phone, 'create_response:subscriptionId');
	    $out = $this->sms->authorizePayment(array(
			'consumerId' => $phone,
			'subscriptionId' => $id
		));
		echo '<pre>';
		print_r($out);
	}

	public function capturepaymentPost($request) {
		$phone = helpers::cleanPhoneNumber($_POST['number'], '31');
		$id    = $this->r->hget('phone:'.$phone.':create_response', 'subscriptionId');
	    $out = $this->sms->authorizePayment(array(
			'consumerId'     => $phone,
			'subscriptionId' => $id
		));
		echo '<pre>';
		print_r($out);
	}

	public function terminatesubscriptionPost($request) {
		$phone = helpers::cleanPhoneNumber($_POST['number'], '31');
		$id    = $this->r->hgetall('evt:create_sub:'.$phone);
		echo '<br>'.$phone.'<br>';
		print_r($id);
	    $out = $this->sms->terminateSubscription(array(
			'consumerId'     => $phone,
			'subscriptionId' => $id
		));
		$this->r->lpush('termination:'.$phone, time());

		echo $phone . '<br/>';
		echo $id    . '<br/>';
		echo $out->responseMessage;

		$this->template('activate/index');
	}
}
