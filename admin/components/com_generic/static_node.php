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

// Session variables
$session = new sessionManager(sessionManager::BACKEND, 'static_node');
$session->init($com_gen->getTablePrefix().'live_archive_status'	, 0); # Default value : view online !
$session->init($com_gen->getTablePrefix().'selection'			, 0); # Default value : root !


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)

$submit = formManager::isSubmitedForm('node_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new'	)->get(); // (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// Permissions
$admin_perm = new adminPermissions();

if ($del && !$admin_perm->delete($perm_denied))
{
	$del = false;
	echo admin_message($perm_denied, 'warning');
}



// (4) & (3) : Report the session into the 'update' & 'new' process
$com_gen->setLiveArchiveStatus('node', $session->get($com_gen->getTablePrefix().'live_archive_status'));



// (4) Case 'publish_status' (change the publish status)
if ($publish_status)
{
	if ($published = $db->selectOne($com_gen->getTablePrefix()."node, published, where: id=$publish_status"))
	{
		$published['published'] == 1 ? $published = '0' : $published = '1';
		$db->update($com_gen->getTablePrefix()."node; published=$published; where: id=$publish_status");
	}
}



// (3) Case 'del'
if ($del)
{
	$del_id = $del; # We need to rename this variable because we need $del_id wich is a global variable

	$have_node    = $db->select($com_gen->getTablePrefix()."node   , id, where: parent_id = $del");
	$have_element = $db->select($com_gen->getTablePrefix()."element, id, where: node_id   = $del");

	if ((!$have_node) && (!$have_element))
	{
		/**
		 * Specific : 'node_item' table
		 */
		$file_exists = $com_gen->volatileFilePath('volatile_node.php', $volatile_del);
		if (!$file_exists) {
			$volatile_result = true;
		}
		/* End of : Specific */

		/**
		 * Global : 'node' table
		 */
		$result = $db->delete($com_gen->getTablePrefix()."node; where: id=$del");
		echo admin_informResult(($result) && ($volatile_result));
	}
	else
	{
		if ($have_node) {
			admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_DEL_NODE_HAVE_NODE)   , 'error');
		}
		if ($have_element) {
			admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_DEL_NODE_HAVE_ELEMENT), 'error');
		}
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	/**
	 * Global : 'node' table
	 */

	// Id
	$upd_id = $filter->requestValue('id')->getInteger();

	// Id_alias
	$id_alias = strtolower($filter->requestValue('id_alias')->getID());

	// Parent_id
	$parent_id = $filter->requestValue('parent_id')->getInteger();

	// Level
	if ($parent_id != 0)
	{
		$parent_level = $db->selectOne($com_gen->getTablePrefix()."node, level, where: id=$parent_id", 'level');
		$level = $parent_level+1;
	} else {
		$level = 0;
	}

	// Access_level
	$access_level = $filter->requestValue('access_level')->getInteger();

	// Published
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;

	// Archived
	$filter->requestValue('archived')->get() ? $archived = 1 : $archived = 0;

	// List order
	# Modification available only on start view

	// Show ...
	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;

	// No duplicate id_alias allowed in the same parent_id
	$current = $db->selectOne($com_gen->getTablePrefix()."node, id_alias, parent_id, where: id=$upd_id");
	$current_id_alias  = $current['id_alias'];
	$current_parent_id = $current['parent_id'];
	// Something have changed, then check for duplicate!
	if (($id_alias != $current_id_alias) || ($parent_id != $current_parent_id))
	{
		if ($db->select($com_gen->getTablePrefix().'node, id, where: id_alias='.$db->str_encode($id_alias)." AND, where: parent_id=$parent_id"))
		{
			$filter->set(false, 'id_alias')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS_ALREADY_EXIST));
		}
	}

	// When moving to another node, place it at the end list of the new node (list_order=999)
	($parent_id != $current_parent_id) ? $list_order = 'list_order=999, ' : $list_order = '';

	/**
	 * Specific : 'node_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_node.php', $volatile_upd_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result =
			$db->update(
					$com_gen->getTablePrefix()."node; id_alias=".$db->str_encode($id_alias).', '.
					"parent_id=$parent_id, level=$level, access_level=$access_level, published=$published, ".$list_order.
					"show_date_creation=$show_date_creation, show_date_modified=$show_date_modified, show_author_id=$show_author_id, show_hits=$show_hits, ".
					"archived=$archived; ".
					"where: id=$upd_id"
			);

		admin_informResult(($result) && ($volatile_result));

		// Update the 'level' field of subnodes
		$result = $com_gen->updateNodeLevel($upd_id);
		if (!$result) {
			echo "<span style=\"color:red;\">Critical Error into 'node' table :<br />We wasn't able to update the 'level' field of sub-nodes.<br />You must check them manually. Good luck!</span>";
		}

		// If just archived now, then archive all subnodes and all elements
		if (($archived == 1) && ($filter->requestValue('current_archived')->get() == 0))
		{
			$result = $com_gen->updateArchivedField($upd_id);
			if (!$result) {
				echo "<span style=\"color:red;\">Error occured : We wasn't able to update the <strong>'archived'</strong> field for subnodes and elements</span>";
			}

			// Also remove archived elements from 'home' table
			$com_gen->removeArchivedFromHomeElm();
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
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_TITLE_UPDATE).'</h2>';

	// Id
	if ($upd)
	{
		$upd_id = $upd;
	} else {
		$upd_id = $filter->requestValue('id')->getInteger();
	}

	// Current_node
	$current_node = $db->selectOne($com_gen->getTablePrefix()."node, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$form->addMultipartFormData(); # Add enctype="multipart/form-data" to allow downloads
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	$wrapper = '';

	/** 
	 * Fieldset - Global : Node
	 */
	$fieldset = '';

	// Id
	$fieldset .= $form->hidden('id', $upd_id);

	// Id_alias
	$fieldset .= $form->text('id_alias', $current_node['id_alias'], $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS), '', 'maxlength=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED;

	// Parent_id
	$nodes_options = formManager::selectOption($com_gen->getNodesOptions(false, LANG_SELECT_OPTION_ROOT, 0,$upd_id), $current_node['parent_id']);
	$fieldset .= '<br /><br />'.$form->select('parent_id', $nodes_options, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_PARENT_ID).'<br />').'<br /><br />';

	// Access_level
	$fieldset .= $form->select('access_level', comUser_getStatusOptions($current_node['access_level']), LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL).'&nbsp; &nbsp;';

	// Published
	$fieldset .= $form->checkbox('published', $current_node['published'], LANG_ADMIN_COM_GENERIC_PUBLISHED.'(left)').'&nbsp; &nbsp;';

	// Archived
	$fieldset .= $form->checkbox('archived', $current_node['archived'], LANG_ADMIN_COM_GENERIC_ARCHIVED.'(left)');
	$fieldset .= $form->hidden('current_archived', $current_node['archived']).' <span style="color:grey;">(*)</span><br />';
	$fieldset .= $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ARCHIVE_WARNING);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_NODE_FIELDSET));  

	/** 
	 * Fieldset - Global : Elements
	 */
	$fieldset = '';

	$fieldset .= $form->checkbox('show_date_creation'	, $current_node['show_date_creation'] , LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $current_node['show_date_modified'] , LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $current_node['show_author_id']	  , LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $current_node['show_hits']		  , LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ELEMENT_FIELDSET));  

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_node.php', $volatile_upd);
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
	 * Global : 'node' table
	 */

	// New id (must be setted to be used by volatile_*.php file)
	$new_id = $db->selectOne($com_gen->getTablePrefix().'node, id(desc)', 'id') +1;

	// Id_alias
	$id_alias = strtolower($filter->requestValue('id_alias')->getID());

	// Parent_id
	$parent_id = $filter->requestValue('parent_id')->getInteger();

	// Level
	if ($parent_id != 0)
	{
		$parent_level = $db->selectOne($com_gen->getTablePrefix()."node, level, where: id=$parent_id", 'level');
		$level = $parent_level+1;
	} else {
		$level = 0;
	}

	// Access_level
	$access_level = $filter->requestValue('access_level')->getInteger();

	// Published
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;

	// Archived
	$filter->requestValue('archived')->get() ? $archived = 1 : $archived = 0;

	// List order
	$list_order = 999;

	// Show ...
	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;

	// No duplicate id_alias allowed in the same parent_id
	if ($db->select($com_gen->getTablePrefix().'node, id, where: id_alias='.$db->str_encode($id_alias)." AND, where: parent_id=$parent_id"))
	{
		$filter->set(false, 'id_alias')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS_ALREADY_EXIST));
	}

	/**
	 * Specific : 'node_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_node.php', $volatile_new_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result =
			$db->insert(
				$com_gen->getTablePrefix()."node; $new_id, ".$db->str_encode($id_alias).', '.
				"$parent_id, $level, $access_level, $published, $list_order, ".
				"$show_date_creation, $show_date_modified, $show_author_id, $show_hits, ".
				"$archived"
			);

		admin_informResult($result && $volatile_result);

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
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_TITLE_NEW).'</h2>';

	$html = '';
	$form = new formManager();
	$form->addMultipartFormData(); # Add enctype="multipart/form-data" to allow downloads
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$wrapper = '';

	/** 
	 *  Fieldset - Global : Node
	 */
	$fieldset = '';

	// Id_alias
	$fieldset .= $form->text('id_alias', '', $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS), '', 'maxlength=50').LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED;

	// Parent_id
	$nodes_options = formManager::selectOption($com_gen->getNodesOptions(), $session->get($com_gen->getTablePrefix().'selection'));
	$fieldset .= '<br /><br />'.$form->select('parent_id', $nodes_options, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_PARENT_ID).'<br />').'<br /><br />';

	// Access_level
	$fieldset .= $form->select('access_level', comUser_getStatusOptions(comUser_getLowerStatus()), LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL).'&nbsp; &nbsp;';

	// Published
	$fieldset .= $form->checkbox('published', 1, LANG_ADMIN_COM_GENERIC_PUBLISHED.'(left)').'&nbsp; &nbsp;';

	// Archived
	$fieldset .= $form->checkbox('archived', 0, LANG_ADMIN_COM_GENERIC_ARCHIVED.'(left)');

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_NODE_FIELDSET));  

	/** 
	 * Fieldset - Global : Elements
	 */
	$fieldset = '';

	$default = $db->selectOne($com_gen->getTablePrefix().'config, show_date_creation, show_date_modified, show_author_id, show_hits');

	$fieldset .= $form->checkbox('show_date_creation'	, $default['show_date_creation'], LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $default['show_date_modified'], LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $default['show_author_id']	, LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $default['show_hits']			, LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_ELEMENT_FIELDSET));  

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_node.php', $volatile_new);
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
if ($submit && $node_id = formManager_filter::arrayOnly($filter->requestName('list_order_')->getInteger()))
{
	for ($i=0; $i<count($node_id); $i++)
	{
		$order = $filter->requestValue('list_order_'.$node_id[$i])->getInteger();
		if ($order !== false)
		{
			$db->update($com_gen->getTablePrefix().'node; list_order='.$order.'; where: id='.$node_id[$i]);
		}
	}
}



