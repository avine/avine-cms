<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Activate demo-mode / debug-mode in the backend
define('ADMIN_DEMO_MODE'	, 0);
define('ADMIN_DEBUG_MODE'	, 0);


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
is_file('../config.php') or header('Location: ../global/system_error.php?alias=config');
require('../config.php'); # relative path

is_file($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php') or header('Location: ../global/system_error.php?alias=include');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');			# Frontend includes
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/admin/global/include.php');	# Backend includes

loaderManager::directAccessBegin(ADMIN_DEBUG_MODE);



// Protocol info
global $g_protocol; # $g_* means global variable
$g_protocol	= 'http://';


// User login process
global $g_user_login;
$g_user_login = new comUser_login(4,1,0);
$g_user_login->process();

// Disable temporarily the login (to check the W3C xhtml validation of the pages)
#$g_user_login->autoLogin(1); # We assume that user_id=1 is an administrator


// Allow to manage quickly the links details for <a href="?"> and <form action="?"> (see 'admin/global/functions.php' for details)
global $g_admin_pathway;
$g_admin_pathway = array();


// Load template
global $g_admin_template_dir;
$g_admin_template_dir = 'default';

require(sitePath()."/admin/templates/$g_admin_template_dir/index.php");



loaderManager::directAccessEnd();

?>