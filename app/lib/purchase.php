<?php
/**
 * Order, Create, Charge, Terminate
 */
class purchase extends ipx {
	public function __construct($wsdl_spec='apis/purchase.xml'){
		parent::__construct($wsdl_spec);
	}

	public function __call($method, $overrides){
		return $this->makeCall($method, $overrides); 
	}

}
