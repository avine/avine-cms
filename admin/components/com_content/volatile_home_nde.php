<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/**
 * Here the 'volatile_*.php' script wich is required into his 'static_*.php' script,
 * using the $com_gen->volatileFilePath() method.
 * So, this script need some globals variables from his main script to do his work
 */
global	$filter;

global	# Posted forms possibilities (with prefix: '$volatile_*')
		$volatile_new, $volatile_new_submit,
		$volatile_upd, $volatile_upd_submit,
		$volatile_del;

global	# Sub-Posted forms possibilities (without prefix)
		$new_submit_validation,
		$upd_submit_validation;

global	# ID field to create or update table
		$new_id,
		$upd_id,
		$del_id;
		
global	$volatile_result;		# To tell back the main script, the result of $db process

global	$html;					# HTML ouput


// admin_comGeneric class object
global $com_gen;
is_subclass_of($com_gen, 'comGeneric_') or trigger_error(LANG_COM_GENERIC_COM_SETUP_MISSING, E_USER_ERROR);



// (3) Case 'del'
if ($volatile_del)
{
	/* --- Database Process --- */
	$volatile_result = $db->delete($com_gen->getTablePrefix()."home_nde_item; where: home_nde_id=$del_id");

	$volatile_del = false; # Always reset the condition !
}



// (2) Case 'upd'
if ($volatile_upd_submit)
{
	/* --- Get $_POST['upd_*'] (inputs validation : $upd_submit_validation) --- */
	$title = $filter->requestValue('title')->getNotEmpty();
	$header = $filter->requestValue('header')->get();
	$footer = $filter->requestValue('footer')->get();

	/* --- Database Process --- */
	if ($upd_submit_validation = $filter->validated())
	{
		$volatile_result = $db->update(
						$com_gen->getTablePrefix()."home_nde_item; ".
						'title='.$db->str_encode($title).', header='.$db->str_encode($header).', footer='.$db->str_encode($footer).
						"; where: home_nde_id=$upd_id" );
		$volatile_result = true;
	}

	$volatile_upd_submit = false; # Always reset the condition !
}
if ($volatile_upd)
{
	$wrapper = '';

	// Get current
	$current_item = $db->selectOne($com_gen->getTablePrefix()."home_nde_item, *, where: home_nde_id=$upd_id");

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'upd_');

	/* ------------- INPUTS FORM ------------- */
	$fieldset  = $form->text('title'		, $current_item['title'], LANG_ADMIN_COM_CONTENT_HOME_TITLE).LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED;
	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONTENT_HOME_FIELDSET1);  

	$fieldset  = $form->textarea('header'	, $current_item['header'], LANG_ADMIN_COM_CONTENT_HOME_HEADER.'<br />', '', 'cols=70;rows=10').'<br /><br />';
	$fieldset .= $form->textarea('footer'	, $current_item['footer'], LANG_ADMIN_COM_CONTENT_HOME_FOOTER.'<br />', '', 'cols=70;rows=10');

	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONTENT_HOME_FIELDSET2); 
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_upd = false; # Always reset the condition !
}



// (1) Case 'new'
if ($volatile_new_submit)
{
	/* --- Get $_POST['new_*'] (inputs validation : $new_submit_validation) --- */
	$title = $filter->requestValue('title')->getNotEmpty();
	$header = $filter->requestValue('header')->get();
	$footer = $filter->requestValue('footer')->get();

	/* --- Database Process --- */
	if ($new_submit_validation = $filter->validated())
	{
		$volatile_result = $db->insert(
						$com_gen->getTablePrefix()."home_nde_item; $new_id, ".
						$db->str_encode($title).', '.$db->str_encode($header).', '.$db->str_encode($footer) );
	}

	$volatile_new_submit = false; # Always reset the condition !
}
if ($volatile_new)
{
	$wrapper = '';

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'new_');

	/* ------------- INPUTS FORM ------------- */
	$fieldset  = $form->text('title'		, '', LANG_ADMIN_COM_CONTENT_HOME_TITLE).LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED;
	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONTENT_HOME_FIELDSET1);  

	$fieldset  = $form->textarea('header'	, '', LANG_ADMIN_COM_CONTENT_HOME_HEADER.'<br />', '', 'cols=70;rows=10').'<br /><br />';
	$fieldset .= $form->textarea('footer'	, '', LANG_ADMIN_COM_CONTENT_HOME_FOOTER.'<br />', '', 'cols=70;rows=10');

	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONTENT_HOME_FIELDSET2); 
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_new = false; # Always reset the condition !
}


?>