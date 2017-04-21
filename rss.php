<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin();



// Rewrite engine
new comRewrite_();


// Protocol info
global $g_protocol;
$g_protocol = 'http://';


// Database connection
global $db;


// User login
global $g_user_login;
$g_user_login = new comUser_login();


// Template of the rss item
$item_tmpl = '
			<item>
				<title>_TITLE_</title>
				<link>_LINK_</link>
				<guid>_LINK_</guid>
				<description>_DESCRIPTION_</description>
				<pubDate>_PUBDATE_</pubDate>
				<category>_CATEGORY_</category>
			</item>
';


// Site name and description
$site_infos = $db->selectOne('config, site_name, meta_desc');

$site_infos['site_name'] = comSearch_::fulltext($site_infos['site_name']);
$site_infos['meta_desc'] = comSearch_::fulltext($site_infos['meta_desc']);


/*
 * Get the last com_content items
 */

$days_offset = 30; # 30 days

$elements =
	$db->select(
			"content_element, id, node_id, date_modified(desc), where: date_modified>=".(time()-60*60*24*$days_offset).", join: id>; ".
			"content_element_item, title, title_alias, image_thumb, text_intro, join: <element_id;"
	);


// Some required variables...
$com_content = comContent_frontendScope();
$site_url = siteUrl();


// Fill the items
$items = '';
for ($i=0; $i<count($elements); $i++)
{
	if ($com_content->isVisibleElement($elements[$i]['id']))
	{
		// Get the item category
		$category = $db->selectOne("content_node, id, where: id={$elements[$i]['node_id']}, join: id>; content_node_item, title, join: <node_id", 'title');

		$item = $item_tmpl;

		$link = $com_content->elementUrlEncoder($elements[$i]['id']);
		if ($com_content->pageUrlRequest())
		{
			$link = comMenu_rewrite($com_content->pageUrlRequest().'&amp;'.$link['href']);
		} else {
			$link = comMenu_rewrite($link['href']);
		}

		$offset = 0;# FIXME - Error can appear in the date (you can fix it manually)

		$item = str_replace('_TITLE_'		, htmlspecialchars(comSearch_::fulltext($elements[$i]['title'])), $item);
		$item = str_replace('_LINK_'		, $link, $item);
		$item = str_replace('_DESCRIPTION_'	, htmlspecialchars(comSearch_::fulltext($elements[$i]['text_intro'])), $item);
		$item = str_replace('_PUBDATE_'		, date("D, d M Y H:i:s", $elements[$i]['date_modified'] -$offset).' GMT', $item);
		$item = str_replace('_CATEGORY_'	, $category, $item);

		// New item added !
		$items .= $item;
	}
}


if (!comConfig_getDebug()) :

//////////
// OUTPUT

header('Content-Type: text/xml; charset=utf-8');

echo <<<END
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>{$site_infos['site_name']}</title> 
		<link>$site_url</link>
		<description>{$site_infos['meta_desc']}</description>
		<language>fr-FR</language>
		<atom:link href="$site_url/rss.php" rel="self" type="application/rss+xml" />
$items
	</channel>
</rss>
END;

else :

/////////
// DEBUG

// Send charset in the header
header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />

<title>Rss feed : Debug manager</title>

<link rel="shortcut icon" href="<?php echo WEBSITE_PATH; ?>/global/favicon.ico" />

<link rel="stylesheet" type="text/css" href="<?php echo WEBSITE_PATH; ?>/global/global_css.css" />
<link rel="stylesheet" type="text/css" href="<?php echo WEBSITE_PATH; ?>/libraries/lib_database/database_style.css" />
<?php
$load = new comTemplate_loader();
$load->resources(array('/plugins/js/jquery-ui/js/jquery.min.js', '/plugins/js/jquery-ui/js/jquery-ui.custom.min.js'));
?>
</head>
<body>

<?php new debugManager(); ?>

</body>
</html>
<?php

endif;



loaderManager::directAccessEnd();

?>