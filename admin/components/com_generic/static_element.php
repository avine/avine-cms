<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Configuration of the script behaviour when (date_creation > date_modified)
$postdated_prevent_online_element = false;


?>

<script type="text/javascript">
$(document).ready(function(){
	// Auto-fill the id_alias input
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

	// Confirm 'element_del_all'
	$("#element_del_all").click(function(){
		return confirm("<?php echo LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS_CONFIRM; ?>");
	});
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


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;

// Session variables
$session = new sessionManager(sessionManager::BACKEND, 'static_element');
$session->init($com_gen->getTablePrefix().'live_archive_status'	, 0); # Default value : view online !
$session->init($com_gen->getTablePrefix().'selection'			, 0); # Default value : root !


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)

$submit = formManager::isSubmitedForm('element_', 'post'); // (0)
if ($submit)
{
	$autoarchive = $filter->requestValue('autoarchive')->get();; // (5)

	$del_all = $filter->requestValue('del_all')->get(); // (6)

	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new'	)->get(); // (1)
} else {
	$autoarchive = false;

	$del_all = false;

	$del = false;
	$upd = false;
	$new = false;
}

$autoarchive_submit = formManager::isSubmitedForm('autoarchive_', 'post'); // (5)

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// Permissions
$admin_perm = new adminPermissions();

if ($publish_status && !$admin_perm->publish($perm_denied))
{
	$publish_status = false;
	echo admin_message($perm_denied, 'warning');
}

if (($del || $del_all) && !$admin_perm->delete($perm_denied))
{
	$del = $del_all = false;
	echo admin_message($perm_denied, 'warning');
}

if ($autoarchive && !$admin_perm->archive($perm_denied))
{
	$autoarchive = false;
	echo admin_message($perm_denied, 'warning');
}



// (4) & (3) : Report the session into the 'update' & 'new' process
if ($com_gen->setLiveArchiveStatus('element', $session->get($com_gen->getTablePrefix().'live_archive_status')) == 0)
{
	$com_gen->setLiveArchiveStatus('node', 0); # View only non archived nodes
} else {
	$com_gen->setLiveArchiveStatus('node', 2); # View all nodes - because an archived element can be in a non archived node !
}



// (5) Case 'autoarchive'
if ($autoarchive_submit)
{
	$autoarchive_submit_validation = true;

	// Published
	$filter->requestValue('publish_when_archive')->get() ? $publish_when_archive = 1 : $publish_when_archive = 0;

	// Database Process
	if ($autoarchive_submit_validation)
	{
 		$mktime = time();
		$elements = $db->select($com_gen->getTablePrefix()."element, id, where: archived=0 AND, where: date_offline IS NOT NULL AND, where: date_offline < $mktime");

		if ($elements)
		{
			$query_id = 'where: ';
			for ($i=0; $i<count($elements); $i++)
			{
				$query_id .= 'id='.$elements[$i]['id'];

				($i != count($elements)-1) ? $query_id .= ' OR ' : '';
			}

			($publish_when_archive) ? $query_publish = ', published=1' : $query_publish = '';

			$result = $db->update($com_gen->getTablePrefix()."element; archived=1, date_online=NULL, date_offline=NULL $query_publish; $query_id");

			admin_informResult($result);
		}
		else {
			echo admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_NOTHING_TO_ARCHIVE), 'info');
		}
	}
}
if ($autoarchive || ($autoarchive_submit && !$autoarchive_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_AUTOARCHIVE).'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'autoarchive_');

	$html .= '<p>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_AUTO_ARCHIVE_HELP).'</p>';
	$html .= $form->checkbox('publish_when_archive', 1, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_PUBLISH_WHEN_ARCHIVE)).'<br />';

	$html .= '<br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();

	echo $html;
}



// (4) Case 'publish_status' (change the publish status)
if ($publish_status)
{
	if ($published = $db->selectOne($com_gen->getTablePrefix()."element, published, where: id=$publish_status"))
	{
		$published['published'] == 1 ? $published = '0' : $published = '1';
		$db->update($com_gen->getTablePrefix()."element; published=$published; where: id=$publish_status");
	}
}