if ($start_view)
{
	// Title
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_TITLE_START).'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'node_');

	// Session variables : begin //

	// Live_archive_status (update class property)
	$archive_node = $com_gen->setLiveArchiveStatus('node', $session->setAndGet($com_gen->getTablePrefix().'live_archive_status', $filter->requestValue('live_archive_status')->getInteger(0)));

	// Node selection
	$node_selection = $session->setAndGet($com_gen->getTablePrefix().'selection', $filter->requestValue('selection')->getInteger(0));

	$nodes_options = formManager::selectOption($com_gen->getNodesOptions(), $node_selection);
	$html .= $form->select('selection', $nodes_options, $com_gen->translate(LANG_ADMIN_COM_GENERIC_NODE_SELECTION));
	// Session variables : end //

	// Live_archive_status (select form)
	$html .= $form->select('live_archive_status', $com_gen->liveArchiveStatusNodesOptions($archive_node), $com_gen->translate(LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION));
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_top').'<br /><br />'; # Submit-button for the start-view

	// Push all archived nodes after non-archived ones (including the subnodes)
	$com_gen->pushArchivedNodes($node_selection);

	// Generic
	$nodes_list = $com_gen->getNodes($node_selection);

	// $nodes_list transformations
	for ($i=0; $i<count($nodes_list); $i++)
	{
		// Input-text for the list_order field
		$nodes_list[$i]['list_order'] = $form->text('list_order_'.$nodes_list[$i]['id'], $nodes_list[$i]['list_order'], '', '', 'size=1;update=no'); // (0)

		// Access_level (_name instead of _id) (get the access-level-name from user_status table)
		$temp = $db->select('user_status, comment, where: id='.$nodes_list[$i]['access_level']);
		$nodes_list[$i]['access_level'] = $temp[0]['comment'];

		// Published (<a> tag with checked/unchecked image)
		$nodes_list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$nodes_list[$i]['id'].'">'.admin_replaceTrueByChecked($nodes_list[$i]['published']).'</a>'; // (4)

		// Archived (<a> tag with archived/empty image)
		$nodes_list[$i]['archived'] = $com_gen->replaceTrueByArchived($nodes_list[$i]['archived']);

		// Archived is now included with the published column
		$nodes_list[$i]['archived'] ? $nodes_list[$i]['published'] .= ' &nbsp;'.$nodes_list[$i]['archived'] : '';
	}

	for ($i=0; $i<count($nodes_list); $i++)
	{
		$update[$i] = $form->submit('upd_'.$nodes_list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$nodes_list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
	}

	for ($i=0; $i<count($nodes_list); $i++)
	{
		$nodes_list[$i]['id'] = "<span style=\"color:#AAA;\">{$nodes_list[$i]['id']}</span>"; # careful : the ID $nodes_list[$i]['id'] is no longer available. So, we have done this at the end !
	}

	// Table
	$table = new tableManager($nodes_list);

	$table_header = $com_gen->getNodesHeader();

	if (count($nodes_list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	array_unshift($table_header, '');
	array_push($table_header, '');

	$table->header($table_header);

	#$table->delCol('1,2,7'); # Delete ID column, level column, and archived column
	$table->delCol('2,7'); # Delete only level column, and archived column

	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_bottom'); # Submit-button for the start-view
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>