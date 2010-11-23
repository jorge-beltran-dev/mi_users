<?php
echo $form->create(null, array('id' => 'loginOrRegister'));
if (empty($texts)) {
	$texts = array();
}
$texts = array_merge($texts, array(
	'new' => __d('mi_users', 'New User', true),
	'existing' => __d('mi_users', 'I\'ve already got an account', true),
	'login' => __d('mi_users', 'Username or email', true),
	'email' =>__d('mi_users', 'Email', true),
	'username' => __d('mi_users', 'Choose your username', true),
	'password'  => __d('mi_users', 'password', true),
	'confirm' => __d('mi_users', 'confirm', true),
	'submitExisting' => __d('mi_users', 'Login to continue', true),
	'submitNew' => __d('mi_users', 'Register to continue', true),
), $texts);
$loginLabel = $texts['login'];
$hideLogin = $hideRegister = $hidePassword = 'hidden';
if (!empty($this->data['User']['type'])) {
	$hidePassword = '';
	if ($this->data['User']['type'] === 'new') {
		$loginLabel = $texts['email'];
		$hideRegister = '';
	} else {
		$hideLogin = '';
	}
}
$options = array('new' => $texts['new'], 'existing' => $texts['existing']);
echo $form->inputs(array(
	'legend' => false,
	'User.type' => array('type' => 'radio', 'options' => $options, 'legend' => false, 'label' => false),
	'User.email' => array('label' => $loginLabel),
	'User.username' => array('div' => 'input text ' . $hideRegister, 'label' => $texts['username']),
	'User.password' => array('div' => 'input password ' . $hidePassword, 'type' => 'password', 'label' => $texts['password'], 'value' => ''),
	'User.confirm' => array('div' => 'input password ' . $hideRegister, 'type' => 'password', 'label' => $texts['confirm'], 'value' => ''),
	'User.tos', array(
		'fieldset' => false, 'type' => 'checkbox', 'div' => 'input checkbox ' . $hideRegister,
		'label' => sprintf(__d('mi_users', 'I agree to the site %1$s', true), $html->link(__d('mi_users', 'terms of service', true), '/condiciones', array('class' => 'popup modal noResize noDrag'))))
));
if (!empty($this->data['User']['type']) && $this->data['User']['type'] === 'existing') {
	echo $form->submit($texts['submitExisting'], array('id' => 'loginOrRegisterSubmit'));
} else {
	echo $form->submit($texts['submitNew'], array('id' => 'loginOrRegisterSubmit'));
}
echo $form->end();

/*
$code = <<<CODE
$(function() {
	$('input#UserTos').parent().hide();
	$('input#UserTypeNew').click(function() {
		$('input#UserEmail').prev().text(__d('mi_users', 'Email'));
		$('input#UserUsername').parent().show();
		$('input#UserPassword').parent().show();
		$('input#UserConfirm').parent().show();
		$('input#User1').parent().show();
		$('input#loginOrRegisterSubmit').text(__d('mi_users', 'Register to continue'));
	});
	$('input#UserTypeExisting').click(function() {
		$('input#UserEmail').prev().text(__d('mi_users', 'Email or username'));
		$('input#UserUsername').parent().hide();
		$('input#UserPassword').parent().show();
		$('input#UserConfirm').parent().hide();
		$('input#User1').parent().hide();
		$('input#loginOrRegisterSubmit').text(__d('mi_users', 'Login to continue'));
	});
});
CODE;
$asset->codeBlock($code);
*/
