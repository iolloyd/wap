<?php
class register extends controller {
	public function phone($request){
		$sms   = new sms();
		$phone = $request['vars'][0];
		$out   = $sms->sendSms($phone);
		$msgid = $out->messageId;
		$this->r->recordEvent('enter_number', $phone, $out);
		$this->template('register/sent');
	}
}

