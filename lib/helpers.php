<?php
class helpers {
	public static function pairs2EqStrings(array $a){
		$out = array();
		foreach ($a as $k => $v) {
			$out[] = $k.'='.$v;
		}
		return $out;
	}

	public static function editLink($collection, $object){
		return "<a href='/$collection/edit/{$object['id']}'>Edit</a>";
	}

	public static function getAppEnv() {
		return (in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1','::1')))
			? 'dev'
			: 'prod';
	}

	public static function generatePassword($len=4) {
		$pwd     = "";
		//$choices = "0123456789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
		$choices = "0123456789";
		while (strlen($pwd) < $len) { 
			$chr = substr($choices, mt_rand(0, strlen($choices)-1), 1);
			if (!strstr($pwd, $chr)) { 
				$pwd .= $chr;
			}
		}
		return $pwd;
	}

	public static function logHit($controller, $method){
		$r = new dbredis();
		$c_m = $controller.':'.$method;
		$r->incr($c_m.':'.date('y:m:d:h:i'));
		$r->incr($c_m.':'.date('y:m:d:h'));
		$r->incr($c_m.':'.date('y:m:d'));
		$r->incr($c_m.':'.date('y:m'));
		$r->incr($c_m.':'.date('y'));
		$r->incr($c_m);
	}

	public static function unCamelize($word){
		$out = preg_replace('/_(\w)(.*)$/', strtoupper('${1}').'${2}', $word);
		return $out;
	}

	public static function phone($prefix, $phone){
		return $prefix.ltrim($phone, '0');
	}

	public static function includeCss(array $css_files){
		foreach ($css_files as $css_file) {
			echo "<link rel='stylesheet' href='/css/{$css_file}.css'>";
		}
	}

	public static function formEntry($form_name, $name, $obj){
		if (empty($obj['view'])) {
			$type = 'input';
		} else {
			$type = $obj['view'];
		}
		$method = 'formEntry'.$type;
		try {
			echo self::$method($form_name, $name, $obj);
		} catch (exception $e) {
			echo $e->getMessage . 'That view no existe';
			exit;
		}
	}

	private static function formEntryInput($form_name, $name, $obj) {
		return "<p>".$obj['label'].
				"
			    <input name='$name' value=''/></p>
		";
	}

	private static function formEntrySelect($form_name, $name, $obj) {
		$options = array();
		$members = $obj['members'];
		if (!is_array($members)) {
			require_once FORMSLOGICDIR.'/'.$form_name.'.php';
			$members = call_user_func_array(
				array(new $form_name(), $members), 
				array()
			);
		} 

		foreach($members as $k => $v) {
			$options[] = "<option value='$k'>$v</option>";
		}

		$options = implode('', $options);
		return $obj['label'] . "
			<select name='$name'> $options </select>
		";
	}

	public static function showForm($name, $data=array()){
		$config = config::readForm($name);
		$fields = $config['fields'];
		foreach ($fields as $n => $f) {
			echo self::formEntry($name, $n, $f);
		}
	}

	public static function noBlanks($array){
		$out = array();
		foreach ($array as $a) {
			if ($a) $out[] = $a;
		}
		return $out;
	}

	/**
	 * Convert a date string to an integer
	 * @param string $date
	 * @param int $len
	 * return int $date; 
	 * <p>
	 * Given a string such as '2011:03:23' it will return 20110323.
	 * Optionally you can restrict the size of the int by choosing 
	 * a length, which by default is set to 8 to return the year, month
	 * and day. For example
	 *     <code>
     *         $response_1 = convertDateToInt('2011:03:04:12:39');
     *         $response_2 = convertDateToInt('2011:03:04:12:39', 6);
	 *         echo $response_1;
	 *         echo $response_2;
	 *     </code>
	 *
	 *     returns
	 *
	 *     <code>
	 *         20110304
	 *         201103
	 *     </code>
	 * </p>
	 */
	public static function convertDateToInt($date, $len=8){
		$no = array(':', '.', '-', '/', '\\', ' ');
		$date = str_replace($no, '', $date);
		$date = substr($date, 0, $len);
		return $date;
	}

	/**
	 * Converts a std object to an array.
	 * @param object $obj
	 * @return array $ob
	 */
	public static function convertStdToArray($obj){
		$out = array();
		foreach ($obj as $k => $v) {
			$out[$k] = $v;
		}
		return $out;
	}

	public static function cleanPhoneNumber($phone, $prefix='') {
		$phone = trim($phone);
		$phone = ltrim($phone, '+');
		$phone = ltrim($phone, '0');
		$phone = preg_replace('/^31/', '', $phone);
		$phone = str_replace('-','', $phone);
		$phone = str_replace(' ','', $phone);
		$phone = $prefix.''.$phone;
		return $phone;
	}

	public static function getDateRange($start, $end) {
		$start = strftime($start);
		$end   = strftime($end);
	}

	public static function beforeNow($unit, $amount){
        $units = array(
			'second' => 1,
		    'minute' => 60,
			'hour'   => 60 * 60,
			'day'    => 60 * 60 * 24,
			'week'   => 60 * 60 * 24 * 7
		);
		return date("Ymdhis", time() - $units[$unit]);
	}
}
