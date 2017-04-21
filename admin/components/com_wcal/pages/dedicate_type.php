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
	$del = $filter->requestName ('del_'	)->getInteger();
	$upd = $filter->requestName ('upd_'	)->getInteger();
	$new = $filter->requestValue('new'	)->get();
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post');
$new_submit = formManager::isSubmitedForm('new_', 'post');



// Case 'del'
if ($del)
{
	if (!$db->selectOne("wcal_dedicate, id, where: type_id=$del", 'id'))
	{
		admin_informResult($db->delete("wcal_dedicate_type; where: id=$del"));
	} else {
		admin_message(LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_DEL_ERROR, 'error');
	}
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	// Get id
	$upd_id = $filter->requestValue('id')->getInteger();

	$title	= $db->str_encode($t = $filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_WCAL_DEDICATE_TYPE_TITLE));
	$sample	= $db->str_encode(strip_tags($filter->requestValue('sample')->get()));

	// Duplicate title ?
	if ($t && $db->selectOne("wcal_dedicate_type, id, where: title=$title AND, where: id!=$upd_id", 'id'))
	{
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_DUPLICATE_TITLE);
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult($db->update("wcal_dedicate_type; title=$title, sample=$sample; where: id=$upd_id"));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("wcal_dedicate_type, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= $form->text('title'		, $current['title'	], LANG_COM_WCAL_DEDICATE_TYPE_TITLE	.'<br />', '', 'size=50').'<br /><br />';
	$html .= $form->textarea('sample'	, $current['sample'	], LANG_COM_WCAL_DEDICATE_TYPE_SAMPLE	.'<br />', '', 'cols=70;rows=7').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$title	= $db->str_encode($t = $filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_WCAL_DEDICATE_TYPE_TITLE));
	$sample	= $db->str_encode(strip_tags($filter->requestValue('sample')->get()));

	// Duplicate title ?
	if ($t && $db->selectOne("wcal_dedicate_type, id, where: title=$title", 'id'))
	{
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_DUPLICATE_TITLE);
	}

	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult($db->insert("wcal_dedicate_type; NULL, $title,$sample"));
	} else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('title'		, '', LANG_COM_WCAL_DEDICATE_TYPE_TITLE	.'<br />', '', 'size=50').'<br /><br />';
	$html .= $form->textarea('sample'	, '', LANG_COM_WCAL_DEDICATE_TYPE_SAMPLE.'<br />', '', 'cols=70;rows=7').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_TITLE_START.'</h2>';

	$html = '';

	// Database
	$list = $db->select('wcal_dedicate_type, id, title(asc)');

	if (!count($list)) {
		admin_message(LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_EMPTY_WARNING, 'warning');
	}

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($list); $i++)
	{
		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

		$list[$i]['id'] = '<span class="grey">'.$list[$i]['id'].'</span>'; # ID data no more available
	}

	// Table
	$table = new tableManager($list);

	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(array('', 'ID', LANG_COM_WCAL_CATEGORY_TITLE, ''));
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>