<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_SEND_MESSAGE_PAGE_TITLE'		, "Nous contacter" );

define( 'LANG_SEND_MESSAGE_FIELDSET_LEGEND'	, "Formulaire transmis à : " );

define( 'LANG_SEND_MESSAGE_TO'				, "Desinataire" );
define( 'LANG_SEND_MESSAGE_FROM'			, "Expéditeur" );
define( 'LANG_SEND_MESSAGE_SUBJECT'			, "Objet" );
define( 'LANG_SEND_MESSAGE_MESSAGE'			, "Message" );
define( 'LANG_SEND_MESSAGE_SUBMIT'			, "Envoyer" );

define( 'LANG_SEND_MESSAGE_DEFAULT_FROM'	, "" );
define( 'LANG_SEND_MESSAGE_DEFAULT_SUBJECT'	, "Demande de renseignements" );
define( 'LANG_SEND_MESSAGE_DEFAULT_MESSAGE'	, "" );

define( 'LANG_SEND_MESSAGE_ERROR_FROM'		, "adresse email invalide" );
define( 'LANG_SEND_MESSAGE_ERROR_SUBJECT'	, "champ non renseigné" );
define( 'LANG_SEND_MESSAGE_ERROR_MESSAGE'	, "champ non renseigné" );

define( 'LANG_SEND_MESSAGE_RESULT_SUCCESS'	, "Votre message à bien été transmis." );
define( 'LANG_SEND_MESSAGE_RESULT_FAILURE'	, "Une erreur est survenue, votre message n'a pu être transmis !" );



////////////////////////
// Script configuration

comConfig_getInfos($site_name, $system_email);
if (!$system_email) {
	trigger_error('$system_email is not defined !');
	exit;
}

$html = '';

$start_view = true;



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

if ($submit = formManager::isSubmitedForm('send_message_', 'post'))
{
	$from		= $filter->requestValue('from')->getEmail(1, LANG_SEND_MESSAGE_ERROR_FROM	, LANG_SEND_MESSAGE_FROM);

	$subject	= strip_tags( $filter->requestValue('subject')->getNotEmpty(1, LANG_SEND_MESSAGE_ERROR_SUBJECT, LANG_SEND_MESSAGE_SUBJECT) ); 
	$message	= strip_tags( $filter->requestValue('message')->getNotEmpty(1, LANG_SEND_MESSAGE_ERROR_MESSAGE, LANG_SEND_MESSAGE_MESSAGE) ); 

	if ($filter->validated())
	{
		// Send mail
		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageTXT($message)
				->addTo($system_email)
				->setSubject($subject)
				->setFrom($from);

		if ($mail->send())
		{
			// Summary
			$html .= '<p><span class="h5">'.LANG_SEND_MESSAGE_FROM." :</span> $from".' <br /> ';
			$html .= '<span class="h5">'.LANG_SEND_MESSAGE_SUBJECT." :</span> $subject" ."</p>\n";

			$html .= '<h5>'.LANG_SEND_MESSAGE_MESSAGE." :</h5>\n".$mail->getMessageHTML()."\n";
			$html .= '<hr /><p style="color:green; margin:15px 0; font-weight:bold;">'.LANG_SEND_MESSAGE_RESULT_SUCCESS."</p>\n";
		}
		else {
			$html .= '<p style="color:red; margin:15px 0; font-weight:bold;">'.LANG_SEND_MESSAGE_RESULT_FAILURE."</p>\n";
		}

		$start_view = false;
	}
	else {
		// Error message
		$html .= $filter->errorMessage();
	}
}



//////////////
// Start view

if ($start_view)
{
	#$html .= '<p>(Your contact message here)</p><hr />'; # Special !

	$form = new formManager();
	$html .= $form->form('post', formManager::reloadPage(), 'send_message_');

	$html .= $form->text('from'			, LANG_SEND_MESSAGE_DEFAULT_FROM	, LANG_SEND_MESSAGE_FROM	.'<br />', '', 'size=36').'<br />';
	$html .= $form->text('subject'		, LANG_SEND_MESSAGE_DEFAULT_SUBJECT	, LANG_SEND_MESSAGE_SUBJECT	.'<br />', '', 'size=36').'<br />';
	$html .= $form->textarea('message'	, LANG_SEND_MESSAGE_DEFAULT_MESSAGE	, LANG_SEND_MESSAGE_MESSAGE	.'<br />', '', 'cols=48;rows=6').'<br /><br />';

	$html .= $form->submit('submit', LANG_SEND_MESSAGE_SUBMIT).'<br /><br />';
	$html .= $form->end();
}



///////////////
// HTML output

echo '<h1>'.LANG_SEND_MESSAGE_PAGE_TITLE.'</h1>';

echo "\n\n<fieldset style=\"width:500px;\"><legend>".LANG_SEND_MESSAGE_FIELDSET_LEGEND."<b>$system_email</b></legend>\n\n";

echo $html;

echo "\n\n</fieldset>\n\n";



?>