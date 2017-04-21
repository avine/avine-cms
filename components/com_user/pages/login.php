<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/**
 * Optionnal config : Use templates
 */
global $tmpl_name_login; # You can handle this variable from the outside
if (!isset($tmpl_name_login))
{
	$tmpl_name_login = false; # false to disable
}

global $tmpl_name_logout; # You can handle this variable from the outside
if (!isset($tmpl_name_logout))
{
	$tmpl_name_logout = false; # false to disable
}



global $g_user_login;

if (!$g_user_login->userID())
{
	// Form (array)
	$data = $g_user_login->getform(); # Notice: if you need, customize here the $data before calling the ->displayForm() method

	// Login Form
	if (!$tmpl_name_login)
	{
		$html  =	"\n<!-- User:Login -->\n".'<fieldset class="comUser_fieldset"><legend>'.LANG_COM_USER_LOGIN_ACCOUNT_TITLE.'</legend>'."\n";

		$html .= $g_user_login->displayForm($data);

		$html .=	"</fieldset><!-- End of : User:Login -->\n\n";

		echo $html;
	}
	else
	{
		echo $g_user_login->displayForm($data, $tmpl_name_login, false);
	}
}
else
{
	// Form (array)
	$data = $g_user_login->getform(); # Notice: if you need, customize here the $data before calling the ->displayForm() method

	// Logout Form
	if (!$tmpl_name_logout)
	{
		$html  = '';
		$html .= "\n<!-- User:Logout -->\n";

		$html .= $g_user_login->displayForm($data);

		$html .= "<!-- End of : User:Logout -->\n\n";
		echo $html;
	}
	else
	{
		echo $g_user_login->displayForm($data, false, $tmpl_name_logout);
	}
}

?>