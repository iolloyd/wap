<?php
/**
 * PHP interface to connect to the IPX SMS soap service
 *
 * <p>
 * Provides a class that allows us to interact with the IPX soap
 * web service. The current (1.2) definition currently works with
 *     -> subscriptionAPI31.wsdl
 *
 * which can be found at 
 *     -> http://www.ipx.com/api/services/subscriptionapi31
 * </p>
 *
 * @author Lloyd Moore <lloyd@lloydsays.com>
 * @version 1.2
 * @package IPXSMS
 * @class SMS
 * @abstract Provides an interface to send commands to the sms server.
 * 
 */
class sub extends ipx {

	/**
	 * @param string $config_file 
	 * @return void
	 */
	public function __construct($wsdl_file){
		parent::__construct($wsdl_file);
	}

	/**
	 * @param array $overrides the default settings overrides
	 * @return string 
	 */
	public function authorizePayment($overrides){
		return $this->makeCall('authorizePayment', $overrides);
	}

	/**
	 * @param array $overrides the default settings overrides
	 * @return string 
	 */
	public function createSubscription($overrides){
		return $this->makeCall('createSubscription', $overrides); 
	}

	/**
	 * @param array $overrides the default settings overrides
	 * @return string 
	 */
	public function finalizeSubscription($overrides){
		return $this->makeCall('finalizeSubscriptionSession', $overrides); 
	}

	/**
	 * @param array $overrides the default settings overrides
	 * @return string 
	 */
	public function getSubscriptionStatus($overrides){
		return $this->makeCall('getSubscriptionStatus', $overrides); 
	}

	/**
	 * @param array $overrides the default settings overrides
	 * @return string 
	 */
	public function capturePayment($overrides){
		return $this->makeCall('capturePayment', $overrides);
	}

	/**
	 * @param array $overrides the default settings overrides
	 * @return string 
	 */
	public function terminateSubscription($overrides){
		return $this->makeCall('terminateSubscription', $overrides);
	}
}
