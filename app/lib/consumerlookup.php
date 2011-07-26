<?php
class consumerlookup extends ipx {

	public function __construct($wsdl_spec='apis/consumer_lookup.xml'){
		parent::__construct($wsdl_spec);
		$this->r = new dbredis();
	}

    public function __call($method, $overrides){
        return $this->makeCall($method, $overrides); 
    }


}

