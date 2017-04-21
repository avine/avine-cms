<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/**
 * Optionnal config : Use template
 */
global $tmpl_name_modify; # You can handle this variable from the outside
if (!isset($tmpl_name_modify))
{
	$tmpl_name_modify = 'default/tmpl_1_modify.html'; # false to disable
}

global $tmpl_name_modify_view; # You can handle this variable from the outside
if (!isset($tmpl_name_modify_view))
{
	$tmpl_name_modify_view = 'default/tmpl_1_view.html'; # false to disable
}


// Configuration
global $g_user_login;
if ($g_user_login->userID())
{
	$start_view = true;
} else {
	$start_view = false;

	echo LANG_COM_USER_MODIFY_ACCOUNT_NOT_CONNECTED.' '.LANG_GO_TO_HOME_PAGE;
}


// Instanciate user form class
$user_form = new comUser_form();
$user_form->enableCaptcha(false); # Captcha feature is not implemented in this page at all


///////////
// Process

$submit = formManager::isSubmitedForm('user_', 'post'); // (0)

$upd_submit = formManager::isSubmitedForm(COM_USER_FORM_PREFIX_MODIFY_, 'post'); // (1)



// (1) Update submit
if ($upd_submit)
{
	$user_form->processForm();
	$upd_submit_validation = $user_form->processData();

	if ($upd_submit_validation)
	{
		// user_id
		$user_id 		= $g_user_login->userID();
		# Notice that the 'user_id' is also available from here
		#$user_id 		= $user_form->getUserID();
		// End of: user_id

		$new_password 	= $user_form->getUserNewPassword();

		if ($new_password)
		{
			# TODO : Send Email to the user with his new password (just to remember...)
		}
	}
	else
	{
		echo '<div class="comUser_form-error">'.$user_form->getFormErrorMessage().'</div>';
	}
}
if (($submit) || (($upd_submit) && (!$upd_submit_validation)) )
{
	$start_view = false;

	$html  = '';
	$form = new formManager();
	$html .= $form->form('post', $form->reloadPage(), COM_USER_FORM_PREFIX_MODIFY_);

	// user_id
	$user_id = $g_user_login->userID();
	# Notice that when (($upd_submit) && (!$upd_submit_validation)) then the 'user_id' is also available from here:
	# $user_form->getUserID() ? $user_id = $user_form->getUserID() : $user_id = $g_user_login->userID();
	// End of: user_id

	$user_form->setUser($user_id);

	// Form (array)
	$data = $user_form->getUserForm(COM_USER_FORM_PREFIX_MODIFY_); # Notice: if you need, customize here the $data before calling the ->displayUserForm() method

	if (!$tmpl_name_modify)
	{
		$html .= "\n<!-- User:Modify -->\n".'<fieldset class="comUser_fieldset"><legend>'.LANG_COM_USER_MODIFY_ACCOUNT_FORM.'</legend>'."\n";

		$html .= $user_form->displayUserForm($data, false);
	
		$html .= '<p class="comUser_form-submit"><br />'.$form->submit('submit', LANG_BUTTON_SUBMIT).'</p>';
		$html .= '<div class="comUser_form-tips">'.comUser_form::requiredFieldsTips().'</div>';

		$html .= "</fieldset><!-- End of : User:Modify -->\n\n";
	}
	else
	{
		// Add the submit button and the required-tips into the $data (to have full control of the view)
		$data['submit'] = $form->submit('submit', LANG_BUTTON_SUBMIT);
		$data['tips'] = comUser_form::requiredFieldsTips();

		$html .= $user_form->displayUserForm($data, $tmpl_name_modify); # Notice: the template must include '{submit}' and optionnaly '{tips}'
	}

	$html .= '<p style="text-align:center;"><a href="'.formManager::reloadPage().'">'.LANG_BUTTON_RESET.'</a></p>';
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	echo "\n<div id=\"comUser_modify-view\">";

	// Title
	echo '<h1>'.LANG_COM_USER_MODIFY_ACCOUNT_TITLE.'</h1>';

	$html = '';
	$form = new formManager();

	$html .= $form->form('post', $form->reloadPage(), 'user_');


	// Set user_id as current logged user
	$user_form->setUser($g_user_login->userID());

	// Get the user datas
	$data = $user_form->getUserView();

	if (!$tmpl_name_modify_view) # Default view
	{
		// username,email, gender,age, first_name,last_name, ...
		$html .= $user_form->displayUserView($data, false);

		$html .= '<p>'.$form->submit('update', LANG_BUTTON_UPDATE).'</p>'; // (0)

		// access_level (if more than just registered), registration_date,last_visit
		$html .= $user_form->displayUserAccess($data);
	}
	else # Template view
	{
		// Add the submit button into the $data (to have full control of the view)
		$data['submit'] = $form->submit('update', LANG_BUTTON_UPDATE); // (0)

		$html .= $user_form->displayUserView($data, $tmpl_name_modify_view);
	}


	$html .= $form->end();
	echo $html;

	echo "</div>\n\n";
}

?>