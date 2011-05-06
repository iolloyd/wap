<?php
class sms extends ipx {
	var $wsdl_file;

	/**
	 * @param string $config_file 
	 * @return void
	 */
	public function __construct(){
		$this->wsdl_file = 'apis/sms.xml';
		$this->init($this->wsdl_file);
	}

	public function sendBillingSms($phone, $reference_id, $tariff='EUR300', $password=false) {
		$text = config::read('billing', 'messages');
		if ($password) {
			$text = str_replace('{PASSWORD}', $password, $text);
		}

		$overrides = array(
			'destinationAddress' => $phone,
			'userData'           => $text,
			'tariffClass'        => $tariff,
			'referenceId'        => $reference_id
		);
		return $this->makeCall('send', $overrides);
	}

	public function sendConfirmationSms($phone) {
		$text = config::read('confirm', 'messages');
		$overrides = array(
			'destinationAddress' => $phone,
			'userData'           => $text,
			'tariffClass'        => 'EUR0'
		);
		return $this->makeCall('send', $overrides);
	}

	public function sendForgottenPassword($phone) {
		$text = config::read('forgotten', 'messages');
		$overrides = array(
			'destinationAddress' => $phone,
			'userData'           => $text,
			'tariffClass'        => 'EUR300'
		);

		return $this->makeCall('send', $overrides);
	}

	public function sendSms($phone, $text, $tariff='EUR0'){
		$overrides = array(
			'destinationAddress' => $phone,
			'userData'           => $text,
			'tariffClass'        => $tariff
		);

		return $this->makeCall('send', $overrides);
	}
}
