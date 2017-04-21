<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


# TODO - Pouvoir rajouter un lock dans schedule_sheet pour ne plus modifier la table schedule associée...


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

if ($submit = formManager::isSubmitedForm('start_', 'post')) // (0)
{
	// Update schedule_config
	$filter->requestValue('use_sheet_menu')->get() ? $use_sheet_menu = '1' : $use_sheet_menu = '0';
	admin_informResult( $db->update("schedule_config; use_sheet_menu=$use_sheet_menu") );
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$sheet = $db->select('schedule_sheet, id, sheet_order(asc), title, tmpl_id, published');

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// schedule_config
	if ($db->selectOne('schedule_config, use_sheet_menu', 'use_sheet_menu')) {
		$use_sheet_menu	= '[1]';
		$display_all	=  '0';
	} else {
		$use_sheet_menu	=  '1';
		$display_all	= '[0]';
	}
	$html .= $form->radio('use_sheet_menu', $use_sheet_menu, LANG_ADMIN_COM_SCHEDULE_CONFIG_USE_MENU, 'config_use_menu').'<br />';
	$html .= $form->radio('use_sheet_menu', $display_all, LANG_ADMIN_COM_SCHEDULE_CONFIG_DISPLAY_ALL, 'config_display_all').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'&nbsp; &nbsp;'; // (0)
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>