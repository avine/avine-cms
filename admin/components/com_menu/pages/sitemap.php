<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post');
if ($submit)
{
	$update = $filter->requestValue('update')->get();
	$exclude = $filter->requestValue('exclude')->get();
} else {
	$update = false;
	$exclude = false;
}



// Case 'update'
if ($update)
{
	// Start from scratch !
	$result = $db->delete('menu_sitemap');

	// Update the menus order
	$menu_ordered = array();

	$menu = $db->select('menu, id');
	for ($i=0; $i<count($menu); $i++)
	{
		if ($order = $filter->requestValue('menu_order_'.$menu[$i]['id'])->getInteger())
		{
			$menu_ordered[$menu[$i]['id']] = $order;
		}
	}
	asort($menu_ordered);
	$menu_ordered = array_keys($menu_ordered);

	for ($i=0; $i<count($menu_ordered); $i++)
	{
		$db->insert("menu_sitemap; col: info_type,info_id; 'menu_order',".$menu_ordered[$i]) or $result = false;
	}

	// Update the list of excluded menus
	if ($exclude_menu = $filter->requestName('exclude_menu_')->getInteger())
	{
		for ($i=0; $i<count($exclude_menu); $i++)
		{
			$db->insert("menu_sitemap; col: info_type,info_id; 'exclude_menu',".$exclude_menu[$i]) or $result = false;
		}
	}

	// Update the list of excluded links
	if ($exclude_link = $filter->requestName('exclude_link_')->getInteger())
	{
		for ($i=0; $i<count($exclude_link); $i++)
		{
			$db->insert("menu_sitemap; col: info_type,info_id; 'exclude_link',".$exclude_link[$i]) or $result = false;
		}
	}

	admin_informResult($result);
}



// Case 'update'
if ($exclude)
{
	// List of unpublished links (from the menu links)
	$link = $db->select('menu_link, [id], where: published=0');

	// Current excluded links (from the sitemap)
	$exclude_link = $db->select("menu_sitemap, [info_id], where: info_type='exclude_link'");

	// Extract the list of unpublished links wich are still not excluded
	$diff = array_keys(array_diff_key($link, $exclude_link));

	$new_links_excluded = 0;
	for ($i=0; $i<count($diff); $i++) {
		$db->insert("menu_sitemap; col: info_type,info_id; 'exclude_link',".$diff[$i]);
		$new_links_excluded++;
	}

	admin_informResult($new_links_excluded, LANG_ADMIN_COM_MENU_SITEMAP_RESULT_NEW_LINKS_EXCLUDED.$new_links_excluded, LANG_ADMIN_COM_MENU_SITEMAP_RESULT_NO_NEW_LINKS_TO_EXCLUDE);
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_MENU_SITEMAP_TITLE_START.'</h2>';

	$html = '';

	// Sitemap
	$sitemap = new comMenu_siteMap();
	$menu_ordered = $sitemap->getMenuOrder();

	// Menu comment
	$menu = $db->select('menu, [id], comment');

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	$list = array();
	for ($i=0; $i<count($menu_ordered); $i++)
	{
		// Get the links of the menu
		$link = admin_comMenu_getMenu($menu_ordered[$i]);

		// Get the current status of the menu
		$menu_is_excluded = in_array($menu_ordered[$i], $sitemap->getExcludeMenu());

		// If the menu is empty of links, exclude it automatically !
		if (!count($link) && !$menu_is_excluded)
		{
			$db->insert("menu_sitemap; col: info_type,info_id; 'exclude_menu',".$menu_ordered[$i]);
			$menu_is_excluded = true; # Overwrite the current status (from now, the variable $sitemap->getExcludeMenu() is not up to date !)
		}

		// Add the menu to the list
		$list[] =
			array(
				$form->text('menu_order_'.$menu_ordered[$i], 2*$i+1, '', '', 'size=2'),
				'<h4>'.$menu[$menu_ordered[$i]]['comment'].'</h4>',
				$form->checkbox('exclude_menu_'.$menu_ordered[$i], $menu_is_excluded, '', '', count($link) ? '' : 'disabled')
			);

		// Add the links to the list
		for ($j=0; $j<count($link); $j++)
		{
			$params = setArrayOfParam($link[$j]['params']);
			if (($link[$j]['link_type'] != 'url') || (isset($params['dynamic'])))
			{
				$href = comMenu_rewrite($link[$j]['href']);
			} else {
				$href = $link[$j]['href'];
			}

			$list[] =
				array(
					'',
					'<a href="'.$href.'" class="external">'.$link[$j]['name'].'</a>',
					$form->checkbox('exclude_link_'.$link[$j]['id'], in_array($link[$j]['id'], $sitemap->getExcludeLink()))
				);
		}
	}

	// Table
	$table = new tableManager($list);
	$table->header(
		array(
			LANG_ADMIN_COM_MENU_SITEMAP_MENU_ORDER,
			LANG_ADMIN_COM_MENU_SITEMAP_MENUS_AND_LINKS,
			LANG_ADMIN_COM_MENU_SITEMAP_EXCLUDE
		)
	);
	$html .= $table->html();

	$html .= $form->submit('update', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('exclude', LANG_ADMIN_COM_MENU_SITEMAP_BUTTON_EXCLUDE_UNPUBLISHED_LINKS);

	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>