<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


?>

<!-- Check 'media_keep_*' checkbox on 'media_title_*' focus -->
<script type="text/javascript">
$(document).ready(function(){
	$("input[name*='media_title_']").focus(function(){
		var name = $(this).attr('name');
		$("input[name='media_keep_" + name.substring(12) + "']").attr('checked', true);
	});
});
</script>

<?php


/**
 * Here the 'volatile_*.php' script wich is required into his 'static_*.php' script,
 * using the $com_gen->volatileFilePath() method.
 * So, this script need some globals variables from his main script to do his work
 */
global	$filter;

global	# Posted forms possibilities (with prefix: '$volatile_*')
		$volatile_new, $volatile_new_submit,
		$volatile_upd, $volatile_upd_submit,
		$volatile_del, $volatile_del_all;

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



// (6) Case 'del_all'
if ($volatile_del_all)
{
	/* --- Database Process --- */
	$volatile_result = true;
	for ($i=0; $i<count($del_id); $i++) {
		if (!$db->delete($com_gen->getTablePrefix().'element_item; where: element_id='.$del_id[$i])) {
			$volatile_result = false;
		}
	}

	$volatile_del_all = false; # Always reset the condition !
}



// (3) Case 'del'
if ($volatile_del)
{
	/* --- Database Process --- */
	$volatile_result = $db->delete($com_gen->getTablePrefix()."element_item; where: element_id=$del_id");

	$volatile_del = false; /* ! Always reset the condition ! */
}



// (2) Case 'upd'
if ($volatile_upd_submit)
{
	/* --- Get $_POST['upd_*'] (inputs validation : $upd_submit_validation) --- */
	$title 			= $filter->requestValue('title')->getNotEmpty(1, '', LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE);
	$title_alias 	= $filter->requestValue('title_alias')->get();
	$title_quote 	= $filter->requestValue('title_quote')->get();

	$text_intro 	= $filter->requestValue('text_intro')->get();
	$text_main 		= $filter->requestValue('text_main')->get();

	$image_thumb 	= $filter->requestValue('image_thumb')->getPathFile(0);
	$image 			= $filter->requestValue('image')->getPathFile(0);

	$_medias = new mediasManager();
	$_medias->formProcess();
	if ($_medias->stringValidated()) {
		$medias = $_medias->stringGet();
	} else {
		$medias = '';
		$filter->set(false)->getError($_medias->stringError());
	}

	$filter->requestValue('show_text_intro'	)->get() ? $show_text_intro = 1 : $show_text_intro 	= 0;
	$filter->requestValue('disable_medias'	)->get() ? $disable_medias 	= 1 : $disable_medias 	= 0;
	$filter->requestValue('use_text_editor'	)->get() ? $use_text_editor = 1 : $use_text_editor 	= 0;

	/* --- Database Process --- */
	if ($upd_submit_validation = $filter->validated())
	{
		$volatile_result =
			$db->update(
					$com_gen->getTablePrefix()."element_item; ".
					'title='.$db->str_encode($title).', title_alias='.$db->str_encode($title_alias).', title_quote='.$db->str_encode($title_quote).', '.
					'text_intro='.$db->str_encode($text_intro).', text_main='.$db->str_encode($text_main).', '.
					'image_thumb='.$db->str_encode($image_thumb).', image='.$db->str_encode($image).', medias='.$db->str_encode($medias).', '.
					"show_text_intro=$show_text_intro, disable_medias=$disable_medias, use_text_editor=$use_text_editor; ".
					"where: element_id=$upd_id"
			);
	}

	$volatile_upd_submit = false; /* ! Always reset the condition ! */
}
if ($volatile_upd)
{
	$wrapper = '';

	// Get element_item
	$current_item = $db->selectOne($com_gen->getTablePrefix()."element_item, *, where: element_id=$upd_id");

	// Form setup (no html output)
	$form = new formManager();
	$form->setForm('post', 'upd_');

	/* ------------- INPUTS FORM ------------- */
	// Title, title_alias, title_quote
	$fieldset  = $form->text('title'		, $current_item['title']		, '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE, '', 'size=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED.'<br />';
	$fieldset .= $form->text('title_alias'	, $current_item['title_alias']	, '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE_ALIAS, '', 'size=50').'<br />';
	$fieldset .= $form->textarea('title_quote', $current_item['title_quote'], '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE_QUOTE, '', 'cols=50;rows=2').'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET1));

	// Text_intro, text_main
	$fieldset  = $form->textarea('text_intro', $current_item['text_intro'], LANG_ADMIN_COM_CONTENT_ELEMENT_TEXT_INTRO.'<br />', '', 'cols=70;rows=10').'<br />';
	$fieldset .= $form->textarea('text_main' , $current_item['text_main'] , LANG_ADMIN_COM_CONTENT_ELEMENT_TEXT_MAIN.'<br />', '', 'cols=70;rows=10').'<br />';
	if ($current_item['use_text_editor']) {
		$my_CKEditor = new loadMyCkeditor();
		$fieldset .= $my_CKEditor->addName("text_intro")->addName("text_main");
	}
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET2));

	// Image_thumb, image
	$fieldset  = $form->select('image_thumb', formManager::selectOption($images_list, $current_item['image_thumb'])	, '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_IMAGE_THUMB	).'<br />';
	$fieldset .= $form->select('image'		, formManager::selectOption($images_list, $current_item['image'])		, '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_IMAGE		).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET3));

	// Medias
	$fieldset = '';
	$_medias = new mediasManager();
	$_medias->stringSet($current_item['medias']);
	$table = new tableManager($_medias->formInputs('upd_', !$volatile_result), $_medias->formInputsHeader());
	$fieldset .= admin_comContent_mediasDialog($table->html().LANG_COM_MEDIAS_FORM_INPUTS_LAST_LINE_FOR_NEW, $_medias->arrayGet());
	$wrapper .= admin_fieldset("<div style=\"width:515px;overflow:auto;\">$fieldset</div>", $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET4)); # Optimized for 1280px width screen

	// Checkboxes...
	$fieldset  = $form->checkbox('show_text_intro'	, $current_item['show_text_intro'], LANG_ADMIN_COM_CONTENT_ELEMENT_SHOW_TEXT_INTRO).'<br />';
	$fieldset .= $form->checkbox('disable_medias'	, $current_item['disable_medias'] , LANG_ADMIN_COM_CONTENT_ELEMENT_DISABLE_MEDIAS).'<br />';
	$fieldset .= $form->checkbox('use_text_editor'	, $current_item['use_text_editor'], LANG_ADMIN_COM_CONTENT_ELEMENT_USE_TEXT_EDITOR).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET5));
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_upd = false; /* ! Always reset the condition ! */
}



