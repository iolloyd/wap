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

    ////////////////////////////////////
    //         IDENTIFICATION         //
    ////////////////////////////////////

	/**
	 * This is called when the user clicks the link in the sms 
     * they receive from us
	 */
	public function playwin($request){
		$ident = new ident();
        $out   = $ident->createSession();
        if ($out->redirectURL) {

            // This will be sed later by 
            // ident:checkstatus and ident:finalizesession
            // after the redirect
            $this->setSessionId($out->sessionId);

            // After successful redirection the user returns
            // to "alias2" to complete identification
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
        $out   = $this->identCheckStatus();
        if ($out->responseMessage !== 'Success') {
            echo 'ident:check failed';
        }

        $out = $this->identFinalizeSession();
        if ($out->responseMessage !== 'Success') {
            echo 'ident:finalize failed';
            exit();
        }

        $this->setAlias($out->consumerId);
        $this->subscribeOrCharge();
    }

    /**
     * Used by the identification process to 
     * check the status of the current user
     */
	private function identCheckStatus(){
		$ident = new ident();
		return $ident->checkStatus(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
	}

	private function identFinalizeSession(){
		$ident = new ident();
		return $ident->finalizeSession(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $this->getSessionId()
		));
	}

    /////////////////////////////////
    //         SUBSCRIPTION        //
    /////////////////////////////////

    /**
     * This is used to check the subscription we want to use
     */
    private function subscriptionGetSubscriptionStatus(){
       if (!$subscription = $this->getSubscriptionId()) {
           return false;
       }
       $sub = new subscription();
       return $sub->getSubscriptionStatus(array(
           'subscriptionId' => $subscription,
           'consumerId'     => $this->getConsumerId(),
           'username'       => $this->getSubscriptionUser(),
		   'password'       => $this->getSubscriptionPwd()
       ));
    }

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
        $this->r->publish('debug', $out->responseMessage);
        if ($out->responseMessage !== 'Success') {
            throw new Exception('Finalize Subscription:'.$out->responseMessage);
        }
        return $out;
	}

	public function subscriptionAuthorizePayment($subscription_id=false){
		$sub = new subscription();
		$out = $sub->authorizePayment(array(
			'username'       => $this->getSubscriptionUser(),
			'password'       => $this->getSubscriptionPwd(),
			'consumerId'     => $this->getConsumerId(),
			'subscriptionId' => $this->getSubscriptionId()
		));
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            throw new Exception('Authorize Payment:'.$out->responseMessage);
        }
	}

    /**
     * The final method in the whole process.
     */
	private function subscriptionCapturePayment($session_id){
        $this->storeChargeDetails();
        die('capture payment here');
		$sub = new subscription();
        // FOR TESTING ONLY. REMOVE WHEN LIVE
		$out = $sub->capturePayment(array(
			'username'  => $this->getSubscriptionUser(),
			'password'  => $this->getSubscriptionPwd(),
			'sessionId' => $session_id
		));
        if ($out->responseMessage == 'Success') {
            $this->storeChargeDetails();
            $this->sendWelcomeEmail();
            return $out;
        } else {
            $this->storeFailedCharge();
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
			'username'          => $this->getSubscriptionUser(),
			'password'          => $this->getSubscriptionPwd(),
			'tariffClass'       => $this->getSubscriptionTariff(),
			'returnURL'         => $this->getSubscriptionSessionUrl(),
			'serviceName'       => $this->getServiceName(),
			'frequencyInterval' => $this->getFrequencyInterval()
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
	private function subscribeOrCharge() {
        $out = $this->subscriptionGetSubscriptionStatus();
        if ($out && $out->subscriptionStatus == 1) {
            $out = $this->subscriptionAuthorizePayment();
            $this->subscriptionCapturePayment($out->sessionId);
        } else {
            try {
                $out = $this->subscriptionCreateSubscriptionSession();

                // After this redirect, we will return to "chargeuser2"
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
        return $status;
    }

    /**
     * This is the second computation once the user has
     * been identified by the IPX service
     */
	public function chargeuser2($request=array()){
        try {
            $out = $this->subscriptionFinalizeSubscriptionSession();
            $this->setSubscriptionId($out->subscriptionId);
            $this->setConsumerId($out->consumerId);
            $out = $this->subscriptionAuthorizePayment($out->subscriptionId);
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

	public function getConsumerId(){
        $alias = $this->getAlias();
		$consumer_id = $this->r->get('consumerid:'.$alias);
        $this->r->publish('debug', 'consumerid:'.$alias.' '.$consumer_id);
        return $consumer_id;
	}

	private function setConsumerId($consumer_id){
        $alias = $this->getAlias();
        $this->r->publish('debug', 'setting consumerid:'.$alias.' to '.$consumer_id);
		return $this->r->set('consumerid:'.$alias, $consumer_id);
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
        return $sid;
	}

	private function setSessionId($session_id) {
		return $this->r->set('session:'.session_id(), $session_id);
	}

    private function getSubscriptionId(){
        $alias = $this->getAlias();
        $id    = $this->r->get('subscription:'.$alias);
        $this->r->publish('debug', 'getting subscriptionId:'.$alias.' -> '.$id);
        return $id;
    }

	private function setSubscriptionId($id){
        $alias = $this->getAlias();
        $this->r->publish('debug', 'setting subscriptionId:'.$alias.' to '.$id);
		return $this->r->set('subscription:'.$alias, $id);
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

    ///////////////////////////////
    //         PURCHASE          //
    ///////////////////////////////

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

    ///////////////////////////////

    private function setAlias($alias){
        $this->r->publish('debug', 'SET alias:'.session_id(). '-> '.$alias);
        $this->r->set('alias:'.session_id(), $alias);
    }

    private function getAlias(){
        return $this->r->get('alias:'.session_id());
    }

    private function sendWelcomeEmail(){
        $phone = $this->getConsumerId();
        $text  = config::read('welcome', 'messages');
        $sms = new sms();
        $sms->sendSms($phone, $text, $tariff='EUR0');
        $this->r->sadd('subscriber', $phone);
    }

    private function storeChargeDetails(){
        $hour = date("Ymdh");
        $day  = date("ymd");
        $month = date("ym");
        $this->r->zincrby('signup', $hour, 1, $time);    
        $this->r->zincrby('signup', $day, 1, $time);    
        $this->r->zincrby('signup', $month, 1, $time);    
        $this->r->publish('signup', $this->getConsumerId);
    }

    private function storeFailedCharge(){
        $hour = date("Ymdh");
        $day  = date("ymd");
        $month = date("ym");
        $this->r->zincrby('signko', $hour, 1, $time);    
        $this->r->zincrby('signko', $day, 1, $time);    
        $this->r->zincrby('signko', $month, 1, $time);    
        $this->r->publish('signko', $this->getConsumerId);
    }
}
