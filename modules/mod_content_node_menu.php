<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/*
 * Notice :  This module is specific for 'com_content' component. It can not be used for 'com_generic' or other components based on it
 */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Manual configuration
$parent_id			= 0;
$add_root_link		= true;



// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
$com_content = comContent_frontendScope();



global $g_user_login;

// Nodes details of the requested parent_id
global $db;
$nodes =
	$db->select(
		"content_node, id, where: parent_id=$parent_id AND, where: published=1 AND, where: access_level>=".$g_user_login->accessLevel()." AND, where: archived=0, join: id>; ".
		"content_node_item, title, title_alias, text, image, join: <node_id");

# You can cutomize the output with any part of the node. To see what keys are availables, use the following code
#echo '<p style="color:blue;"><b>Keys of each nodes[$i] :</b><br />'.implode('<br />', array_keys($nodes[0])).'</p>';



$html = '';

if ($add_root_link) {
	$href= WEBSITE_PATH.'/index.php?form_id=node_selector_&amp;com=content&amp;page=index&amp;section_id=0'; # Careful : litteral link path (taken from the node selector) !
	$html .= "<li><a href=\"$href\"><i>Toutes les catégories</i></a></li>";
}

for ($i=0; $i<count($nodes); $i++)
{
	// Url of the node
	$link = $com_content->nodeUrlEncoder($nodes[$i]['id']);
	if ($com_content->pageUrlRequest())
	{
		$href = $com_content->pageUrlRequest().'&amp;'.$link['href'];
	} else {
		$href = $link['href'];
	}
	$href = comMenu_rewrite($href);

	if ($nodes[$i]['title_alias'])
	{
		$title_alias = ' ('.$nodes[$i]['title_alias'].')';
	} else {
		$title_alias = '';
	}

	$html .= "<li><a href=\"$href\">{$nodes[$i]['title']}$title_alias</a></li>";
}

echo "\n<ul class=\"menu\">$html</ul>\n\n"; # Notice : the css class="menu" is taken from the comMenu_ component (for basic menu)

?>