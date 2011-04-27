<? 
class auth extends controller {
	var $layout = 'auth';
	public function login(){
		$template = config::read('auth_template', 'auth');
		$this->template($template);
	}

	public function loginPost($request){
		$cfg = config::read('authorized_users', 'auth');
		if ($_POST['pass'] == $cfg[$_POST['user']]) {
			$_SESSION['is_authorized'] = true;
			$this->redirect('/admin');
		} else {
			echo 'You are not authorized to be here';
			$this->template('auth/login');
		}
	}
}
