<?php
/**
 * Short description for message_fixture.php
 *
 * Long description for message_fixture.php
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
 * @subpackage           mi.tests.fixtures
 * @since                v 1.0
 * @modifiedBy           $LastChangedBy$
 * @lastModified         $Date$
 * @license              http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * MessageFixture class
 *
 * @uses                 CakeTestFixture
 * @package              mi
 * @subpackage           mi.tests.fixtures
 */
class UserFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'User'
 * @access public
 */
	var $name = 'User';

/**
 * fields property
 *
 * @var array
 * @access public
 */
	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'username' => array('type' => 'string', 'null' => false),
		'password' => array('type' => 'string', 'null' => false),
		'email' => array('type' => 'string', 'null' => false),
	);

/**
 * records property
 *
 * The records are created out of sequence so that theirs id are not sequncial.
 * The order field values are used only in the list behavior test
 *
 * @var array
 * @access public
 */
	var $records = array(
		// password in clear: Password1234
		array('username' => 'fabio', 'password' => 'afb8e50ae19f7cba86bee859c08f9d7cb2c1b2b7', 'email' => 'fabio@test.com'),
	);
}