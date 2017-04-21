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
		$volatile_submit,
		$volatile_start_view;

global	$submit_validation; # Sub-Posted forms possibilities (without prefix)

global	$volatile_result;	# To tell back the main script, the result of $db process

global	$html;				# HTML ouput


// admin_comGeneric class object
global $com_gen;
is_subclass_of($com_gen, 'comGeneric_') or trigger_error(LANG_COM_GENERIC_COM_SETUP_MISSING, E_USER_ERROR);



///////////
// Process

// (1) Case 'upd'
if ($volatile_submit)
{
	/* --- Get $_POST['config_*'] (inputs validation : $submit_validation) --- */
	#$filter->requestValue('field1')->get() ? $field1 = 1 : $field1 = 0;
	#$filter->requestValue('field2')->get() ? $field2 = 1 : $field2 = 0;
	#$filter->requestValue('field3')->get() ? $field3 = 1 : $field3 = 0;
	#$filter->requestValue('field4')->get() ? $field4 = 1 : $field4 = 0;

	/* --- Database Process --- */
	if ($submit_validation = $filter->validated())
	{
		#$volatile_result = $db->update(
		#				$com_gen->getTablePrefix()."config_item; ".
		#				"field1=$field1, field2=$field2, field3=$field3, field4=$field4");
		$volatile_result = true;
	}

	$volatile_submit = false; # Always reset the condition !
}


//////////////
// Start view

if ($volatile_start_view)
{
	$wrapper = '';

	// Get config_item
	$config = $db->selectOne($com_gen->getTablePrefix().'config_item, *');

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'config_');

	/* ------------- INPUTS FORM ------------- */
	#$fieldset .= $form->checkbox('field1'	, $config['field1'], FIELD1);
	#$fieldset .= $form->checkbox('field2'	, $config['field2'], FIELD2);
	#$wrapper .= admin_fieldset($fieldset, $com_gen->translate(FIELDSET1));  

	#$fieldset .= $form->checkbox('field3'	, $config['field3'], FIELD3);
	#$fieldset .= $form->checkbox('field4'	, $config['field4'], FIELD4);
	#$wrapper .= admin_fieldset($fieldset, $com_gen->translate(FIELDSET2)); 
	$wrapper = LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC_EMPTY;
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_start_view = false; # Always reset the condition !
}


?>