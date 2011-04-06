<?php
/**
 * PHP interface to connect to the IPX SMS soap service
 *
 * <p>
 * Provides a class that allows us to interact with the IPX soap
 * web service. The current (1.2) definition currently works with
 *     -> identificationAPI31.wsdl
 *
 * which can be found at 
 *     -> http://www.ipx.com/api/services/identificationapi31
 * </p>
 *
 * @author Lloyd Moore <manchesterboy@gmail.com
 * @version 2.1
 * @package IPXSMS
 * 
 */
class ident extends api {

	public function __construct($wsdl_spec){
		parent::__construct($wsdl_spec);
	}

	public function createSession($overrides){
		return $this->makeCall($this->client, 'createSession', $this->wsdl_file, $overrides); 
	}

	public function checkStatus($overrides){
		return $this->makeCall($this->client, 'checkStatus', $this->wsdl_file, $overrides); 
	}

	public function finalizeSession($overrides){
		return $this->makeCall($this->client, 'finalizeSession', $this->wsdl_file, $overrides); 
	}
}
