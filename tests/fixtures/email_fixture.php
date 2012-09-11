<?php

class EmailFixture extends CakeTestFixture {

	var $name = 'Email';

	var $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'from_user_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'to_user_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'chain_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'ip' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'send_date' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'unsent', 'length' => 30),
		'type' => array('type' => 'string', 'null' => true, 'default' => 'normal', 'length' => 10),
		'from' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'to' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'reply_to' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'cc' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'bcc' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'send_as' => array('type' => 'string', 'null' => false, 'default' => 'both', 'length' => 4),
		'subject' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'template' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'layout' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'data' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		
	);
}