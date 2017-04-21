<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/*
 * Usage : there's a download.php file at the root of the website, wich is including this script
 *
 * So, to call this script, use this url :
 * http://www.mysite.com/download.php?filepath=/resource/image/sample.jpg
 *
 * Instead of this one, wich is more longer :
 * http://www.mysite.com/libraries/lib_medias/download.php?filepath=/resource/image/sample.jpg
 */


// Direct access authorized
defined('_DIRECT_ACCESS') or define('_DIRECT_ACCESS', 1);


// Only in case this script is called directly !
if (is_file('../../config.php')) {
	require_once('../../config.php'); # Relative path
}

// Load mediasManager class
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/libraries/lib_medias/medias_class.php');
$medias = new mediasManager();


// mbstring internal encoding
mb_internal_encoding("UTF-8");


// Protocol info
global $g_protocol;
$g_protocol	= 'http://';


// Get the requested File path, relative to the website root (example : $_GET['filepath'] = '/resource/image/sample.jpg';)
$filepath_relative = $_GET[mediasManager::DOWNLOAD_QUERY_STRING] or die("No file requested !\n");


// If this is a full url, try to get his relative path to the local server !
$parse_url = parse_url($filepath_relative);
(!isset($parse_url['host']) || $parse_url['host'] == $_SERVER['HTTP_HOST']) or die ("Download is restricted to : ".$_SERVER['HTTP_HOST']);
$filepath_relative = $parse_url['path'];


// Download is restricted to the RESOURCE_PATH directory
#preg_match('~^('.pregQuote(WEBSITE_PATH.RESOURCE_PATH).')~', $filepath_relative) or die ("Download is restricted to : ".WEBSITE_PATH.RESOURCE_PATH."\n");
preg_match('~^('.preg_quote(WEBSITE_PATH.RESOURCE_PATH, '~').')~', $filepath_relative) or die ("Download is restricted to : ".WEBSITE_PATH.RESOURCE_PATH."\n");


// Full path on the local server
$filepath_full = $_SERVER['DOCUMENT_ROOT'].$filepath_relative;


$pathinfo = pathinfo($filepath_full);


// Is file available ?
is_file($filepath_full) or die ("File not found !\n");

// Is extension authorized ?
$medias->isAuthorizedExtension($pathinfo['extension']) or die ("Forbidden file extension !\n");


// So, let's go !
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment; filename={$pathinfo['basename']};");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($filepath_full));
readfile($filepath_full);

?>