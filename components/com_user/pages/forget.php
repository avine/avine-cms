<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$start_view = true;


global $db;


////////////////////
// Process (Special)

$filter = new formManager_filter();
$filter->requestVariable('get');

// Posted possibilities : send new_password after validation of request_code sent by email
$get_request_code = $filter->requestValue('request_code')->get();
$get_id = $filter->requestValue('id')->get();


if (($get_request_code) && ($get_id))
{
	$start_view = false;

	if ( (!formManager_filter::isMD5($get_request_code)) || (!formManager_filter::isInteger($get_id)) )
	{
		echo '<p>'.LANG_COM_USER_FORGET_REQUEST_ERROR.'</p>';
	}
	else
	{
		$user_forget = $db->select("user_forget, request_date, where: user_id=$get_id AND, where: request_code=".$db->str_encode($get_request_code));

		if (count($user_forget))
		{
			$db->delete("user_forget; where: user_id=$get_id"); # We don't need this request_code anymore

			// User info
			$user = $db->select("user, username, email, where: id=".$get_id);
			$user = $user[0];
			$username = $user['username'];
			$email    = $user['email'];

			// New password
			$new_password = comUser_form::randomString();

			// Site info
			comConfig_getInfos($site_name, $system_email); # passed by reference

			// User Message
			$user_message =
				searchAndReplace( LANG_COM_USER_FORGET_SEND_MAIL_NEW_PASSWORD,
					array(
						'{site_name}'		=> $site_name,
						'{username}'		=> $username,
						'{new_password}'	=> $new_password,
						'{new_date}'		=> getTime('', 'format=long')
					)
				);

			$mail = new emailManager();
			$mail	->useDefaultTemplate()
					->addMessageHTML($user_message)
					->addTo($email)
					->setSubject(LANG_COM_USER_FORGET_SEND_MAIL_NEW_PASSWORD_SUBJECT)
					->setFrom($system_email/*, $site_name*/); # Notice : Don't use $site_name as a recipient name, because it can contain some characters like `:` that can make the email invalid !

			$result = $mail->send();

			// Update database (if mail sent successfully)
			if ($result)
			{
				$result = $db->update("user; password=".$db->str_encode(sha1($new_password))."; where: id=$get_id");

				if ($result)
				{
					// Title
					echo '<h1>'.LANG_COM_USER_FORGET_CODE_REQUEST_SUCCESS_TITLE.'</h1>';

					$html = LANG_COM_USER_FORGET_CODE_REQUEST_SUCCESS_TIPS;
					$html = str_replace('{username}'	, $username	, $html);
					$html = str_replace('{email}'		, $email	, $html);
					echo $html.'<br />';
				}
				else {
					echo '<p style="color:red;">'.LANG_COM_USER_FORGET_ERROR_OCCRURED.'</p>';
				}
			}
			else {
				echo '<p style="color:red;">'.LANG_COM_USER_FORGET_ERROR_OCCRURED.'</p>';
			}
		}
		else {
			echo '<p>'.LANG_COM_USER_FORGET_REQUEST_ERROR.'</p>';
		}
	}
}



// Fill the default 'username_or_email' input of the start_view
$get_default_username_or_email = $filter->requestValue('remember')->get();
if (!formManager_filter::isUserPass	($get_default_username_or_email) &&
	!formManager_filter::isEmail	($get_default_username_or_email)) {
	$get_default_username_or_email = false;
}



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
if ($forget = formManager::isSubmitedForm('forget_', 'post'))
{
	$username_or_email = $filter->requestValue('username_or_email')->getNotEmpty();
} else {
	$username_or_email = false;
}

