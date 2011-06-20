<?php
class ident extends ipx {
	public function __construct($wsdl_spec='apis/ident.xml'){
		parent::__construct($wsdl_spec);
        $this->r = new dbredis();
	}

	public function __call($method, $overrides){
		return $this->makeCall($method, $overrides); 
	}

	public function createSession(){
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

	public function checkStatus(){
		$out = $this->makeCall('checkStatus', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
        if ($out->responseMessage == "Success") {
            $this->setStatus($out->statusCode);
            return $out;
        } else {
            throw new Exception("Ident: check status fail");
        }
	}

    /** 
     * Returns the long form (vodafone) of the consumerId
     */
	private function finalizeSession(){
		$out = $this->makeCall('finalizeSession', array(
			'username'  => $this->getUserName(),
			'password'  => $this->getPassword(),
			'sessionId' => $this->getSessionId()
		));
        return $out;
	}

    private function addSubscriber($id){
        $this->r->sadd('subscribers', $id); 
    }

	private function getSessionId(){
        $x = session_id();
		return $this->r->get('session:'.$x);
	}

	private function setSessionId($session_id){
        $x = session_id();
		$this->r->set('session:'.$x, $session_id);
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

    private function setStatus($code){
        $key = 'status:'.session_id();
        $this->r->set($key, $code);
    }
}
