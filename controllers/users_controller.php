<?php
/**
 * UsersController class
 *
 * @uses          AppController
 * @package       mi_users
 * @subpackage    mi_users.controllers
 */
class UsersController extends AppController {
/**
 * name property
 *
 * @var string 'Users'
 * @access public
 */
	var $name = 'Users';
/**
 * components property
 *
 * @var array
 * @access public
 */
	var $components = array('Auth');

/**
 * beforeFilter method
 *
 * Allow access to the recovery methods if debug is enabled
 * Set the black hole to prevent white-screen-of-death symptoms for invalid form submissions.
 *
 * @return void
 * @access public
 */
	function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			'register',
			'forgotten_password',
			'reset_password',
			'confirm_account',
			'logout'
		);

		if ($this->action == 'login' && $callback = Configure::read('Users.legacy_password_callback')) {
			$this->__oldPassword = $this->data['User'][$this->Auth->fields['password']];
		}
	}

/**
 * login method
 *
 * @return void
 * @access public
 */
	function login() {
		if($this->Auth->user('id')) {
			if(!empty($this->data['User']['redirect'])){
				$this->redirect($this->data['User']['redirect']);
			} else {
				$this->redirect($this->Auth->redirect());
			}
		} elseif($this->data && $callback = Configure::read('Users.legacy_password_callback')) {
			$OldPassword = ClassRegistry::init('Users.OldPassword');
			$id = $OldPassword->field('id', array(
				'username' => $this->data['User'][$this->Auth->fields['username']],
				'password' => call_user_func($callback, $this->__oldPassword)
			));
			if ($id) {
				$uId = $this->User->field('id', array(
					$this->Auth->fields['username'] => $this->data['User'][$this->Auth->fields['username']]
				));
				$this->User->id = $uId;
				$password = $this->Auth->password($this->__oldPassword);
				$this->User->saveField($this->Auth->fields['password'], $password,
					array('callbacks' => false));
				if ($this->Auth->login($uId)) {
					$OldPassword->delete($id);
					return $this->redirect($this->Auth->redirect());
				}
			}
		}
	}
}