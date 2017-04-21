<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


global $db;
$session = new sessionManager(sessionManager::FRONTEND, 'schedule');

$filter = new formManager_filter();
$filter->requestVariable('post');

$submit = formManager::isSubmitedForm('schedule_', 'post'); // (0)

$schedule = new comSchedule_();
$sheets_options = $schedule->sheetsOptions('', false);

$config = $db->selectOne('schedule_config, *');


// Title
echo '<h1>'.LANG_COM_SCHEDULE_INDEX_TITLE.'</h1>';

if (count($sheets_options))
{
	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', formManager::reloadPage(), 'schedule_');

	if ($config['use_sheet_menu'] && count($sheets_options) > 1)
	{
		// Session
		list($sheet_id, $sheet_title) = each($sheets_options); # First sheet
		$session->init('sheet_id_selector', $sheet_id);
		if ($submit) {
			$session->set('sheet_id_selector', $filter->requestValue('sheet_id_selector')->getInteger());
		}
		elseif ($session->get('sheet_id_selector') && !$db->selectCount('schedule_sheet, where: id='.$session->get('sheet_id_selector'))) {
			$session->reset(); # This sheet has gone !
		}
		$html .= '<p>'.$form->select('sheet_id_selector', formManager::selectOption($sheets_options, $session->get('sheet_id_selector')));
		$html .= $form->submit('submit', LANG_BUTTON_SUBMIT).'</p>'; // (0)

		$sheet_id_selector[] = $session->get('sheet_id_selector');
	}
	else {
		$sheet_id_selector = array_keys($sheets_options);
	}

	for ($i=0; $i<count($sheet_id_selector); $i++)
	{
		$html .= $schedule->displaySheet($sheet_id_selector[$i]);
		($i == count($sheet_id_selector)-1) or $html .= '<hr />';
	}

	$html .= $form->end(); // End of Form
	echo $html;
}
else {
	echo '<p>'.LANG_COM_SCHEDULE_SCHEDULE_NO_SHEET_AVAILABLE.'</p>';
}


?>