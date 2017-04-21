<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Max elements for users and visiters
$max_elems_user		= 10;
$max_elems_visiter	= 5;



// Get the last date_modified of the content_element table
global $db;
$date_modified = $db->selectOne('content_element, date_modified(desc); limit: 1', 'date_modified');

$session = new sessionManager(sessionManager::FRONTEND, 'content_last_elements');

if ($date_modified != $session->get('date_modified'))
{
	// Put the elements summary in the session
	$get_latest = new comContent_getLatest();
	$get_latest->limit($max_elems_user);
	$session->set('elements', $get_latest->elementsSummary());

	// Update the last date modification
	$session->set('date_modified', $date_modified);
}

/*
 * Elements summary
 *
 * This code :		echo implode(', ', array_keys($elements[$i]));
 * will return :	title, title_alias, text_intro, image_thumb, element_link, date_modified, username, node_title
 */
$elements = $session->get('elements');

global $g_user_login;
if ($max_elems_visiter != $max_elems_user && !$g_user_login->userID()) {
	$elements = array_slice($elements, 0,$max_elems_visiter, true);
}



$list = array();
foreach($elements as $id => $infos)
{
	// Elements list
	$list[] = '<a href="'.comMenu_rewrite("com=content&page=last&id=$id").'">'.$elements[$id]['title']."</a>";
}



if (count($elements)) {
	echo "\n<ul>\n\t<li>".implode("</li>\n\t<li>", $list)."</li>\n</ul>\n";

} else {
	echo
		'<p>'.LANG_COM_CONTENT_LAST_NO_ELEMENT."</p>\n".
		'<p><a href="'.comMenu_rewrite('com=content&page=index').'">'.LANG_COM_CONTENT_LAST_VIEW_OLDER."</a></p>\n\n";
}

?>