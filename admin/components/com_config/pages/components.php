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

$filter = new formManager_filter(true);
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('components_', 'post'); // (0)

$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)


// (4) Case 'publish_status'
if ($publish_status)
{
	$published = $db->select("components, published, where: id=$publish_status");
	if ($published)
	{
		$published[0]['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("components; published=$published; where: id=$publish_status");
	}
}


// (0) Case 'submit'
if ($submit)
{
	// Components list (online page)
	$com_list = $db->select('components, id,title, com(asc),page(asc)');
	$result1 = true;
	$result2 = true;
	for ($i=0; $i<count($com_list); $i++)
	{
		$filter->reset();

		$title 			= $filter->requestValue('title_'		.$com_list[$i]['id'])->get();
		$access_level 	= $filter->requestValue('access_level_'	.$com_list[$i]['id'])->getInteger();

		if ($filter->validated())
		{
			$result_temp = $db->update("components; title=".$db->str_encode($title).", access_level=$access_level; where: id=".$com_list[$i]['id']);
			if (!$result_temp) $result1 = false;
		}
		else
		{
			admin_message(LANG_ADMIN_COM_COMPONENTS_UPDATE_FAILED.$com_list[$i]['title'].' ('.$com_list[$i]['com'].', '.$com_list[$i]['page'].')', 'error');
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
	echo '<h2>'.LANG_ADMIN_COM_COMPONENTS_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'components_'); # Form begin

	// Components list (online page)
	$com_list = $db->select('components, id,title, access_level,published, com(asc),page(asc)');
	for ($i=0; $i<count($com_list); $i++)
	{
		// Title
		$com_list[$i]['title'] = $form->text('title_'.$com_list[$i]['id'], $com_list[$i]['title'], '', '', 'size=50'); // (0)

		// Access_level
		$options = comUser_getStatusOptions($com_list[$i]['access_level']);
		$com_list[$i]['access_level'] = $form->select('access_level_'.$com_list[$i]['id'], $options); // (0)

		// Published (<a> tag with checked/unchecked image)
		$com_list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$com_list[$i]['id'].'">'.admin_replaceTrueByChecked($com_list[$i]['published']).'</a>'; // (4)

		// Add link to component page
		$link = comMenu_rewrite("com={$com_list[$i]['com']}&page={$com_list[$i]['page']}");
		$com_list[$i]['url'] = "<a href=\"$link\" class=\"external\">$link</a>";
	}

	// Table
	$table = new tableManager($com_list);

	$table->header(
				array(
					LANG_ADMIN_COM_COMPONENTS_ID,
					LANG_ADMIN_COM_COMPONENTS_TITLE,
					LANG_ADMIN_COM_COMPONENTS_ACCESS_LEVEL,
					LANG_ADMIN_COM_COMPONENTS_PUBLISHED,
					LANG_ADMIN_COM_COMPONENTS_COM,
					LANG_ADMIN_COM_COMPONENTS_PAGE,
					LANG_ADMIN_COM_COMPONENTS_URL
				)
			);

	$table->delCol(0); # Delete the 'id' column

	$html .= $table->html();

	// Buttons
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (0)

	$html .= $form->end(); # End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>