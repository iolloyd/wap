<?php
class main extends controller {
    var $layout = 'main';

    /**
     * This method shows the link in the website at
     * the very start of the process
     */
    public function index($request){
        $this->template('main/index', array(
            'start_time' => time()
        ));
    }

    public function loginPost($request){
        if (!$_POST['telefono']) {
            $this->template('main/index');
            exit();
        }
        $phone  = helpers::cleanPhoneNumber($_POST['telefono'], '34');
        $this->r->zincrby('phone_entered', 1, date('Ymd'));
        $this->checkForRegisteredUser($phone);
        $lookup = $this->getOperator($phone);
        $this->storeOperatorStatistics($lookup);
        $this->checkIfAlreadySubscribed();
        $this->handleVodafoneUser($lookup, $phone);
        $out        = $this->sendInitialSms($phone);
        $out->phone = $phone;
        $this->r->save('sms', $out);
        $this->r->zincrby('phone_entered_and_email_sent', 1, date('Ymd'));
        $this->r->zincrby('send_sms', 1, date('Ymd'));
        $this->template('main/login');
    }

    public function calificationPost($request){
        $this->template('main/calification');
    }

    public function finally($request){
        if ($this->r->sismember('pins', $_GET['password'])) {
            $this->template('main/finally');
        } else {
            $this->template('main/finally');
            //$this->template('main/login');
        }
    }

    public function finallyPost($request){
        $this->template('main/finally', array(
            'start_time'       => $_POST['start_time'],
            'previous_answers' => $_POST
        ));
    }

    public function calification($request){
        $this->template('main/calification', array(
        ));
    }

    ///////////////////////////
    //         IDENT         //
    ///////////////////////////

    /**
     * This is called when the user clicks the link in the sms 
     * they receive from us
     */
    public function playwin($request){
        $phone = helpers::cleanPhoneNumber($_POST['phone'], '34');
        $ident = new ident();
        $out   = $ident->createSession();
        if ($out->redirectURL) {
            // This will be used later by 
            // ident:checkstatus and ident:finalizesession
            // after the redirect
            $this->setSessionId($out->sessionId);

            // After successful redirection the user returns
            // to "alias2" to complete identification
            $x = $out->redirectURL;
            header('Location: '.$x);
            exit();
        } else {
            $this->r->zincrby('fail:ident_create_session', 1, date('Ymd'));
        }
    }

