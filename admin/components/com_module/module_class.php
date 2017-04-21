<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions


// Search & validate modules into '/modules' directory
function admin_comModule_readDir()
{
	$module = array();

	clearstatcache();

	$dir = opendir(sitePath().'/modules');
	while ($mod_file = readdir($dir))
	{
		if ( ($mod_file != '.') && ($mod_file != '..') && ($mod_file != 'index.html') )
		{
			$module[] = $mod_file;
		}
	}
	closedir($dir);

	return $module;
}



// Re-order the modules list by 'name' using the powerfull usort() function
function admin_comModule_orderByfile( $x, $y )
{
	if ($x['file_for_order'] == $y['file_for_order']) {
		return 0;
	}
	elseif ($x['file_for_order'] < $y['file_for_order']) {
		return -1;
	}
	else {
		return 1;
	}
}



/**
 * Some selects fields
 */

// Return a select of availables positions (selected position returned by reference with $selected variable)
function admin_comModule_selectPosition ( $form_prefix, $field_name,  $selected_pos = false )
{
	global $db;
	$position_list = $db->select('module_pos, pos(asc)');

	if ($position_list)
	{
		$temp = array();
		$temp['no-filter'] = LANG_SELECT_OPTION_ROOT; # Important: formManager::isVar('no-filter') == false

		for ($i=0; $i<count($position_list); $i++)
		{
			$temp[$position_list[$i]['pos']] = $position_list[$i]['pos'];
		}
		$temp = formManager::selectOption($temp, $selected_pos);
		$position_list = $temp;

		$form = new formManager();
		$form->setForm('post', $form_prefix); # Only set the $this->form['method'] and the $this->form['name']. No Html-output of <form></form> tag

		return $form->select($field_name, $position_list, LANG_ADMIN_COM_MODULE_POSITION_FILTER);
	}
	else
	{
		admin_message(LANG_ADMIN_COM_MODULE_NO_POSITION_AVAILABLE, 'error');
		return false;
	}
}



// Return a select of availables modules (selected module returned by reference with $selected variable)
function admin_comModule_selectModule( $form_prefix, $field_name, $selected_mod = false )
{
	global $db;
	$file_list = $db->select('module_list, file(asc)');

	if ($file_list)
	{
		$temp = array();
		$temp['no-filter'] = LANG_SELECT_OPTION_ROOT; # Important: formManager::isFile('no-filter') == false

		for ($i=0; $i<count($file_list); $i++)
		{
			/**
			 * Careful! The '$file_list' can contain '.' symbol (exemple: my_module.php). After posted into the form, they will be replaced by '_'.
			 * Then for the name attribute, we should use our formManager::encodePoint() method.
			 */
			$temp[formManager::encodePoint($file_list[$i]['file'])] = $file_list[$i]['file'];
		}
		$temp = formManager::selectOption($temp, formManager::encodePoint($selected_mod));
		$file_list = $temp;

		$form = new formManager();
		$form->setForm('post', $form_prefix); # Only set the $this->form['method'] and the $this->form['name']. No Html-output of <form></form> tag

		return $form->select($field_name, $file_list, LANG_ADMIN_COM_MODULE_MODULE_FILTER);
	}
	else
	{
		admin_message(LANG_ADMIN_COM_MODULE_NO_MODULE_AVAILABLE, 'error');
		return false;
	}
}



