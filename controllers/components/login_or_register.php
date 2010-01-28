<?php
/**
 * Login or register component
 *
 * For normal use, setup the auth component to have the email as the 'username' field
 * Use the element users/login_or_redirect (copy/create in your own project)
 * A user will then be able to login with their email/username and password
 * OR
 * Register
 *
 * PHP version 4 and 5
 *
 * Copyright (c) 2009, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2009, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi_users
 * @subpackage    mi_users.controllers.components
 * @since         v 1.0 (17-Sep-2009)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * LoginOrRegisterComponent class
 *
 * @uses          Object
 * @package       mi_users
 * @subpackage    mi_users.controllers.components
 */
class LoginOrRegisterComponent extends Object {

/**
 * settings property
 *
 * @var array
 * @access public
 */
	var $settings = array(
		'mode' => 'merge',
		'view' => '/elements/users/login_or_register',
		'altFields' => array(
			'username' => 'username',
			'password' => 'password'
		),
		'loginCallback' => false,
	);

/**
 * initialize method
 *
 * @param mixed $Controller
 * @param array $config array()
 * @return void
 * @access public
 */
	function initialize(&$Controller, $config = array()) {
		$this->Controller =& $Controller;
		$this->Auth =& $Controller->Auth;
		$this->Session =& $Controller->Session;

		if (!array_key_exists('loginCallback', $config) && isset($this->Controller->components['MiUsers.RememberMe'])) {
			$config['loginCallback'] = $this->Controller->components['MiUsers.RememberMe']['loginCallback'];
		}
		$this->settings = array_merge($this->settings, $config);
		$this->__data = $_POST;
	}

/**
 * go method
 *
 * @return void
 * @access public
 */
	function go() {
		if ($this->Auth->user('id')) {
			return $this->_return(true);
		}
		$C =& $this->Controller;
		$userModel = $this->Auth->userModel;
		extract($this->Auth->fields);
		if (empty($this->Controller->data[$userModel]) || !array_key_exists($username, $this->Controller->data[$userModel])) {
			if (!$this->Session->check('LoginOrRegister.data.' . $C->name . '.' . $C->action)) {
				$this->Session->write('LoginOrRegister.data.' . $C->name . '.' . $C->action, $this->Controller->data);
				if ($this->settings['mode'] !== 'merge') {
					$C->data = array();
				}
			}
			return $this->_return(false);
		}
		$data = $this->Controller->data;
		$__data = $this->Auth->hashPasswords($this->__data['data']);
		if (isset($__data[$userModel][$password])) {
			$data[$userModel][$password] = $__data[$userModel][$password];
		}

		if ($data[$userModel]['type'] === 'new') {
			$this->Session->delete('Message.auth');
			$User = $this->Auth->getModel();
			$User->create($data);
			if ($User->save($data) && $this->Auth->login($User->id)) {
				return $this->_return(true);
			}
		} else {
			if ($this->Auth->login($data)) {
				$this->Session->delete('Message.auth');
				return $this->_return(true);
			} else {
				$data[$userModel][$this->settings['altFields']['username']] = $data[$userModel][$username];
				$this->Auth->fields = $this->settings['altFields'];
				if ($this->Auth->login($data)) {
					$this->Session->delete('Message.auth');
					return $this->_return(true);
				}
			}
			$this->Session->setFlash($this->Auth->loginError, 'default', array(), 'auth', true);
		}
		$this->_return(false);
	}

/**
 * return method
 *
 * @param bool $success false
 * @return void
 * @access protected
 */
	function _return($success = false) {
		if (!$success) {
			$this->_render();
			return $success;
		}
		$C =& $this->Controller;
		if ($data = $this->Session->read('LoginOrRegister.data.' . $C->name . '.' . $C->action)) {
			$this->Session->delete('LoginOrRegister.data.' . $C->name . '.' . $C->action);
			if ($this->settings['mode'] === 'merge') {
				$C->data = array_merge($C->data, $data);
			} else {
				$C->data = $data;
			}
		}
		if ($success) {
			$this->_postLogin('form');
		}
		return $success;
	}

/**
 * render method
 *
 * @return void
 * @access protected
 */
	function _render() {
		if ($this->settings['view']) {
			echo $this->Controller->render($this->settings['view']);
			$this->_stop();
		}
	}

/**
 * Called after successfully logging in, or after detecting and successfully processing a remember me cookie
 *
 * If the user logged in normally and requested remember me, write the cookie
 * In both cases, check for a loginCallback, which is either a requestAction url, or params to be passed to
 * call_user_func_array
 *
 * @param mixed $mode  either 'form' or 'cookie'
 * @return void
 * @access protected
 */
	function _postLogin($mode = null) {
		$C =& $this->Controller;

		if (empty($this->settings['loginCallback'])) {
			return;
		}
		if (is_array($this->settings['loginCallback'])) {
			$keys = array_keys($this->settings['loginCallback']);
			if (is_numeric($keys[0])) {
				return call_user_func_array($this->settings['loginCallback'], array($this->Auth->user(), $mode));
			}
		}
		return $this->requestAction($this->settings['loginCallback'], compact('mode'));
	}
}