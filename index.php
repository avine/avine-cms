<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
is_file('config.php') or header("Location: global/system_error.php?alias=config");
require('config.php'); # Relative path

is_file($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php') or header("Location: global/system_error.php?alias=include");
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin();



// Rewrite engine
new comRewrite_();


// Protocol info
global $g_protocol; # $g_* means global variable
$g_protocol = 'http://';


// Page informations
global $g_page;
$g_page = array();


// User login process
global $g_user_login;
$g_user_login = new comUser_login();
$g_user_login->process();


// Set $g_page details
comConfig_();
comMenu_();
comTemplate_();


// Logged administrator info
$admin_is_logged = new comUser_login(1,1,0);

if ($g_page['config_online'] || $admin_is_logged->userID()) # Site online or Administrator logged
{
	require(sitePath().$g_page['template_dir'].'/index.php');
} else {
	require(sitePath().'/offline_message.php');
}



loaderManager::directAccessEnd();

?>