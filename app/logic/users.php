<?php
class users extends controller {

	/**
	 * This is called by default if not specified
	 * and will show all users in the database;
	 */
	public function index($request){
		$fields_to_show = array('name', 'age', 'city', 'email');
		$users = $this->r->getAll('users');
		echo 'users<br>';
		print_r($users);
		require $this->template('users/all');
	}

	public function show($request){
		$id = $request['vars'][0];
	}

	public function add($request, $datii = array()) {
		print_r($request);
		require $this->template('users/edit');
	}

	public function addPost($request){
		$id = $request['vars'][0];
		$this->r->save('phone', $id, $_POST);
		$user = $this->r->hgetall('phone:'.$id);
		$user['time_updated'] = date('Y:M:D:h:i:s');
		require $this->template('users/edit', array('id' => $id));
	}

	public function edit($request){
		$id   = $request['vars'][0];
		$user = $this->r->hgetall('phone:'.$id);
		require $this->template('users/edit');
	}
}