// Return a select of all availables pages
function admin_comModule_selectPage( $form_prefix, $field_name, $current_mod_id = false )
{
	global $db;
	$menu_id = $db->select("menu, id, name(asc)");

	$link_id_x_name = array();
	$link_id_x_href = array();

	if ($menu_id) 
	{
		// All availables pages of the site
		for ($i=0; $i<count($menu_id); $i++)
		{
			$temp = admin_comModule_getMenu($menu_id[$i]['id']);

			for ($j=0; $j<count($temp); $j++)
			{
				$link_id_x_name[$temp[$j]['id']] = $temp[$j]['name'];
				$link_id_x_href[$temp[$j]['id']] = $temp[$j]['href'];
			}
		}

		// Keep only the primary links
		reset($link_id_x_name);
		$temp = array();
		$primary_link_message = '';
		foreach($link_id_x_name as $id => $name)
		{
			if (!admin_comModule_findPrimaryLink($id)) {
				$temp[$id] = $name;
			} else {
				$primary_link_message = LANG_ADMIN_COM_MODULE_LINK_FILTER_TIPS_PRIMARY_LINK;
			}
		}
		$link_id_x_name = $temp;

		// For the update form, we need to get the currents pages where the module appears
		$default_module_message = '';
		if ($current_mod_id)
		{
			reset($link_id_x_href);

			$pages = array();
			foreach($link_id_x_href as $link_id => $link_href)
			{
				// Alternative 1: Match process based on 'link_href'
				#$module_on_page = $db->select('module_xhref, id, where: mod_id='.$current_mod_id.' AND, where: link_href='.$db->str_encode($link_href));

				// Alternative 2: Match process based on 'link_id'
				$module_on_page = $db->select('module_xhref, id, where: mod_id='.$current_mod_id." AND, where: link_id=$link_id");

				if ($module_on_page) {
					$pages[] = $link_id;
				}
			}
			$link_id_x_name = formManager::selectOption($link_id_x_name, $pages);

			// Is this a default module ?
			if ($db->selectOne("module_default, mod_id, where: mod_id=$current_mod_id")) {
				$default_module_message = ' '.LANG_ADMIN_COM_MODULE_LINK_FILTER_TIPS_MODULE_DEFAULT;
			}
		}

		// Return the form select
		$form = new formManager();
		$form->setForm('post', $form_prefix); # Only set the $this->form['method'] and the $this->form['name']. No Html-output of <form></form> tag
		(count($link_id_x_name) > 15) ? $size = 15 : $size = count($link_id_x_name);
		return $form->select($field_name, $link_id_x_name, LANG_ADMIN_COM_MODULE_LINK_FILTER.$default_module_message.'<br />', '', "size=$size;multiple").$primary_link_message;
	}
	else
	{
		admin_message(LANG_ADMIN_COM_MODULE_NO_MENU_AVAILABLE, 'error');
		return false;
	} 
}



// Return a menu links list (this is the com_module version of the admin_comMenu_getMenu() function)
function admin_comModule_getMenu( $menu_id, $parent_id = 0, $link_prefix = '' )
{
	static $menu_links = array();
	if ($parent_id == 0) {
		$menu_links = array(); # Reset when begining
	}

	global $db;

	$menu_name = $db->select("menu, name, where: id=$menu_id"); $menu_name = $menu_name[0]['name'];
	if ($link_prefix == '') {
		$link_prefix = '| '.$menu_name;
	}

	if ($link = $db->select("menu_link, id, name, href, link_order(asc), where: menu_id=$menu_id AND, where: parent_id=$parent_id"))
	{
		for ($i=0; $i<count($link); $i++)
		{
			$temp['id'] 	= $link[$i]['id'];
			$temp['name'] 	= $link_prefix.' | '.strip_tags($link[$i]['name']);
			$temp['href'] 	= $link[$i]['href'];
			array_push($menu_links, $temp);

			admin_comModule_getMenu( $menu_id, $temp['id'], $temp['name'] );
		}
	}
	return $menu_links;
}



// This method return false if $link_id is itself the primary link of the associated page. Else, the method wil determine who is it !
function admin_comModule_findPrimaryLink( $link_id )
{
	global $db;

	if ($link = $db->selectOne("menu_link, href,unique_id,link_type_id, where: id=$link_id"))
	{
		$link_href 		= $link['href'			];
		$link_unique_id = $link['unique_id'		];
		$link_type_id 	= $link['link_type_id'	];

		// This link have a unique_id (or it's a simple separator), so no problem !
		if ($link_unique_id == 1 || $link_type_id == 6) {
			return false;
		}

		$primary_link_id = false;

		if ($link_type_id != 7)
		{
			// The primary link is type of url (even with a bigger ID)
			$primary_link_id = $db->selectOne("menu_link, id(asc), where: href=".$db->str_encode($link_href)." AND, where: link_type_id=7", 'id');

			if (!$primary_link_id)
			{
				// The primary link have a smaller ID
				$primary_link_id = $db->selectOne("menu_link, id(asc), where: href=".$db->str_encode($link_href)." AND, where: id<$link_id", 'id');
			}
		}
		else
		{
			// The link is type of url, so only another url with a smaller ID can be the primary link !
			$primary_link_id = $db->selectOne("menu_link, id(asc), where: href=".$db->str_encode($link_href)." AND, where: id<$link_id AND, where: link_type_id=7", 'id');
		}

		if ($primary_link_id)
		{
			$primary_link = $db->selectOne("menu_link, name, where: id=$primary_link_id, join: menu_id>; menu, name AS menu_name, join: <id");

			return
				array(
					'id'		=>	$primary_link_id,
					'name'		=>	$primary_link['name'],
					'menu_name'	=>	$primary_link['menu_name']
				);
		}
	}

	return false;
}


?>