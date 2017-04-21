<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();

$session = new sessionManager(sessionManager::BACKEND, 'wcal_dedicate_details');


// Configuration
$start_view = true;

$node_error_message = '<img src="'.WEBSITE_PATH.'/admin/components/com_wcal/images/node_error.png" alt="" title="'.LANG_ADMIN_COM_WCAL_CATEGORY_NODE_ID_MISSING.'" style="cursor:help;" />';
$element_error_message = '<img src="'.WEBSITE_PATH.'/admin/components/com_wcal/images/element_error.png" alt="" title="'.LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ELEM_ID_NO_MATCH.'" style="cursor:help;" />';


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post');
if ($submit)
{
	$upd = $filter->requestName ('upd_'	)->getInteger();
} else {
	$upd = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post');



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	// Get id
	$upd_id = $filter->requestValue('id')->getInteger();

	if ($element_id = $filter->requestValue('element_id')->getInteger(1, '', LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ASSOCIATED_ELEMENT))
	{
		$element = $db->selectOne("content_element, node_id,date_creation, where: id=$element_id");
		$element or $filter->set(false)->getError('Error occured : unable to select row from "content_element" table where id='.$element_id);
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update("wcal_dedicate_details; node_id={$element['node_id']}, elm_date_creation={$element['date_creation']}; where: id=$upd_id");
		admin_informResult($result);

		$result ? $update_id_success = $upd_id : ''; # For message...
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $upd_id);

	// Get all about the dedicate and it's details
	$dedicate_id = $db->selectOne("wcal_dedicate_details, dedicate_id, where: id=$upd_id", 'dedicate_id');
	$current = wcal::dedicateDetails($dedicate_id);

	// Dedicate summary
	$html .=
		'<p>'.
			"<span class=\"grey\"><strong>".LANG_COM_WCAL_DEDICATE_ID." :</strong> $dedicate_id </span><br />\n".
			'<strong>'.LANG_COM_WCAL_DEDICATE_RECORDING_DATE.' :</strong> '.getTime($current['recording_date'], 'time=no')."<br />\n".
		"</p>\n";

	// List of content elements associated to this dedicate
	$element_id = array();
	$details = $current['details'];
	for ($i=0; $i<count($details); $i++)
	{
		$element_id = array_merge($element_id, array_keys(wcal::matchElement($details[$i]['node_id'], $details[$i]['elm_date_creation'])));
	}
	$element_id = array_unique($element_id);

	// List of all content elements which are not in $element_id
	$com_content = comContent_frontendScope();
	$summary = $com_content->summaryNodes(true);

	$summary_options = array();
	for ($i=0; $i<count($summary); $i++)
	{
		if (($summary[$i]['type'] == 'element') && !in_array($summary[$i]['id'], $element_id))
		{
			$summary_options[$summary[$i]['id']] = $summary[$i]['id_alias'];
		}
	}

	(count($summary_options) > 20) ? $size = 20 : $size = count($summary_options);
	$html .= $form->select('element_id', $summary_options, LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ASSOCIATED_ELEMENT.'<br />', '', "size=$size");
	// End of list

	$html .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_TITLE_START.'</h2>';

	$html = '';

	// Perform a maintenance before any database select
	wcal::updatePaymentStatus();

	$details_number = $db->selectCount('wcal_dedicate_details, join: dedicate_id>; wcal_dedicate, where: payment_status=1, join: <id');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Multipage
	$multipage = new simpleMultiPage($details_number);
	$multipage->setFormID('start_');
	$multipage->updateSession($session->returnVar('multipage'));
	$html .=
		admin_floatingContent(
			array(
				$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),
				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT)
			)
		);

	// Database
	$list =
		$db->select(
			'wcal_dedicate_details, id, elm_date_creation(desc), node_id, join: dedicate_id>; '.
			'wcal_dedicate, id AS dedicate_id(desc), where: payment_status=1, join: <id; '.
			'limit:'.$multipage->dbLimit()
		);

	$wcal = new wcal();
	$type = wcal::dedicateTypeOptions();

	$time = time();
	for ($i=0; $i<count($list); $i++)
	{
		$match_info = '';
		$match = wcal::matchElement($list[$i]['node_id'], $list[$i]['elm_date_creation']);
		foreach($match as $element) {
			$match_info .= '<a class="external" href="'.$element['href'].'">'.$element['node'].$element['title']."</a><br />\n";
		}

		$update[$i] = '';
		if (isset($update_id_success) && ($list[$i]['id'] == $update_id_success))
		{
			$span_l = '<span class="green">';
			$span_r = '</span>';

			// New icon !
			$update[$i] = '<img src="'.WEBSITE_PATH.'/admin/images/new.png" alt="" />';
			$add_col_update = true;
		}
		elseif (count($match))
		{
			$span_l = '';
			$span_r = '';
		}
		elseif ($list[$i]['elm_date_creation'] > $time)
		{
			$span_l = '<span class="grey">';
			$span_r = '</span>';
		}
		else
		{
			$span_l = '<span class="red">';
			$span_r = '</span>';

			$match_info = $element_error_message;

			// Update button is available only when no matched element were found
			$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
			$add_col_update = true;
		}

		$node_title = $db->selectOne('content_node_item, title, where: node_id='.$list[$i]['node_id'], 'title');

		$list[$i]['elm_date_creation'	] = $span_l.	getTime($list[$i]['elm_date_creation'])				.$span_r;
		$list[$i]['node_id'				] = $span_l.	($node_title ? $node_title : $node_error_message)	.$span_r;
		$list[$i]['dedicate_id'			] = $span_l.	$list[$i]['dedicate_id']							.$span_r;

		$list[$i]['match_info'			] = $match_info;
	}

	// Table
	$table = new tableManager($list);
	$table->delCol(0); # Delete the 'id' column

	$table->header(
		array(
			LANG_COM_WCAL_DEDICATE_DETAILS_ELM_DATE_CREATION,
			LANG_COM_WCAL_DEDICATE_DETAILS_NODE_ID,
			LANG_COM_WCAL_DEDICATE_ID,
			LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ASSOCIATED_ELEM,
		)
	);
	if (count($list) && isset($add_col_update)) {
		$table->addCol($update, 999);
	}
	$html .= $table->html();

	$html .= $form->end();
	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>