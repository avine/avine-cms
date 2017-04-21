<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'module');


// Needed infos
$pos_files_options = admin_comTemplate_positionsFilesOptions($template_name_current);
if ($template_name_current)
{
	$pos_files_options_tips = '<br /><span class="grey">'. LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_DIRECTORY."/templates/$template_name_current/positions/" .'</span>';
} else {
	$pos_files_options_tips = ' <span class="red">'. LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_TEMPLATE_ERROR .'</span>';
}



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)

$submit = formManager::isSubmitedForm('module_', 'post'); // (0)
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



// (4) Case 'publish_status'
if ($publish_status)
{
	$published = $db->select("module, published, where: id=$publish_status");
	if ($published)
	{
		$published[0]['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("module; published=$published; where: id=$publish_status");
	}
}



// (3) Case 'del'
if ($del)
{
	$result_module 			= $db->delete("module; 		 where:     id = $del");
	$result_module_xhref 	= $db->delete("module_xhref; where: mod_id = $del");

	admin_informResult(($result_module && $result_module_xhref));
}



// (2) Case 'upd'
if ($upd_submit)
{
	// Fields validation
	$upd_submit_validation = true;

	$filter->reset();

	// id
	$upd_id = $filter->requestValue('id')->getInteger(); # (*)

	// Name
	$name = $filter->requestValue('name')->getNotEmpty(1, LANG_ADMIN_COM_MODULE_MODULE_NO_TITLE);

	// Show_ame
	$filter->requestValue('show_name')->get() ? $show_name = 1 : $show_name = 0;

	// Mod_pos
	$mod_pos = $filter->requestValue('mod_pos')->getVar(1, LANG_ADMIN_COM_MODULE_MODULE_POS_NOT_SELECTED);

	// Mod_file
	$mod_file = $filter->requestValue('mod_file')->get();
	$mod_file = formManager::decodePoint($mod_file);
	$mod_file = $filter->set($mod_file)->getFile(1, LANG_ADMIN_COM_MODULE_MODULE_FILE_NOT_SELECTED);

	// Page_xhref
	$link_href = array();
	if ($link_id = formManager_filter::arrayOnly($filter->requestValue('page_href')->getInteger(0)))
	{
		for ($i=0; $i<count($link_id); $i++)
		{
			$temp = $db->select("menu_link, href, where: id={$link_id[$i]}");
			isset($temp[0]['href']) ? $link_href[$link_id[$i]] = $temp[0]['href'] : $link_href[$link_id[$i]] = '';
		}
	}

	// Access_level
	$access_level = $filter->requestValue('access_level')->getInteger();

	// Published
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;

	// Html_pos
	$html_pos = $filter->requestValue('html_pos')->getID(0);

	// Comment
	$comment = $filter->requestValue('comment')->get();

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result_module = $db->update("module; name=".$db->str_encode($name).", show_name=$show_name, mod_file=".$db->str_encode($mod_file).', mod_pos='.$db->str_encode($mod_pos).", access_level=$access_level, published=$published, html_pos=".$db->str_encode($html_pos).", comment=".$db->str_encode($comment)."; where: id=$upd_id");

		$result_module_xhref_del = $db->delete("module_xhref; where: mod_id=$upd_id");

		$result_module_xhref_upd = true;
		reset($link_href);
		foreach($link_href as $lk_id => $lk_href)
		{
			$local_result = $db->insert("module_xhref; NULL, $upd_id, ".$db->str_encode($lk_href).", $lk_id");
			if (!$local_result) {
				$result_module_xhref_upd = false;
			}
		}
		admin_informResult(($result_module && $result_module_xhref_del && $result_module_xhref_upd));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_TITLE_UPDATE.'</h2>';

	// Id
	if ($upd) {
		$upd_id = $upd;
	} else {
		# $upd_id already set (see before (*));
	}

	$current = $db->select("module, *, where: id=$upd_id");
	$current = $current[0];

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	// Name
	$html .= $form->text('name', $current['name'], LANG_ADMIN_COM_MODULE_MODULE_NAME, '', 'maxlength=100').'&nbsp; &nbsp;';

	// Show_ame
	$html .= $form->checkbox('show_name', $current['show_name'], LANG_ADMIN_COM_MODULE_MODULE_SHOW_NAME.'(left)').'<br /><br />';

	// mod_pos & mod_file
	$html .= admin_comModule_selectPosition('upd_', 'mod_pos' , $current['mod_pos']).'&nbsp; &nbsp;';
	$html .= admin_comModule_selectModule  ('upd_', 'mod_file', $current['mod_file']).'<br /><br />';

	// Pages_xhref
	$html .= admin_comModule_selectPage('upd_', 'page_href', $current['id']).'<br /><br />';

	# Mod_order (you can change it only on $start_view)

	// Access_level
	$html .= $form->select('access_level', comUser_getStatusOptions($current['access_level']), LANG_ADMIN_COM_MODULE_MODULE_ACCESS_LEVEL).'&nbsp; &nbsp;';

	// Published
	$html .= $form->checkbox('published', $current['published'], LANG_ADMIN_COM_MENU_LINK_PUBLISHED.'(left)').'<br /><br />';

	// Html_pos (overwrite the html-code module)
	$html .= $form->select('html_pos', formManager::selectOption($pos_files_options, $current['html_pos']), LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_LABEL)."$pos_files_options_tips<br /><br />";

	// Comment
	$html .= $form->text('comment', $current['comment'], LANG_ADMIN_COM_MENU_DESC, '', 'maxlength=255').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;

	$filter->reset();

	// New id
	$new_id = 'NULL'; # part of query

	// Name
	$name = $filter->requestValue('name')->getNotEmpty(1, LANG_ADMIN_COM_MODULE_MODULE_NO_TITLE);

	// Show_ame
	$filter->requestValue('show_name')->get() ? $show_name = 1 : $show_name = 0;

	// Mod_pos
	$mod_pos = $filter->requestValue('mod_pos')->getVar(1, LANG_ADMIN_COM_MODULE_MODULE_POS_NOT_SELECTED);

	// Mod_file
	$mod_file = $filter->requestValue('mod_file')->get();
	$mod_file = formManager::decodePoint($mod_file);
	$mod_file = $filter->set($mod_file)->getFile(1, LANG_ADMIN_COM_MODULE_MODULE_FILE_NOT_SELECTED);

	// Page_xhref
	$link_href = array();
	if ($link_id = formManager_filter::arrayOnly($filter->requestValue('page_href')->getInteger(0)))
	{
		for ($i=0; $i<count($link_id); $i++)
		{
			$temp = $db->select("menu_link, href, where: id={$link_id[$i]}");
			isset($temp[0]['href']) ? $link_href[$link_id[$i]] = $temp[0]['href'] : $link_href[$link_id[$i]] = '';
		}
	}

	// Access_level
	$access_level = $filter->requestValue('access_level')->getInteger();

	// Published
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;

	// Html_pos
	$html_pos = $filter->requestValue('html_pos')->getID(0);

	// Comment
	$comment = $filter->requestValue('comment')->get();

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result_module = $db->insert("module; $new_id, ".$db->str_encode($name).", $show_name, ".$db->str_encode($mod_file).', '.$db->str_encode($mod_pos).", 9999, $access_level, $published, ".$db->str_encode($html_pos).", '', ".$db->str_encode($comment));
		$new_id = $db->insertID();

		$result_module_xhref = true;
		reset($link_href);
		foreach($link_href as $lk_id => $lk_href)
		{
			$local_result = $db->insert("module_xhref; NULL, $new_id, ".$db->str_encode($lk_href).", $lk_id");
			if (!$local_result) {
				$result_module_xhref = false;
			}
		}
		admin_informResult(($result_module && $result_module_xhref));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	// Name
	$html .= $form->text('name', '', LANG_ADMIN_COM_MODULE_MODULE_NAME, '', 'maxlength=100').'&nbsp; &nbsp;';

	// Show_ame
	$html .= $form->checkbox('show_name', 1, LANG_ADMIN_COM_MODULE_MODULE_SHOW_NAME.'(left)').'<br /><br />';

	// mod_pos & mod_file
	$html .= admin_comModule_selectPosition('new_', 'mod_pos' ).'&nbsp; &nbsp;';
	$html .= admin_comModule_selectModule  ('new_', 'mod_file').'<br /><br />';

	// Pages_xhref
	$html .= admin_comModule_selectPage('new_', 'page_href').'<br /><br />';

	# Mod_order (you can change it only on $start_view)

	// Access_level
	$html .= $form->select('access_level', comUser_getStatusOptions(comUser_getLowerStatus()), LANG_ADMIN_COM_MODULE_MODULE_ACCESS_LEVEL).'&nbsp; &nbsp;';

	// Published
	$html .= $form->checkbox('published', 1, LANG_ADMIN_COM_MENU_LINK_PUBLISHED.'(left)').'<br /><br />';

	// Html_pos (overwrite the html-code module)
	$html .= $form->select('html_pos', $pos_files_options, LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_LABEL)."$pos_files_options_tips<br /><br />";

	// Comment
	$html .= $form->text('comment', '', LANG_ADMIN_COM_MENU_DESC, '', 'maxlength=255').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

// (0) Always check for: module_order update
if ($mod_id = formManager_filter::arrayOnly($filter->requestName('mod_order_')->get()))
{
	for ($i=0; $i<count($mod_id); $i++)
	{
		$order = $filter->requestValue('mod_order_'.$mod_id[$i])->getInteger();
		if ($order !== false)
		{
			$db->update('module; mod_order='.$order.'; where: id='.$mod_id[$i]);
		}
	}
}

// Format all modules mod_order
if ($module = $db->select("module, id, mod_pos(asc), mod_order(asc)"))
{
	$counter = 0;
	$current_pos = $module[0]['mod_pos'];
	for ($i=0; $i<count($module); $i++)
	{
		if ($module[$i]['mod_pos'] != $current_pos)
		{
			$current_pos = $module[$i]['mod_pos'];
			$counter = 0;
		}
		$db->update('module; mod_order='.(2*($counter++)+1).'; where: id='.$module[$i]['id']);
	}
}



if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_MODULE_MOD_TITLE_START.'</h2>';

	$html = '';

	// Session variables
	if ($submit)
	{
		if ($filter->requestValue('mod_pos')->get() == 'no-filter')
		{
			$session->reset('mod_pos');
		} else {
			$session->setAndGet('mod_pos', $filter->requestValue('mod_pos')->getVar(0));
		}

		if ($filter->requestValue('mod_file')->get() == 'no-filter')
		{
			$session->reset('mod_file');
		} else {
			$session->setAndGet('mod_file', $filter->set( formManager::decodePoint($filter->requestValue('mod_file')->get()) )->getFile()); # Remember that files names are encoded when posted
		}
	}

	// Alias
	$current_mod_pos  = $session->get('mod_pos' );
	$current_mod_file = $session->get('mod_file');

	// Position and module filter
	$mod_pos  = admin_comModule_selectPosition('module_', 'mod_pos' , $current_mod_pos);
	$mod_file = admin_comModule_selectModule  ('module_', 'mod_file', $current_mod_file);

	if (($mod_pos) && ($mod_file))
	{
		$form = new formManager(0);
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'module_');

		// Position & module filters
		$html .= $mod_pos.'&nbsp; &nbsp;'.$mod_file.'&nbsp; &nbsp;'.$form->submit('order', LANG_ADMIN_BUTTON_SUBMIT, 'order_top').'<br /><br />';

		// Modules list (filtered by $current_mod_pos & $current_mod_file)
		$where = '';
		if ($current_mod_pos) {
			$where .= ', where: mod_pos='.$db->str_encode($current_mod_pos);
		}
		if (($current_mod_pos) && ($current_mod_file)) {
			$where .= ' AND ';
		}
		if ($current_mod_file) {
			$where .= ', where: mod_file='.$db->str_encode($current_mod_file);
		}
		$module_list = $db->select("module, id, name, mod_pos(asc), mod_order(asc), access_level, published, mod_file, comment $where");

		for ($i=0; $i<count($module_list); $i++)
		{
			// Input-text for the mod_order field (Notice: do not use the posted info - Use only the db info. Because each time we format the mod_order)
			$module_list[$i]['mod_order'] = $form->text('mod_order_'.$module_list[$i]['id'], $module_list[$i]['mod_order'], '', '', 'size=1'); // (0)

			// Access_level (_name instead of _id) (get the access-level-name from user_status table)
			$user_status = comUser_getStatusOptions();
			$module_list[$i]['access_level'] = $user_status[$module_list[$i]['access_level']];

			// Published (<a> tag with checked/unchecked image)
			$module_list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$module_list[$i]['id'].'">'.admin_replaceTrueByChecked($module_list[$i]['published']).'</a>'; // (4)
		}

		for ($i=0; $i<count($module_list); $i++)
		{
			$update[$i] = $form->submit('upd_'.$module_list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
			$delete[$i] = $form->submit('del_'.$module_list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
		}

		$table = new tableManager($module_list);

		if (count($module_list)) {
			$table->addCol($delete, 0);
			$table->addCol($update, 999);
		}

		$table->header(
					array( '',
						LANG_ADMIN_COM_MODULE_MODULE_ID				, LANG_ADMIN_COM_MODULE_MODULE_NAME		 , 
						LANG_ADMIN_COM_MODULE_MODULE_POS			, LANG_ADMIN_COM_MODULE_MODULE_ORDER	 , 
						LANG_ADMIN_COM_MODULE_MODULE_ACCESS_LEVEL	, LANG_ADMIN_COM_MODULE_MODULE_PUBLISHED , 
						LANG_ADMIN_COM_MODULE_MODULE_FILE			, LANG_ADMIN_COM_MODULE_MODULE_COMMENT	 , ''
					)
		);

		$table->delCol(1); # Delete the 'id' column wich is no use for the view
		$html .= $table->html();

		// Buttons
		$html .= $form->submit('order', LANG_ADMIN_BUTTON_SUBMIT, 'order_bottom'); // (0)
		$html .= $form->submit('new'  , LANG_ADMIN_BUTTON_CREATE); // (1)

		$html .= $form->end();
		echo $html;
	}
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>