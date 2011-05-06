<?php
require_once(dirname(dirname(__FILE__)).'/lib/qedis.php');
$x = new qedis('localhost', '6379');
$x->r->flushall();
die;
$x->add('user', array(
	'name' => 'lloyd', 'age'  => '40',
	'hair' => 'bald'
));
$x->add('user', array(
	'name' => 'jason',
	'age'  => '39',
	'hair' => 'short'
));
$x->add('user', array(
	'name' => 'jackie',
	'age'  => '35',
	'hair' => 'short'
));
echo '......';
$out = $x->find(array('hair' => 'short', 'name' => 'jason'));
print_r($out);
