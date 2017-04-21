<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


define('CONTENTS_NEW_CONTENTS_TITLE'	, "Nouveaux articles");
define('CONTENTS_NEW_CONTENTS_EMPTY'	, "Pas de nouveaux articles...");
define('CONTENTS_NEW_CONTENTS_OLDS'		, "Consulter les anciens articles");
define('CONTENTS_NEW_CONTENTS_MORE'		, "Plus d'articles");


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<h1>'.CONTENTS_NEW_CONTENTS_TITLE."</h1>\n";



// Config
$max_count_elements_for_visiter = 10; # false to disable



// Get the last date_modified of the content_element table
global $db;
$date_modified = $db->selectOne('content_element, date_modified(desc); limit: 1', 'date_modified');

$session = new sessionManager(sessionManager::FRONTEND, 'content_last_elements');

if ($date_modified != $session->get('date_modified'))
{
	// Put the elements summary in the session
	$get_latest = new comContent_getLatest();
	$get_latest->limit(10);
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



// Slice the elements array
global $g_user_login;
if (!$g_user_login->userID() && count($elements) && (count($elements) > $max_count_elements_for_visiter))
{
	$elements = array_slice($elements, 0, $max_count_elements_for_visiter, true);
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

echo "\n<ul>\n\t<li>".implode("</li>\n\t<li>", $list)."</li>\n</ul>\n<hr />\n\n";


if (!count($elements)) {
	echo
		'<p>'.CONTENTS_NEW_CONTENTS_EMPTY."</p>\n".
		'<p><a href="'.comMenu_rewrite('com=content&page=index').'" class="button">'.CONTENTS_NEW_CONTENTS_OLDS."</a></p>\n\n";
}



if (isset($_GET['id']) && formManager_filter::isInteger($_GET['id']) && array_key_exists($_GET['id'], $elements))
{
	$tmpl_html = <<< END

<!-- {medias_button} --><div class="item-medias-pref-button">{medias_button}</div><!-- {medias_button} -->
<h2><a href="{element_link}">{title}<!-- {title_alias} --><br /><span>{title_alias}</span><!-- {title_alias} --></a></h2>
{image_thumb}
{text_intro}
{medias}

END;

	$template = new templateManager();
	echo $template->setTmplHtml($tmpl_html)->setReplacements($elements[$_GET['id']])->process();
}

if (count($elements)) {
	echo '<p><a href="'.comMenu_rewrite('com=user&page=create').'" class="button">'.CONTENTS_NEW_CONTENTS_MORE."</a></p>\n\n";
}

?>