// Case 'forget'
if ($forget && $username_or_email)
{
	$start_view = false;

	// Fields validation
	$email    = false;
	$username = false;
	if (mb_strstr($username_or_email, '@'))	# Email was entered
	{
		if (formManager_filter::isEmail($username_or_email)) {
			$email = $username_or_email;
		}
	}
	else									# Username was entered
	{
		if (formManager_filter::isUserPass($username_or_email))	{
			$username = $username_or_email;
		}
	}

	if ($email)
	{
		$user = $db->select('user, id, username, where: email='.$db->str_encode($email));
		if (count($user))
		{
			/*
			 * Notice : if (comUser_form::isAllowDuplicateEmail()==true) then it's possible we found more than 1 user wich have this $email !
			 * We decide to send the Email only to the last registered user wich have it !
			 */
			$user = $user[count($user)-1];

			$username = $user['username'];
		}
		else {
			echo userMessage(LANG_COM_USER_FORGET_UNKNOWN_EMAIL, 'error');
			$start_view = true;
		}
	}
	elseif ($username)
	{
		$user = $db->select('user, id, email, where: username='.$db->str_encode($username));
		if (count($user))
		{
			$user = $user[0];
			isset($user['email']) ? $email = $user['email'] : $email = 'undefined';
		}
		else {
			echo userMessage(LANG_COM_USER_FORGET_UNKNOWN_USERNAME, 'error');
			$start_view = true;
		}
	}
	else {
		echo userMessage(LANG_COM_USER_FORGET_UNKNOWN, 'error');
		$start_view = true;
	}

	if ( (($email) && ($email != 'undefined')) && ($username) )
	{
		$user_id = $user['id'];
		$request_code = md5(rand());
		$request_date = time();

		$request_link = comMenu_rewrite('com=user&amp;page=forget&amp;id='.$user_id.'&amp;request_code='.$request_code);
		$request_link = "<a href=\"$request_link\">$request_link</a>";

		// User Message
		comConfig_getInfos($site_name, $system_email); # passed by reference

		// User Message
		$user_message =
			searchAndReplace( LANG_COM_USER_FORGET_SEND_MAIL_REQUEST,
					array(
					'{site_name}'		=> $site_name,
					'{username}'		=> $username,
					'{request_date}'	=> getTime($request_date, 'format=long'),
					'{request_link}'	=> $request_link
				)
			);

		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageHTML($user_message)
				->addTo($email)
				->setSubject(LANG_COM_USER_FORGET_SEND_MAIL_REQUEST_SUBJECT)
				->setFrom($system_email/*, $site_name*/); # Notice : Don't use $site_name as a recipient name, because it can contain some characters like `:` that can make the email invalid !

		$result = $mail->send();

		// request_code recording
		if ($result)
		{
			$db->delete("user_forget; where: user_id=$user_id");
			$result = $db->insert("user_forget; col: user_id, request_code, request_date; $user_id, ".$db->str_encode($request_code).", $request_date");

			// Final message
			if ($result)
			{
				// Title
				echo '<h1>'.LANG_COM_USER_FORGET_CODE_REQUEST_TITLE.'</h1>';

				$html = LANG_COM_USER_FORGET_CODE_REQUEST_TIPS;
				$html = str_replace('{username}'	, $username	, $html);
				$html = str_replace('{email}'		, $email	, $html);
				echo $html.'<br />';
			}
			else {
				echo userMessage(LANG_COM_USER_FORGET_ERROR_OCCRURED, 'error');
				$start_view = true;
			}
		}
		else {
			echo userMessage(LANG_COM_USER_FORGET_ERROR_OCCRURED, 'error');
			$start_view = true;
		}

		echo '<br />'.LANG_GO_TO_HOME_PAGE;
	}

	if ($email == 'undefined') {
		echo userMessage(LANG_COM_USER_FORGET_EMAIL_UNDEFINED, 'error');
		$start_view = true;
	}
}




//////////////
// Start view

if ($start_view)
{
	echo "\n<!-- comUser:forget -->\n".'<fieldset class="comUser_fieldset"><legend>'.LANG_COM_USER_FORGET_ACCOUNT_TITLE.'</legend>'."\n";

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $form->reloadPage(), 'forget_');

	$html .= '<p class="comUser_forget-tips">'.LANG_COM_USER_FORGET_TIPS.'</p>';
	$html .= $form->text('username_or_email', $get_default_username_or_email, LANG_COM_USER_FORGET_ENTRY);

	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;

	echo "</fieldset>\n<!-- End of : comUser:forget -->\n\n";
}


?>