<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$db = new databaseManager();
$db_show = $db->db_show();


if ( count($db_show) && in_array(DB_TABLE_PREFIX.'user', $db_show) && in_array(DB_TABLE_PREFIX.'config', $db_show))
{
	echo '<a name="default"></a>'."\n";
	$html = '';


	$config	= $db->selectOne('config, *');
	$user	= $db->selectOne('user, *, where: id=1');

	$default = array(
		'username'		=>	$user['username'],
		'password'		=>	'', # Unknown !
		'email'			=>	$user['email'],

		'site_name'		=>	$config['site_name'],
		'system_email'	=>	$config['system_email']
	);


	$filter = new formManager_filter();

	$update = 1;
	$disabled = '';


	///////////
	// Process

	$default_ = formManager::isSubmitedForm('default_');

	if ($default_ && $filter->requestValue('submit')->get())
	{
		$username 		= $filter->requestValue('username'	)->getUserPass	(1, '', LANG_INSTALL_DEFAULT_ADMIN_USERNAME);
		$password 		= $filter->requestValue('password'	)->getUserPass	(1, '', LANG_INSTALL_DEFAULT_ADMIN_PASSWORD);
		$email 			= $filter->requestValue('email'		)->getEmail		(1, '', LANG_INSTALL_DEFAULT_ADMIN_EMAIL);

		$site_name 		= $filter->requestValue('site_name'		)->getNotEmpty	(1, '', LANG_INSTALL_DEFAULT_SITE_NAME);
		$system_email 	= $filter->requestValue('system_email'	)->getEmail		(1, '', LANG_INSTALL_DEFAULT_SYSTEM_EMAIL);

		if ($filter->validated())
		{
			$result_user 	= $db->update('user; username='.$db->str_encode($username).', password='.$db->str_encode(sha1($password)).', email='.$db->str_encode($email).'; where: id=1');
			$result_config 	= $db->update('config; site_name='.$db->str_encode($site_name).', system_email='.$db->str_encode($system_email));

			if ($result_user && $result_config) {
				$disabled = ';disabled';
			}
		}
		else {
			echo $filter->errorMessage();
		}
	}
	elseif ($default_ && $filter->requestValue('modify')->get())
	{
		$update = 0;
	}
	elseif ($db->selectOne('user, password, where: id=1', 'password'))
	{
		$disabled = ';disabled';
		$default['password'	] = 'xxxxx'; # Just to inform that the password exists...
	}

	//////////////
	// Start view

	$form = new formManager($update);
	$html .= $form->form('post', formManager::reloadPage().'#default', 'default_');

	$html .= '<h3>'.LANG_INSTALL_DEFAULT_ADMIN."</h3>\n";
	$html .= $form->text('username'	, $default['username'	], LANG_INSTALL_DEFAULT_ADMIN_USERNAME	, '', "wrapper=div.label-150px$disabled");
	$html .= $form->password('password'	, $default['password'	], LANG_INSTALL_DEFAULT_ADMIN_PASSWORD	, '', "wrapper=div.label-150px$disabled");
	$html .= $form->text('email'	, $default['email'		], LANG_INSTALL_DEFAULT_ADMIN_EMAIL		, '', "wrapper=div.label-150px$disabled");

	$html .= '<h3>'.LANG_INSTALL_DEFAULT_SITE."</h3>\n";
	$html .= $form->text('site_name'		, $default['site_name'		], LANG_INSTALL_DEFAULT_SITE_NAME		, '', "wrapper=div.label-150px;size=50;$disabled");
	$html .= $form->text('system_email'		, $default['system_email'	], LANG_INSTALL_DEFAULT_SYSTEM_EMAIL	, '', "wrapper=div.label-150px;size=50;$disabled");

	!$disabled ? $html .= '<p><br />'.$form->submit('submit', LANG_INSTALL_DEFAULT_BUTTON_SUBMIT)."</p>\n" : $html .= '<p><br />'.$form->submit('modify', LANG_INSTALL_DEFAULT_BUTTON_MODIFY)."</p>\n";

	$html .= $form->end();

	// Fieldset
	echo "<fieldset><legend class=\"default\">".LANG_INSTALL_FIELDSET_DEFAULT."</legend>\n$html</fieldset>\n";

	// Final message
	if ($disabled) {
		echo $box->message(LANG_INSTALL_NAVIG_TIPS, 'ok', true, '530');
		echo '<p class="navig"><a href="'.WEBSITE_PATH.'/admin/index.php">'.LANG_INSTALL_NAVIG_ADMINISTRATOR."</a></p>\n";
	}
}

?>