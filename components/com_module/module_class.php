<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Search and include modules on page
function comModule_( $pos )
{
	$module = array();
	$mod_id = array(); # Temporary search

	$default_module = true;

	global $g_page;
	global $db;
	global $g_user_login;

	// Second part of the query to select the availables modules of the $pos position
	$query_module = 'module, join: <id, where: mod_pos='.$db->str_encode($pos).' AND, where: published=1 AND, where: access_level>='.$g_user_login->accessLevel();

	if ($g_page['link_id'])
	{
		$mod_id = array_keys( $db->select('module_xhref, [mod_id], join: mod_id>, where: link_id='.$g_page['link_id'].' AND; '.$query_module) );

		// Disable default modules ?
		$params = setArrayOfParam( $db->selectOne('menu_link, params, where: id='.$g_page['link_id'], 'params') );
		if (isset($params['default_module']) && $params['default_module'] == 'no') {
			$default_module = false;
		}
	}
	else
	{
		/*
		 * If we are here, that mean :
		 * There's no link wich this page is the target !
		 * Then, this page will not have any module on it ! To bad !
		 * So, imagine, this page is a sub-page of something.
		 * Find the parent-page, and apply his modules to his son !
		 *
		 * Example1 : Say '/index.php?node=my_node' have some modules.
		 * 			Then display them on '/index.php?node=my_node&element=my_element'
		 *
		 * So, we are using this pattern for the com_content component.
		 */

		// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
		$com_content = comContent_frontendScope();

		$com_in_url = $com_content->findComponentInUrl();
		$id_alias = $com_in_url['node_alias_array'];

		// Get the nodes possibilities { example: $node_up = array( 'node=n1/n2/n3', 'node=n1/n2', 'node=n1'); }
		$node_up = array();
		$parent_id = 0;
		for ($i=0; $i<count($id_alias); $i++)
		{
			$node = $db->selectOne($com_content->getTablePrefix()."node, id, where: parent_id=$parent_id AND, where: id_alias=".$db->str_encode($id_alias[$i]));
			if ($node)
			{
				$url_encoder = $com_content->nodeUrlEncoder($node['id']);

				if ($com_content->pageUrlRequest())
				{
					$node_up[] = $com_content->pageUrlRequest().'&amp;'.$url_encoder['href'];
				} else {
					$node_up[] = $url_encoder['href'];
				}

				$parent_id = $node['id'];
			}
			else {
				break;
			}
		}
		$node_up = array_reverse($node_up);

		if (count($node_up))
		{
			$i = 0;
			do {
				$mod_id = array_keys( $db->select('module_xhref, [mod_id], join: mod_id>, where: link_href='.$db->str_encode($node_up[$i]).' AND; '.$query_module) );
			}
			while ((!$mod_id) && (++$i < count($node_up)) && ($i <= 1)); # Absolute limitation: go backward 1 level max! (or the displayed modules will be unpredictable)
		}
	}

	// Add default modules
	if ($default_module)
	{
		$mod_def_id = array_keys( $db->select('module_default, [mod_id], join: mod_id>; '.$query_module) );

		// Remove duplicate mod_id
		$mod_id = array_unique( array_merge($mod_id, $mod_def_id) );
	}

	// Get modules details
	if (count($mod_id)) {
		$module = $db->select('module, name,show_name,mod_file,mod_order(asc),html_pos,params, where: id='.implode(' OR, where: id=', $mod_id));
	}
	return $module;
}



