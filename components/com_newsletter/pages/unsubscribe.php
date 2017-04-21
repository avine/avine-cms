<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Site infos
comConfig_getInfos($site_name, $system_email); # passed by reference


// User infos
global $g_user_login;
if ($user_id = $g_user_login->userID()) {
	$user_details = new comUser_details($user_id);
}


// Newsletter
$newsletter = new comNewsletter();


// Database
global $db;


// Page title
echo '<h1>'.LANG_COM_NEWSLETTER_UNSUBSCRIBE_TITLE.'</h1>';


/*
 * Verify request_code
 */
if (isset($_GET['request_code']) && formManager_filter::isMD5($_GET['request_code']))
{
	$request_code = $_GET['request_code'];

	if ($subscriber = $db->selectOne('newsletter_subscriber, *, where: request_code='.$db->str_encode($request_code)))
	{
		$email = $newsletter->subscriberEmail($subscriber['id']);

		if ($subscriber['activated'])
		{
			$db->delete('newsletter_subscriber; where: id='.$subscriber['id']);

			echo '<p>'.str_replace('{email}', $email, LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_SUCCESS).'</p>';
		} else {
			echo '<p>'.str_replace('{email}', $email, LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_ALREADY).'</p>';
		}
	}
	else {
		echo '<p>'.LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_INVALID_REQUEST_CODE.'</p>';
	}

	$start_view = false;
}

/*
 * Logged user has not subscribed !
 */
elseif ( $user_id && !$newsletter->isSentToUser($user_id, $exact_subscriber) )
{
	echo '<p>'.str_replace('{email}', $user_details->get('email'), LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_ALREADY).'</p>';
	echo '<p><a href="'.comMenu_rewrite('com=newsletter&amp;page=subscribe').'">'.LANG_COM_NEWSLETTER_SUBSCRIBE_SUBMIT.'</a></p>';
}

/*
 * Anonymous user or logged user who has subscribed !
 */
else
{
	$start_view = true;

	///////////
	// Process

	$filter = new formManager_filter();
	$filter->requestVariable('post');

	if (formManager::isSubmitedForm(comNewsletter::FORM_ID_, 'post'))
	{
		$filter->reset();
		$email = $filter->requestValue(comNewsletter::INPUT_NAME)->getEmail();

		if ($filter->validated())
		{
			// Check the requested email
			$subscribed = $newsletter->isSentToEmail($email, $status); # $status passed by reference

			// No subscription !
			if (!$subscribed)
			{
				echo '<p>'.str_replace('{email}', $email, LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_ALREADY).'</p>';
			}

			// Delete the subscription of the logged user without asking an email confirmation
			elseif ($user_id)
			{
				if ($status['subscription'] == 'registered')
				{
					if ($db->delete("newsletter_subscriber; where: user_id=".$status['user_id']))
					{
						echo '<p>'.str_replace('{email}', $email, LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_SUCCESS).'</p>';
					} else {
						trigger_error('An error occurred while we were trying to delete the subscription !');
					}
				}
				else # ($status['subscription'] == 'anonymous')
				{
					trigger_error('An unsolved conflict has been detected !'); # This case should be already solved by the method comNewsletter::subscriberToUserConflict()
				}
			}

			// Anonymous user online. Ask for a confirmation by email...
			else
			{
				// The activation code
				$request_code = md5(rand());

				$request_link = comMenu_rewrite("com=newsletter&amp;page=unsubscribe&amp;request_code=$request_code");
				$request_link = "<a href=\"$request_link\">$request_link</a>";

				// The message
				$message =
					searchAndReplace( LANG_COM_NEWSLETTER_UNSUBSCRIBE_SEND_EMAIL_REQUEST,
							array(
							'{site_name}'		=> $site_name,
							'{request_link}'	=> $request_link
						)
					);

				$mail = new emailManager();
				$mail	->useDefaultTemplate()
						->addMessageHTML($message)
						->addTo($email)
						->setSubject(LANG_COM_NEWSLETTER_UNSUBSCRIBE_SEND_EMAIL_SUBJECT)
						->setFrom($system_email/*, $site_name*/); # Notice : Don't use $site_name as a recipient name, because it can contain some characters like `:` that can make the email invalid !

				$result = $mail->send();

				// Record the request
				if ($result)
				{
					switch($status['subscription'])
					{
						case 'anonymous':
							$db->update('newsletter_subscriber; request_code='.$db->str_encode($request_code).'; where: email='.$db->str_encode($email));
							break;

						case 'registered':
							$db->update('newsletter_subscriber; request_code='.$db->str_encode($request_code).'; where: user_id='.$status['user_id']);
							break;
					}

					echo '<p>'.str_replace('{email}', $email, LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_WAITING_FOR).'</p>';
				}
			}

			$start_view = false;
		}
		else {
			echo $filter->errorMessage();
		}
	}



	//////////////
	// Start view

	if ($start_view)
	{
		if ($user_id && ($email = $user_details->get('email'))) # The logged user have an email !
		{
			$param	= 'readonly';
		} else {
			$email	= '';
			$param	= '';
		}

		echo	"\n<!-- comNewsletter:unsubscribe -->\n".
				'<fieldset id="newsletter-fieldset"><legend>'.LANG_COM_NEWSLETTER_UNSUBSCRIBE_FIELDSET."</legend>\n";

		$html = '';
		$form = new formManager();
		$html .= $form->form('post', $form->reloadPage(), comNewsletter::FORM_ID_);

		$html .= '<p><br />'.str_replace('{site_name}', $site_name, LANG_COM_NEWSLETTER_UNSUBSCRIBE_TIPS)."</p>\n";

		$html .= '<p>'.$form->text(comNewsletter::INPUT_NAME, $email, LANG_COM_NEWSLETTER_UNSUBSCRIBE_EMAIL, '', $param);
		$html .= $form->submit('submit', LANG_COM_NEWSLETTER_UNSUBSCRIBE_SUBMIT)."</p>\n";

		$html .= $form->end();
		echo $html;

		echo "</fieldset>\n";

		echo "\n<!-- End of : comNewsletter:unsubscribe -->\n\n";
	}

}


?>