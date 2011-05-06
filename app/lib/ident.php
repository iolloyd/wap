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
		echo '<pre>';
		print_r($out); 
		if (@$out->sessionId) {
			$r = new dbredis();

			// Set the session id for later calls
			$r->set('session:'.session_id(), $out->sessionId);
			$out = $this->checkStatus($details, $out);
			print_r($out);
			$out = $this->finalizeSession($details, $out);
			print_r($out);
			return $out;
		}
	}

	public function initIdentitySession(){
		$details = config::read('defaults', 'ipx');
		$out     = $this->createSession($details);
		return $out;
	}

	private function createSession($details){
		$overrides = array(
			'returnURL' => $details['redirectURL'],
			'username'  => $details['username2'],
			'password'  => $details['password2']
		);
		return $this->makeCall('createSession', $overrides); 
	}

	private function checkStatus($details, $prev_step){
		if ($prev_step->responseMessage == 'Success'){
			$overrides = array(
				'username'  => $details['username2'],
				'password'  => $details['password2'],
				'sessionId' => $prev_step->sessionId
			);
			return $this->makeCall('checkStatus', $overrides); 
		}
	}

	private function finalizeSession($details, $prev_step){
		if ($prev_step->responseMessage == 'Success') {
			echo 'finalizing ident.<br>';
			$r = new dbredis();
			$session_id = $r->get('session:'.session_id());
			echo $session_id; die;
			$overrides = array(
				'username'  => $details['username2'],
				'password'  => $details['password2'],
				'sessionId' => $r->get('session:'.session_id()) 
			);
			return $this->makeCall('finalizeSession', $overrides); 
		}
	}
}
