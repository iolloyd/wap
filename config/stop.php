<?php 
class stop extends controller {
	/**
	 * This is called by IPX to stop a subscription
	 * The subscriptionId and subscriptionId are sent to terminate
	 * The user's subscription.
	 *
	 * The username and password are required.
	 */
	public function index($request) {
		$out = $this->sms->terminateSubscription(array(
			'subscriptionId' => $subscription_id,
			'consumerId'     => $consumer_id
		));
		$this->request('activate/index');
	}


}
