<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */

# TODO - rajouter une protection : on n'envoie pas plus d'un email toutes les minutes... (session + ip server de l'internaute)

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Config
$html = '';
$start_view = true;



// Security
define('COM_CONTACT_LATENCY_BETWEEN_2_EMAILS', 60); // in secondes
$session = new sessionManager(sessionManager::FRONTEND, 'contact');



// Instanciate the contact class
$contact = new comContact_();

// Anti spam protection : create or update the images of the emails
$contact->emailImageTask();

// Get contacts options
$options = $contact->options();

if (!count($options)) {
	$html .= LANG_CONTACT_NO_CONTACT_AVAILABLE;
	$start_view = false;
}



if (count($options) >= 2) { ?>

<!-- Display only the preview of the selected contact -->
<script type="text/javascript">
$(document).ready(function(){
	// Init
	$(".contact_preview").hide();
	$("select#contact_to_id option:selected").each(function(){
		$("#contact_preview_" + $(this).val()).show();
	});

	// On change
	$("select#contact_to_id").change(function() {
		$(".contact_preview").hide();
		$("select#contact_to_id option:selected").each(function(){
			$("#contact_preview_" + $(this).val()).fadeIn('slow');
		});
	});
});
</script>

<?php }


// Filter
$filter = new formManager_filter();
$filter->requestVariable('post');


// Specified contact requested ?
$selected = $filter->requestValue('id', 'get')->getInteger(0);



///////////
// Process

if ($submit = formManager::isSubmitedForm('contact_', 'post'))
{
	$to_id		= $filter->requestValue('to_id')->getInteger(1, LANG_CONTACT_ERROR_TO, LANG_CONTACT_TO);
	if ($to_details = $contact->userDetails($to_id))
	{
		$to = $to_details->get('email');
	}
	(isset($to) && $to) or $filter->set(false, 'to_id')->getError(LANG_CONTACT_ERROR_TO, LANG_CONTACT_TO);

	$from		= $filter->requestValue('from')->getEmail(1, LANG_CONTACT_ERROR_FROM, LANG_CONTACT_FROM);
	$subject	= strip_tags( $filter->requestValue('subject')->getNotEmpty(1, LANG_CONTACT_ERROR_SUBJECT, LANG_CONTACT_SUBJECT) ); 
	$message	= strip_tags( $filter->requestValue('message')->getNotEmpty(1, LANG_CONTACT_ERROR_MESSAGE, LANG_CONTACT_MESSAGE) ); 

	if ($filter->validated())
	{
		$box = new boxManager();

		// Send mail
		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageTXT($message)
				->addTo($to)
				->setSubject($subject)
				->setFrom($from);

		if ($mail->send())
		{
			$summary_to = $options[$to_id];
			if ($to_image = $contact->emailImage($to_id)) {
				$summary_to .= "<br />$to_image";
			}

			// Summary
			$html .= '<h3>'.LANG_CONTACT_TO		." :</h3>\n<p>$summary_to</p>\n";
			$html .= '<h3>'.LANG_CONTACT_FROM	." :</h3>\n<p>$from</p>\n";
			$html .= '<h3>'.LANG_CONTACT_SUBJECT." :</h3>\n<p>$subject</p>\n";
			$html .= '<h3>'.LANG_CONTACT_MESSAGE." :</h3>\n<div id=\"message-preview\">".$mail->getMessageHTML()."</div>\n";

			$session->set($_SERVER['REMOTE_ADDR'], time());
			$message_result = $box->message(LANG_CONTACT_RESULT_SUCCESS, 'ok');
		} else {
			$message_result = $box->message(LANG_CONTACT_RESULT_FAILURE, 'error');
		}

		$start_view = false;
	}
	else {
		// Error message
		$message_error = $filter->errorMessage();
	}
}



//////////////
// Start view

// Check the logged user email (from)
global $g_user_login;
if ($g_user_login->userID())
{
	global $db;
	$from = $db->selectOne('user, email, where: id='.$g_user_login->userID(), 'email');
} else {
	$from = '';
}



if ($start_view)
{
	if ($session->get($_SERVER['REMOTE_ADDR']) > time() - COM_CONTACT_LATENCY_BETWEEN_2_EMAILS)
	{
		$box = new boxManager();
		$message_result = $box->message( str_replace('{delay}', COM_CONTACT_LATENCY_BETWEEN_2_EMAILS, LANG_CONTACT_DELAY_BETWEEN_2_EMAILS.' <a href="'.$_SERVER['REQUEST_URI'].'">'.LANG_CONTACT_RELOAD.'</a>'), 'warning', true, '500');

		$disabled = 'disabled;';
	} else {
		$disabled = '';
	}

	$form = new formManager();
	$html .= $form->form('post', formManager::reloadPage(), 'contact_');

	if ($preview = $contact->previewAll()) {
		$html .= "$preview<hr />\n";
	}

	if (count($options) >= 2) {
		$html .= '<p>'.$form->select('to_id', formManager::selectOption($options, $selected), LANG_CONTACT_TO.'<br />')."</p>\n";
	}
	else {
		list($to_id, $info) = each($options);
		$html .= $form->hidden('to_id', $to_id);

		if (!$preview) {
			$html .= "<p>".LANG_CONTACT_TO." : <strong>$info</strong></p>\n"; # For a unique contact, the title's contact is used only if there's no preview !
		}
	}

	$html .= $form->text('from'			, $from		, LANG_CONTACT_FROM	.'<br />', '', $disabled.'size=36'.($from ? ';readonly' : ''))."<br /><br />\n";
	$html .= $form->text('subject'		, LANG_CONTACT_DEFAULT_SUBJECT	, LANG_CONTACT_SUBJECT	.'<br />', '', $disabled.'size=48')."<br /><br />\n";
	$html .= $form->textarea('message'	, LANG_CONTACT_DEFAULT_MESSAGE	, LANG_CONTACT_MESSAGE	.'<br />', '', $disabled.'cols=48;rows=6')."<br /><br />\n";

	$html .= $form->submit('submit', LANG_CONTACT_SUBMIT, '', $disabled)."<br /><br />\n";
	$html .= $form->end();
}



///////////////
// HTML output

echo '<h1>'.LANG_CONTACT_TITLE.'</h1>';

if (isset($message_error)) {
	echo $message_error;
}

if ($html) {
	echo "\n\n<fieldset id=\"contact-fieldset\"><legend>".LANG_CONTACT_FIELDSET_LEGEND."</legend>\n\n";
	echo $html;
	echo "\n\n</fieldset>\n\n";
}

if (isset($message_result)) {
	echo $message_result;
}

?>