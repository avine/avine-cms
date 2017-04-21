<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

/* ------
   Config */



/* ----------
   Admin Menu */

// Get admin_menu ordered list
function admin_system_ListAdminMenu( $name = 'root', $level = 0 )
{
	static $admin_menu_list = array();
	if ($name == 'root') {
		$admin_menu_list = array(); # init
	}

	$prefix_html = '';
	$shift_html = '........';
	for ($i=0; $i<$level; $i++) {
		$prefix_html .= $shift_html;
	}
	$prefix_html .= ' ';

	global $db;
	$admin_menu = $db->select('admin_menu, id, link_name, link_order(asc),access_level,published, name,url_value, inc_file, where: name='.$db->str_encode($name));

	for ($i=0; $i<count($admin_menu); $i++)
	{
		// Add prefix to link_name
		$admin_menu[$i]['link_name'] = $prefix_html.$admin_menu[$i]['link_name'];
		// Format link_order
		$admin_menu[$i]['link_order'] = 2*$i +1;

		$admin_menu_list[count($admin_menu_list)] = $admin_menu[$i];

		admin_system_ListAdminMenu($admin_menu[$i]['url_value'], $level+1);
	}

	return $admin_menu_list;
}

// Header
function admin_system_HeaderAdminMenu()
{
	return
		array(
			LANG_ADMIN_COM_ADMIN_MENU_ID,
			LANG_ADMIN_COM_ADMIN_MENU_LINK_NAME,
			LANG_ADMIN_COM_ADMIN_MENU_LINK_ORDER,
			LANG_ADMIN_COM_ADMIN_MENU_ACCESS_LEVEL,
			LANG_ADMIN_COM_ADMIN_MENU_PUBLISHED,
			LANG_ADMIN_COM_ADMIN_MENU_NAME,
			LANG_ADMIN_COM_ADMIN_MENU_URL_VALUE,
			LANG_ADMIN_COM_ADMIN_MENU_INC_FILE
		);
}


/* ----------
   Components */



?>