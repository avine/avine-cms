<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Login/logout config
global $tmpl_name_login;
$tmpl_name_login = 'default/tmpl_1_login.html'; # false to disable

global $tmpl_name_logout;
$tmpl_name_logout = false; # false to disable

// Create configuration
global $tmpl_name_create;
$tmpl_name_create = 'default/tmpl_1_create.html'; # false to disable



// Configuration
global $g_user_login;


if (!$g_user_login->userID())
{
	// Default behavior : Login form removed when create account process detected
	if (!(isset($_POST['form_id']) && $_POST['form_id'] == COM_USER_FORM_PREFIX_CREATE_))
	{
		require(sitePath().'/components/com_user/pages/login.php');
	}
	// Alternative behavior : Login form always appear
	#require(sitePath().'/components/com_user/pages/login.php');

	require(sitePath().'/components/com_user/pages/create.php');
}
else
{
	require(sitePath().'/components/com_user/pages/login.php');
}


?>