// (1) Case 'new'
if ($volatile_new_submit)
{
	/* --- Get $_POST['new_*'] (inputs validation : $new_submit_validation) --- */
	$title 			= $filter->requestValue('title')->getNotEmpty(1, '', LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE);
	$title_alias 	= $filter->requestValue('title_alias')->get();
	$title_quote 	= $filter->requestValue('title_quote')->get();

	$text_intro 	= $filter->requestValue('text_intro')->get();
	$text_main 		= $filter->requestValue('text_main')->get();

	$image_thumb 	= $filter->requestValue('image_thumb')->getPathFile(0);
	$image 			= $filter->requestValue('image')->getPathFile(0);

	$_medias = new mediasManager();
	$_medias->formProcess();
	if ($_medias->stringValidated()) {
		$medias = $_medias->stringGet();
	} else {
		$medias = '';
		$filter->set(false)->getError($_medias->stringError());
	}

	$filter->requestValue('show_text_intro'	)->get() ? $show_text_intro = 1 : $show_text_intro 	= 0;
	$filter->requestValue('disable_medias'	)->get() ? $disable_medias 	= 1 : $disable_medias 	= 0;
	$filter->requestValue('use_text_editor'	)->get() ? $use_text_editor = 1 : $use_text_editor 	= 0;

	/* --- Database Process --- */
	if ($new_submit_validation = $filter->validated())
	{
		$volatile_result =
			$db->insert(
					$com_gen->getTablePrefix()."element_item; $new_id, ".
					$db->str_encode($title).', '.$db->str_encode($title_alias).', '.$db->str_encode($title_quote).', '.
					$db->str_encode($text_intro).', '.$db->str_encode($text_main).', '.
					$db->str_encode($image_thumb).', '.$db->str_encode($image).', '.$db->str_encode($medias).', '.
					"$show_text_intro, $disable_medias, $use_text_editor"
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
	// Title, title_alias, title_quote
	$fieldset  = $form->text('title'			, '', '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE, '', 'size=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED.'<br />';
	$fieldset .= $form->text('title_alias'		, '', '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE_ALIAS, '', 'size=50').'<br />';
	$fieldset .= $form->textarea('title_quote'	, '', '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE_QUOTE, '', 'cols=50;rows=2').'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET1));

	// Text_intro, text_main
	$fieldset  = $form->textarea('text_intro'	, '', LANG_ADMIN_COM_CONTENT_ELEMENT_TEXT_INTRO.'<br />', '', 'cols=70;rows=10').'<br />';
	$fieldset .= $form->textarea('text_main'	, '', LANG_ADMIN_COM_CONTENT_ELEMENT_TEXT_MAIN.'<br />', '', 'cols=70;rows=10').'<br />';
	if ($config['elm_use_text_editor']) {
		$my_CKEditor = new loadMyCkeditor();
		$fieldset .= $my_CKEditor->addName("text_intro")->addName("text_main");
	}
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET2));

	// Image_thumb, image, medias
	$fieldset  = $form->select('image_thumb', $images_list, '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_IMAGE_THUMB).'<br />';
	$fieldset .= $form->select('image'		, $images_list, '(right)'.LANG_ADMIN_COM_CONTENT_ELEMENT_IMAGE		).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET3));

	// Medias
	$fieldset = '';
	$_medias = new mediasManager();
	$table = new tableManager($_medias->formInputs('new_', true), $_medias->formInputsHeader());
	$fieldset .= admin_comContent_mediasDialog($table->html().LANG_COM_MEDIAS_FORM_INPUTS_LAST_LINE_FOR_NEW);
	$wrapper .= admin_fieldset("<div style=\"width:515px;overflow:auto;\">$fieldset</div>", $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET4)); # Optimized for 1280px width screen

	// Checkboxes...
	$fieldset  = $form->checkbox('show_text_intro'	, $config['elm_show_text_intro'], LANG_ADMIN_COM_CONTENT_ELEMENT_SHOW_TEXT_INTRO).'<br />';
	$fieldset .= $form->checkbox('disable_medias'	, 0								, LANG_ADMIN_COM_CONTENT_ELEMENT_DISABLE_MEDIAS).'<br />';
	$fieldset .= $form->checkbox('use_text_editor'	, $config['elm_use_text_editor'], LANG_ADMIN_COM_CONTENT_ELEMENT_USE_TEXT_EDITOR).'<br />';
	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET5));
	/* ----------------- END ----------------- */

	// Html
	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');

	$volatile_new = false; /* ! Always reset the condition ! */
}


?>