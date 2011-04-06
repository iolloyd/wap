<?
/**
 * Given a days period, this code will attempt to rebill
 * all those people that were billed previously.
 */

$days = $argv[1];

// Get the people who were rebilled more than {days} ago
$time = $days * 24 * 60 * 60;
$now  = time();
<<<<<<< HEAD
$billed = $this->r->zrangebyscore('evt:billed', 0, $now - $time);
=======
$billed_1_numbers = $this->r->zrangebyscore('evt:billed1', 0, $now - $time);
$billed_x_numbers = $this->r->zrangebyscore('evt:billedx', 0, $now - $time);
>>>>>>> e2cbc79... added code to track who has been billed


// Send them another billing sms depending on how 
// many days they were rebilled


