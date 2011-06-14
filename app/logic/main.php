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

	public function indexPost($request){
		$phone    = helpers::cleanPhoneNumber($_REQUEST['telefono']);
		$sms      = new sms();
		$response = $sms->sendSms($phone, config::read('free', 'messages'));
        $this->r->save('sms_response', $response);
        $this->template('main/simple', array());
	}

	/**
	 * This is called when the user clicks the link in the sms they
     * receive.
	 */
	public function playwin($request){
		$ident = new ident();
        $alias = $ident->getAliasForUser();
        if ($alias->redirectURL) {

            // After successful redirection the user returns
            // to alias2 to complete identification
            header('Location: '.$alias->redirectURL);
            exit();
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
            echo 'ok, lets try to charge...<br>';
            try {
                $this->tryToChargeUser();
                $this->template('main/thanks');
            } catch (Exception $e) {
                $this->template('main/oops');
                die;
            }
        }
    }

    public function tryToChargeUser(){
        try {
             $this->subscribeOrCharge();
        } catch (Exception $e) {
            try {
                $this->oneshot();
            } catch (Exception $e) {
                throw new Exception("All methods failed");
            } 
        }
    }

    /**
     * If the user has been identified as a current subscriber
     * we will send then straight to the authorizePayment method
     * otherwise we will create a subscription
     */
	private function subscribeOrCharge($tariff_class = 'EUR300ES') {
		$consumer_id = $this->getConsumerId();
        if ($this->isSubscriber($consumer_id)) {
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

    /**
     * This is the second computation once the user has
     * been identified by the IPX service
     */
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

    /**
     * This is only called if the user has just been identified
     * and does NOT have a current subscription
     */
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
     * The final method in the whole process.
     */
	private function capturePayment(){
        die('here we capture payment');
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

    /**
     * This method is called by the IPX service to inform us of 
     * a terminated subscription.
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

    private function isSubscriber($id){
        return $this->r->sismember('subscribers', $id);
    }

    /**
     * Used by the identification process to check the status
     * of the current user
     */
	private function checkStatus($user, $pwd){
		$ident = new ident();
		$out   = $ident->checkStatus(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
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
		$ident = new ident();
		$out = $ident->finalizeSession(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
		return $out;
	}

	private function getConsumerId(){
		return $this->r->get('consumerid:'.session_id());
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
}
