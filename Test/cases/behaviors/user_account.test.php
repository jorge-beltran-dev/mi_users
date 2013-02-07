<?php
/**
 * Test case for user_account behavior
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright            Copyright (c) 2008, Andy Dawson
 * @link                 www.ad7six.com
 * @package              mi
 * @subpackage           mi.tests.cases.behaviors
 * @since                v 1.0
 * @modifiedBy           $LastChangedBy$
 * @lastModified         $Date$
 * @license              http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * MessageList class
 *
 * @uses                 CakeTestModel
 * @package              mi
 * @subpackage           mi.tests.cases.behaviors
 */

App::import('Model', 'MiEmail.MiEmail');
App::import('Component', 'Security');
Mock::generate('MiEmail');

class User extends CakeTestModel {

/**
 * actsAs property
 *
 * @var array
 * @access public
 */
	var $actsAs = array('MiUsers.UserAccount' => array(

	));
}

/**
 * MiUsersTestCase class
 *
 * @uses                 CakeTestCase
 * @package              mi_users
 * @subpackage           mi_users.tests.cases.behaviors
 */
class MiUsersTestCase extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	var $fixtures = array('plugin.mi_users.user', 'plugin.mi_users.email');
	
/**
 * start method
 *
 * @return void
 * @access public
 */
	function start() {
		parent::start();
		Configure::write('Security.salt', 'JfIxfs2guVoUubWDYhG93b0qyJfIxfs2guwvniR2G0FgaC9mi');
	}
	
	function startTest() {
		$this->User = new User();
		$this->MockMiEmail = new MockMiEmail();
		//$this->MockMiEmail->setReturnValue('send', true);
		ClassRegistry::addObject('MiEmail', $this->MockMiEmail);
	}
	
	function endTest() {
		ClassRegistry::flush();
	}
	
	function hashPwd($password) {
		return Security::hash(Configure::read('Security.salt') . $password);
	}
	
	function hasError($field, $message) {
		$expected = array($field => $message);
		$test = array_diff_assoc($expected, $this->User->invalidFields());
		return empty($test);
	}

/**
 * testFind method
 *
 * @return void
 * @access public
 */
	function testValidationRules() {
		$this->User->Behaviors->attach('MiUsers.UserAccount', array(
			'passwordPolicy' => 'normal'
		));
		
		// password unset
		$data = array('User' => array(
			'confirm' => 'dummy'
		));
		$this->User->set($data);
		$this->assertTrue($this->hasError('password', 'This field cannot be left blank'));
		
		// password empty
		$data = array('User' => array(
			'password' => '', 
			'confirm' => 'dummy'
		));
		$this->User->set($data);
		$this->assertTrue($this->hasError('password', 'This field cannot be left blank'));
		
		// password length
		$data = array('User' => array(
			'password' => $this->hashPwd('short'), 
			'confirm' => 'short'
		));
		$this->User->set($data);
		$this->assertTrue($this->hasError('password', 'tooShort'));
		
		// password mismatch
		$data = array('User' => array(
			'password' => $this->hashPwd('test123'), 
			'confirm' => 'test123a'
		));
		$this->User->set($data);
		$this->assertTrue($this->hasError('confirm', 'notSame'));
		
		// password number
		$data = array('User' => array(
			'password' => $this->hashPwd('testwithoutnumber'), 
			'confirm' => 'testwithoutnumber'
		));
		$this->User->set($data);
		$this->assertTrue($this->hasError('password', 'number'));
		
		// password contains username
		$data = array('User' => array(
			'username' => 'test',
			'password' => $this->hashPwd('testandtest'), 
			'confirm' => 'testandtest'
		));
		$this->User->set($data);
		$this->assertTrue($this->hasError('password', 'containsUsername'));
	}
	
	function testPasswordGeneration() {
		$this->User->Behaviors->attach('MiUsers.UserAccount', array(
			'passwordPolicy' => 'strong'
		));
		
		$data = array('User' => array(
			'username' => 'test',
			'generate' => true
		));
		$this->User->set($data);
		$random = $this->User->generatePassword();
		$data['User']['password'] = $this->hashPwd($random);
		$data['User']['confirm'] = $random;
		$this->User->set($data);
		
		$this->assertTrue($this->User->validates());
		
		// respect additional validation rules
		$this->User->validate['username'] = array(
			'tooShort' => array(
				'rule' => array('minLength', 8)
			)
		);
		
		$data = array('User' => array(
			'username' => 'test',
			'generate' => true
		));
		
		$this->User->set($data);
		$this->assertTrue($this->hasError('username', 'tooShort'));
	}
	
	function testRegister() {
		// validation issue
		$data = array('User' => array(
			'username' => 'test_first',
			'password' => '',
			'confirm' => 'dummy'
		));
		
		list($return, $message) = $this->User->register($data);
		$this->assertFalse($return);
		
		$user = $this->User->findByUsername('test_first');
		$this->assertTrue(empty($user));
		
		// password generation
		$data = array('User' => array(
			'username' => 'test_second',
			'generate' => true
		));
		
		list($return, $message) = $this->User->register($data);
		$this->assertTrue($return);
		
		$user = $this->User->findByUsername('test_second');
		$this->assertFalse(empty($user));
		
		//user inserted password
		$data = array('User' => array(
			'username' => 'test_third',
			'password' => $this->hashPwd('Foobar123'),
			'confirm' => 'Foobar123',
		));
		
		list($return, $message) = $this->User->register($data);
		$this->assertTrue($return);
		
		$user = $this->User->findByUsername('test_third');
		$this->assertFalse(empty($user));
	}
	
	function testForgottenPassword() {
		$this->User->Behaviors->attach('MiUsers.UserAccount', array(
			'passwordPolicy' => 'normal',
			'fields' => array('username' => 'username', 'password' => 'password')
		));
		
		debug($this->User->forgottenPassword('fake@email.it'));
	}
}