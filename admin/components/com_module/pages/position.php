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

$filter = new formManager_filter(1);
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('modpos_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_')->getVar();	// (3)
	$upd = $filter->requestName ('upd_')->getVar();	// (2)
	$new = $filter->requestValue('new' )->get();	// (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// (3) Case 'del'
if ($del)
{
	// Database Process
	if (!$db->select('module, id, where: mod_pos='.$db->str_encode($del))) # No module is using this position, then ok!
	{
		$result = $db->delete('module_pos; where: pos='.$db->str_encode($del));

		admin_informResult($result);
	}
	else {
		admin_message(LANG_ADMIN_COM_MODULE_POSITION_USED_BY_MODULE, 'error');
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	$pos  = $filter->requestValue('pos' )->getVar();
	$desc = $filter->requestValue('desc')->get();

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update('module_pos; comment='.$db->str_encode($desc).'; where: pos='.$db->str_encode($pos));

		admin_informResult($result);
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_POSITION_TITLE_UPDATE.'</h2>';

	// Id
	if ($upd)
	{
		$pos = $upd;
	} else {
		# $pos already set before (see (*))
	}

	$current = $db->select('module_pos, comment, where: pos='.$db->str_encode($pos));

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('pos', $pos);
	$html .= LANG_ADMIN_COM_MODULE_POSITION_POS.' : <strong>'.$pos.'</strong> &nbsp; &nbsp; &nbsp; ';
	$html .= $form->text('desc', $current[0]['comment'], LANG_ADMIN_COM_MODULE_POSITION_DESC, '', 'maxlength=255');
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;

	$filter->reset();

	$pos  = $filter->requestValue('pos' )->getVar();
	$desc = $filter->requestValue('desc')->get();

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result = $db->insert('module_pos; '.$db->str_encode($pos).', '.$db->str_encode($desc));

		admin_informResult($result, '', LANG_ADMIN_COM_MODULE_POSITION_POS_ALREADY_EXIST);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_POSITION_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');
	$html .= $form->text('pos',  '', LANG_ADMIN_COM_MODULE_POSITION_POS , '', 'maxlength=50' );
	$html .= $form->text('desc', '', LANG_ADMIN_COM_MODULE_POSITION_DESC, '', 'maxlength=255');
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_POS_TITLE_START.'</h2>';

	$html = '';

	// Database
	$module_pos = $db->select('module_pos, pos(asc), comment');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'modpos_');

	for ($i=0; $i<count($module_pos); $i++)
	{
		$update[$i] = $form->submit('upd_'.$module_pos[$i]['pos'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (1)
		$delete[$i] = $form->submit('del_'.$module_pos[$i]['pos'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (2)
	}

	// Table
	$table = new tableManager($module_pos);

	if (count($module_pos)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}

	$table->header(array('', LANG_ADMIN_COM_MODULE_POSITION_POS, LANG_ADMIN_COM_MODULE_POSITION_DESC, ''));
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (3)

	$html .= $form->end();
	
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>