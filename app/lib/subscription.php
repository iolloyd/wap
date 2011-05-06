<?php
class subscription extends ipx {

	public function __construct($wsdl_file='apis/subscription.xml'){
		parent::__construct($wsdl_file);
	}

	public function __call($method, $overrides) {
		return $this->makeCall($method, $overrides);
	}
}
