<?php
class main extends controller {
	var $layout = 'main';
	public function authorizePayment(){
		$sub = new subscription();
		$out = $sub->authorizePayment(array(
			'username'       => $this->getSubscriptionUser(),
			'password'       => $this->getSubscriptionPwd(),
			'consumerId'     => $this->getConsumerId(),
			'subscriptionId' => $this->getSubscriptionId(),
		));
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            throw new Exception('AuthorizePayment: ' . $out->responseMessage);
        }
	}

    /**
     * This is the second and final part of the 
     * ident process. If successful it will continue
     * to the subscription flow (tryToChargeUser)
     */
    public function alias2(){
        $ident = new ident();
        $out   = $ident->alias2();
        if ($out->responseMessage == 'Success') {
            $this->tryToChargeUser();
        }
    }

    /**
     * This method shows the link in the website at
     * the very start of the process
     */
	public function index($request){
        $this->template('main/simple', array());
	}

	public function indexPost($request){
		$phone    = helpers::cleanPhoneNumber($_REQUEST['telefono']);
		$sms      = new sms();
		$response = $sms->sendSms($phone, config::read('free', 'messages'));
        $this->r->save('sms_response', $response);
        $this->template('main/simple', array());
	}

	/**
	 * This is called when the user clicks the link in the email
	 */
	public function playwin($request){
		$ident = new ident();
        $alias = $ident->getAliasForUser();
        if ($alias->redirectURL) {
            header('Location: '.$alias->redirectURL);
            exit();
        }
	}
    public function tryToChargeUser(){
        try {
             $this->chargeUser();
            echo 'id and charged';
        } catch (Exception $e) {
            try {
                echo '...that failed, trying one-shot';
                $this->oneshot();
                echo 'oneshot';
            } catch (Exception $e) {
                echo 'one-shot failed.';
            } 
        }
    }

	public function chargeuser2($request=array()){
        try {
            $this->finalizeSubscriptionSession();
            $this->authorizePayment();
            $this->capturePayment();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
	}
    public function oneshot(){
        $purchase = new purchase();
        $out = $purchase->createSession();
        if ($out->responseMessage == 'Success') {
            header('Location: '. $out->redirectURL);
            exit();
        } else {
            throw new Exception("Could not do oneshot");
        }
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

    /////////////////////
    // Private methods // 
    /////////////////////

	private function capturePayment(){
        //die('here we capture payment');
		$sub = new subscription();
		$out = $sub->capturePayment(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            throw new Exception("Capture Payment: " . $out->responseMessage);
        }
	}
	private function chargeUser($tariff_class = 'EUR300ES') {
		$sub = new subscription();
        if ($this->isSubscriber($this->getCurrentConsumerId()){
            $this->authorizePayment();
            $this->capturePayment();
        } else {
            try {
                $out = $this->createSubscriptionSession();
                $this->setSessionId($out->sessionId);
                header('Location: ' . $out->redirectURL);
                exit();
            } catch (Exception $e) { 
                echo $e->getMessage();
                exit();
            }
        }
	}

    private function getCurrentConsumerId(){
        return $this->r->get('consumer_id:'.session_id());
    }

    private function isSubscriber($id){
        return $this->r->sismember('subscribers', $id);
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
        if ($out->responseMessage == 'Success') {
            $this->setSessionId($out->sessionId);
            return $out;
        } else {
            throw new Exception('CreateSubscriptionSession: ' . $out->responseMessage);
        }
	}
	private function finalizeSession($user, $pwd){
		$session_id = $this->getSessionId();
		$ident = new ident();
		$out = $ident->finalizeSession(array(
			'username'  => $user,
			'password'  => $pwd,
			'sessionId' => $session_id
		));
		return $out;
	}
	private function finalizeSubscriptionSession(){ 
		$sub = new subscription();
		$out = $sub->finalizeSubscriptionSession(array(
			'sessionId' => $this->getSessionId(),
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd()
		));
        if ($out->responseMessage == 'Success') {
            $this->setConsumerId($out->consumerId);
            $this->setSubscriptionId($out->subscriptionId);
            return $out;
        } else {
            throw new Exception('Finalize Subscription: ' . $out->responseMessage);
        }
	}
	private function getConsumerId(){
		return $this->r->get('consumer_id:'.session_id());
	}
	private function getFrequencyInterval(){
		$details = config::read('defaults', 'ipx');
		return $details['frequency_interval'];
	}
	private function getServiceName(){
		$details = config::read('defaults', 'ipx');
		echo $details['service_name'];
		return $details['service_name'];
	}
	private function getSessionId(){
		return $this->r->get('session:'.session_id());
	}
    private function getSubscriptionId(){
        $id = $this->r->get('subscription:'.session_id());
        return $id;
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
	private function getSubscriptionTariff(){
		$details = config::read('defaults', 'ipx');
		return $details['subscription_tariff'];
	}
    private function oneshot2($req){
        $purchase = new purchase();
        $out = $this->oneshotCheckStatus();
        $out = $this->oneshotFinalizeSession();
        $this->template('main/simple');
    }
    private function oneshotCheckStatus(){
        $purchase = new purchase();
        $out = $purchase->checkStatus();
        if ($out->responseMessage != 'Success') {
            $this->template('main/error', array(
                'error' => 'Unable to complete single purchase'
            ));
            exit();
        }
    }
    private function oneshotFinalizeSession(){
        $purchase = new purchase();
        $out = $purchase->finalizeSession();
        if ($out->responseMessage != 'Success') {
            $this->template('main/error', array(
                'error' => 'Unable to finalize single purchase'
            ));
            exit();
        }
    }
	private function setConsumerId($consumer_id){
		return $this->r->set('consumer_id:'.session_id(), $consumer_id);
	}
	private function setConsumerIdForSubscriptionId($out){
		$this->r->set('consumer_id:'.$out->consumerId.':subscription_id', $out->subscriptionId);
		$this->r->set('subscription_id:'.$out->subscriptionId.':consumer_id', $out->consumerId);
		
	}
	private function setSessionId($session_id) {
		return $this->r->set('session:'.session_id(), $session_id);
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
	private function setSubscriptionId($id){
		return $this->r->set('subscription:'.session_id(), $id);
	}
	private function setSubscriptionSessionId($session_id){
		return $this->r->set('subscription_session:'.session_id(), $session_id);
	}
}