// (3) Case 'del_all'
if ($del_all)
{
	$node_id = $session->get($com_gen->getTablePrefix().'selection');

	$archived = $session->get($com_gen->getTablePrefix().'live_archive_status');
	$archived = ($archived != '2') ? " AND archived=$archived" : '';

	if ($elements = $db->select($com_gen->getTablePrefix()."element, [id], id_alias, where: node_id=$node_id".$archived)) {
		$del_id = array_keys($elements);
	} else {
		$del_id = array();
	}

	/**
	 * Specific : 'element_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_element.php', $volatile_del_all);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	/**
	 * Global : 'element' table
	 */
	if (count($del_id))
	{
		$result = $db->delete($com_gen->getTablePrefix()."element; where: node_id=$node_id".$archived);
		admin_informResult($result && $volatile_result,
			str_replace('{count}', count($del_id), $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS_COUNT)));
	} else {
		admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS_EMPTY), 'warning');
	}

	// Don't forget to delete the elements from all home pages !
	for ($i=0; $i<count($del_id); $i++) {
		$db->delete($com_gen->getTablePrefix().'home_elm; where: elm_id='.$del_id[$i]);
	}
}



// (3) Case 'del'
if ($del)
{
	$del_id = $del; # We need to rename this variable because we need $del_id wich is a global variable

	/**
	 * Specific : 'element_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_element.php', $volatile_del);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	/**
	 * Global : 'element' table
	 */
	$result = $db->delete($com_gen->getTablePrefix()."element; where: id=$del");
	admin_informResult($result && $volatile_result);

	// Don't forget to delete this element from all home pages !
	$db->delete($com_gen->getTablePrefix()."home_elm; where: elm_id=$del");
}



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	/**
	 * Global : 'element' table
	 */

	// Id
	$upd_id = $filter->requestValue('id')->getInteger();

	// Id_alias
	$id_alias = strtolower($filter->requestValue('id_alias')->getID(1, '', $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS)));

	// Node_id
	$node_id = $filter->requestValue('node_id')->getInteger();
	if (!$node_id) {
		$filter->set(false, 'node_id')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_NODE_ID_NOT_SELECTED));
	}

	// Access_level
	$access_level = $filter->requestValue('access_level')->getInteger();

	// Published
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;
	$current_published = $filter->requestValue('current_published')->get();

	if (($published != $current_published) && !$admin_perm->publish($perm_denied_publish)) {
		$published = $current_published;
	}

	// Archived
	$filter->requestValue('archived')->get() ? $archived = 1 : $archived = 0;
	// Unarchive element into an archived node is not allowed !
	if (!$com_gen->isNotArchivedNode($node_id) && $filter->requestValue('current_archived')->get() && !$archived)
	{
		$filter->set(false, 'archived')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_UNARCHIVE_ERROR));
	}

	// List order
	# Modification available only on start view

	// Date creation (Yes, it can be modified ! Use case : Assuming the item is talking about an event. It might be interesting to associate the item to the day of the event !)
	$date_creation = $filter->requestValue('date_creation')->getFormatedDate(0); # FIXME : The time (hh:mm) is lost...
	$date_creation or $date_creation = $filter->requestValue('date_creation_current')->getFormatedDate(); # In case the 'date_creation' field was disabled...

	// Date modified
	$date_modified = time();

	// Is date_creation postdated ?
	if ($date_creation > $date_modified) {
		$date_modified = $date_creation;
		$postdated_detected = true;
	}

	// Online & Offline
	$date_online = false;
	$date_offline = false;
	if (!$archived) # set dates available only for non-archived elements
	{
		$date_online  = $filter->requestValue('date_online' )->getFormatedDate(0);
		$date_offline = $filter->requestValue('date_offline')->getFormatedDate(0);

		// If the element is postdated, it can not be online before this date !
		if ($postdated_prevent_online_element && isset($postdated_detected) && !($date_online >= $date_creation)) {
			$date_online = $date_creation;
			$date_online_overwritten = true;
		}

		// Check up : $online < $offline
		if ( ($date_online && $date_offline) && ($date_online > $date_offline) ) {
			$filter->set(false, 'date_online')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE_OFFLINE_ERROR));
		}
	}
	!$date_online  ? $date_online  = 'NULL' : ''; # query part
	!$date_offline ? $date_offline = 'NULL' : ''; # query part

	// Author_id
	$author_id = $filter->requestValue('author_id')->getInteger();
	if ($author_id === LANG_ADMIN_COM_USER_USER_OPTION_ROOT_ID) {
		$filter->set(false, 'author_id')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR_ID_NOT_SELECTED));
	}

	// Metas
	$meta_key  = $filter->requestValue('meta_key' )->get();
	$meta_desc = $filter->requestValue('meta_desc')->get();

	// Hits
	# Do nothing

	// Show ...
	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;

	// No duplicate id_alias allowed in the same node_id
	$current = $db->select($com_gen->getTablePrefix()."element, id_alias, node_id, where: id=$upd_id");
	$current_id_alias = $current[0]['id_alias'];
	$current_node_id  = $current[0]['node_id' ];
	// Something have changed, then check for duplicate!
	if (($id_alias != $current_id_alias) || ($node_id != $current_node_id))
	{
		if ($db->select($com_gen->getTablePrefix().'element, id, where: id_alias='.$db->str_encode($id_alias)." AND, where: node_id=$node_id"))
		{
			$filter->set(false, 'id_alias')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS_ALREADY_EXIST));
		}
	}

	/*
	 *  Specific : 'element_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_element.php', $volatile_upd_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result =
			$db->update(
				$com_gen->getTablePrefix()."element; id_alias=".$db->str_encode($id_alias).', '.
				"node_id=$node_id, access_level=$access_level, published=$published, ".
				"date_creation=$date_creation, date_modified=$date_modified, date_online=$date_online, date_offline=$date_offline, author_id=$author_id, ".
				'meta_key='.$db->str_encode($meta_key).', meta_desc='.$db->str_encode($meta_desc).', '.
				"show_date_creation=$show_date_creation, show_date_modified=$show_date_modified, show_author_id=$show_author_id, show_hits=$show_hits, ".
				"archived=$archived; ".
				"where: id=$upd_id"
			);

		admin_informResult(($result) && ($volatile_result));

		if (isset($date_online_overwritten)) {
			admin_message(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE_OVERWRITTEN, 'warning');
		}

		if (isset($perm_denied_publish) && $perm_denied_publish) {
			admin_message($perm_denied_publish, 'warning');
		}

		// Remove archived elements from 'home' table
		if ($archived == 1) {
			$com_gen->removeArchivedFromHomeElm();
		}

		// Reload the form instead of go back to the start view
		if ($filter->requestValue('record')->get())
		{
			$upd_submit_validation = false; # This is a trick to reload the form
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	$html = '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_UPDATE).'</h2>';

	// Id
	if ($upd)
	{
		$upd_id = $upd;
	} else {
		$upd_id = $filter->requestValue('id')->getInteger();
	}

	// Current_element
	$current_element = $db->selectOne($com_gen->getTablePrefix()."element, *, where: id=$upd_id");

	if (!$admin_perm->update($current_element['published'], $current_element['author_id'], $perm_denied)) {
		admin_message($perm_denied, 'warning');
	}

	$form = new formManager();
	$form->addMultipartFormData(); # Add enctype="multipart/form-data" to allow downloads
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	$wrapper = '';

	/**
	 * Fieldset - Global : Element
	 */
	$fieldset = '';

	// Id
	$fieldset .= $form->hidden('id', $upd_id);

	// Id_alias
	$fieldset .= $form->text('id_alias', $current_element['id_alias'], $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS), '', 'maxlength=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED;

	// Node_id
	$nodes_options = formManager::selectOption($com_gen->getNodesOptions(), $current_element['node_id']);
	$fieldset .= '<br /><br />'.$form->select('node_id', $nodes_options, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_NODE_ID).'<br />').'<br /><br />';

	// Access_level
	$fieldset .= $form->select('access_level', comUser_getStatusOptions($current_element['access_level']), LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL).'&nbsp; &nbsp;';

	// Published
	$fieldset .= $form->checkbox('published', $current_element['published'], LANG_ADMIN_COM_GENERIC_PUBLISHED.'(left)').'&nbsp; &nbsp;';
	$fieldset .= $form->hidden('current_published', $current_element['published']);

	// Archived
	$fieldset .= $form->checkbox('archived', $current_element['archived'], LANG_ADMIN_COM_GENERIC_ARCHIVED.'(left)');
	$fieldset .= $form->hidden('current_archived', $current_element['archived']);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET1));

	/**
	 * Fieldset - Global : (suite)
	 */
	$fieldset = '';

	// Online & Offline
	$fieldset .= '<script type="text/javascript">$(function(){$(\'#upd_date_online\').datepicker({inline: true});});</script>'."\n";
	$fieldset .= '<script type="text/javascript">$(function(){$(\'#upd_date_offline\').datepicker({inline: true});});</script>'."\n";
	$current_element['date_online' ] ? $date_online  = getTime($current_element['date_online' ], 'time=no') : $date_online  = '';
	$current_element['date_offline'] ? $date_offline = getTime($current_element['date_offline'], 'time=no') : $date_offline = '';
	# Notice : The date_online field should be not updated. Why ? Because it might be overwritten, in case the date_creation is postdated.
	$fieldset .= LANG_DATE_FORMAT.$form->text('date_online' , $date_online , '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE ), '', 'maxlength=10;size=10;update=no').'<br />';
	$fieldset .= LANG_DATE_FORMAT.$form->text('date_offline', $date_offline, '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_OFFLINE), '', 'maxlength=10;size=10').'<br /><br />';

	// Author_id (select a user wich his access_level <= 4)
	if ($admin_perm->canAccessStatus('editor')) {
		$disabled = '';
	} else {
		$disabled = 'disabled';
		$fieldset .= $form->hidden('author_id', $current_element['author_id']); # Fixed author_id !
	}
	$fieldset .= $form->select('author_id', admin_comUser_getUserOptions(1, 4, $current_element['author_id']), LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR, '', $disabled).'<br /><br />';

	// Metas
	$fieldset .= $form->text('meta_key' , $current_element['meta_key' ], '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_META_KEY ), '', 'size=60').'<br />';
	$fieldset .= $form->text('meta_desc', $current_element['meta_desc'], '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_META_DESC), '', 'size=60');

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET2));

	/**
	 * Fieldset - Global : (Show...)
	 */
	$fieldset = '';

	$fieldset .= $form->checkbox('show_date_creation'	, $current_element['show_date_creation'], LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $current_element['show_date_modified'], LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $current_element['show_author_id'	   ], LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $current_element['show_hits'		   ], LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET3));

	/**
	 * Fieldset - Global : (Read only)
	 */
	$fieldset = '';

	$fieldset .= '<script type="text/javascript">$(function(){$(\'#upd_date_creation\').datepicker({inline: true});});</script>'."\n";
	$fieldset .= $form->text('date_creation', getTime($current_element['date_creation'], 'time=no'), LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_CREATION, '', 'maxlength=10;size=10;disabled'); # FIXME : The time (hh:mm) is lost...
	$fieldset .= $form->hidden('date_creation_current', getTime($current_element['date_creation'], 'time=no'));
	if (!admin_comGeneric_compareDays($current_element['date_creation'], time()))
	{
		$postdated_message = $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_CREATION_POSTDATED);
		if ($postdated_prevent_online_element) {
			$postdated_message .= ' '.LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_CREATION_POSTDATED_WAITING;
			$class = ' class="red"';
		} else {
			$class = ' class="grey"';
		}
		$fieldset .= "&nbsp; <span$class>$postdated_message</span>";
	}

