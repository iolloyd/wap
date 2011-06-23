<? 
class auth extends controller {
	var $layout = 'auth';
	public function login(){
		$template = config::read('auth_template', 'auth');
		$this->template($template);
	}

	public function loginPost($request){

		// The file that has authorized users information
		$cfg = config::read('authorized_users', 'auth');
		if ($_POST['pass'] == $cfg[$_POST['user']]) {
			$_SESSION['is_authorized'] = true;
			$this->redirect('/admin');
		} else {
            $class  = $cfg['_lookup']['controller'];
            $method = $cfg['_lookup']['method'];
            return call_user_func_array(array($class, $method), array());
			echo 'You are not authorized to be here';
			$this->template('auth/login');
		}
	}

    public function storePassword($user, $pwd){
        $salt = $this->getSalt();
        $pwd  = sha1($salt.$pwd);
        $this->r->hset($user, 'password', $pwd);
    }

    public function checkCredentials($user, $entered_password){
        $salt            = $this->getSalt();
        $stored_password = $this->r->hget($user, 'password');
        return $stored_password == sha1($salt.$entered_password);
    }

    public function isAuthorizedUser($user){
        $users = $this->r->smembers('authorized_users');
        list($user, $pwd) = explode(':', $user);
        return $this->checkCredentials($user, $pwd);
    }

    private function getSalt(){
        return config::read('salt', 'auth');
    }

    public function signout(){
        $_SESSION['is_authorized'] = 0;
        $this->redirect('/main/index');
    }

}
