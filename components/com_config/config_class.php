<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Configuration parameters
function comConfig_ ()
{
	global $g_page;
	global $db;

	if ($config = $db->selectOne('config, *'))
	{
		// Site status  
		$g_page['config_online'] = $config['online'];

		if (!$g_page['config_online'])
		{
			if ($config['offline_message'] != "")
			{
				$g_page['config_offline_message'] = nl2br($config['offline_message']); 
			} else {
				$g_page['config_offline_message'] = LANG_CONFIG_DEFAULT_OFFLINE_MESSAGE;
			}
		}

		// Site name
		$g_page['config_site_name'] = $config['site_name'];

		// Metas : about site
		if ($config['meta_desc'] != "") {
			comConfig_getMetaHtml( 'desc', $config['meta_desc'    ]);
		}
		if ($config['meta_keywords'] != "") {
			comConfig_getMetaHtml( 'key' , $config['meta_keywords']);
		}

		// Metas : about you
		if ($config['meta_author'] != "") {
			$g_page['config_meta_author'  ] = '<meta name="Author" lang="fr" content="'.htmlspecialchars($config['meta_author']).'" />'."\n";
		}

		// Check unique HOST
		$g_page['config_http_host'] = $config['http_host'];
		if (($config['http_host']) && ($config['http_host'] != $_SERVER['HTTP_HOST']))
		{
			global $g_protocol;
			$location = $g_protocol.$config['http_host'].WEBSITE_PATH.preg_replace('~^'.pregQuote(WEBSITE_PATH).'~', '', $_SERVER['REQUEST_URI']);
			header("Location: $location");
		}

		/*
		 *  In most of cases, a page target have a menu link to go in.
		 *  What the engine have to do, if the target defined by the url exist, but it's not linked into the 'menu_link' table ?
		 * 
		 *  Exemple:
		 *  In the contents directory, we put a file named 'myfile.html'.
		 *  But in the 'menu_link' table there's no record with href='file=myfile.html' !
		 *
		 *  Now, if the user go to the url : http://www.mysite.com/index.php?file=myfile.html
		 *  The target exist, and the url is valid !
		 *  Only if $no_linked_content_access=1, then the engine will display the page.
		 * 
		 *  Note: of course, there will not any modules on this page, because modules are linked to pages via the 'menu_link' table.
		 */
		$g_page['config_no_linked_content_access'] = $config['no_linked_content_access'];

		// Debug mode
		$g_page['debug'] = $config['debug'];
	}
	else
	{
		if (file_exists(sitePath().'/installation/installation.php')) {
			header('Location: '.WEBSITE_PATH.'/global/system_error.php?alias=database');
			exit;
		}

		// Survival Kit
		echo LANG_CONFIG_NOT_FOUND;
		$g_page['config_online'			] = 1;
		$g_page['config_site_name'		] = '';
		$g_page['config_meta_desc'		] = '';
		$g_page['config_meta_keywords'	] = '';
		$g_page['config_meta_author'	] = '';
		$g_page['config_no_linked_content_access'] = 1;
		$g_page['debug'					] = 0;
	}
}



// Return Html code for metas Desc & Keywords
function comConfig_getMetaHtml ( $name, $content )
{
	global $g_page;

    if (stristr($name, 'desc')) {
    	$name = 'Description';
    }
	elseif (stristr($name, 'key' )) {
		$name = 'Keywords';
	}
    else {
    	echo '<p>Error occured in <strong>comConfig_getMetaHtml($name,$content)</strong> : Invalid $name parameter !</p>';
    }

	switch ($name)
	{
		case 'Description':
			$g_page['config_meta_desc'    ] = '<meta name="'.$name.'" content="'.htmlspecialchars($content).'" />'."\n";
			break;

		case 'Keywords':
			$g_page['config_meta_keywords'] = '<meta name="'.$name.'" content="'.htmlspecialchars($content).'" />'."\n";
			break;
	}
}



function comConfig_getInfos( &$site_name, &$system_email )
{
	global $db;
	$config = $db->selectOne('config, site_name, system_email');

	$site_name 		= $config['site_name'];
	$system_email 	= $config['system_email'];
}



function comConfig_getDebug()
{
	global $db;
	$db->selectOne('config, debug', 'debug') ? $debug = true : $debug = false;

	return $debug;
}


?>