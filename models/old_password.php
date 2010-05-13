<?php
/**
 * Short description for old_password.php
 *
 * If passwords were previously stored with a different hash mechanism, the old passwords can be stored and queried here to
 * allow code not to be held back by the previous incarnation. Intended to transparently migrate user's passwords from old
 * to new hash mechanism (md5+nosalt => sha1+salt) when they login. This isn't a model for any "you used that password
 * before" type logic.
 *
 * PHP version 4 and 5
 *
 * Copyright (c) 2009, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright (c) 2009, Andy Dawson
 * @link          www.ad7six.com
 * @package       users
 * @subpackage    users.models
 * @since         v 1.0 (20-Feb-2009)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * OldPassword class
 *
 * @uses
 * @package       users
 * @subpackage    users.models
 */
class OldPassword extends UsersAppModel {
/**
 * name property
 *
 * @var string 'OldPassword'
 * @access public
 */
	var $name = 'OldPassword';
/**
 * beforeFind method
 *
 * No table, no find
 *
 * @return void
 * @access public
 */
	function beforeFind() {
		if (!$this->useTable) {
			return false;
		}
		return true;
	}
/**
 * setSource method
 * If the table doesn't exist - no problem
 *
 * @param string $tableName 'old_passwords'
 * @return void
 * @access public
 */
	function setSource($tableName = 'old_passwords') {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$sources = $db->listSources();
		if (is_array($sources) && !in_array(strtolower($this->tablePrefix . $tableName), array_map('strtolower', $sources))) {
			$this->useTable = false;
			return;
		}
		parent::setSource($tableName);
	}
}
?>