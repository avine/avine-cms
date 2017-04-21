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
$submit = formManager::isSubmitedForm('mod_default_', 'post'); // (0)

if ($submit)
{
	$update	= $filter->requestValue('update')->get();			// (2)
	$del	= $filter->requestName ('del_'	)->getInteger();	// (3)
} else {
	$update	= false;
	$del	= false;
}

$update_submit = formManager::isSubmitedForm('update_submit_', 'post'); // (2)



// (3) Case 'delete'
if ($del)
{
	admin_informResult( $db->delete('module_default; where: mod_id='.$del) );
}



// (2) Case 'update'
if ($update_submit)
{
	$mod_id = formManager_filter::arrayOnly( $filter->requestValue('mod_id')->getInteger(), false );

	$result = true;
	$db->delete('module_default');
	for ($i=0; $i<count($mod_id); $i++) {
		$db->insert('module_default; '.$mod_id[$i]) or $result = false;
	}
	admin_informResult($result);
}
if ($update)
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_DEFAULT_UPDATE.'</h2>';

	// Current default modules
	$default = array_keys($db->select('module_default, [mod_id]'));

	// Modules options
	$options = array();
	$module = $db->select('module, id, mod_pos(asc), name(asc), mod_file');
	$mod_pos = false;
	for ($i=0; $i<count($module); $i++)
	{
		if ($module[$i]['mod_pos'] != $mod_pos) {
			$mod_pos = $module[$i]['mod_pos'];
			$options["$mod_pos(optgroup)"] = '';
		}
		$options[$module[$i]['id']] = $module[$i]['name'].' ('.$module[$i]['mod_file'].')';
	}

	$html = '';
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'update_submit_');

	// Select
	count($options) > 20 ? $size = 20 : $size = count($options);
	$html .= $form->select('mod_id', formManager::selectOption($options, $default), '', '', "multiple;size=$size").'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br /><br />';
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_DEFAULT_TITLE_START.'</h2>';

	$html = '';

	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'mod_default_');

	$default = $db->select('module_default, mod_id, join: mod_id>; module, mod_pos(asc), name(asc), mod_file, join: <id');
	for ($i=0; $i<count($default); $i++) {
		$delete[$i] = $form->submit('del_'.$default[$i]['mod_id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
	}

	// Table
	$table = new tableManager($default);
	$table->delCol(0); # Delete mod_id column

	if (count($default)) {
		$table->addCol($delete, 0);
	}
	$table->header(array('', LANG_ADMIN_COM_MODULE_MODULE_POS, LANG_ADMIN_COM_MODULE_MODULE_NAME, LANG_ADMIN_COM_MODULE_MODULE_FILE));

	$html .= $table->html();

	$html .= $form->submit('update', LANG_ADMIN_BUTTON_UPDATE).'<br /><br />'; // (2)
	$html .= $form->end();
	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>