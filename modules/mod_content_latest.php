<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */

# TODO - ce script peut avantageusement etre simplifié grâce à la class : comContent_getLatest

/*
 * Notice :  This module is specific for 'com_content' component. It can not be used for 'com_generic' or other components based on it
 */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Manual configuration
$days_offset 				= 30;		# 30 days

$title_maxlength 			= 35;		# num of chars
$title_alias_maxlength 		= 25;		# num of chars
$text_intro_maxlength		= 65;		# num of chars
$thumb_width 				= '48';		# px



// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
$com_content = comContent_frontendScope();



global $g_user_login;

// Elements details of the requested node_id
global $db;
$elements =
	$db->select("content_element, id, access_level, published, date_online, date_offline, archived, date_modified(desc),
					where: date_modified>=".( time() - 60*60*24 *$days_offset )." AND, 
					where: published=1 AND, where: access_level>=".$g_user_login->accessLevel()." AND, where: archived=0,
					join: id>; ".
				"content_element_item, title, title_alias, image_thumb, text_intro,
					join: <element_id; ".
				"limit: 10"
	);



// Keep only the current online elements
$temp = array();
for ($i=0; $i<count($elements); $i++)
{
	// Check availibility (inside date_online and date_offline ; if defined)
	if (comGeneric_::checkDates($elements[$i]['date_online'], $elements[$i]['date_offline'])) {
		$temp[] = $elements[$i];
	}
}
$elements = $temp;
unset($temp);



if (count($elements))
{
	// Prepare replacements !
	$mod_content_latest = array();

	for ($i=0; $i<count($elements); $i++)
	{
		// Url of the element (general info)
		$link = $com_content->elementUrlEncoder($elements[$i]['id']);
		if ($com_content->pageUrlRequest())
		{
			$href = $com_content->pageUrlRequest().'&amp;'.$link['href'];
		} else {
			$href = $link['href'];
		}
		$href = comMenu_rewrite($href);

		// image thumb (specific to com_content)
		if ($elements[$i]['image_thumb']) {
			# TODO - resize the image using the comResource_::createThumbnail() method...
			$img_thumb = '<img src="'.WEBSITE_PATH.$elements[$i]['image_thumb'].'" alt="" width="'.$thumb_width.'" />';
		} else {
			$img_thumb = '';
		}

		// title, title alias, text intro
		$title 			= wordwrapContent( $elements[$i]['title'		], $title_maxlength				);
		$title_alias 	= wordwrapContent( $elements[$i]['title_alias'	], $title_alias_maxlength		);
		$text_intro 	= wordwrapContent( $elements[$i]['text_intro'	], $text_intro_maxlength, true	);

		// date modified
		if ($elements[$i]['date_modified'])
		{
			$date_modified = getTime($elements[$i]['date_modified'], 'time=no');
		} else {
			$date_modified = '';
		}

		// Fill the replacements
		$mod_content_latest[$i] =
			array(
				'title'			=> $title,			# specific to com_content
				'title_alias'	=> $title_alias,	# specific to com_content
				'image_thumb'	=> $img_thumb,		# specific to com_content
				'text_intro'	=> $text_intro,		# specific to com_content
				'date_modified'	=> $date_modified,	# general info
				'href'			=> $href			# general info
			);
	}

	// Process replacements !
	$html = '';
	for ($i=0; $i<count($mod_content_latest); $i++)
	{
		# The content_view() method will search the template at this location: "/components/com_{$this->com_name}/tmpl/{dir}/{$tmpl_name}.html"
		# where {dir} is the current template name, of the default template name, or the default directory of the component: 'default'
		$tmpl_name = array('tmpl_mod_content_latest'); # only one name
		$html .= $com_content->contentView($tmpl_name, $mod_content_latest[$i]);
	}
	echo '<div id="pane-latest-content" class="com-content-scroller scroll-pane">'.$html.'</div>'; # scroll-pane require the jScrollPane jQuery plugin
}
else {
	echo '...'; # No elements !
}

?>