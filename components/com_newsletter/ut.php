<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin(false);



// Protocol info
global $g_protocol;
$g_protocol	= 'http://';


// Database connection
global $db;


// Increase hits
if (isset($_GET['id']) && formManager_filter::isInteger($_GET['id']))
{
	$id = $_GET['id']; # Alias

	if ($send = $db->selectOne("newsletter_send, hits, where: id=$id AND, where: date_begin IS NOT NULL"))
	{
		$hits = $send['hits'] + 1;
		$db->update("newsletter_send; hits=$hits; where: id=$id");
	}
}



// Load the "user track" image : 'ut.gif'
$user_track = sitePath().'/components/com_newsletter/images/ut.gif';

if (is_file($user_track))
{
	header("Content-type: image/gif");
	readfile($user_track);
}



loaderManager::directAccessBegin();

?>