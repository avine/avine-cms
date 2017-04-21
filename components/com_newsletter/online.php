<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin();



// Protocol info
global $g_protocol;
$g_protocol = 'http://'; # Required by the class 'comNewsletter_tmpl'


if ( (isset($_GET['id']) && formManager_filter::isInteger($_GET['id'])) && ($message = comNewsletter_tmpl::getMessage($_GET['id'])) )
{
	echo $message;
}
else
{
	// Basic version : Newsletter not founded, but don't loose the user, redirect to the homepage !
	#header('Location: '.siteUrl());

	// Alternative version : Newsletter not founded, but don't loose the user, redirect to the list of archived newsletters !
	header('Location: '.comMenu_rewrite('com=newsletter&page=archived&founded=0'));
}



loaderManager::directAccessEnd();

?>