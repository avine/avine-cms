<?php
/* Avine. Copyright (c) 2008 Stéphane Francel (http://avine.io). Dual licensed under the MIT and GPL Version 2 licenses. */

//////////
// CONFIG

// Set where to get the term of the search
$query_string	= 'term'; # Set 'term' to use with the plugin jQuery-UI-Autocomplete

// Debug this page
$debug			= false;

// End of config
////////////////



// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin($debug);

// Protocol info
global $g_protocol;
$g_protocol = 'http://';



$suggestions = array();

if (isset($_GET[$query_string]) && $_GET[$query_string])
{
	if (isset($_GET['ext']) && $_GET['ext']) {
		$extensions = explode(',', $_GET['ext']);
	} else {
		$extensions = array();
	}
	$resources = mediasManager::getResourcesList('', $extensions, false);

	foreach($resources as $dir => $files)
	{
		for ($i=0; $i<count($files); $i++) {
			$files[$i] = "$dir/".$files[$i];
		}
		$suggestions = array_merge($suggestions, preg_grep('~'.pregQuote($_GET[$query_string]).'~', $files));
	}
}



if (!$debug)
{
	// Only logged user in backend can access the results !
	$g_user_login = new comUser_login(4,1,0);
	if ($g_user_login->accessLevel() <= 3)
	{
		header('Content-Type: text/plain; charset=utf-8');
		echo json_encode($suggestions);
	} else {
		die('Restricted access');
	}
}
else
{
	header('Content-Type: text/html; charset=utf-8');
	echo '<html><head><title>Debug - mediasManager autocompletion</title></head>'."\n";
	echo '<body><div style="font:medium Monospace;">'."\n";
	echo '<h1>Founded resources :</h1>'."\n";
	echo '<p>'.implode("<br />\n", $suggestions)."</p>\n";
	echo '</div></body></html>';
}



loaderManager::directAccessEnd();

?>