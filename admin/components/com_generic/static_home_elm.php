<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/**
 * Unlike static_config.php, static_node.php and static_element.php, that for each one of them we need his volatile_*.php file ;
 * this time static_home.php don't have any volatile_*.php file.
 * So, it will be more easy !
 */

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
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)

$submit = formManager::isSubmitedForm('start_', 'post'); // (0)
if ($submit)
{
	$del 		= $filter->requestName ('del_'		)->getInteger(); // (3)
	$upd_all 	= $filter->requestValue('upd_all'	)->get(); // (2)
	$new 		= $filter->requestValue('new'		)->get(); // (1)
} else {
	$del 		= false;
	$upd_all 	= false;
	$new 		= false;
}

$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)
$upd_all_submit = formManager::isSubmitedForm('upd_all_', 'post'); // (2)



// Session
$session = new sessionManager(sessionManager::BACKEND, 'static_home_elm');
if ($submit && ($home_nde_id = $filter->requestValue('home_nde_id')->getInteger()))
{
	if ($session->get('home_nde_id') != $home_nde_id) {
		$session->set('home_nde_id', $home_nde_id);
		$session_updated = true;
	}
}
else {
	$session->init('home_nde_id', $db->selectOne($com_gen->getTablePrefix().'home_nde, id, where: default_nde=1', 'id'));
}
$home_nde_id = $session->get('home_nde_id'); # Simple alias



/*
 * Get elements summary
 */

// Nodes restriction
if ($nodes_id = $db->selectOne($com_gen->getTablePrefix()."home_nde, nodes_id, where: id=$home_nde_id", 'nodes_id'))
{
	$summary = array();

	$nodes_id = explode(';', $nodes_id);
	for ($i=0; $i<count($nodes_id); $i++) {
		$summary = array_merge($summary, $com_gen->summaryElements($nodes_id[$i]));
	}
}
// No nodes restriction...
else
{
	$summary = $com_gen->summaryNodes();
}

// Extract and customize the summary of the elements
$summary_element = array();
for ($i=0; $i<count($summary); $i++)
{
	if ($summary[$i]['type'] == 'element') {
		$summary_element[$summary[$i]['id']] = $summary[$i]['id_alias'];
	}
}

// Usefull for (1) & (2) : List of current home-page elements (before any 'new' or 'upd_all' process)
$home_elm_id_current = array_keys($db->select($com_gen->getTablePrefix()."home_elm, [elm_id], where: home_nde_id=$home_nde_id"));

/* En of summary */



// (4) Case 'publish_status' (change the publish status)
if ($publish_status)
{
	if ($published = $db->selectOne($com_gen->getTablePrefix()."home_elm, elm_published, where: elm_id=$publish_status AND home_nde_id=$home_nde_id"))
	{
		$published['elm_published'] == 1 ? $published = 0 : $published = 1;
		$db->update($com_gen->getTablePrefix()."home_elm; elm_published=$published; where: elm_id=$publish_status AND home_nde_id=$home_nde_id");
	}
}



// (3) Case 'del'
if ($del)
{
	admin_informResult($db->delete($com_gen->getTablePrefix()."home_elm; where: elm_id=$del AND home_nde_id=$home_nde_id"));
}



