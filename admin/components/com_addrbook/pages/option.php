<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'addrbook_option');
if ($session->get('select_filter') && !$db->select('addrbook_filter, *, where: id='.$session->get('select_filter')))
{
	// Filter has gone !
	$session->reset('select_filter');
}



// Images Html
$png_new = '<img src="'.WEBSITE_PATH.'/admin/images/new.png" alt="new" />';



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



// Update session
if ($submit) {
	$select_filter = $filter->requestValue('select_filter')->getInteger();
	if ($select_filter !== false) {
		$session->set('select_filter', $select_filter);
	}
}



// Case 'del'
if ($del)
{
	if (!$db->select("addrbook_filter_search, addrbook_id, where: option_id=$del"))
	{
		admin_informResult( $db->delete("addrbook_filter_option; where: id=$del") );
	} else {
		admin_message(LANG_ADMIN_COM_ADDRBOOK_OPTION_DEL_ERROR, 'error');
	}
}



if ($new)
{
	$filter->reset();
	if ($name = $filter->requestValue('new_name')->getNotEmpty(1, '', LANG_ADMIN_COM_ADDRBOOK_OPTION_FIELDSET_NEW))
	{
		if (!admin_addrbook_getOptionID($name, $session->get('select_filter')))
		{
			admin_informResult($db->insert('addrbook_filter_option; col: filter_id, name; '.$session->get('select_filter').', '.$db->str_encode($name)));
			$new_name = $name; # Remember !
		} else {
			$filter->set(false, 'new_name')->getError(LANG_ADMIN_COM_ADDRBOOK_OPTION_ERROR_DUPLICATE, LANG_ADMIN_COM_ADDRBOOK_OPTION_FIELDSET_NEW);
		}
	}

	if (!$filter->validated()) {
		echo $filter->errorMessage();
	}
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	// Fields validation
	$upd_id = $filter->requestValue('id')->getInteger();

	$name = $filter->requestValue('name')->getNotEmpty();

	// Duplicate name ?
	$option_id = admin_addrbook_getOptionID($name, $session->get('select_filter'));
	if ($option_id && $option_id != $upd_id) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_ADDRBOOK_OPTION_ERROR_DUPLICATE, LANG_ADDRBOOK_FILTER_OPTION_NAME);
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult( $db->update('addrbook_filter_option; name='.$db->str_encode($name)."; where: id=$upd_id") );
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_FILTER_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("addrbook_filter_option, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= $form->text('name'	, $current['name'], LANG_ADDRBOOK_FILTER_OPTION_NAME.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_OPTION_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Select filter
	$html .= $form->select('select_filter', formManager::selectOption(admin_addrbook_filterList(), $session->get('select_filter', 0)), LANG_ADMIN_COM_ADDRBOOK_OPTION_SELECT_FILTER);
	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT).'<br /><br />';

	if ($session->get('select_filter'))
	{
		// Database
		$list = $db->select('addrbook_filter_option, id, name(asc), where: filter_id='.$session->get('select_filter'));

		for ($i=0; $i<count($list); $i++)
		{
			$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
			$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

			// Highlight new option !
			if (isset($new_name) && $list[$i]['name'] == $new_name)
			{
				$delete[$i] = $png_new; # Replace the delete button
				$list[$i]['name'] = '<span class="green">'.$list[$i]['name'].'</span>';
			}

			$list[$i]['id'] = "<span class=\"grey\">{$list[$i]['id']}</span>"; # Carefull : ID info no more available
		}

		// Table
		$table = new tableManager($list, array('ID', LANG_ADDRBOOK_FILTER_OPTION_NAME));
		#$table->delCol(0); # Delete the 'id' column

		if (count($list)) {
			$table->addCol($delete, 0);
			$table->addCol($update, 999);
		}
		$html .= $table->html();

		$fieldset = $form->text('new_name', '', LANG_ADDRBOOK_FILTER_OPTION_NAME, '', isset($new_name) ? 'update=0' : '');
		$fieldset .= $form->submit('new', LANG_BUTTON_CREATE);
		$html .= '<br />'.admin_fieldset($fieldset, LANG_ADMIN_COM_ADDRBOOK_OPTION_FIELDSET_NEW);
	}

	$html .= $form->end();
	echo $html;
}


echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>