$modify_date_creation =
'<!-- Enable the modification of the "date_creation" -->
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	$("#upd_date_creation").after(" <a href=\"#\" id=\"modify_date_creation\" title=\"'.LANG_ADMIN_COM_GENERIC_ELEMENT_MODIFY_DATE_CREATION.'\"><img src=\"'.WEBSITE_PATH.'/admin/components/com_generic/images/modify_date_creation.png\" /><"+"/span>");
	$("#modify_date_creation").click(function(){
		var disabled = $("#upd_date_creation").attr("disabled");
		$("#upd_date_creation").attr("disabled", !disabled);
		if (disabled){
			$("#upd_date_creation").removeClass("form-text-disabled").addClass("form-text").focus();
		}else{
			$("#upd_date_creation").addClass("form-text-disabled").removeClass("form-text");
		}
		return false;
	});
});
//]]></script>';

	$fieldset .= "\n$modify_date_creation\n";

	$fieldset .= '<br /><br />'.LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_MODIFIED.' : <strong>'.getTime($current_element['date_modified'], 'time=no').'</strong><br />';
	$fieldset .= LANG_ADMIN_COM_GENERIC_ELEMENT_HITS.' : <strong>'.$current_element['hits'].'</strong><br />';

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET4));

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_element.php', $volatile_upd);
	if (!$file_exists) {
		$html .= admin_fieldsetsWrapper(LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC_EMPTY, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');
	}
	/* End of : Specific */

	$html .= admin_fieldsetsWrapper('', '', 'clear');

	$html .= '<br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('record', LANG_ADMIN_BUTTON_RECORD).'<br />';
	$html .= $form->end();

	if ($perm_denied)
	{
		$start_view = true;
	} else {
		echo $html;
	}
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	/**
	 * Global : 'element' table
	 */

	// New id (must be setted to be used by volatile_*.php file)
	$new_id = $db->selectOne($com_gen->getTablePrefix().'element, id(desc)', 'id') +1;

	// Id_alias
	$id_alias = strtolower($filter->requestValue('id_alias')->getID(1, '', $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS)));

	// Node_id
	$node_id = $filter->requestValue('node_id')->getInteger();
	if (!$node_id) {
		$filter->set(false, 'node_id')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_NODE_ID_NOT_SELECTED));
	}

	// Access_level
	$access_level = $filter->requestValue('access_level')->getInteger();

	// Published
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;

	if ($published && !$admin_perm->publish($perm_denied_publish)) {
		$published = 0;
	}

	// Archived
	$filter->requestValue('archived')->get() ? $archived = 1 : $archived = 0;

	// List order
	$list_order = 0; # Set 999 to insert new item at the end of the list (or 0 to insert at the top)

	// Date (creation & modified)
	$date = time();

	// Online & Offline
	$date_online = false;
	$date_offline = false;
	if (!$archived)	# set dates available only for non-archived elements
	{
		$date_online  = $filter->requestValue('date_online' )->getFormatedDate(0);
		$date_offline = $filter->requestValue('date_offline')->getFormatedDate(0);

		// Check up : $online < $offline
		if ( ($date_online && $date_offline) && ($date_online > $date_offline) ) {
			$filter->set(false, 'date_online')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE_OFFLINE_ERROR));
		}
	}
	!$date_online  ? $date_online  = 'NULL' : ''; # query part
	!$date_offline ? $date_offline = 'NULL' : ''; # query part

	// Author_id
	$author_id = $filter->requestValue('author_id')->getInteger();
	if ($author_id === LANG_ADMIN_COM_USER_USER_OPTION_ROOT_ID) {
		$filter->set(false, 'author_id')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR_ID_NOT_SELECTED));
	}

	// Metas
	$meta_key  = $filter->requestValue('meta_key' )->get();
	$meta_desc = $filter->requestValue('meta_desc')->get();

	// Hits
	$hits = 0;

	// Show ...
	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;


	// No duplicate id_alias allowed in the same node_id
	if ($db->select($com_gen->getTablePrefix().'element, id, where: id_alias='.$db->str_encode($id_alias)." AND, where: node_id=$node_id"))
	{
		$filter->set(false, 'id_alias')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS_ALREADY_EXIST));
	}

	/**
	 * Specific : 'element_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_element.php', $volatile_new_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result =
			$db->insert(
					$com_gen->getTablePrefix()."element; $new_id, ".$db->str_encode($id_alias).', '.
					"$node_id, $access_level, $published, $list_order, ".
					"$date, $date, $date_online, $date_offline, $author_id, ".
					$db->str_encode($meta_key).', '.$db->str_encode($meta_desc).', '.
					"$hits, ".
					"$show_date_creation, $show_date_modified, $show_author_id, $show_hits, ".
					"$archived"
			);

		admin_informResult($result && $volatile_result);

		if (isset($perm_denied_publish) && $perm_denied_publish) {
			admin_message($perm_denied_publish, 'warning');
		}

		// Remove archived elements from 'home' table
		if ($archived == 1) {
			$com_gen->removeArchivedFromHomeElm();
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_NEW).'</h2>';

	$html = '';
	$form = new formManager();
	$form->addMultipartFormData(); # Add enctype="multipart/form-data" to allow downloads
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$wrapper = '';

	/**
	 * Fieldset : Global
	 */
	$fieldset = '';

	// Id_alias
	$fieldset .= $form->text('id_alias', '', $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS), '', 'maxlength=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED;

	// Node_id
	$nodes_options = formManager::selectOption($com_gen->getNodesOptions(), $session->get($com_gen->getTablePrefix().'selection'));
	$fieldset .= '<br /><br />'.$form->select('node_id', $nodes_options, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_NODE_ID).'<br />').'<br /><br />';

	// Access_level
	$fieldset .= $form->select('access_level', comUser_getStatusOptions(comUser_getLowerStatus()), LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL).'&nbsp; &nbsp;';

	// Published
	$fieldset .= $form->checkbox('published', $admin_perm->publish($perm_denied) ? 1 : 0, LANG_ADMIN_COM_GENERIC_PUBLISHED.'(left)').'&nbsp; &nbsp;';

	// Archived
	$fieldset .= $form->checkbox('archived', 0, LANG_ADMIN_COM_GENERIC_ARCHIVED.'(left)');

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET1));

	/**
	 * Fieldset : Global (suite)
	 */
	$fieldset = '';

	// Online & Offline
	$fieldset .= '<script type="text/javascript">$(function(){$(\'#new_date_online\').datepicker({inline: true});});</script>'."\n";
	$fieldset .= '<script type="text/javascript">$(function(){$(\'#new_date_offline\').datepicker({inline: true});});</script>'."\n";
	$fieldset .= LANG_DATE_FORMAT.$form->text('date_online' , '', '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE ), '', 'maxlength=10;size=10').'<br />';
	$fieldset .= LANG_DATE_FORMAT.$form->text('date_offline', '', '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_OFFLINE), '', 'maxlength=10;size=10').'<br /><br />';

	// Author_id (select a user wich his access_level <= 4)
	global $g_user_login;
	if ($admin_perm->canAccessStatus('editor'))
	{
		$disabled = '';
	} else {
		$disabled = 'disabled';
		$fieldset .= $form->hidden('author_id', $g_user_login->userID()); # Fixed author_id !
	}
	$fieldset .= $form->select('author_id', admin_comUser_getUserOptions(1, 4, $g_user_login->userID()), LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR, '', $disabled).'<br /><br />';

	// Metas
	$fieldset .= $form->text('meta_key' , '', '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_META_KEY ), '', 'size=60').'<br />';
	$fieldset .= $form->text('meta_desc', '', '(right)'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_META_DESC), '', 'size=60');

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET2));

	/**
	 * Fieldset : Show... (global)
	 */
	$fieldset = '';

	$default = $db->select($com_gen->getTablePrefix().'config, show_date_creation, show_date_modified, show_author_id, show_hits'); $default = $default[0];

	$fieldset .= $form->checkbox('show_date_creation'	, $default['show_date_creation'], LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $default['show_date_modified'], LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $default['show_author_id']	, LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $default['show_hits']			, LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET3));

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_element.php', $volatile_new);
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

