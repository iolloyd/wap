<?php
echo 'ok'; die;
ob_start();
$root = dirname(dirname(__FILE__));
include($root.'/lib/setup.php');
run();

