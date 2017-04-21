<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

// Dialog box for the medias block (jQuery)
function admin_comContent_mediasDialog( $medias, $current_medias = false )
{
	$return = '';

	// Current medias summary
	if ($current_medias) {
		$summary = array();
		for ($i=0; $i<count($current_medias); $i++) {
			$summary[] = array($current_medias[$i]['title'], $current_medias[$i]['src']);
		}
		$table = new tableManager($summary, array(LANG_COM_MEDIAS_LABEL_TITLE, LANG_COM_MEDIAS_LABEL_SRC));
		$return .= $table->html();
	}

	// Medias form
	$return .= jsManager::formDialog($medias, LANG_ADMIN_COM_CONTENT_ELEMENT_MEDIAS_MODIFY, 900, 300);

	return $return;
}



/*
 * Some methods designed for editors and authors to be viewed in the welcome page of the administration
 */

function admin_comContent_pendingTasks( $limit = false, $preview_size = 100 )
{
	$html = '';

	// Get elements summary
	$get_latest = new comContent_getLatest();
	$get_latest	->dateReference('creation')
				->published(0)
				->checkDates(0)
				->limit($limit);

	global $g_user_login;
	if ($g_user_login->accessLevel() == 4) {
		$get_latest->authorID($g_user_login->userID()); # View only the author's content
	}

	$elements = $get_latest->elementsSummary();

	if (!count($elements)) {
		return;
	}

	// Let's go !
	$contents = array();
	foreach($elements as $id => $infos)
	{
		foreach($infos as $k => $v) {
			$infos[$k] = strip_tags($v);
		}

		// Content preview
		$title			= admin_textPreview($infos['title'		], 30);
		$title_alias	= admin_textPreview($infos['title_alias']);

		$preview = '<h4>' . $infos['node_title'] .' | '. $title .($title_alias ? "<br /><span>$title_alias<span>" : '') . '</h4>';
		$preview .= '<p>';
		if ($infos['text_intro']) {
			$preview .= admin_textPreview($infos['text_intro'], $preview_size).'<br />';
		}
		$element_link = preg_replace('~^http(s)?://'.pregQuote($_SERVER['HTTP_HOST']).'~', '', $infos['element_link']);
		$preview .= admin_textPreview($element_link, 60,'span','grey').'</p>';

		// Basic infos
		$headers = array(LANG_COM_GENERIC_DATE_CREATION, LANG_ADMIN_COM_CONTENT_PREVIEW);
		$item = array(
			'date_creation'	=> $infos['date_creation'],
			'preview'		=> $preview
		);

		// Add author info (for editors)
		if ($g_user_login->accessLevel() < 4) {
			$headers[] = LANG_COM_GENERIC_AUTHOR;
			$item['username'] = $infos['username'];
		}

		$contents[] = $item;

		/*// Limit the item number (old code before $get_latest->limit($limit);)
		if ($limit && (count($contents) == $limit)) {
			break;
		}*/
	}

	$table = new tableManager($contents, $headers);
	return '<h3>'.LANG_ADMIN_COM_CONTENT_PENDING_TASKS."</h3>\n".$table->html();
}



function admin_comContent_lastPublished( $limit = false, $preview_size = 100 )
{
	$html = '';

	// Get elements summary
	$get_latest = new comContent_getLatest();
	$get_latest->limit($limit);
	$elements = $get_latest->elementsSummary();

	if (!count($elements)) {
		return;
	}

	// Let's go !
	$contents = array();
	foreach($elements as $id => $infos)
	{
		foreach($infos as $k => $v) {
			$infos[$k] = strip_tags($v);
		}

		// Content preview
		$title			= admin_textPreview($infos['title'		], 30);
		$title_alias	= admin_textPreview($infos['title_alias']);

		$preview =
			'<h4><a href="'.$infos['element_link'].'" class="external">' . $infos['node_title'] .' | '.
			$title .($title_alias ? "<br /><span>$title_alias<span>" : '') . '</a></h4>';

		$preview .= '<p>';
		if ($infos['text_intro']) {
			$preview .= admin_textPreview($infos['text_intro'], $preview_size).'<br />';
		}
		$element_link = preg_replace('~^http(s)?://'.pregQuote($_SERVER['HTTP_HOST']).'~', '', $infos['element_link']);
		$preview .= admin_textPreview($element_link, 60,'span','grey').'</p>';

		// Basic infos
		$headers = array(LANG_COM_GENERIC_DATE_MODIFIED, LANG_ADMIN_COM_CONTENT_PREVIEW, LANG_COM_GENERIC_AUTHOR);
		$contents[] = array(
			'date_modified'	=> $infos['date_modified'],
			'preview'		=> $preview,
			'username'		=> $infos['username']
		);

		/*// Limit the item number (old code before $get_latest->limit($limit);)
		if ($limit && (count($contents) == $limit)) {
			break;
		}*/
	}

	$table = new tableManager($contents, $headers);
	return '<h3>'.LANG_ADMIN_COM_CONTENT_LAST_PUBLISHED."</h3>\n".$table->html();
}

?>