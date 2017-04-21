<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions


// Get a menu links
function admin_comMenu_getMenu( $menu_id, $excluded_link_id = false, $parent_id = 0, $level = 0 )
{
	static $menu_links = array();
	$parent_id == 0 ? $menu_links = array() : ''; # Reset when begining

	$level_html = '';
	for ($i=0; $i<$level; $i++) {
		$level_html .= '....';
	}
	$level_html .= ' ';

	global $db;
	$link =
		$db->select(
			'menu_link_type, name AS link_type_name, join: id>; '.
			'menu_link, id,name,href,link_type_id,link_order(asc),access_level,published,template_id,params, join: <link_type_id, '.
			"where: menu_id=$menu_id AND, where: parent_id=$parent_id"
		);

	if ($link)
	{
		for ($i=0; $i<count($link); $i++)
		{
			if ($link[$i]['id'] != $excluded_link_id)
			{
				$temp['id'					] = $link[$i]['id'];
				$temp['name'				] = $level_html.$link[$i]['name'];
				$temp['href'				] = $link[$i]['href'];
				$temp['link_type'			] = $link[$i]['link_type_name'];
				$temp['link_order'			] = 2*$i+1;
				$temp['access_level'		] = $link[$i]['access_level'];
				$temp['published'			] = $link[$i]['published'];
				if ($link[$i]['template_id'	] == 0)
				{
					$temp['template_id'		] = '';
				} else {
					$temp['template_id'		] = $link[$i]['template_id'];
				}
				$temp['params'				] = $link[$i]['params'];
				array_push($menu_links, $temp);

				admin_comMenu_getMenu($menu_id, $excluded_link_id, $link[$i]['id'], $level+1);
			}
		}
	}
	return $menu_links;
}



// Header of the menu links
function admin_comMenu_getMenuHeader()
{
	return
		array(
			LANG_ADMIN_COM_MENU_LINK_ID,
			LANG_ADMIN_COM_MENU_LINK_NAME,
			LANG_ADMIN_COM_MENU_LINK_HREF,
			LANG_ADMIN_COM_MENU_LINK_TYPE,
			LANG_ADMIN_COM_MENU_LINK_ORDER,
			LANG_ADMIN_COM_MENU_LINK_ACCESS,
			LANG_ADMIN_COM_MENU_LINK_PUBLISHED,
			LANG_ADMIN_COM_MENU_LINK_TEMPLATE,
			LANG_ADMIN_COM_MENU_LINK_PARAMS
		);
}



// Add $new_param to $params
function admin_comMenu_AddToParams( $news, $params = '' )
{
	if (!$params) {
		return $news;
	} else {
		return preg_replace('~;$~', '', $params).";$news"; # preg_replace() function is a simple security
	}
}



?>