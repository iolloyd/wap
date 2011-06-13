<?php
class ident extends ipx {

	public function __construct($wsdl_spec='apis/ident.xml'){
		parent::__construct($wsdl_spec);
	}

	public function __call($method, $overrides){
		return $this->makeCall($method, $overrides); 
	}

	public function getAliasForUser(){
		$details = config::read('defaults', 'ipx');
		$out = $this->createSession($details);
        return $out;
	}

	public function chargeuser2(){
		$out = $this->setSessionId($out->sessionId);
		$out = $this->checkStatus();
		if ($out->responseMessage !== 'Success') {
			trigger_error("Failure with check status", E_USER_ERROR);
		}
		$out = $this->finalizeSession($details);
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
		return $this->makeCall('checkStatus', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
	}

	private function finalizeSession($details){
		return $this->makeCall('finalizeSession', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
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
