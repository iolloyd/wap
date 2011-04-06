<?php
$phone = $argv[1];
$msgid = $argv[2];

$dir   = dirname(dirname(dirname(__FILE__)));
require_once($dir.'/lib/dbredis.php');
require_once($dir.'/app/lib/sms.php');
require_once($dir.'/lib/predis/lib/Predis.php');
require_once($dir.'/lib/config.php');
require_once($dir.'/lib/helpers.php');

$sms  = new sms($dir.'/config/sms_config.ini');
$red  = new dbredis();
$time = date('Y:m:d:h:i:s', time());

sleep(3);

$red->publish('evt.create_sub.TX'.$phone, $msgid);

$details        = $red->hgetall('evt:create_sub:'.$phone);
$current_status = $details['responseMessage'];

if ($current_status == 'Success') { 
	return false;
}

$out = $sms->createSubscription(array( 
	'consumerId'  => $phone,
	'referenceId' => $msgid,
	'tariffClass' => 'EUR300'
));

$red->recordEvent('create_sub', $phone, $out);

$confirm_response = $sms->sendConfirmationSms($phone);
$red->recordEvent('send_confirm_sms', $phone, $confirm_response);

$pwd = helpers::generatePassword();
$red->lpush('pwd:'.$phone, $pwd);

$out = $sms->sendBillingSms($phone, $out->subscriptionId, 'EUR300', $pwd);

$red->recordEvent('send_billing_sms', $phone, $out);
$red->recordEvent('billed', $phone, array(
));
