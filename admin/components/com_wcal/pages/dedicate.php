<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();

$session = new sessionManager(sessionManager::BACKEND, 'wcal_dedicate');

$node_error_message = '<img src="'.WEBSITE_PATH.'/admin/components/com_wcal/images/node_error.png" alt="" title="'.LANG_ADMIN_COM_WCAL_CATEGORY_NODE_ID_MISSING.'" style="cursor:help;" />';


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
	$upd = $filter->requestName ('upd_')->getInteger();
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

	$event_date	= $filter->requestValue('event_date')->getFormatedDate	(1, '', LANG_COM_WCAL_DEDICATE_EVENT_DATE	);
	$type_id	= $filter->requestValue('type_id'	)->getInteger		(1, '', LANG_COM_WCAL_DEDICATE_TYPE			);
	$comment	= $filter->requestValue('comment'	)->getNotEmpty		(1, '', LANG_COM_WCAL_DEDICATE_COMMENT		);

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update("wcal_dedicate; event_date=$event_date, type_id=$type_id, comment=".$db->str_encode(strip_tags($comment))."; where: id=$upd_id");
		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = wcal::dedicateDetails($upd_id);

	$html = '';

	/*
	 * Static info
	 */

	$html .= 
		'<p>'.
			"<span class=\"grey\"><strong>ID :</strong> $upd_id </span><br />\n".
			'<strong>'.LANG_COM_WCAL_DEDICATE_RECORDING_DATE.' :</strong> '.getTime($current['recording_date'], 'time=no')."<br />\n".
			'<strong>'.LANG_COM_WCAL_DEDICATE_PAYMENT_STATUS.' :</strong> '.admin_replaceTrueByChecked($current['payment_status'], false).
		"</p>\n";

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	/*
	 * Dedicate
	 */

	$html .= $form->text('event_date', getTime($current['event_date'], 'time=no'), LANG_COM_WCAL_DEDICATE_EVENT_DATE.'<br />', '', 'size=12').'<br /><br />';
	$html .= '<script type="text/javascript">$(function(){$(\'#upd_event_date\').datepicker({inline: true});});</script>'."\n";

	$html .= $form->select('type_id', formManager::selectOption(wcal::dedicateTypeOptions(), $current['type_id']), LANG_COM_WCAL_DEDICATE_TYPE.'<br />').'<br /><br />';

	$html .= $form->textarea('comment', $current['comment'], LANG_COM_WCAL_DEDICATE_COMMENT.'<br />', '', 'cols=70;rows=7').'<br /><br />';

	/*
	 * Dedicate details
	 */

	$details_html = '';
	$details = $current['details'];
	for ($i=0; $i<count($details); $i++)
	{
		$details_html .=
			admin_replaceTrueByChecked( count(wcal::matchElement($details[$i]['node_id'], $details[$i]['elm_date_creation'])), false ).
			'&nbsp; '.($details[$i]['title'] ? '<strong>'.$details[$i]['title'].'</strong>' : $node_error_message).
			' - '.getTime($details[$i]['elm_date_creation'], 'format=long').'<br />';
	}
	$html .= "<h3>".LANG_ADMIN_COM_WCAL_DEDICATE_DEDICATED_EVENTS." : </h3>\n<p>$details_html</p>\n";

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_DEDICATE_TITLE_START.'</h2>';

	$html = '';

	// Perform a maintenance before any database select
	admin_wcal::purgeDedicate();
	wcal::updatePaymentStatus();

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Filter
	$session->init('validated', 1);
	!$submit or $session->set('validated', $filter->requestValue('validated')->get());

	// Query addon
	$session->get('validated') ? $validated = ', where: payment_status=1' : $validated = '';

	$dedicate_number = $db->selectCount('wcal_dedicate'.$validated);

	// Multipage
	$multipage = new simpleMultiPage($dedicate_number);
	$multipage->setFormID('start_');
	$multipage->updateSession($session->returnVar('multipage'));
	$html .=
		admin_floatingContent(
			array(
				$form->checkbox('validated', $session->get('validated'), LANG_ADMIN_COM_WCAL_DEDICATE_ONLY_VALIDATED),
				$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),
				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT)
			)
		);

	$type = wcal::dedicateTypeOptions();

	// Database
	$list = $db->select(
					"wcal_dedicate, id, recording_date(desc), event_date, type_id, donate_id, payment_status$validated, join: donate_id>; ".
					'donate, payment_id, join: <id; limit:'.$multipage->dbLimit());

	for ($i=0; $i<count($list); $i++)
	{
		$list[$i]['payment_status'	] = admin_replaceTrueByChecked($list[$i]['payment_status'], false);

		$list[$i]['recording_date'	] = getTime($list[$i]['recording_date']);
		$list[$i]['event_date'		] = ucfirst(getTime($list[$i]['event_date'], 'format=long;time=no'));

		$list[$i]['type_id'			] = $type[$list[$i]['type_id']];

		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');

		$list[$i]['id'				] = '<span class="grey">'.$list[$i]['id'].'</span>'; # 'id' no more available
	}

	// Table
	$table = new tableManager($list);
	$table->header(
		array(
			'ID',
			LANG_COM_WCAL_DEDICATE_RECORDING_DATE,
			LANG_COM_WCAL_DEDICATE_EVENT_DATE,
			LANG_COM_WCAL_DEDICATE_TYPE,
			LANG_COM_WCAL_DEDICATE_DONATE_ID,
			LANG_COM_WCAL_DEDICATE_PAYMENT_STATUS,
			LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID,
		)
	);
	$table->delCol(6); # Delete the 'payment_id' column
	if (count($list)) {
		$table->addCol($update, 999);
	}
	$html .= $table->html();

	$html .= $form->end();
	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>