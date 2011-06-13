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
		$out = $this->createSession($details);
        if ($out->responseMessage == 'Success') {
            $this->setSessionId($out->sessionId);
            return $out;
        } else {
            throw new Exception("ident: could not create session");
        }
	}

	public function alias2(){
		$out = $this->checkStatus();
		if ($out->responseMessage !== 'Success') {
			trigger_error("Failure with check status", E_USER_ERROR);
		}
        $status_code = $out->statusCode;
		$out = $this->finalizeSession($status_code);
		if ($out->responseMessage !== 'Success') {
			trigger_error("Problem finalizing identification", E_USER_ERROR);
		}
		return $out;
	}

	private function createSession($details){
		return $this->makeCall('createSession', array(
			'returnURL' => $this->getRedirectUrl(),
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword()
		));
	}

	private function checkStatus(){
		$out = $this->makeCall('checkStatus', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
        $key = 'known:'.session_id();
        if($out->statusCode == 2) {
            $this->r->set($key, 1);
        } else {
            $this->r->set($key, 0);
        }
        return $out;
	}

	private function finalizeSession($status_code){
		$out = $this->makeCall('finalizeSession', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
        $this->r->set('consumer_id:'.session_id(), $out->consumerId);
        if ($out->responseMessage == 'Success') {
            if (in_array($status_code, array(0, 1, 2))) {
                $this->r->sadd('subscribers', $out->consumerId); 
            }
        }
        return $out;
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

	private function setSessionId($session_id){
	    $r = new dbredis();
		return $r->set('session:'.session_id(), $session_id);
	}
}
