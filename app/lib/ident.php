<?php
class ident extends ipx {
	public function __construct($wsdl_spec='apis/ident.xml'){
		parent::__construct($wsdl_spec);
        $this->r = new dbredis();
	}

	public function __call($method, $overrides){
		return $this->makeCall($method, $overrides); 
	}

	public function getAliasForUser(){
		$details = config::read('defaults', 'ipx');
		$out = $this->createSession();
        if ($out->responseMessage == 'Success') {
            echo 'setting session id: '.$out->sessionId;
            $this->setSessionId($out->sessionId);
            return $out;
        } else {
            throw new Exception("ident: could not create session");
        }
	}

	public function alias2(){
        try {
            $out = $this->checkStatus();
            $out = $this->finalizeSession($out->statusCode);
            return $out;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
	}

	private function createSession(){
		$out = $this->makeCall('createSession', array(
			'returnURL' => $this->getRedirectUrl(),
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword()
		));
        if ($out->responseMessage == 'Success') {
            return $out;
        } else {
            throw new Exception("Ident: could not create session");
        }
	}

	private function checkStatus(){
		$out = $this->makeCall('checkStatus', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
        if ($out->responseMessage == "Success") {
            $key = 'status_code:'.session_id();
            $this->r->set($key, ($out->statusCode == 2));
            return $out;
        } else {
            throw new Exception("Ident: check status fail");
        }
	}

	private function finalizeSession($status_code){
		$out = $this->makeCall('finalizeSession', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
        if ($out->responseMessage == 'Success') {
            $this->setCurrentConsumer($out->consumerId);
            if (in_array($status_code, array(0, 1, 2))) {
                $this->addSubscriber($id);
            }
        }
        return $out;
	}

    private function addSubscriber($id){
        $this->r->sadd('subscribers', $id); 
    }

	private function getSessionId(){
	    $r = new dbredis();
		return $r->get('session:'.session_id());
	}

	private function getRedirectUrl(){
		$details = config::read('defaults', 'ipx');
		return $details['redirectURL'];
	}

	private function getPassword(){
		$details = config::read('defaults', 'ipx');
		return $details['password2'];
	}

	private function getUserName(){
		$details = config::read('defaults', 'ipx');
		return $details['username2'];
	}

    private function setCurrentConsumer($consumer_id){
        $this->r->set('consumerid:'.session_id(), $consumer_id);
    }

	private function setSessionId($session_id){
	    $r = new dbredis();
		return $r->set('session:'.session_id(), $session_id);
	}
}
