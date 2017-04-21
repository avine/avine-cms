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



// Resources list (for select form options)
$ftp = new ftpManager(sitePath());
$images_list =
	$ftp->setTree(RESOURCE_PATH)
			->reduceTree('remove_invalid_dir_and_file')
			->reduceTree('keep_file_by_extension', array('jpg', 'gif', 'png'))
			->getTree('file_options');
$images_list = array_merge( array('' => LANG_SELECT_OPTION_ROOT), $images_list ); # Add root option



// (3) Case 'del'
if ($volatile_del)
{
	/* --- Database Process --- */
	$volatile_result = $db->delete($com_gen->getTablePrefix()."node_item; where: node_id=$del_id");

	$volatile_del = false; /* ! Always reset the condition ! */
}



// (2) Case 'upd'
if ($volatile_upd_submit)
{
	/* --- Get $_POST['upd_*'] (inputs validation : $upd_submit_validation) --- */
	$title 			= $filter->requestValue('title')->getNotEmpty();
	$title_alias 	= $filter->requestValue('title_alias')->get();
	$text 			= $filter->requestValue('text')->get();
	$image 			= $filter->requestValue('image')->getPathFile(0);

	$filter->requestValue('view_title')->get() ? $view_title = 1 : $view_title = 0;

	$filter->requestValue('show_image_thumb')->get() ? $show_image_thumb 	= 1 : $show_image_thumb 	= 0;
	$filter->requestValue('show_medias'		)->get() ? $show_medias 		= 1 : $show_medias 			= 0;

	/* --- Database Process --- */
	if ($upd_submit_validation = $filter->validated())
	{
		$volatile_result =
			$db->update(
					$com_gen->getTablePrefix()."node_item; ".
					'title='.$db->str_encode($title).', title_alias='.$db->str_encode($title_alias).', '.
					'text='.$db->str_encode($text).', image='.$db->str_encode($image).', '.
					"view_title=$view_title, show_image_thumb=$show_image_thumb, show_medias=$show_medias;".
					"where:  node_id=$upd_id"
			);
	}

	$volatile_upd_submit = false; /* ! Always reset the condition ! */
}
if ($volatile_upd)
{
	$wrapper = '';

	// Get node_item
	$current_item = $db->selectOne($com_gen->getTablePrefix()."node_item, *, where: node_id=$upd_id");

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'upd_');

	/* ------------- INPUTS FORM ------------- */
	// Title, view_title, title_alias, text, image
	$fieldset  = $form->text('title'			, $current_item['title']		, '(right)'.LANG_ADMIN_COM_CONTENT_NODE_TITLE, '', 'size=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED.'&nbsp; &nbsp;';
	$fieldset .= $form->checkbox('view_title'	, $current_item['view_title']	, LANG_ADMIN_COM_CONTENT_NODE_VIEW_TITLE).'<br />';
	$fieldset .= $form->text('title_alias'		, $current_item['title_alias']	, '(right)'.LANG_ADMIN_COM_CONTENT_NODE_TITLE_ALIAS, '', 'size=50').'<br />';
	$fieldset .= $form->textarea('text'			, $current_item['text']			, LANG_ADMIN_COM_CONTENT_NODE_TEXT.'<br />', '', 'cols=70;rows=10').'<br />';
	$fieldset .= $form->select('image', formManager::selectOption($images_list, $current_item['image']), '(right)'.LANG_ADMIN_COM_CONTENT_NODE_IMAGE).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_NODE_FIELDSET1));

	// Show...
	$fieldset  = $form->checkbox('show_image_thumb'	, $current_item['show_image_thumb']	, LANG_ADMIN_COM_CONTENT_NODE_SHOW_IMAGE_THUMB).'<br />';
	$fieldset .= $form->checkbox('show_medias'		, $current_item['show_medias']		, $com_gen->translate(LANG_ADMIN_COM_CONTENT_NODE_SHOW_MEDIAS)).'<br />';

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_NODE_FIELDSET2));
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_upd = false; /* ! Always reset the condition ! */
}



// (1) Case 'new'
if ($volatile_new_submit)
{
	/* --- Get $_POST['new_*'] (inputs validation : $new_submit_validation) --- */
	$title 			= $filter->requestValue('title')->getNotEmpty();
	$title_alias 	= $filter->requestValue('title_alias')->get();
	$text 			= $filter->requestValue('text')->get();
	$image 			= $filter->requestValue('image')->getPathFile(0);

	$filter->requestValue('view_title'		)->get() ? $view_title 			= 1 : $view_title 			= 0;
	$filter->requestValue('show_image_thumb')->get() ? $show_image_thumb 	= 1 : $show_image_thumb 	= 0;
	$filter->requestValue('show_medias'		)->get() ? $show_medias 		= 1 : $show_medias 			= 0;

	/* --- Database Process --- */
	if ($new_submit_validation = $filter->validated())
	{
		$volatile_result =
			$db->insert(
					$com_gen->getTablePrefix()."node_item; $new_id, ".
					$db->str_encode($title).', '.$db->str_encode($title_alias).', '.
					$db->str_encode($text).', '.$db->str_encode($image).', '.
					"$view_title, $show_image_thumb, $show_medias"
			);
	}

	$volatile_new_submit = false; /* ! Always reset the condition ! */
}
if ($volatile_new)
{
	$wrapper = '';

	// Get config_item
	$config = $db->selectOne($com_gen->getTablePrefix().'config_item, *');

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'new_');

	/* ------------- INPUTS FORM ------------- */
	// Title, view_title, title_alias, text, image
	$fieldset  = $form->text('title'					, '', '(right)'.LANG_ADMIN_COM_CONTENT_NODE_TITLE, '', 'size=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED.'&nbsp; &nbsp;';
	$fieldset .= $form->checkbox('view_title'			, $config['nod_view_title']	, LANG_ADMIN_COM_CONTENT_NODE_VIEW_TITLE).'<br />';
	$fieldset .= $form->text('title_alias'				, '', '(right)'.LANG_ADMIN_COM_CONTENT_NODE_TITLE_ALIAS, '', 'size=50').'<br />';
	$fieldset .= $form->textarea('text'					, '', LANG_ADMIN_COM_CONTENT_NODE_TEXT.'<br />', '', 'cols=70;rows=10').'<br />';
	$fieldset .= $form->select('image', $images_list, '(right)'.LANG_ADMIN_COM_CONTENT_NODE_IMAGE).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_NODE_FIELDSET1));

	// Show...
	$fieldset  = $form->checkbox('show_image_thumb'	, $config['nod_show_image_thumb']	, LANG_ADMIN_COM_CONTENT_NODE_SHOW_IMAGE_THUMB).'<br />';
	$fieldset .= $form->checkbox('show_medias'		, $config['nod_show_medias']		, $com_gen->translate(LANG_ADMIN_COM_CONTENT_NODE_SHOW_MEDIAS)).'<br />';

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_NODE_FIELDSET2));
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_new = false; /* ! Always reset the condition ! */
}


?>