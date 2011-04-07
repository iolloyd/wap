<?php
class ident extends ipx {
	public function __construct($wsdl_spec='ident.xml'){
		parent::__construct($wsdl_spec);
	}

	public function createSession($overrides){
		return $this->makeCall('createSession', $overrides); 
	}

	public function checkStatus($overrides){
		return $this->makeCall('checkStatus', $overrides); 
	}

	public function finalizeSession($overrides){
		return $this->makeCall('finalizeSession', $overrides); 
	}
}
