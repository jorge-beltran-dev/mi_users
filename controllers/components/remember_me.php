<?php
/**
 * A simple component to automatically remember you when you login,
 * 	so you don't need to login on every visit
 *
 * Include BEFORE the auth component, as otherwise it can't auto detect a user logging in normally
 * and the auth component will redirect users before the cookie can be detected
 *
 * PHP versions 4 and 5
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
 * @since         v 1.0 (11-Sep-2009)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * RememberMeComponent class
 *
 * @uses          Object
 * @package       mi_users
 * @subpackage    mi_users.controllers.components
 */

class RememberMeComponent extends Object {

/**
 * name property
 *
 * @var string 'RememberMe'
 * @access public
 */
	var $name = 'RememberMe';

/**
 * settings property
 *
 * Note that this component runs before you can edit the Auth component - hence any overrides are
 * specified here
 *
 * auth settings any fields to override auth component *defaults*
 * 	logoutAction - the name of the logout action to be able to auto-delete the remember me cookie
 * cookie settings passed directly to the cookie component when loaded
 * rememberMeField the component expects Controller->data[<your user model name>][rememberMeField] to be set
 * loginCallback either a requestAction url to call after logging in (normally, or by cookie) or params
 * 	to pass to call_user_func_array
 * 	the function will recieve the current user session data, and how they logged in (cookie, or form)
 * @var array
 * @access public
 */
	var $settings = array(
		'auth' => array(
			'loginAction' => array('controller' => 'users', 'action' => 'login'),
			'logoutAction' => array('controller' => 'users', 'action' => 'logout'),
		),
		'cookie' => array(
			'name' => 'RememberMe',
			'time' => '+2 weeks',
		),
		'rememberMeField' => 'remember_me',
		'loginCallback' => false,
		'debug' => false
	);

/**
 * authUserId property
 *
 * This is the user id according to the Auth component
 *
 * @var bool false
 * @access private
 */
	var $__authUserId = false;

/**
 * initialize method
 *
 * (Must) Run before the Auth component initialize
 * If they're trying to logout - delete the remember me cookie if it exists and disable further processing
 * If there's already a user logged in, there's nothing this component needs to do so disable the component
 * Else check for cookie data, and attempt to log in if found
 *
 * @param mixed $Controller
 * @param array $config array()
 * @return void
 * @access public
 */
	function initialize(&$C, $config = array()) {
		if ((Configure::read() && !$this->_setupCheck($C)) || !empty($C->params['requested'])) {
			$this->enabled = false;
			return;
		}

		$this->settings = Set::merge($this->settings, $config);
		$this->Controller =& $C;
		$this->_setupAuth();
		$url = Router::normalize($C->params['url']['url']);
		if ($url === Router::normalize($this->settings['auth']['logoutAction'])) {
			//$this->Controller->Auth->logout();
			$this->_cookieDestroy();
			$this->log('Disabling - logging out');
			$this->enabled = false;
		} elseif ($this->__authUserId) {
			$this->log('Disabling - User already logged in');
			$this->enabled = false;
		} elseif ($this->_cookieAuth()) {
			$this->log('Disabling - Nothing else to do');
			$this->enabled = false;
		} elseif ($url !== Router::normalize($this->settings['auth']['loginAction'])) {
			$this->log('Disabling - Not the login action');
			$this->enabled = false;
		}
	}

/**
 * beforeRedirect method
 *
 * If there was no user id in initialize, and there is when redirecting; it's because the Auth
 * component, during Auth::initialize, just logged them in. Call postLogin to write the cookie
 * if requested
 *
 * @param mixed $Controller
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @return void
 * @access public
 */
	function beforeRedirect(&$C, $url, $status, $exit) {
		if (!isset($this->Controller)) {
			$this->initialize($C);
		}
		if (!$this->__authUserId && $this->Auth->user('id')) {
			$this->_postLogin('form');
		}
		return $url;
	}

/**
 * log method
 *
 * @param mixed $message
 * @return void
 * @access public
 */
	function log($message) {
		if (!$this->settings['debug']) {
			return;
		}
		parent::log($message, 'remember_me');
	}

/**
 * cookieAuth method
 *
 * Check for a remember me cookie if there's not an (encrypted) cookie with the right name bail early
 *
 * @return void
 * @access protected
 */
	function _cookieAuth() {
		if (empty($_COOKIE[$this->settings['cookie']['name']])) {
			$this->log('No cookie received with name ' . $this->settings['cookie']['name']);
			return;
		}

		$this->_setupCookie();
		$cookie = $this->Controller->Cookie->read($this->Auth->userModel);
		if (empty($cookie['id']) || empty($cookie['token'])) {
			$this->log('Cookie missing id or token');
			$this->log($cookie);
			$this->_cookieDestroy();
			return;
		}

		$currentToken = $this->_token($cookie['id']);
		if ($cookie['token'] !== $currentToken) {
			$this->log('Cookie tokens do not match');
			$this->log(' expected: ' . $currentToken);
			$this->log(' received: ' . $cookie['token']);
			$this->_cookieDestroy();
		} elseif ($this->Auth->login($cookie['id'])) {
			$this->log('Cookie login successful');
			$this->_postLogin('cookie');
			return true;
		}
	}

/**
 * cookieDestroy method
 *
 * Delete the remember me cookie if it exists
 *
 * @return void
 * @access protected
 */
	function _cookieDestroy() {
		if (empty($_COOKIE[$this->settings['cookie']['name']])) {
			$this->log('No cookie found to be destroyed');
			return;
		}
		$this->_setupCookie();
		$this->log('destroying cookie');
		$this->Controller->Cookie->destroy();
	}

/**
 * cookieWrite method
 *
 * For the currently logged in user, write a remember me cookie
 *
 * @return void
 * @access protected
 */
	function _cookieWrite() {
		$id = $this->Auth->user('id');
		$token = $this->_token($id);
		$data = compact('id', 'token');

		$this->_setupCookie();
		$this->log('writing cookie:');
		$this->log($data);
		$this->Controller->Cookie->write($this->Auth->userModel, $data);
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

		if ($mode === 'form' && !empty($C->data[$this->Auth->userModel][$this->settings['rememberMeField']])) {
			$this->_cookieWrite();
		}
		if (!$this->settings['loginCallback']) {
			return;
		}
		$this->log('processing loginCallback');
		$this->log($this->settings['loginCallback']);
		if (is_array($this->settings['loginCallback'])) {
			$keys = array_keys($this->settings['loginCallback']);
			if (is_numeric($keys[0])) {
				return call_user_func_array($this->settings['loginCallback'], array($this->Auth->user(), $mode));
			}
		}
		return $this->requestAction($this->settings['loginCallback'], compact('mode'));
	}

/**
 * setupAuth method
 *
 * @return void
 * @access protected
 */
	function _setupAuth() {
		$this->Auth =& $this->Controller->Auth;
		foreach($this->settings['auth'] as $field => $val) {
			$this->Auth->$field = $val;
		}
		$this->__authUserId = $this->Auth->user('id');
	}

/**
 * check the component is setup correctly/before Auth
 *
 * @param Controller $C
 * @return bool
 * @access private
 */
	function _setupCheck($C) {
		$components = array_flip(array_keys($C->components));
		if (isset($components['Auth']) && $components['Auth'] > $components['MiUsers.RememberMe']) {
			return true;
		}
		if (in_array('Auth', $components)) {
			//trigger_error('RememberMeComponent: How did we get this far');
			return true; // assumed. shouldn't be possible to get here
		}
		if ($C->name !== 'CakeError') {
			trigger_error('RememberMeComponent: The RememberMe component must be included before the Auth component to operate correctly');
			debug ($components); //@ignore
		}
		return false;
	}

/**
 * cookie method
 *
 * @return void
 * @access private
 */
	function _setupCookie() {
		if (isset($this->Controller->Cookie)) {
			if ($this->Controller->Cookie->name == $this->settings['cookie']['name']) {
				return;
			} else {
				$this->Controller->Cookie->initialize($this->Controller, $this->settings['cookie']);
			}
		} else {
			App::import('Component', 'Cookie');
			$this->Controller->Cookie = new CookieComponent();
			$this->Controller->Cookie->initialize($this->Controller, $this->settings['cookie']);
		}
	}

/**
 * token method
 *
 * Generate a unique token, based on the user's password and username
 *
 * @param mixed $id user id
 * @return string token
 * @param int $length 100
 * @access protected
 */
	function _token($id, $length = 100) {
		$User = $this->Auth->getModel();
		$conditions = array($User->primaryKey => $id);
		$fields = $this->Auth->fields;
		$recursive = -1;
		$data = $User->find('first', compact('conditions', 'fields', 'recursive'));
		$return = Security::hash(serialize($data), null, true);
		while(strlen($return) < $length) {
			$return .= Security::hash($return, null, true);
		}
		$return = substr($return, 0, $length);
		$this->log('Token Generated: ' . $return);
		return $return;
	}
}