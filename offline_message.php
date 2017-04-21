<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );

// Redirection
$_SERVER['REQUEST_URI'] == WEBSITE_PATH.'/' or header('Location: '.siteUrl().'/');

global $g_page;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="index,follow" />

<title><?php echo $g_page['config_site_name']; ?></title>

<link rel="shortcut icon" href="<?php echo WEBSITE_PATH; ?>/global/favicon.ico" />

<style type="text/css">
* {
	margin: 0;
	padding: 0;
}
html {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #888;	
}

body {
	background-color: #FAFAFA;
}

#offline-title {
	margin-top: 100px;
	text-align: center;
	font-size: 28px;
	font-weight: bold;
	color: #DDD;

	/* Html 5 */
	text-shadow: 0 2px 0 #FFF;
}
#offline-title a,
#offline-title a:link,
#offline-title a:visited {
	color: #DDD;
	text-decoration: none;
}
#offline-title a:hover,
#offline-title a:active,
#offline-title a:focus {
	color: #CCC;
}
#offline-message {
	margin: 30px auto;
	width: 500px;
	border: 1px solid #DDD;
	background-color: #FFF;
	padding: 1px;

	/* Html 5 */
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;

	/* Html 5 */
	-moz-box-shadow: 1px 1px 3px #DDD;
	-webkit-box-shadow: 1px 1px 3px #DDD;
	box-shadow: 1px 1px 3px #DDD;
}
#offline-message p {
	line-height: 19px;
	letter-spacing: normal;
	text-align: center;
	border: 1px solid #CCC;
	background: url(global/offline-bg.gif) no-repeat left top #FFF;
	padding: 15px;
	margin: 0;

	/* Html 5 */
	-moz-border-radius: inherit;
	-webkit-border-radius: inherit;
	border-radius: inherit;
}
</style>

</head>
<body>

<div id="offline-title"><a href="<?php echo siteUrl(); ?>" title="<?php echo siteUrl(); ?>"><?php echo $g_page['config_site_name']; ?></a></div>
<div id="offline-message"><p><?php echo $g_page['config_offline_message']; ?></p></div>

</body>
</html>