<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Contest
$contest = new comContest();


// Title
#echo '<h1><span>'.LANG_COM_CONTEST_PAGE_CONTEST_YEAR.' '.$contest->getConfig('year').'</span><br />'.LANG_COM_CONTEST_PAGE_LOGIN_TITLE.'</h1>';


global $g_user_login;

if ($user_id = $g_user_login->userID())
{
	require(sitePath().'/components/com_user/pages/modify.php');
}
else
{
	require(sitePath().'/components/com_user/pages/login_create.php');

	if ($g_user_login->userID())
	{
		echo '<p class="comContest-register"><a href="'.comMenu_rewrite('com=contest&amp;page=project').'">'.LANG_COM_CONTEST_LOGIN_CLICK_TO_CONTINUE.'</a></p>';
	}
}


?>