<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Newsletter
$newsletter = new comNewsletter();



/*
 * Maintenance : search for duplicate email subscriptions
 * Yes, yes, this case can happen ! Think hard, and you will find how...
 */
$subscriber = $db->select('newsletter_subscriber, *, id(asc)');
$user_emails = $db->select('user, [id], email');

$list = array();
for ($i=0; $i<count($subscriber); $i++)
{
	if ($subscriber[$i]['email'])
	{
		$email = $subscriber[$i]['email'];
	} else {
		$email = $user_emails[ $subscriber[$i]['user_id'] ]['email'];
	}

	if (!in_array($email, $list))
	{
		$list[] = $email;
	} else {
		$db->delete('newsletter_subscriber; where: id='.$subscriber[$i]['id']);
	}
}
// end of : maintenance



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
} else {
	$del = false;
	$new = false;
}

$new_submit = formManager::isSubmitedForm('new_', 'post');



// Delete subscription
if ($del)
{
	$db->delete("newsletter_subscriber; where: id=$del");
}



// Add new subscription
if ($new_submit)
{
	$filter->reset();

	$email = $filter->requestValue('email')->getEmail();

	if ($new_submit_validation = $filter->validated())
	{
		$subscribed = $newsletter->isSentToEmail($email, $status);

		if ($subscribed)
		{
			admin_message(str_replace('{email}', $email, LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_NEW_SUBSCRIBED_ALREADY), 'warning');
		}
		elseif ($subscribed === NULL)
		{
			// Add a new "anonymous" subscription
			$db->insert('newsletter_subscriber; col: email, activated; '.$db->str_encode($email).', 1');

			// And in case, the email belongs to some "registered user", call the method comNewsletter::subscriberToUserConflict()
			$newsletter = new comNewsletter();
		}
		else
		{
			switch($status['subscription'])
			{
				case 'anonymous':
					$result = $db->update('newsletter_subscriber; activated=1; where: email='.$db->str_encode($email));
					break;

				case 'registered':
					$result = $db->update('newsletter_subscriber; activated=1; where: user_id='.$status['user_id']);
					break;
			}
			admin_informResult($result, str_replace('{email}', $email, LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_NEW_SUBSCRIBED_SUCCESS));
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= '<p>'.$form->text('email', '', LANG_COM_NEWSLETTER_SUBSCRIBE_EMAIL);
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT)."</p>\n";

	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Database
	$subscriber = $db->select('newsletter_subscriber, *, id(desc)');
	$user_emails = $db->select('user, [id], email');

	$list = array();
	$export = array();
	for ($i=0; $i<count($subscriber); $i++)
	{
		if ($subscriber[$i]['email'])
		{
			$email	= $subscriber[$i]['email'];
			$uid	= '<span class="grey">'.LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_UID_ANONYMOUS.'</span>';
		} else {
			$email	= $user_emails[ $subscriber[$i]['user_id'] ]['email'];
			#$email	= $newsletter->subscriberEmail($subscriber[$i]['id']); # You can also use this method to find the associated email (but it's compute a new query for each user)
			$uid	= $subscriber[$i]['user_id'];
		}

		$list[] = array(
			'email'		=>	$email,
			'uid'		=>	$uid,
			'activated'	=>	admin_replaceTrueByChecked($subscriber[$i]['activated'], false)
		);

		// Delete
		$delete[] = $form->submit('del_'.$subscriber[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

		if ($subscriber[$i]['activated'])
		{
			$export[]['email'] = $email;
		}
	}

	// Table
	$table = new tableManager($list);

	if ($list) {
		$table->addCol($delete, 0);
	}

	$table->header(
				array(
					'',
					LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_EMAIL,
					LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_UID,
					LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_ACTIVATED
				)
	);

	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();

	// CSV
	if ($export) {
		comConfig_getInfos($site_name, $system_email);
		$csv = new simpleCSV();
		$csv->addSummaryBefore("$site_name : ".LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_CSV_SUMMARY.' ('.getTime('', 'time=no').')');
		$csv->set($export, array('Email'));
		$html .= '<br /><br />'.$csv->textareaBox(LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_CSV).'<br />';
	}

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>