    /**
     * This is the second and final part of the 
     * ident process. If successful it will continue
     * to the subscription flow and try to charge user
     */
    public function alias2(){
        $browser_info = get_browser();
        $this->r->zincrby('browser:' .date('Ymd'), 1, $browser_info->browser);
        $this->r->zincrby('platform:'.date('Ymd'), 1, $browser_info->platform);
        try {
            $ident = new ident();
            $out   = $this->identCheckStatus();
            if ($out->responseMessage !== 'Success' 
            ||  $out->statusMessage == 'Unknown consumer') {
                $this->r->zincrby('fail:ident_check_status', 1, date('Ymd'));
                $this->r->zincrby('fail:browser:' .date('Ymd'), 1, $browser_info->browser);
                $this->r->zincrby('fail:platform:'.date('Ymd'), 1, $browser_info->platform);
                return $this->oneshot();
            } else {
                $out = $this->identFinalizeSession();
                if ($out->responseMessage !== 'Success') {
                    $this->r->zincrby('fail:ident_finalize_session', 1, date('Ymd'));
                    $this->r->zincrby('fail:browser:' .date('Ymd'), 1, $browser_info->browser);
                    $this->r->zincrby('fail:platform:'.date('Ymd'), 1, $browser_info->platform);
                    throw new Exception('ident: finalize failed');
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
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
     * If the user has been identified as a current subscriber
     * we will send then straight to the authorizePayment method
     * otherwise we will create a subscription
     */
    private function subscribeOrCharge() {
        $out = $this->subscriptionGetSubscriptionStatus();
        if ($out && $out->subscriptionStatus == 1) {
            try {
                $out = $this->subscriptionAuthorizePayment();
                $this->subscriptionCapturePayment($out->sessionId);
            } catch (Exception $e) {
                $this->r->zincrby('fail:subscription_finalize_session', 1, date('Ymd')); 
                $this->template('main/oops', array(
                    'error' => $e->getMessage()
                ));
                die;
            }
            $this->storePinForConsumer($pin);
        } else {
            $this->template('main/already', array($msg => 'Ya tienes un subscripcion'));
            die;
            try {
                $out = $this->subscriptionCreateSubscriptionSession();
                // After this redirect, we will return to "chargeuser2"
                header('Location: '.$out->redirectURL);
                exit();
            } catch (Exception $e) { 
                echo "No podemos procesar el transaccion";
                exit();
            }
        }
    }

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
        $this->r->save('sub_finalize_sub', $out);
        if ($out->responseMessage !== 'Success') {
            $this->r->zincrby('fail:subscription_finalize_session', 1, date('Ymd')); 
            throw new Exception('Finalize Subscription:'.$out->responseMessage);
        }
        return $out;
    }

    public function subscriptionAuthorizePayment(){
        $sub = new subscription();
        $out = $sub->authorizePayment(array(
            'username'       => $this->getSubscriptionUser(),
            'password'       => $this->getSubscriptionPwd(),
            'consumerId'     => $this->getConsumerId(),
            'subscriptionId' => $this->getSubscriptionId()
        ));
        $this->r->save('sub_authorize_payment', $out);
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            $this->r->zincrby('fail:authorize_payment', 1, date('Ymd')); 
            throw new Exception('Authorize Payment:'.$out->responseMessage);
        }
    }

    public function game($request){
        $this->template('main/game');
    }
    /**
     * The final method in the whole process.
     */
    private function subscriptionCapturePayment($session_id){
        $sub = new subscription();
        $out = $sub->capturePayment(array(
            'username'  => $this->getSubscriptionUser(),
            'password'  => $this->getSubscriptionPwd(),
            'sessionId' => $session_id
        ));
        $this->r->save('sub_capture_payment', $out);
        $this->setPassword($this->getAlias());
        if ($out->responseMessage == 'Success') {
            $this->storeChargeDetails();
            $this->storeAliasWithSubscription();
            $this->template('main/simple');
            // return $out;
        } else {
            $this->r->zincrby('fail:capture_payment', 1, date('Ymd')); 
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

    private function setConsumerSubscription(){
        $this->r->set('subscriptionForConsumer:'.$this->getConsumerId(), $this->getSubscriptionId());
    }

    private function getConsumerSubscription(){
        return $this->r->get('subscriptionForConsumer:'.$this->getConsumerId());
    }

    private function subscribeAsVodafone($phone){
        $out = $this->subscriptionCreateSubscriptionSession('EUR300ES', true, $phone);
        if ($out->redirectURL) {
            header('Location: '.$out->redirectURL);
            exit();
        }
    }

    private function subscriptionCreateSubscriptionSession(
        $tariff_class='EUR300ES', $vodafone=false, $phone=false
    ){
        $sub = new subscription();
        $data = array(
            'username'          => $this->getSubscriptionUser(),
            'password'          => $this->getSubscriptionPwd(),
            'tariffClass'       => $this->getSubscriptionTariff(),
            'returnURL'         => $this->getSubscriptionSessionUrl(),
            'serviceName'       => $this->getServiceName(),
            'frequencyInterval' => $this->getFrequencyInterval()
        );
        if ($vodafone) {
            $data['campaignName']    = 'WEB';
            $data['serviceMetaData'] = 'msisdn='.$phone;
            $data['contentMetaData'] = 'msisdn='.$phone;
        }
        $out = $sub->createSubscriptionSession($data);
        if ($out->responseMessage == 'Success') {
            $this->setSessionId($out->sessionId);
            return $out;
        } else {
            throw new Exception('CreateSubscriptionSession: ' . $out->responseMessage);
        }
    }

    private function storePinForConsumer($pin){
        $this->r->hset('pins', $consumer, $pin);
    }

    private function sendPinEmail($pin){
        $sms = new sms();
        $txt = config::read('code', 'messages');
        $txt = str_replace('{code}', $pin, $txt);
        $out = $sms->sendSms($phone, $txt, 'EUR0');
        return $out;
    }

    public function getSubscriptionStatus(){
        $status = $this->r->get('subscription:status:'.session_id());
        return status;
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
 
    public function getConsumerId(){
        $alias       = $this->getAlias();
        $consumer_id = $this->r->get('consumerid:'.$alias);
        return $consumer_id;
    }

    private function setConsumerId($consumer_id){
        $alias = $this->getAlias();
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
        return $id;
    }

    private function setSubscriptionId($id){
        $alias = $this->getAlias();
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
        return $details['subscription_url'].'?pre_session_id='.session_id();
    }

    private function getSubscriptionTariff(){
        $details = config::read('defaults', 'ipx');
        return $details['subscription_tariff'];
    }

    ///////////////////////////////
    //         PURCHASE          //
    ///////////////////////////////

    /**
     * This is called if the user cannot be identified and we
     * cannot create a subscription.
     */
    public function oneshot(){
        $purchase = new purchase();
        $defaults = config::read('defaults', 'ipx');
        $url      = $defaults['purchaseRedirect'].'?pre_session_id='.session_id();
        $out = $purchase->createSession(array(
            'returnURL' => $url
        ));
        $this->setSessionId($out->sessionId);
        if ($out->responseMessage == 'Success') {
            $this->r->zincrby('purchase_create_session', 1, date('Ymd'));
            $this->setPurchaseSessionId($out->sessionId);
            $url  = $out->redirectURL;
            header('Location: '. $url);
            exit();
        } else {
            $this->r->save('failed:oneshot:createsession', $out);
            $this->r->zincrby('fail:purchase_create_session', 1, date('Ymd'));
            throw new Exception("Could not do oneshot");
        }
    }

    public function purchase2($request){
        $out = $this->oneshotCheckStatus();
        $out = $this->oneshotFinalizeSession();
        $this->template('main/simple');
    }

    private function oneshotCheckStatus(){
        $purchase = new purchase();
        $out = $purchase->checkStatus(array(
            'sessionId' => $this->getPurchaseSessionId()
        ));
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            $out->sessionIdSent = $this->getPurchaseSessionId();
            $this->r->save('failed:oneshot:checkstatus', $out);
            $this->r->zincrby('fail:oneshot_check_status', 1, date('Ymd'));
            $this->template('main/error', array(
                'error' => 'Unable to complete single purchase'));
            exit();
        }
    }

    private function oneshotFinalizeSession(){
        $purchase = new purchase();
        $out = $purchase->finalizeSession(array(
            'sessionId' => $this->getPurchaseSessionId()
        ));
        if ($out->responseMessage != 'Success') {
            $out->sessionIdSent = $this->getPurchaseSessionId();
            $this->r->save('failed:oneshot:finalize', $out);
            $this->r->zincrby('fail:oneshot_finalize_session', 1, date('Ymd'));

            $this->template('main/error', array(
                'error' => 'Unable to finalize single purchase')
            );
            exit();
        } else {
            $this->r->save('oneshot', $out);
            $this->r->zincrby('sale', 1, date('Ymd'));
            $this->r->zincrby('sale_by_oneshot', 1, date('Ymd'));
            $this->template('game/play');
        } 
    }

    private function setPurchaseSessionId($id){
        $this->r->set('purchase_session_id:'.session_id(), $id);
    }

    private function getPurchaseSessionId(){
        return $this->r->get('purchase_session_id:'.session_id());
    }

    ///////////////////////////////

    private function setAlias($alias){
        $this->r->set('alias:'.session_id(), $alias);
    }

    private function getAlias($phone=false){
        return $phone 
            ? $this->r->get('alias:'.$phone)
            : $this->r->get('alias:'.session_id());
    }

    private function storeChargeDetails(){
        list($hour, $day, $month)  = array(date("Ymdh"), date("Ymd"), date("Ym"));
        $this->r->zincrby('signup_by_day:'.$month, 1, $day);
        $this->r->zincrby('signup_by_hour:'.$day, 1, $hour);
    }

    ///////////////////////////////

    private function checkForRegisteredUser($phone){
        if ($_POST['contrasenya']) {
            $pwd      = $this->getPassword($phone);
            $template = (trim($pwd) == trim($_POST['contrasenya'])) ? 'game/play' : 'main/index';
            $this->template($template, array(
                'time'      => time(),
                'questions' => config::get('questions', 'questions')
            )); 
            die;
        }
    }

    private function setPassword($phone, $pwd=false){
        $pwd = $pwd ?: helpers::generatePassword();
        $this->r->hset('password', $phone, $pwd);
    }

    private function getPassword($phone){
        return $this->r->hget('password', $phone);
    }

    private function storeOperatorStatistics($lookup){
        $this->r->zincrby('operator', 1, $lookup->operator);
        $this->r->zincrby('operator:'.date('Ymd'), 1, $lookup->operator);
    }

    private function getOperator($phone){
        $look   = new consumerlookup();
        return $look->resolveOperator(array(
            'consumerId'    => $phone,
            'correlationId' => 'IPXWAP',
            'username'      => $this->getSubscriptionUser(),
            'password'      => $this->getSubscriptionPwd(),
        ));
    }

    private function sendInitialSms($phone){
        $sms        = new sms();
        $text       = config::read('free', 'messages');
        $out = $sms->sendSms($phone, $text, $tariff='EUR0');
        return $out;
    }

    private function checkIfAlreadySubscribed(){
        //return true;
        $out = $this->subscriptionGetSubscriptionStatus();
        //print_r($out); die;
        $this->r->save('check:subscribed', $out);
        if ($out && $out->subscriptionStatus == 1) {
            $this->template('main/already', array(
               'msg' => 'Ya no se puede con este numero del telefono'
            ));
            die;
        }
    }

    private function handleVodafoneUser($lookup, $phone){
        if ($lookup->operator == 'AIRTEL') {
            $this->r->zincrby('sent_to_vodafone', 1, date('Ymd'));
            $out = $this->subscribeAsVodafone($phone);
            if ($out->redirectURL) {
                header('Location: '.$out->redirectURL);
                exit();
            }
        }
    }

}
