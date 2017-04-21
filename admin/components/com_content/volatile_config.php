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
	$filter->requestValue('nod_view_title'		)->get() ? $nod_view_title 			= 1 : $nod_view_title 		= 0;
	$filter->requestValue('nod_show_image_thumb')->get() ? $nod_show_image_thumb 	= 1 : $nod_show_image_thumb = 0;
	$filter->requestValue('nod_show_medias'		)->get() ? $nod_show_medias 		= 1 : $nod_show_medias 		= 0;

	$filter->requestValue('elm_show_text_intro'	)->get() ? $elm_show_text_intro 	= 1 : $elm_show_text_intro 	= 0;
	$filter->requestValue('elm_use_text_editor'	)->get() ? $elm_use_text_editor 	= 1 : $elm_use_text_editor 	= 0;

	/* --- Database Process --- */
	if ($submit_validation = $filter->validated())
	{
		$volatile_result =
			$db->update(
					$com_gen->getTablePrefix()."config_item; ".
					"nod_view_title=$nod_view_title, nod_show_image_thumb=$nod_show_image_thumb, nod_show_medias=$nod_show_medias, ".
					"elm_show_text_intro=$elm_show_text_intro, elm_use_text_editor=$elm_use_text_editor"
			);
	}

	$volatile_submit = false; /* ! Always reset the condition ! */
}



//////////////
// Start view

if ($volatile_start_view)
{
	$wrapper = '';

	// Get config_item
	$config = $db->selectOne($com_gen->getTablePrefix().'config_item, *');
	$config = $config;

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'config_');

	/* ------------- INPUTS FORM ------------- */
	$fieldset  = '<h3>'.$com_gen->translate(LANG_ADMIN_COM_CONTENT_CONFIG_NOD_VIEW).'</h3>';
	$fieldset .= $form->checkbox('nod_view_title'	, $config['nod_view_title']	, LANG_ADMIN_COM_CONTENT_CONFIG_NOD_VIEW_TITLE).'<br />';
	$fieldset .= '<h3>'.$com_gen->translate(LANG_ADMIN_COM_CONTENT_CONFIG_ELM_VIEW).'</h3>';
	$fieldset .= $form->checkbox('nod_show_image_thumb', $config['nod_show_image_thumb'], LANG_ADMIN_COM_CONTENT_CONFIG_NOD_SHOW_IMAGE_THUMB).'<br />';
	$fieldset .= $form->checkbox('nod_show_medias'	 , $config['nod_show_medias']	  , LANG_ADMIN_COM_CONTENT_CONFIG_NOD_SHOW_MEDIAS).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_CONFIG_FIELDSET_NODE));  

	$fieldset  = $form->checkbox('elm_show_text_intro'	, $config['elm_show_text_intro']	, LANG_ADMIN_COM_CONTENT_CONFIG_ELM_SHOW_TEXT_INTRO).'<br />';
	$fieldset .= $form->checkbox('elm_use_text_editor'	, $config['elm_use_text_editor']	, LANG_ADMIN_COM_CONTENT_CONFIG_ELM_USE_TEXT_EDITOR).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_CONFIG_FIELDSET_ELEMENT));  
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_start_view = false; /* ! Always reset the condition ! */
}

?>