<?php
class main extends controller {
	var $layout = 'main';

	public function index($request){
		$qs = Config::read('questions', 'questions');
		$nq = 2;
		$this->template('main/index', array(
			'questions' => array(
				'box'              => array_slice($qs, 0        , $nq),
				'box2'             => array_slice($qs, $nq      , $nq),
				'finallyquestions' => array_slice($qs, $nq + $nq, $nq)
			)
		));
	}

	public function indexPost($request){
		$phone    = helpers::cleanPhoneNumber($_REQUEST['telefono']);
		$sms      = new sms();
		$response = $sms->sendSms($phone, config::read('free', 'messages'));

        $this->r->save('sms_response', $response);
        $this->template('main/login', array());
	}

	/**
	 * This is called when the user clicks the link in the email
	 */
	public function playwin($request){
		$ident = new ident();
        try {
            $alias = $ident->getAliasForUser();
            $out   = $this->chargeUser();
        } catch (Exception $e) {
            $out   = $this->chargeUser();
        }
	}

	private function chargeUser($tariff_class = 'EUR300ES') {
		$this->chargeuser1();
	}

	private function chargeuser1(){
		$sub = new subscription();
		$out = $this->createSubscriptionSession();
		if ($out->responseMessage == 'Success'){
			$this->setSubscriptionSessionId($out->sessionId);
			header('Location: ' . $out->redirectURL);
			exit();
		}
	}

	public function chargeuser2(){
		$out = $this->finalizeSubscriptionSession();

		if ($out->responseMessage !== 'Success') {
			trigger_error("subscription: could not finalize subscription", E_USER_ERROR);
		}

		$out = $this->authorizePayment();

		if ($out->responseMessage !== 'Success') {
			trigger_error("subscription: could not authorize payment", E_USER_ERROR);
		}

        $this->setSessionId($out->sessionId);

		$out = $this->capturePayment();
		if ($out->responseMessage !== 'Success') {
			trigger_error("subscription: could not capture payment", E_USER_ERROR);
		}

		return $out;
	}

	private function capturePayment(){
		$sub = new subscription();
		$out = $sub->capturePayment(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
		return $out;
	}

	public function terminateSubscription($request){
		$sub      = new subscription();
		$con_id   = helpers::cleanPhoneNumber($_GET['consumerId']);
		$sub_id   = $_GET['subscriptionId'];
		$response = $sub->terminateSubscription(array(
			'consumerId'     => $con_id,
			'subscriptionId' => $sub_id
		));
	}


	private function getSessionId(){
		return $this->r->get('session:'.session_id());
	}

	private function setSessionId($session_id) {
		$this->r->set('session:'.session_id(), $session_id);
	}

	private function setConsumerId($consumer_id){
		$this->r->set('consumer_id:'.session_id(), $consumer_id);
	}

	private function getConsumerId(){
		return $this->r->get('consumer_id:'.session_id());
	}

	private function sendInitialSms($phone) {
		$msg    = config::read('free', 'messages');
		$sms    = new sms();
		$out    = $sms->sendSms($phone, $msg, 'EUR0ES');
		$choice = $out->responseMessage == 'Success';

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

	private function createSubscriptionSession($tariff_class='EUR300ES'){
		$sub = new subscription();
		$out = $sub->createSubscriptionSession(array(
			'tariffClass'       => $this->getSubscriptionTariff(),
			'returnURL'         => $this->getSubscriptionSessionUrl(),
			'serviceName'       => $this->getServiceName(),
			'frequencyInterval' => $this->getFrequencyInterval(),
			'username'          => $this->getSubscriptionUser(),
			'password'          => $this->getSubscriptionPwd(),
			'sessionId'         => $this->getSessionId(),
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
		$this->setConsumerId($out->consumerId);
        $this->setSubscriptionId($out->subscriptionId);
		return $out;
	}

	public function authorizePayment(){
		$sub = new subscription();
		return $sub->authorizePayment(array(
			'username'       => $this->getSubscriptionUser(),
			'password'       => $this->getSubscriptionPwd(),
			'consumerId'     => $this->getConsumerId(),
			'subscriptionId' => $this->getSubscriptionId(),
		));
	}

	private function setSubscriptionId($id){
		return $this->r->set('subscription:'.session_id(), $id);
	}

    private function getSubscriptionId(){
        $id = $this->r->get('subscription:'.session_id());
        return $id;
    }

	private function setSubscriptionSessionId($session_id){
		return $this->r->set('subscription_session:'.session_id(), $session_id);
	}

	private function getSubscriptionSessionId(){
		return $this->r->get('subscription_session:'.session_id());
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

	private function getFrequencyInterval(){
		$details = config::read('defaults', 'ipx');
		return $details['frequency_interval'];
	}

	private function getServiceName(){
		$details = config::read('defaults', 'ipx');
		return $details['service_name'];
	}

	private function setConsumerIdForSubscriptionId($out){
		$this->r->set('consumer_id:'.$out->consumerId.':subscription_id', $out->subscriptionId);
		$this->r->set('subscription_id:'.$out->subscriptionId.':consumer_id', $out->consumerId);
		
	}

	private function getSubscriptionIdForConsumerId($consumer_id){
		return $this->r->get('consumer_id:'.$consumer_id.':subscription_id');
	}

	private function getConsumerIdForSubscriptionId($subscription_id){
		return $this->r->get('subscription_id:'.$subscription_id.':consumer_id');
	}

	private function getSubscriptionTariff(){
		$details = config::read('defaults', 'ipx');
		return $details['subscription_tariff'];
	}
}
