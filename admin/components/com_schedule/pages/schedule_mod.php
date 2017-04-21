<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


# TODO - Pouvoir rajouter un lock dans schedule_sheet pour ne plus modifier la table schedule associée...


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Instanciate
$schedule = new comSchedule_();



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post'); // (0)


$update_form = 1;
if ($submit)
{
	$filter->reset();

	$mod_suffix = $filter->requestValue('mod_suffix')->getID(1, '', LANG_ADMIN_COM_SCHEDULE_MODULE_SUFFIX);
	$sheet_id = $filter->requestValue('sheet_id')->getInteger(1, LANG_ADMIN_COM_SCHEDULE_MODULE_SHEET_ERROR, LANG_ADMIN_COM_SCHEDULE_MODULE_SHEET);

	$module_filename = "mod_schedule_$mod_suffix.php";
	if ($filter->validated())
	{
		$ftp = new ftpManager(sitePath().'/modules/');
		if ($ftp->isFile($module_filename)) {
			$filter->set($module_filename)->getError(LANG_ADMIN_COM_SCHEDULE_MODULE_SUFFIX_ALREADY_EXISTS);
		}
	}

	if ($filter->validated())
	{
		$result = $ftp->write($module_filename, 
			"<?php\n\n".
			'$schedule = new comSchedule_();'."\n\n".
			'echo $schedule->displaySheetModule('.$sheet_id.');'."\n\n".
			'echo "<div><a href=\"".comMenu_rewrite("com=schedule&amp;page=index")."\">".LANG_COM_SCHEDULE_MODULE_COMPONENT_LINK."</a></div>";'.
			"\n\n?>"
		);

		admin_informResult($result, str_replace('{filename}', $module_filename, LANG_ADMIN_COM_SCHEDULE_MODULE_NEW_SUCCESS));

		if ($result) {
			$update_form = 0;
		}
	}
	else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_MODULE_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager($update_form);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	$html .= $form->label('module_suffix', LANG_ADMIN_COM_SCHEDULE_MODULE_SUFFIX.'<br />');
	$html .= '<span class="grey">mod_schedule_'.$form->text('mod_suffix', '', '', '', 'size=18').'.php</span><br /><br />';

	$html .= $form->select('sheet_id', $schedule->sheetsOptions(), LANG_ADMIN_COM_SCHEDULE_MODULE_SHEET.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (0)
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>