<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'newsletter_send');

/*
 * Reset each time the main script is loaded
 * So, only the script "_batchsend.php" (loaded into an iframe) can use the session
 */
$session->reset();


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
	$new = $filter->requestValue('new'	)->get();
	$send = $filter->requestName('send_')->getInteger();
} else {
	$del = false;
	$new = false;
	$send = false;
}

$new_submit = formManager::isSubmitedForm('new_', 'post');



// Case 'send'
if ($send)
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE.'</h2>';

	// Put the send request into the session. So, the script '_batchsend.php' can access this info.
	$session->set('send', $send);
	echo
		admin_fieldset(
			'<iframe src="'.WEBSITE_PATH.'/admin/components/com_newsletter/_batchsend.php" class="admin_comNewsletter_send"><p>Your browser does not support iframes.</p></iframe>',
			"Fenêtre d'envoi"
		);
}



// Case 'del'
if ($del)
{
	admin_informResult( $db->delete("newsletter_send; where: id=$del") );
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$newsletter_id = $filter->requestValue('newsletter_id')->getInteger();

	if ($new_submit_validation = $filter->validated())
	{
		$db->insert("newsletter_send; col: newsletter_id; $newsletter_id");
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_SEND_NEW_TITLE.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	// Newsletters options
	$opt = array();
	$nl = $db->select("newsletter, id(desc), subject, date_creation");
	for ($i=0; $i<count($nl); $i++) {
		$opt[ $nl[$i]['id'] ] = $nl[$i]['subject'].' ('.getTime($nl[$i]['date_creation'], 'time=no').')';
	}
	$html .= $form->select('newsletter_id', $opt, LANG_ADMIN_COM_NEWSLETTER_SEND_NEW_OPTIONS.'<br />');

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_SEND_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Database
	$send = $db->select('newsletter_send, id(desc),date_begin,date_end,sent_count,hits, join: newsletter_id>; newsletter, id AS newsletter_id, subject,date_creation, join: <id');

	$list = array();
	for ($i=0; $i<count($send); $i++)
	{
		$send[$i]['date_begin'	] ? $date_begin	= getTime($send[$i]['date_begin']) : $date_begin	= '';
		$send[$i]['date_end'	] ? $date_end	= getTime($send[$i]['date_end'	]) : $date_end		= '';

		if ($send[$i]['date_begin']) {
			if ($send[$i]['date_end']) {
				$task_length = getLength($send[$i]['date_end'] - $send[$i]['date_begin']);
			} else {
				$task_length = '<i class="red">'.LANG_ADMIN_COM_NEWSLETTER_SEND_NOT_FINISHED.'</i>';
			}
		} else {
			$task_length = '';
		}

		($date_begin) ? $hits = $send[$i]['hits'] : $hits = '';

		$href = siteUrl().'/components/com_newsletter/online.php?id='.$send[$i]['newsletter_id'];

		$list[$i]['subject'			] = "<a href=\"$href\" title=\"$href\" class=\"external\">{$send[$i]['subject']}</a>";
		$list[$i]['date_creation'	] = getTime($send[$i]['date_creation'], 'time=no');
		$list[$i]['task_start'		] = $date_begin;
		$list[$i]['task_length'		] = $task_length;
		$list[$i]['sent_count'		] = $send[$i]['sent_count'] ? $send[$i]['sent_count'] : '';
		$list[$i]['hits'			] = $hits;

		if (!$date_begin || !$date_end) {
			$list[$i]['send'		] = $form->submit('send_'.$send[$i]['id'], LANG_ADMIN_COM_NEWSLETTER_BUTTON_SEND);
		} else {
			$list[$i]['send'		] = '';
		}
	}

	for ($i=0; $i<count($send); $i++) {
		$delete[$i] = $form->submit('del_'.$send[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');
	}

	// Table
	$table = new tableManager($list);
	if (count($list)) {
		$table->addCol($delete, 0);
	}
	$table->header(
		array(
			'',
			LANG_ADMIN_COM_NEWSLETTER_SUBJECT,
			LANG_ADMIN_COM_NEWSLETTER_DATE_CREATION,
			LANG_ADMIN_COM_NEWSLETTER_SEND_TASK_START,
			LANG_ADMIN_COM_NEWSLETTER_SEND_TASK_LENGTH,
			LANG_ADMIN_COM_NEWSLETTER_SEND_SENT_COUNT,
			LANG_ADMIN_COM_NEWSLETTER_SEND_HITS,
			''
		)
	);
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_COM_NEWSLETTER_BUTTON_PREPARE_SEND);
	$html .= $form->end();

	if ($db->selectCount("newsletter"))
	{
		echo $html;
	} else {
		admin_message(LANG_ADMIN_COM_NEWSLETTER_SEND_NO_NEWSLETTER_AVAILABLE, 'warning');
	}
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>