// (0) Always check for: list_order update
if ($submit && $element_id = formManager_filter::arrayOnly($filter->requestName('list_order_')->getInteger()))
{
	for ($i=0; $i<count($element_id); $i++)
	{
		$order = $filter->requestValue('list_order_'.$element_id[$i])->getInteger();
		if ($order !== false)
		{
			$db->update($com_gen->getTablePrefix().'element; list_order='.$order.'; where: id='.$element_id[$i]);
		}
	}
}



if ($start_view)
{
	// Title
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_START).'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'element_');

	// Session variables : begin //

	// Live_archive_status : element (update class property)
	$archive_element = $com_gen->setLiveArchiveStatus('element', $session->setAndGet($com_gen->getTablePrefix().'live_archive_status', $filter->requestValue('live_archive_status')->getInteger(0)));
	// Live_archive_status : nodes (update class property)
	if ($archive_element == 0)
	{
		$com_gen->setLiveArchiveStatus('node', 0); # View only non archived nodes
	} else {
		$com_gen->setLiveArchiveStatus('node', 2); # View all nodes - because an archived element can be in a non archived node !
	}

	// Node selection
	$node_selection = $session->setAndGet($com_gen->getTablePrefix().'selection', $filter->requestValue('selection')->getInteger(0));

	$node_available = $db->select($com_gen->getTablePrefix().'node, id; limit: 1');
	$node_available_message = LANG_ADMIN_COM_GENERIC_ELEMENT_NO_ONE_NODE_AVAILABLE;

	$nodes_options = formManager::selectOption($com_gen->getNodesOptions(true), $node_selection);
	$html .= $form->select('selection', $nodes_options, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_SELECTION));

	// Session variables : end //

	// Live_archive_status (select form)
	$html .= $form->select('live_archive_status', $com_gen->liveArchiveStatusElementsOptions($archive_element), $com_gen->translate(LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION));
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_top').'<br /><br />'; # Submit-button for the start-view

	if (!$node_available) {
		admin_message($com_gen->translate($node_available_message), 'warning');
	}

	// Push all archived elements after non-archived ones
	$com_gen->pushArchivedElements($node_selection);

	// Generic
	$elements_list = $com_gen->getElements($node_selection);

	// Is a node selected ?
	if (($node_available) && ($node_selection != '0'))
	{
		// $elements_list transformations
		for ($i=0; $i<count($elements_list); $i++)
		{
			$item_field = $com_gen->getElementItemField();
			if (array_key_exists($item_field, $elements_list[$i])) {
				$elements_list[$i][$item_field] = admin_textPreview($elements_list[$i][$item_field], 50, 'span');
			}
			// Input-text for the list_order field
			$elements_list[$i]['list_order'] = $form->text('list_order_'.$elements_list[$i]['id'], $elements_list[$i]['list_order'], '', '', 'size=1;update=no'); // (0)

			// Access_level (_name instead of _id) (get the access-level-name from user_status table)
			$temp = $db->select('user_status, comment, where: id='.$elements_list[$i]['access_level']);
			$elements_list[$i]['access_level'] = $temp[0]['comment'];

			// Published (<a> tag with checked/unchecked image)
			$elements_list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$elements_list[$i]['id'].'">'.admin_replaceTrueByChecked($elements_list[$i]['published']).'</a>'; // (4)

			// Archived (<a> tag with archived/empty image)
			$elements_list[$i]['archived'] = $com_gen->replaceTrueByArchived($elements_list[$i]['archived']);

			// Archived is now included with the published column
			if ($elements_list[$i]['archived']) {
				$elements_list[$i]['published'] .= ' &nbsp;'.$elements_list[$i]['archived'];
			}

			// Date expired
			if ($elements_list[$i]['expired']) {
				$auto_archive_button = true;
			}
		}

		for ($i=0; $i<count($elements_list); $i++)
		{
			$update[$i] = $form->submit('upd_'.$elements_list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
			$delete[$i] = $form->submit('del_'.$elements_list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
		}

		for ($i=0; $i<count($elements_list); $i++)
		{
			$elements_list[$i]['id'] = "<span style=\"color:#AAA;\">{$elements_list[$i]['id']}</span>"; # careful : the ID $nodes_list[$i]['id'] is no longer available. So, we have done this at the end !
		}

		// Table
		$table = new tableManager($elements_list);

		$table_header = $com_gen->getElementsHeader();

		if (count($elements_list)) {
			$table->addCol($delete, 0);
			$table->addCol($update, 999);
		}
		array_unshift($table_header, '');
		array_push($table_header, '');

		$table->header($table_header);
		$table->delCol('6,11'); # Delete 'Archived' and 'DATE EXPIRED' columns

		$html .= $table->html();

		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_bottom'); # Submit-button for the start-view
		$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)

		global $g_user_login;
		if ($g_user_login->accessLevel() == 1) {
			$html .= $form->submit('del_all', $com_gen->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS)); // (6)
		}

		if (isset($auto_archive_button)) {
			$html .= '&nbsp; &nbsp; &nbsp;'.$form->submit('autoarchive', LANG_ADMIN_COM_GENERIC_ELEMENT_BUTTON_AUTO_ARCHIVE); // (5)
		}
	}
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>