// (2) Case 'upd_all'
if ($upd_all_submit)
{
	$upd_all_submit_validation = true;
	$filter->reset();

	// elm_id list
	$elm_id = formManager_filter::arrayOnly($filter->requestValue('elm_id')->getInteger(0), false);

	// publish
	$filter->requestValue('publish')->get() ? $publish = '1' : $publish = '0';

	// Database Process
	if ($upd_all_submit_validation = $filter->validated())
	{
		$result = true;

		// Remove from existing elements
		for ($i=0; $i<count($home_elm_id_current); $i++)
		{
			if (!in_array($home_elm_id_current[$i], $elm_id))
			{
				$db->delete($com_gen->getTablePrefix()."home_elm; where: elm_id={$home_elm_id_current[$i]} AND home_nde_id=$home_nde_id") ? '' : $result = false;
			}
		}

		// Add new elements
		$added_num = 0;
		$query_where = '';
		for ($i=0; $i<count($elm_id); $i++)
		{
			if (!in_array($elm_id[$i], $home_elm_id_current))
			{
				$db->insert($com_gen->getTablePrefix()."home_elm; $home_nde_id, {$elm_id[$i]}, ".(2*$added_num +1).", $publish") ? '' : $result = false;
				$query_where .= ", where: elm_id != {$elm_id[$i]} AND";

				$added_num++;
			}
		}

		// Push the others elements
		if ($added_num != 0)
		{
			$home_list = $db->select( $com_gen->getTablePrefix()."home_elm, elm_id,elm_order(asc) $query_where, where: home_nde_id=$home_nde_id" );
			for ($i=0; $i<count($home_list); $i++) {
				$db->update($com_gen->getTablePrefix().'home_elm; elm_order='.(2*$i+1 +2*$added_num)."; where: elm_id={$home_list[$i]['elm_id']} AND home_nde_id=$home_nde_id") ? '' : $result = false;
			}
		}

		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd_all) || (($upd_all_submit) && (!$upd_all_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_TITLE_UPD_ALL).'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_all_');

	(count($summary_element) < 15) ? $size = count($summary_element) : $size = 15;
	$html .= $form->select('elm_id', formManager::selectOption($summary_element, $home_elm_id_current), $com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_UPD_SELECT).'<br />', '', "size=$size;multiple");
	$html .= '<br /><br />'.$form->checkbox('publish', 0, $com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_UPD_PUBLISH_NEW_ONES));

	$html .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br />';
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	// elm_id
	$elm_id = $filter->requestValue('elm_id')->getInteger(1, $com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_FAILED));

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult($db->insert($com_gen->getTablePrefix()."home_elm; $home_nde_id, $elm_id, 0, 0"));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	// Keep only the elements wich are not currently on home-page
	$summary_new_element = array();
	reset($summary_element);
	foreach($summary_element as $id => $alias)
	{
		if (!in_array($id, $home_elm_id_current)) {
			$summary_new_element[$id] = $alias;
		}
	}

	if (count($summary_new_element))
	{
		$start_view = false;

		// Title
		echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_TITLE_NEW).'</h2>';

		$html = '';
		$form = new formManager();
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

		(count($summary_new_element) < 15) ? $size = count($summary_new_element) : $size = 15;
		$html .= $form->select('elm_id', $summary_new_element, $com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_SELECT).'<br />', '', "size=$size");

		$html .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br />';
		$html .= $form->end();
		echo $html;
	}
	elseif (!count($home_elm_id_current)) {
		admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_NO_ELM_AVAILABLE), 'info');
	}
	else {
		admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_NO_NEW_AVAILABLE), 'warning');
	}
}



//////////////
// Start view

// (0) Check for: elm_order update
if ($submit && !isset($session_updated) && ($elm_id = formManager_filter::arrayOnly($filter->requestName('elm_order_')->getInteger())))
{
	for ($i=0; $i<count($elm_id); $i++)
	{
		$elm_order = $filter->requestValue('elm_order_'.$elm_id[$i])->getInteger();
		if ($elm_order !== false)
		{
			$db->update($com_gen->getTablePrefix()."home_elm; elm_order=$elm_order; where: elm_id={$elm_id[$i]} AND home_nde_id=$home_nde_id");
		}
	}
}



if ($start_view)
{
	// Title
	echo '<h2>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_TITLE_START).'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// home_nde selection
	$html .= $form->select('home_nde_id', formManager::selectOption($com_gen->getHomeNdeOptions(), $home_nde_id), LANG_SELECT_OPTION_ROOT);
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit-top').'<br /><br />';

	// Database
	$home_list = $com_gen->getHomeElm($home_nde_id, $deleted_elm_id);

	$temp = array();
	$element_not_visible = false;
	for ($i=0; $i<count($home_list); $i++)
	{
		// element full_path
		$temp[$i]['id_alias'		] = $summary_element[ $home_list[$i]['elm_id'] ];
		$temp[$i]['elm_id'			] = $home_list[$i]['elm_id'];
		$temp[$i]['elm_order'		] = $form->text('elm_order_'.$home_list[$i]['elm_id'], (2*$i+1), '', '', 'size=1;update=no'); // (0)
		$temp[$i]['elm_published'	] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$home_list[$i]['elm_id'].'">'.admin_replaceTrueByChecked($home_list[$i]['elm_published']).'</a>'; // (4)

		// if the element is not visible, put it in italic
		if (!$com_gen->isVisibleElement( $home_list[$i]['elm_id'], 1 ))
		{
			$temp[$i]['id_alias'	] = '<span style="color:#999;font-style:italic;">'.$temp[$i]['id_alias'].'</span>';
			$element_not_visible = true;
		}

		// Del column
		$delete[$i] = $form->submit('del_'.$home_list[$i]['elm_id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
	}
	$home_list = $temp;

	if (count($deleted_elm_id)) {
		admin_message($com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_NODES_ID_HAVE_BEEN_UPDATED), 'info');
	}

	// Table
	$table = new tableManager($home_list);
	$table_header = $com_gen->getHomeHeader();
	if (count($home_list)) {
		$table->addCol($delete, 0);
	}
	array_unshift($table_header, '');
	$table->header($table_header);
	$table->delCol(2); # Delete ID column
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit-bottom');
	$html .= '&nbsp; '.$form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)
	$html .= '&nbsp; '.$form->submit('upd_all', LANG_ADMIN_COM_GENERIC_HOME_ELM_BUTTON_UPDATE); // (2)

	if ($element_not_visible) {
		$html .= '<p style="color:#999;font-style:italic;"><br />'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_HOME_ELM_NOT_VISIBLE_ELEMENT).'</p>';
	}

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>