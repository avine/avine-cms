<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<h1>'.LANG_COM_CONTENT_LAST_TITLE."</h1>\n";



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
	$elements = array_slice($elements, 0, $max_elems_visiter, true);
}



$list = array();
foreach($elements as $id => $infos)
{
	// Get medias (if exists)
	if ($item_media = $db->selectOne("content_element_item, medias, where: element_id=$id", 'medias'))
	{
		$medias = new mediasManager();
		$medias->stringSet($item_media);
		$medias_count = count($medias->arrayGet());

		// Each element might have 2 additionals keys : medias, medias_button
		if ($medias_count)
		{
			$medias_button = mediasManager::mediaPreferenceButton();
			$elements[$id]['medias'] = $medias->showMedias(WEBSITE_PATH);

			if (count($medias->arrayGet()) != $medias_count) {
				$elements[$id]['medias_button'] = $medias_button; # The feature has reduce the medias count. So, display the preference button !
			}
		}
	}

	// Elements list
	$list[] = '<a href="'.formManager::reloadPage(true, "id=$id").'">'.$elements[$id]['title']."</a>";
}



// Current request
if (isset($_GET['id']) && formManager_filter::isInteger($_GET['id']) && array_key_exists($_GET['id'], $elements))
{
	$requested_id = $_GET['id'];
} else {
	$requested_id = false;
}



if (count($elements))
{
	// If no current request, slide down the toggle
	$show = $requested_id ? '' : ' show';

	$ul = "\n<ul class=\"comContent-last\">\n\t<li>".implode("</li>\n\t<li>", $list)."</li>\n</ul>\n<br class=\"comContent-last\" />\n";

	$title = LANG_COM_CONTENT_LAST_LIST;

	echo <<<END

<div class="toggle">
<h3 class="toggle-title$show">$title</h3>
<div class="toggle-content">
$ul
</div>
<hr />
</div>


END;

} else {
	echo
		'<p>'.LANG_COM_CONTENT_LAST_NO_ELEMENT."</p>\n".
		'<p><a href="'.comMenu_rewrite('com=content&page=index').'" class="button">'.LANG_COM_CONTENT_LAST_VIEW_OLDER."</a></p>\n\n";
}



if ($requested_id)
{
	$tmpl_html = <<< END

<!-- {medias_button} --><div class="item-medias-pref-button">{medias_button}</div><!-- {medias_button} -->
<h2><a href="{element_link}">{title}<!-- {title_alias} --><br /><span>{title_alias}</span><!-- {title_alias} --></a></h2>
<!-- {image_thumb} --><div style="float:left; margin:0 15px 5px 0;">{image_thumb}</div><!-- {image_thumb} -->
{text_intro}
<div style="clear:left;"></div>
{medias}

END;

	$template = new templateManager();
	echo $template->setTmplHtml($tmpl_html)->setReplacements($elements[$_GET['id']])->process();
}

if (!$g_user_login->userID() && count($elements)) {
	echo '<p><a href="'.comMenu_rewrite('com=user&page=create').'" class="button">'.LANG_COM_CONTENT_LAST_VIEW_MORE."</a></p>\n\n";
}

?>