<?php
class main extends controller {
	var $layout = 'main';

    /**
     * This method shows the link in the website at
     * the very start of the process
     */
	public function index($request){
        $this->template('main/simple', array());
	}

    //////////////////////////////////////
    //         IDENTIFICATION           //
    //////////////////////////////////////

	/**
	 * This is called when the user clicks the link in the sms 
     * they receive from us
	 */
	public function playwin($request){
		$ident = new ident();
        $out   = $ident->createSession();
        if ($out->redirectURL) {
            $this->setSessionId($out->sessionId);

            // After successful redirection the user returns
            // to alias2 to complete identification
            $x = $out->redirectURL . '&PHPSESSID='. session_id();
            header('Location: '.$x);
            exit();
        }
	}

    /**
     * This is the second and final part of the 
     * ident process. If successful it will continue
     * to the subscription flow and try to charge user
     */
    public function alias2(){
        $ident = new ident();
        $out = $this->identCheckStatus();
        if ($out->responseMessage !== 'Success') {
            echo 'ident:check failed';
        }

        $status_code = $out->statusCode;

        $out = $this->identFinalizeSession();
        if ($out->responseMessage !== 'Success') {
            echo 'ident:finalize failed';
        }

        $this->setAlias($out->consumerId);
        $this->subscribeOrCharge($status_code);
        $this->template('main/thanks');
    }

    /**
     * Used by the identification process to 
     * check the status of the current user
     */
	private function identCheckStatus(){
		$ident = new ident();
		$out   = $ident->checkStatus(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
		return $out;
	}

	private function identFinalizeSession(){
		$ident = new ident();
		$out = $ident->finalizeSession(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
		return $out;
	}

    /////////////////////////////////
    //         SUBSCRIPTION        //
    /////////////////////////////////

    /**
     * This is only called if the user has just been identified
     * and does NOT have a current subscription
     */
	private function subscriptionFinalizeSubscriptionSession(){ 
		$sub = new subscription();
		$out = $sub->finalizeSubscriptionSession(array(
			'sessionId' => $this->getSessionId(),
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd()
		));
        if ($out->responseMessage == 'Success') {
            $alias = $this->r->get('alias:'.session_id());
            $this->r->set('consumerid:'.$alias, $out->consumerId);
            $this->setSubscriptionId($out->subscriptionId);
            $this->setConsumerId($out->consumerId);
            $this->storeConsumerIdWithAlias();
            return $out;
        } else {
            throw new Exception('Finalize Subscription:'.$out->responseMessage);
        }
	}

	public function subscriptionAuthorizePayment(){
		$sub = new subscription();
		$out = $sub->authorizePayment(array(
			'username'       => $this->getSubscriptionUser(),
			'password'       => $this->getSubscriptionPwd(),
			'consumerId'     => $this->getConsumerId(),
			'subscriptionId' => $this->getSubscriptionId(),
		));
        if ($out->responseMessage == 'Success') {
            print_r($out);
            return $out;
        } else {
            throw new Exception('Authorize Payment:'.$out->responseMessage);
        }
	}

    /**
     * The final method in the whole process.
     */
	private function subscriptionCapturePayment($session_id){
        $this->r->publish('debug', 'capture payment using session_id -> '. $this->getSessionId());
		$sub = new subscription();
		$out = $sub->capturePayment(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $session_id
		));
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            throw new Exception('Capture Payment: '.$out->responseMessage);
        }
	}

    /**
     * This method is called by the IPX service to inform us of 
     * a terminated subscription.
     */
	public function subscriptionTerminateSubscription($request){
		$sub      = new subscription();
		$con_id   = helpers::cleanPhoneNumber($_GET['consumerId']);
		$sub_id   = $_GET['subscriptionId'];
		$response = $sub->terminateSubscription(array(
			'consumerId'     => $con_id,
			'subscriptionId' => $sub_id
		));
	}

	private function subscriptionCreateSubscriptionSession($tariff_class='EUR300ES'){
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

    /**
     * If the user has been identified as a current subscriber
     * we will send then straight to the authorizePayment method
     * otherwise we will create a subscription
     */
	private function subscribeOrCharge($status) {
        if ($status == 1) {
            $out = $this->subscriptionAuthorizePayment();
            $this->subscriptionCapturePayment($out->sessionId);
        } else {
            try {
                $out = $this->subscriptionCreateSubscriptionSession();
                header('Location: '.$out->redirectURL . '&PHPSESSID='. session_id());
                exit();
            } catch (Exception $e) { 
                echo $e->getMessage();
                exit();
            }
        }
	}

    public function getSubscriptionStatus(){
        $status = $this->r->get('subscription:status:'.session_id());
        $this->r->publish('debug', 'getting status -> ' . $status);
        return $status;
    }

    public function setSubscriptionStatus($status){
        $this->r->publish('debug', 'setting status -> ' . $status);
        $this->r->set('subscription:status:'.session_id(), $status);
    }

    /**
     * This is the second computation once the user has
     * been identified by the IPX service
     */
	public function chargeuser2($request=array()){
        try {
            $out = $this->subscriptionFinalizeSubscriptionSession();
            $out = $this->subscriptionAuthorizePayment();
            $this->subscriptionCapturePayment($out->sessionId);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
	}

    /**
     * This is called if the user cannot be identified and we
     * cannot create a subscription.
     */
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

	private function getConsumerId(){
        $alias = $this->getAlias();
		return $this->r->get('consumerid:'.$alias);
	}

	private function getFrequencyInterval(){
		$details = config::read('defaults', 'ipx');
		return $details['frequency_interval'];
	}

	private function getServiceName(){
		$details = config::read('defaults', 'ipx');
		return $details['service_name'];
	}

	private function getSessionId(){
		$sid = $this->r->get('session:'.session_id());
        $this->r->publish('debug', 'fetching session_id -> ' . $sid);
        return $sid;
	}

	private function setSessionId($session_id) {
        $this->r->publish('debug', 'setting session_id -> ' . $session_id);
		return $this->r->set('session:'.session_id(), $session_id);
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

    public function oneshot2($request){
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
                'error' => 'Unable to complete single purchase'));
            exit();
        }
    }

    private function oneshotFinalizeSession(){
        $purchase = new purchase();
        $out = $purchase->finalizeSession();
        if ($out->responseMessage != 'Success') {
            $this->template('main/error', array(
                'error' => 'Unable to finalize single purchase'));
            exit();
        }
    }

    private function setAlias($alias){
        $this->r->set('alias:'.session_id(), $alias);
    }

    private function getAlias(){
        return $this->r->get('alias:'.session_id());
    }
	private function setConsumerId($consumer_id){
		return $this->r->set('consumerid:'.session_id(), $consumer_id);
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
			'message'  => $messages[$choice]));
	}

	private function setSubscriptionId($id){
		return $this->r->set('subscription:'.session_id(), $id);
	}
}
