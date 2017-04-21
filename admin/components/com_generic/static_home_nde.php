<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );

?>

<!-- Auto-fill the id_alias input -->
<script type="text/javascript">
$(document).ready(function(){
	$("input[name='title']").blur(function (){
		var id_alias = $("input[name='id_alias']").val();
		if (!id_alias) {
			var title = $(this).val();

			var  isNotID = new RegExp("[^\.a-zA-Z_0-9\-]","g");
			var  duplicate = new RegExp("-+","g");
			var  trim = new RegExp("(^-|-$)","g");

			id_alias = title.toLowerCase().replace(isNotID,"-").replace(duplicate,"-").replace(trim,"");
			$("input[name='id_alias']").val(id_alias);
		}
	}).keyup();
});
</script>

<?php

/**
 * This script 'static_*.php' is a little different : some variables must be defined as 'global' !
 * Because, we need here to require a file wich his called 'volatile_*.php' (by using $com_gen->volatileFilePath() method).
 * And this required script should access to important variables of this main script.
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



// (3) Case 'del'
if ($del)
{
	$del_id = $del; # We need to rename this variable because we need $del_id wich is a global variable

	if ($db->selectCount($com_gen->getTablePrefix()."home_nde, where: id=$del AND, where: default_nde=1"))
	{
		admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_NDE_DEL_ERROR_DEFAULT_NDE), 'error');
	}
	elseif ($db->selectCount($com_gen->getTablePrefix()."home_elm, where: home_nde_id=$del"))
	{
		admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_NDE_DEL_ERROR_HAVE_ELM), 'error');
	}
	else
	{
		/**
		 * Specific : 'home_nde_item' table
		 */
		$file_exists = $com_gen->volatileFilePath('volatile_home_nde.php', $volatile_del);
		if (!$file_exists) {
			$volatile_result = true;
		}
		/* End of : Specific */

		/**
		 * Global : 'home_nde' table
		 */
		$result = $db->delete($com_gen->getTablePrefix()."home_nde; where: id=$del");
		echo admin_informResult($result && $volatile_result);
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	/**
	 * Global : 'home_nde' table
	 */

	$upd_id = $filter->requestValue('id')->getInteger();

	$current_id_alias = $filter->requestValue('current_id_alias')->getID();
	$id_alias = strtolower($filter->requestValue('id_alias')->getID());

	if ($nodes_id = formManager_filter::arrayOnly($filter->requestValue('nodes_id')->getInteger(0))) {
		$nodes_id = implode(';', $nodes_id);
	}

	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;

	// No duplicate id_alias allowed
	if ($id_alias !== $current_id_alias)
	{
		if ($db->selectCount($com_gen->getTablePrefix().'home_nde, where: id_alias='.$db->str_encode($id_alias)))
		{
			$filter->set(false, 'id_alias')->getError(LANG_ADMIN_COM_GENERIC_HOME_NDE_ALIAS_ALREADY_EXIST);
		}
	}

	/**
	 * Specific : 'home_nde_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_home_nde.php', $volatile_upd_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result =
			$db->update(
					$com_gen->getTablePrefix()."home_nde; id_alias=".$db->str_encode($id_alias).', '.
					'nodes_id='.($nodes_id ? $db->str_encode($nodes_id) : 'NULL').', '.
					"show_date_creation=$show_date_creation, show_date_modified=$show_date_modified, show_author_id=$show_author_id, show_hits=$show_hits; ".
					"where: id=$upd_id"
			);

		admin_informResult(($result) && ($volatile_result));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_GENERIC_HOME_NDE_TITLE_UPD.'</h2>';

	// Id
	if ($upd)
	{
		$upd_id = $upd;
	} else {
		$upd_id = $filter->requestValue('id')->getInteger();
	}

	// Current home_nde
	$current = $db->selectOne($com_gen->getTablePrefix()."home_nde, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	$wrapper = '';

	/** 
	 * Fieldset - Global : Nodes
	 */
	$fieldset = '';

	$fieldset .= $form->hidden('id', $upd_id);
	$fieldset .= $form->hidden('current_id_alias', $current['id_alias']);

	if ($current['default_nde']) {
		$fieldset .= '<p class="green">'.LANG_ADMIN_COM_GENERIC_HOME_NDE_DEFAULT.'</p>';
	}

	$fieldset .= $form->text('id_alias', $current['id_alias'], LANG_ADMIN_COM_GENERIC_HOME_NDE_ID_ALIAS, '', 'maxlength=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED.'<br /><br />';

	$fieldset .= $form->select('nodes_id', formManager::selectOption($com_gen->getNodesOptions(false, false), explode(';', $current['nodes_id'])), $com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_NDE_NODES_ID).'<br />', '', 'multiple;size=6');
	$fieldset .= '<p class="grey">'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_NDE_NODES_ID_TIPS).'<p>';

	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_GENERIC_HOME_NDE_CONFIG_FIELDSET);

	/** 
	 * Fieldset - Global : Elements
	 */
	$fieldset = '';

	$fieldset .= $form->checkbox('show_date_creation'	, $current['show_date_creation'	], LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $current['show_date_modified'	], LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $current['show_author_id'		], LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $current['show_hits'			], LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ELEMENT_FIELDSET));  

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_home_nde.php', $volatile_upd);
	if (!$file_exists) {
		$html .= admin_fieldsetsWrapper(LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC_EMPTY, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');
	}

	$html .= admin_fieldsetsWrapper('', '', 'clear');
  
	$html .= '<br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br />';
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	/**
	 * Global : 'home_nde' table
	 */

	// New id (must be setted to be used by volatile_*.php file)
	$new_id = $db->selectOne($com_gen->getTablePrefix().'home_nde, id(desc)', 'id') +1;

	$id_alias = strtolower($filter->requestValue('id_alias')->getID());

	if ($nodes_id = formManager_filter::arrayOnly($filter->requestValue('nodes_id')->getInteger(0))) {
		$nodes_id = implode(';', $nodes_id);
	}

	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;

	// No duplicate id_alias allowed
	if ($db->selectCount($com_gen->getTablePrefix().'home_nde, where: id_alias='.$db->str_encode($id_alias)))
	{
		$filter->set(false, 'id_alias')->getError(LANG_ADMIN_COM_GENERIC_HOME_NDE_ALIAS_ALREADY_EXIST);
	}

	/**
	 * Specific : 'home_nde_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_home_nde.php', $volatile_new_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result =
			$db->insert(
					$com_gen->getTablePrefix()."home_nde; $new_id, ".$db->str_encode($id_alias).', '.
					($nodes_id ? $db->str_encode($nodes_id) : 'NULL').', '.
					"$show_date_creation, $show_date_modified, $show_author_id, $show_hits, 0"
			);

		admin_informResult(($result) && ($volatile_result));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_GENERIC_HOME_NDE_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$wrapper = '';

	/** 
	 * Fieldset - Global : Nodes
	 */
	$fieldset = '';

	$fieldset .= $form->text('id_alias', '', LANG_ADMIN_COM_GENERIC_HOME_NDE_ID_ALIAS, '', 'maxlength=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED.'<br /><br />';

	$fieldset .= $form->select('nodes_id', $com_gen->getNodesOptions(false, false), $com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_NDE_NODES_ID).'<br />', '', 'multiple;size=6');
	$fieldset .= '<p class="grey">'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_NDE_NODES_ID_TIPS).'<p>';

	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_GENERIC_HOME_NDE_CONFIG_FIELDSET);

	/** 
	 * Fieldset - Global : Elements
	 */
	$fieldset = '';

	$default = $db->selectOne($com_gen->getTablePrefix().'config, show_date_creation, show_date_modified, show_author_id, show_hits');

	$fieldset .= $form->checkbox('show_date_creation'	, $default['show_date_creation'	], LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $default['show_date_modified'	], LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $default['show_author_id'		], LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $default['show_hits'			], LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ELEMENT_FIELDSET));  

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_home_nde.php', $volatile_new);
	if (!$file_exists) {
		$html .= admin_fieldsetsWrapper(LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC_EMPTY, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');
	}

	$html .= admin_fieldsetsWrapper('', '', 'clear');
  
	$html .= '<br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br />';
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

// Update default_nde
if ($submit)
{
	if ($default_nde = $filter->requestValue('default_nde')->getInteger())
	{
		$db->update($com_gen->getTablePrefix().'home_nde; default_nde=0');
		$db->update($com_gen->getTablePrefix()."home_nde; default_nde=1; where: id=$default_nde");
	}
}

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_GENERIC_HOME_NDE_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	$i = 0;
	$list = array();
	$home_nde = $com_gen->getHomeNde();
	foreach ($home_nde as $id => $details)
	{
		$list[$i]['delete'] = $form->submit("del_$id", LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); 

		$full_href = comMenu_rewrite('com='.$com_gen->getComName().'&amp;page=home&amp;alias='.$details['id_alias']);
		$href = preg_replace('~^(http(s)?://)'.pregQuote($_SERVER['HTTP_HOST']).'~', '', $full_href);
		$list[$i]['id_alias'] = "<a href=\"$full_href\" title=\"$full_href\" class=\"external\">$href</a>";

		if ($com_gen->getNodeItemField()) {
			$list[$i][$com_gen->getNodeItemField()] = $details[$com_gen->getNodeItemField()];
		}

		$list[$i]['default_nde'] = $form->radio('default_nde', formManager::checkValue($id, $details['default_nde']));

		$list[$i]['update'] = $form->submit("upd_$id", LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');

		$i++;
	}

	// Table
	if ($com_gen->getNodeItemField())
	{
		$table_header = array('', LANG_ADMIN_COM_GENERIC_HOME_NDE_ID_ALIAS, $com_gen->getNodeItemField(), LANG_ADMIN_COM_GENERIC_HOME_NDE_DEFAULT_NDE_HEADER, '');
	} else {
		$table_header = array('', LANG_ADMIN_COM_GENERIC_HOME_NDE_ID_ALIAS, LANG_ADMIN_COM_GENERIC_HOME_NDE_DEFAULT_NDE_HEADER, '');
	}
	$table = new tableManager($list, $table_header);
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>