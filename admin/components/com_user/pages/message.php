<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


#
# TODO - Possible upgrade : Moteur de recherche des users
#

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'user_message');


// Configuration
$start_view = true;


// Current logged admin
global $g_user_login;
$logged_admin_id = $g_user_login->userID();
$logged_admin_email = $db->selectOne("user, email, where: id=$logged_admin_id", 'email');



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

$submit = formManager::isSubmitedForm('message_', 'post'); // (0)



if ($submit)
{
	// from, subject, message
	$from		= $filter->requestValue('from'		)->getEmail		(1, '', LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_FROM);
	$subject	= $filter->requestValue('subject'	)->getNotEmpty	(1, '', LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_SUBJECT);
	$message	= $filter->requestValue('message'	)->getNotEmpty	(1, '', LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_MESSAGE);

	// emails list
	$access_level = array();
	foreach(comUser_getStatusOptions() as $status_id => $status_comment) {
		$filter->requestValue("to_$status_id")->get() ? $access_level[] = $status_id : '';
	}
	if (count($access_level)) {
		$access_level = ', where: activated=1 AND, where: access_level='.implode(' OR, where: activated=1 AND, where: access_level=', $access_level);

		$email_list = $db->select("user, [email]$access_level");
		$email_list = array_keys($email_list);
	}
	else {
		$email_list = array();
		$filter->set(false)->getError(LANG_ADMIN_COM_USER_MESSAGE_ERROR_NO_ACCESS_LEVEL_SELECTED, LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_TO);
	}

	if ($filter->validated() && !count($email_list))
	{
		admin_message(LANG_ADMIN_COM_USER_MESSAGE_ERROR_NO_USER_SELECTED, 'warning');
	}
	elseif ($filter->validated())
	{
		// Send mail
		$mail = new emailManager();
		$mail	->setTemplateHTML('newsletter/tmpl.html') # Special newsletter template !
				->addMessageHTML($message)
				->setSubject($subject)
				->setFrom($from);

		for ($i=0; $i<count($email_list); $i++) {
			$mail->addTo($email_list[$i]);
		}

		// Send the sender a copy !
		if ($logged_admin_email && !in_array($logged_admin_email, $email_list)) {
			$mail->addTo($logged_admin_email);
		}

		$result = $mail->send();

		admin_informResult($result);

		if ($result)
		{
			if ($logged_admin_email) {
				admin_message(LANG_ADMIN_COM_USER_MESSAGE_SUCCESS_SEND_AUTHOR."<b>$logged_admin_email</b>", 'info');
			}

			$form = new formManager(0,0);
			echo $form->textarea('', implode("\n", $email_list), LANG_ADMIN_COM_USER_MESSAGE_SUCCESS_SEND_LIST.'<br />', '', 'rows=10;readonly');

			echo '<p><br /><b><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_CONTINUE.' &gt;</a></b></p>'; # Alternative reset button...
			$start_view = false;
		}
	}
	else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_USER_MESSAGE_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'message_');


	$fieldset = '';


	/* Who is the sender ?
	 * You can choose the current logged admin. But in that case you are not sure about the reverse DNS !
	 * So, it's better to choose the system email !
	 */
	comConfig_getInfos($site_name, $system_email); # passed by reference

	// From
	$fieldset .= $form->text('from', $system_email, LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_FROM.LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_FROM_TIPS.'<br />', '', 'size=40;readonly').'<br />';

	// Subject
	$fieldset .= $form->text('subject', "$site_name : ".LANG_ADMIN_COM_USER_MESSAGE_FORM_SUBJECT_DEFAULT, LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_SUBJECT.'<br />', '', 'size=70').'<br /><br />';

	// Message
	$fieldset .= $form->textarea('message'	, '', LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_MESSAGE.'<br />', '', 'cols=70;rows=10').'<br />';
	$my_CKEditor = new loadMyCkeditor();
	$fieldset .= $my_CKEditor->addName("message");

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_MESSAGE);


	// To
	$fieldset = '';
	foreach(comUser_getStatusOptions(false, true) as $status_id => $status_comment) {
		$fieldset .= $form->checkbox("to_$status_id", 1, $status_comment.'s').'<br />';
	}
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_TO);


	$html .= $form->submit('submit', LANG_ADMIN_COM_USER_MESSAGE_FORM_SUBMIT); // (0)

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>