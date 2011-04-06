<?php
class AuthHelper{
	public function getAuthorizedUsers(){
		$config = config::read('authorized_users', 'auth');
	}
}
