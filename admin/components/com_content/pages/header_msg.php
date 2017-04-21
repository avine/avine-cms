<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */



///////////////////////////
// Get the $com_gen object

// Get component setting
global $init;
!isset($init) or trigger_error(LANG_COM_GENERIC_INIT_OVERWRITTEN, E_USER_WARNING);
require(comGeneric_::comSetupPath(__FILE__));

// Instanciate class object
global $com_gen;
$com_gen = new admin_comGeneric($init);

// Unset temporary variable
$init = NULL;

// end
//////



// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post'); // (0)
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



// (3) Case 'del'
if ($del)
{
	admin_informResult($db->delete($com_gen->getTablePrefix()."header_msg; where: id=$del"));
}



// Get global nodes options for the entire page
$nodes_options = $com_gen->getNodesOptions(false);



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	$upd_id			= $filter->requestValue('id')->getInteger();
	$date_creation	= $filter->requestValue('date_creation')->getFormatedDate(1, '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_DATE_CREATION);
	$node_id		= $filter->requestValue('node_id')->getInteger(1, '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID);
	$node_id or $filter->requestValue('node_id')->getError(LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID_NOT_SELECTED, LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID);
	$message		= $filter->requestValue('message')->getNotEmpty(1, '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_MESSAGE);

	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult($db->update($com_gen->getTablePrefix()."header_msg; ".
			"date_creation=$date_creation, node_id=$node_id, message=".$db->str_encode($message)."; where: id=$upd_id"
		));
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTENT_HEADER_MSG_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne($com_gen->getTablePrefix()."header_msg, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= $form->text('date_creation', getTime($current['date_creation'], 'time=0'), LANG_ADMIN_COM_CONTENT_HEADER_MSG_DATE_CREATION, '', 'size=10').'&nbsp &nbsp;';
	$html .= $form->select('node_id', formManager::selectOption($nodes_options, $current['node_id']), LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID).'<br /><br />';
	$html .= $form->textarea('message', $current['message'], LANG_ADMIN_COM_CONTENT_HEADER_MSG_MESSAGE.'<br />', '', 'cols=55').'<br /><br />';

	$html .= '<script type="text/javascript">$(function(){$(\'#upd_date_creation\').datepicker({inline: true});});</script>'."\n";

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$date_creation	= $filter->requestValue('date_creation')->getFormatedDate(1, '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_DATE_CREATION);
	$node_id		= $filter->requestValue('node_id')->getInteger(1, '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID);
	$node_id or $filter->requestValue('node_id')->getError(LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID_NOT_SELECTED, LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID);
	$message		= $filter->requestValue('message')->getNotEmpty(1, '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_MESSAGE);

	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult($db->insert($com_gen->getTablePrefix()."header_msg; NULL, $date_creation, $node_id, ".$db->str_encode($message)));
	} else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTENT_HEADER_MSG_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('date_creation', '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_DATE_CREATION, '', 'size=10').'&nbsp &nbsp;';
	$html .= $form->select('node_id', $nodes_options, LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID).'<br /><br />';
	$html .= $form->textarea('message', '', LANG_ADMIN_COM_CONTENT_HEADER_MSG_MESSAGE.'<br />', '', 'cols=55').'<br /><br />';

	$html .= '<script type="text/javascript">$(function(){$(\'#new_date_creation\').datepicker({inline: true});});</script>'."\n";

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTENT_HEADER_MSG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$list = $db->select($com_gen->getTablePrefix().'header_msg, *, date_creation(desc)');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($list); $i++)
	{
		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)

		$list[$i]['node_id'			] = $com_gen->nodeFullPath($list[$i]['node_id'], '/', false);
		$list[$i]['date_creation'	] = getTime($list[$i]['date_creation'], 'time=0');

		$max = 30;
		if (mb_strlen($list[$i]['message']) > $max) {
			$list[$i]['message'		] = mb_substr($list[$i]['message'], 0, $max).'...';
		}
		$list[$i]['id'] = "<span style=\"color:#999;\">{$list[$i]['id']}</span>"; # Carefull : ID info no more available
	}

	// Table
	$table = new tableManager($list);
	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(array('',
			LANG_ADMIN_COM_CONTENT_HEADER_MSG_ID,
			LANG_ADMIN_COM_CONTENT_HEADER_MSG_DATE_CREATION,
			LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID,
			LANG_ADMIN_COM_CONTENT_HEADER_MSG_MESSAGE, ''));
	$table->delCol(1); # Delete the 'id' column
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)
	$html .= $form->end();

	echo $html;
}


echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>