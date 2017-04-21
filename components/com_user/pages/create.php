<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


# TODO : Upgrades
# 	- Limiter le temps de validité du code d'activation
#	- Interdire la création successive de comptes avec un compteur de Xmn associé à l'IP de l'utilisateur...


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


global $db;


/**
 * Optionnal config : Use template
 */
global $tmpl_name_create; # You can handle this variable from the outside
if (!isset($tmpl_name_create))
{
	$tmpl_name_create = false; # false to disable (for example, you can use : 'default/tmpl_1_create.html')
}


/*
 * Manual setting : disable all Html outputs when processing registration
 * (usefull, if you need to integrate this script in another)
 */
global $com_user_create_disable_html_output; # You can handle this variable from the outside
if (!isset($com_user_create_disable_html_output))
{
	$com_user_create_disable_html_output = false;
}


// Configuration
global $g_user_login;
if (!$g_user_login->userID())
{
	$start_view = true;
} else {
	$start_view = false;
	echo '<h1>'.LANG_COM_USER_CREATE_ACCOUNT_ALREADY_CONNECTED.'</h1>';
	$go_to_home_page = true;
}



////////////////////
// Process (Special)

$filter = new formManager_filter();
$filter->requestVariable('get');

$get_activation_code = $filter->requestValue('activation_code')->get();
$get_id = $filter->requestValue('id')->get();

if ($get_activation_code && $get_id)
{
	$start_view = false;

	$activation_result = false;
	$activation_message = '<p style="color:red;">'.LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_ERROR.'</p>';

	if (!$g_user_login->userID() && formManager_filter::isMD5($get_activation_code) && formManager_filter::isInteger($get_id))
	{
		$user = $db->select("user, id, activated, activation_code, where: id=$get_id");
		if (count($user))
		{
			$user = $user[0];
			if (($user['activation_code'] == $get_activation_code) && ($user['activation_code'] != ""))
			{
				if ($user['activated'] == 0)
				{
					$activation_result = $db->update("user; activated=1; where: id=$get_id");
					if ($activation_result)
					{
						// Make the user automatically logged
						global $g_user_login;
						$g_user_login->autoLogin($get_id); # Notice : this will be fully effective only on the next page

						comConfig_getInfos($site_name, $system_email); # passed by reference

						$activation_message =
							'<h1>'.LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_SUCCESS."</h1>\n".
							'<p class="comUser_activate-success">'.searchAndReplace(LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_SUCCESS_TIPS,
								array('{login}'=>comMenu_rewrite('com=user&page=login'), '{site_name}'=>$site_name)).'</p>';
					} else {
						$activation_message = '<p style="color:red;">'.LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_FAILED.'</p>';
					}
				}
				else
				{
					$activation_message =
						'<h1>'.LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_NOT_REQUIRED."</h1>\n".
						'<p>'.str_replace('{login}', comMenu_rewrite('com=user&page=login'), LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_NOT_REQUIRED_TIPS).'</p>';
				}
			}
		}
	}
	echo $activation_message;
	$go_to_home_page = true;
}

if (isset($go_to_home_page)) {
	echo "\n<p>".LANG_GO_TO_HOME_PAGE."</p>\n";
}



// Instanciate user form class
$user_form = new comUser_form();
$user_form->enableCaptcha(true); # Comment this line to disable this feature # TODO - allow the configuration of this in the admin...

// If necessary overwrite default user_config
#$user_form->overwriteConfig('registration_silent'		, 1);
#$user_form->overwriteConfig('allow_duplicate_email'	, 1);
#$user_form->overwriteConfig('activation_method'		, 1);

# If you need to overwrite the user fields list (if you integrate this code in another component like com_donate wich also need a user registration)
#$user_form->overwriteUserFieldsList( array('username', 'password', 'email', 'last_name', 'first_name'), array('username', 'password', 'email'));



///////////
// Process

$new_submit = formManager::isSubmitedForm(COM_USER_FORM_PREFIX_CREATE_, 'post'); // (1)



// (1) Update submit
if ($new_submit)
{
	$user_form->processForm();

	if ($user_form->processData())
	{
		$start_view = false;

		// Need some infos about the new user ?
		$new_user_id 	= $user_form->getUserID();
		$new_password 	= $user_form->getUserNewPassword();

		$user_db = new comUser_db();
		$select_user = $user_db->selectUser($new_user_id); // end

		// Config
		comConfig_getInfos($site_name, $system_email); # passed by reference

		if ($select_user['activated'])
		{
			$message = '<p>'.LANG_COM_USER_CREATE_ACCOUNT_CREATE_AUTO.'</p>';
		}
		else
		{
			if ($select_user['activation_code'] != '')
			{
				$message = LANG_COM_USER_CREATE_ACCOUNT_CREATE_EMAIL;
			} else {
				$message = LANG_COM_USER_CREATE_ACCOUNT_CREATE_ADMIN;
			}
			$message = '<p class="comUser_activate-email">'.str_replace('{email}', $select_user['email'], $message).'</p>';
		}

		// Final message
		if (!$com_user_create_disable_html_output)
		{
			$html  = '<h1>'.LANG_COM_USER_CREATE_ACCOUNT_CREATE_SUCCESS.'</h1>';
			$html .= "\n$message\n";
			$html .= '<p class="comUser_create-law">'.str_replace('{site_name}', $site_name, LANG_COM_USER_CREATE_ACCOUNT_LAW.'</p>');
			#$html .= '<br />'.LANG_GO_TO_HOME_PAGE;
			echo $html;
		}
	}
	else
	{
		echo '<div class="comUser_form-error">'.$user_form->getFormErrorMessage().'</div>';
	}
}



//////////////
// Start view

if ($start_view)
{
	$html  = '';

	$form = new formManager();

	$html .= $form->form('post', $form->reloadPage(), COM_USER_FORM_PREFIX_CREATE_);

	// Form (array)
	$data = $user_form->getUserForm(COM_USER_FORM_PREFIX_CREATE_);

	if (!$tmpl_name_create)
	{
		$html .= "\n<!-- comUser:create -->\n".'<fieldset class="comUser_fieldset"><legend>'.LANG_COM_USER_CREATE_ACCOUNT_TITLE.'</legend>'."\n";

		$html .= $user_form->displayUserForm($data);

		$html .= '<p class="comUser_form-submit"><br />'.$form->submit('submit', LANG_BUTTON_SUBMIT).'</p>';
		$html .= '<div class="comUser_form-tips">'.comUser_form::requiredFieldsTips().'</div>';

		$html .= "</fieldset><!-- End of : comUser:create -->\n\n";
	}
	else
	{
		// Add the submit-button and the required-tips into the $data (to have full control of the view)
		$data['submit'] = $form->submit('submit', LANG_BUTTON_SUBMIT);
		$data['tips'] = comUser_form::requiredFieldsTips();

		$html .= $user_form->displayUserForm($data, $tmpl_name_create); # Notice: the template must include '{submit}' and optionnaly '{tips}'

		// If your template don't use the submit-button and the required-tips $data, add them here
	}

	#$html .= '<p style="text-align:center;"><a href="'.formManager::reloadPage().'">'.LANG_BUTTON_RESET.'</a></p>';
	$html .= $form->end();
	echo $html;
}


?>