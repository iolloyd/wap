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
			echo 'You are not authorized to be here';
			$this->template('auth/login');
		}
	}

    public function storedPwd($user, $pwd){
        $salt = $this->getSalt();
        $pwd  = sha1($salt.$pwd);
        $this->r->hset($user, 'pwd', $pwd);
    }

    public function checkPwd($user){
        $salt = $this->getSalt();
        $pwd = $this->r->hget($user);
        return $pwd == sha1($salt.$pwd);
    }

    public function getAuthorizedUsers(){
        $users = $this->r->hgetk
    }

    private function getSalt(){
        return 'mnilmailfatiwIalfTgSe';
    }
}
