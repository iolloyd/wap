<?php 
class activate extends controller {
	/**
	 * This method is called by IPX providing the details * in the GET 
	 * request. When called, we need to provide a '200 OK response and 
	 * xml that states ack=true in the template. 
	 *
	 * We then need to wait a few seconds to allow time for PX to log 
	 * the 200 OK response before we make an attempt to create a subscription. 
	 *
	 * To do this we fork a process to allow us to both provide the response 
	 * and then after the delay initiate the createSubscription request.
	 */
	public function index($request) {
		$phone   = $_GET['OriginatorAddress'];
		$msgid   = $_GET['MessageId'];
		$message = trim($_GET['Message']);
		$message = strtolower($message);
		$ipcheck = new ipcheck();

		if ($ipcheck->maxPerTimeLimitExceeded(IP, 60)) {
			return true;
		}

		if ($message == "win aan") {
			$six_weeks         = 6 * 7 * 24 * 60 * 60 ;
			$last_cancellation = $this->r->get('cancellation:'.$phone); 

			if ((time() - $last_cancellation) < $six_weeks) {
				return false;
			}

			exec("php ".dirname(__FILE__)."/do_activate.php $phone $msgid > /dev/null &", $a, $b);
			$this->template('activate/index');
		}
	}
}
