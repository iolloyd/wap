<?php
class sub extends ipx {

	public function __construct($wsdl_file='sub.xml'){
		parent::__construct($wsdl_file);
	}

	/**
	 * consumerId
	 * subscriptionId
	 */
	public function authorizePayment(array $overrides){
		return $this->makeCall('authorizePayment', $overrides);
	}

	/**
	 * consumerId
	 * referenceId
	 */
	public function createSubscription(array $overrides){
		return $this->makeCall('createSubscription', $overrides); 
	}

	/**
	 * sessionId
	 */
	public function finalizeSubscription(array $overrides){
		return $this->makeCall('finalizeSubscriptionSession', $overrides); 
	}

	/**
	 * consumerId
	 * subscriptionId
	 */
	public function getSubscriptionStatus(array $overrides){
		return $this->makeCall('getSubscriptionStatus', $overrides); 
	}

	/**
	 * sessionId
	 */
	public function capturePayment(array $overrides){
		return $this->makeCall('capturePayment', $overrides);
	}

	/**
	 * consumerId
	 * subscriptionId
	 */
	public function terminateSubscription(array $overrides){
		return $this->makeCall('terminateSubscription', $overrides);
	}
}
