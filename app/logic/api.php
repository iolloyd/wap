<?php 
$sms   = new sms();
$redis = new Predis\Client();
$redis->connect();
$method = str_replace('/','',$_SERVER['REQUEST_URI']);
$method = preg_replace('/\?.*$/','',$method);

if ($method == 'register'){
	$phone = $_GET['phone'];
	$redis->sadd('sent', $phone);
	$out   = $sms->sendSms($phone);
	$msgid = $out->messageId;
	$redis->set('1:'.$phone, $msgid);
} 

if ($method == 'activate') {
    $phone = $_GET['OriginatorAddress'];
	$msgid = $_GET['MessageId'];	
	exec("php activate.php $phone $msgid > /dev/null &");	
}

echo "<DeliveryResponse ack='true'/>";

?>
