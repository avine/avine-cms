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
$submit = formManager::isSubmitedForm('admin_menu_', 'post');

$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)



// (4) Case 'publish_status'
if ($publish_status)
{
	$published = $db->selectOne("admin_menu, published, where: id=$publish_status");
	if ($published)
	{
		$published['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("admin_menu; published=$published; where: id=$publish_status");
	}
}



// (0) Case 'submit'
if ($submit)
{
	// Admin_menu list
	$admin_menu_list = admin_system_ListAdminMenu();

	$result1 = true;
	$result2 = true;
	for ($i=0; $i<count($admin_menu_list); $i++)
	{
		$filter->reset();

		$link_order   = $filter->requestValue('link_order_'  .$admin_menu_list[$i]['id'])->getInteger();
		$access_level = $filter->requestValue('access_level_'.$admin_menu_list[$i]['id'])->getInteger();

		if ($filter->validated())
		{
			$result_temp = $db->update("admin_menu; link_order=$link_order, access_level=$access_level; where: id=".$admin_menu_list[$i]['id']);
			if (!$result_temp) $result1 = false;
		}
		else
		{
			admin_message(LANG_ADMIN_COM_ADMIN_MENU_UPDATE_FAILED.$admin_menu_list[$i]['link_name'].' ('.$admin_menu_list[$i]['name'].', '.$admin_menu_list[$i]['url_value'].')', 'error');
			$result2 = false;
		}
	}
	if ($result2) admin_informResult($result1);
}


//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADMIN_MENU_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'admin_menu_'); # Form begin

	// Admin_menu list
	$admin_menu_list = admin_system_ListAdminMenu();
	for ($i=0; $i<count($admin_menu_list); $i++)
	{
		// Link_order
		$admin_menu_list[$i]['link_order'] = $form->text('link_order_'.$admin_menu_list[$i]['id'], $admin_menu_list[$i]['link_order'], '', '', 'size=1'); // (0)

		// Access_level
		$options = comUser_getStatusOptions($admin_menu_list[$i]['access_level'], true);
		$admin_menu_list[$i]['access_level'] = $form->select('access_level_'.$admin_menu_list[$i]['id'], $options); // (0)

		// Published
		if ($admin_menu_list[$i]['name'] != 'system' && $admin_menu_list[$i]['url_value'] != 'system')
		{
			$admin_menu_list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$admin_menu_list[$i]['id'].'">'.admin_replaceTrueByChecked($admin_menu_list[$i]['published']).'</a>'; // (4)
		} else {
			$admin_menu_list[$i]['published'] = admin_replaceTrueByChecked($admin_menu_list[$i]['published'], false); # 'com_config' must be always available !
		}
	}

	// Table
	$table = new tableManager($admin_menu_list);
	$table->header(admin_system_HeaderAdminMenu());

	$table->delCol(0); # Delete the 'id' column

	$html .= $table->html();

	// Buttons
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (0)

	$html .= $form->end(); # End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>