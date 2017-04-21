<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Filter
$filter = new formManager_filter();
$filter->requestVariable('post');

	
// User
global $g_user_login;
$user_id = $g_user_login->userID();


// Donate
$donate = new comDonate_();
$form_id = 'donate_form_';
$donate->setFormID($form_id);


if ( !$user_id && ($filter->requestValue('login', 'get')->get() == 'request' || comUser_login::isSubmited()) )
{
	// Login form
	echo '<div class="comDonate_login_request"><a href='.formManager::reloadPage(true, '', 'login').'>'.LANG_COM_DONATE_LOGIN_BACK_TO_DONATE_FORM.'</a></div>'."\n";

	echo 	'<h1>'.
			LANG_COM_DONATE_LOGIN_TITLE.
			#'<br class="comDonate_login_request" />'. # if necessary add this tag
			'</h1>'."\n";

	echo $g_user_login->displayForm( $g_user_login->getform(formManager::reloadPage(true, '', 'login')), 'default/tmpl_donate_login.html' ); # Carefull : the tmpl_name is relative to the base defined by the com_user component
}

else
{
	///////////
	// Process

	$donate->sessionCheck(); # Do this before any process (important task) !

	if (formManager::isSubmitedForm('donate_summary_', 'post') && $filter->requestValue('reload_form')->get())
	{
		$donate->updateFormPassed(0);
	}

	if (formManager::isSubmitedForm($form_id, 'post'))
	{
		$donate->allFormProcess();

		if (!$donate->formIsValidated())
		{
			$form_message = $donate->formErrors();
		}
	}

	/////////
	// Forms

	if (!$donate->isFormPassed())
	{
		// Login request
		if (!$user_id) {
			# TODO - get '/donate/index/login/' instead of '/donate/index/?login=request'
			echo '<div class="comDonate_login_request"><a href="'.formManager::reloadPage(true, 'login=request').'">'.LANG_COM_DONATE_LOGIN_REQUEST.'</a></div>'."\n";
		}

		// Page title
		echo 	'<h1>'.
				LANG_COM_DONATE_TITLE.
				#'<br class="comDonate_login_request" />'. # if necessary add this tag
				'</h1>'."\n";

		// Donate form
		$html = '';

		isset($form_message) ? $html .= $form_message : '';

		$form = new formManager(); # Form begin
		$html .= $form->form('post', $form->reloadPage(), $form_id);

		if ($donate->isDonateAvailable())
		{
			$html .= "<fieldset><legend>".LANG_COM_DONATE_FIELDSET_DONATE."</legend>\n";
			$html .= $donate->donateForm();
			$html .= "</fieldset>\n\n";

			$html .= "<fieldset><legend>".LANG_COM_DONATE_FIELDSET_DONOR."</legend>\n";
			$html .= $donate->donorForm();
			$html .= "</fieldset>\n\n";

			$html .= $form->submit('submit', LANG_COM_DONATE_FORM_SUBMIT);
		}
		else
		{
			$html .= '<p>'.LANG_COM_DONATE_NO_DESIGNATION_AVAILABLE.'</p>';
		}

		$html .= $form->end(); # Form end

		echo $html;
	}
	else
	{
		echo '<h1>'.LANG_COM_DONATE_SUMMARY_TITLE.'</h1>';

		$html = '';

		$html .= $donate->donateSummary('tmpl_donor_html.html');

		$form = new formManager(); # Form begin
		$html .= $form->form('post', $form->reloadPage(), 'donate_summary_');
		$html .= $form->submit('reload_form', LANG_COM_DONATE_SUMMARY_RELOAD_FORM);
		$html .= $form->end(); # Form end

		$html .= '<p id="checkout-button"><a href="'.comMenu_rewrite('com=donate&amp;page=checkout').'">'.LANG_COM_DONATE_SUMMARY_CHECKOUT.'</a></p>';

		echo "\n<div id=\"comDonate_summary\">\n$html\n</div>\n";
	}

}


?>