// Search and include modules on page
function comModule_show( $pos, $module )
{
	global $g_page;

	// Find the template of the position
	$locations =
		array(
			$g_page['template_dir']."/positions/$pos.html",		# module html code for this position
			$g_page['template_dir'].'/positions/default.html', 	# default module html code of this template
			'/components/com_module/tmpl/tmpl_default.html'   	# default module html code from admin
		);

	$i = 0;
	do {
		$module_html_path = sitePath().$locations[$i++];
		$module_html = comModule_getModuleHtml($module_html_path);
	}
	while ((!$module_html) && ($i<3));

	// Show the modules
	for ($i=0; $i<count($module); $i++)
	{
		if (file_exists($mod_path = sitePath().'/modules/'.$module[$i]['mod_file']))
		{
			// Search for special html-code for this module
			if ($module[$i]['html_pos'] != "")
			{
				$module_html_path = sitePath().$g_page['template_dir'].'/positions/'.$module[$i]['html_pos'].'.html';
				$module_html_special = comModule_getModuleHtml($module_html_path);
			} else {
				$module_html_special = false;
			}

			if ($module_html_special) {
				$html = $module_html_special;
			} else {
				$html = $module_html;
			}

			// Module title
			if ($module[$i]['name'] && $module[$i]['show_name']) {
				$html['title'] = str_replace('{title}', $module[$i]['name'], $html['title']);
			} else {
				$html['title'] = '';
			}

			// Module path
			$html['path'] = $mod_path;

			// Show module
			echo _comModule_show($html);
		}
		else {
			echo LANG_MODULE_NOT_AVAILABLE;
		}
	}
}



function _comModule_show( $_h_t_m_l )
{
	ob_start();
	require($_h_t_m_l['path']);
	$_h_t_m_l['content'] = ob_get_contents();
	ob_end_clean();

	return
		$_h_t_m_l['header'	].
		$_h_t_m_l['title'	].
		$_h_t_m_l['middle'	].
		$_h_t_m_l['content'	].
		$_h_t_m_l['footer'	];
}



// Html-code for the modules
function comModule_getModuleHtml( $module_html_path )
{
	if (!file_exists($module_html_path)) {
		return false;
	}

	// Get and analize the module file
	$module_html = explode('{exploder}', file_get_contents($module_html_path));
	switch (count($module_html))
	{
		case 1:
			$header 	= '';
			$title 		= '';
			$content 	= $module_html[0];
			break;
		case 2:
			$header 	= '';
			$title 		= $module_html[0];
			$content 	= $module_html[1];
			break;
		case 3:
			$header 	= $module_html[0];
			$title 		= $module_html[1];
			$content 	= $module_html[2];
			break;
		default:
			$header 	= LANG_MODULE_HTML_ANALYSE_FAILED; $title="{title}<br />";
			$content 	= "{content}";
			break;
	}
	$content = explode('{content}', $content);
	$middle = $content[0];
	$footer = $content[1];

	// Exception, for 'case 1:' we will find the '$title' section into the '$middle' section
	if (mb_strstr($middle, '{title}')) {
		$title = $middle; $middle = '';
	}

	$return['header'] = $header;
	$return['title' ] = $title;
	$return['middle'] = $middle;
	$return['footer'] = $footer;

	return $return;
}



/*
 * Include one module wherever you need !
 * --------------------------------------
 *
 * This function is very usefull in case you have a section of the website with many pages,
 * and you need to display the same list of modules for all thoses pages.
 *
 * Then instead of associate each module of this list to each page of this section, 
 * you simply need to include ONE module wich contains all the modules you need !
 */
function comModule_file( $pos, $mod_file, $mod_title = '', $access_level = false )
{
	if ($access_level) {
		global $g_user_login;
		if ($g_user_login->accessLevel() > $access_level) {
			return;
		}
	}	
	// Find the template of the position
	global $g_page;
	$locations =
		array(
			$g_page['template_dir']."/positions/$pos.html",		# module html code for this position
			$g_page['template_dir'].'/positions/default.html', 	# default module html code of this template
			'/components/com_module/tmpl/tmpl_default.html'   	# default module html code from admin
		);

	$i = 0;
	do {
		$module_html_path = sitePath().$locations[$i++];
		$module_html = comModule_getModuleHtml($module_html_path);
	}
	while ((!$module_html) && ($i<3));

	// Show the module
	if (file_exists($mod_path = sitePath()."/modules/$mod_file"))
	{
		echo $module_html['header'];

		if ($mod_title) {
			echo str_replace('{title}', $mod_title, $module_html['title']); # Title
		}

		echo $module_html['middle'];

		require($mod_path); # Content

		echo $module_html['footer'];
	}
	else {
		echo LANG_MODULE_NOT_AVAILABLE;
	}
}



?>