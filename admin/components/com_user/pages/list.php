<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */

#
# TODO : Possible upgrade
#		- Menu déroulant pour filtrer les user_status
#		- Moteur de recherche des users
#

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'user_list');


// Configuration
$start_view = true;


// Current logged admin
global $g_user_login;
$logged_admin_id 	= $g_user_login->userID();
$logged_admin_level = $g_user_login->accessLevel();



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$activated = $filter->requestValue('activated', 'get')->getInteger(0); // (4)

$submit = formManager::isSubmitedForm('user_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new'	)->get(); // (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$activated_submit = formManager::isSubmitedForm('activated_', 'post'); // (4)

$del_submit = formManager::isSubmitedForm('del_', 'post'); // (3)
$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// Simple security : None Administrators restrictions
if ($logged_admin_level != 1)
{
	$activated = false;
	$del = false;
	$upd = false;
	$new = false;
}



// (4) Case 'activated' (change the activated status)
if ($activated_submit)
{
	$activated_validation = true;

	$filter->reset();

	// id (hidden field)
	$activated_id = $filter->requestValue('id')->getInteger();

	if ($activated_validation = $filter->validated())
	{
		// Activate user
		$activation_result = $db->update("user; activated=1; where: id=$activated_id");

		// Send mail option
		if ($activation_result && $filter->requestValue('mail')->get())
		{
			// User info
			$user = $db->select("user, username, email, where: id=".$activated_id);
			$user = $user[0];
			$username = $user['username'];
			$email    = $user['email'	];

			// Site info
			comConfig_getInfos($site_name, $system_email); # passed by reference

			// User message
			$user_message =
				searchAndReplace( LANG_ADMIN_COM_USER_LIST_ACTIVATE_SEND_MAIL,
					array(
						'{site_name}'		=> htmlentities($site_name, ENT_COMPAT, 'UTF-8'),
						'{username}'		=> $username,
						'{activation_date}'	=> htmlentities(getTime('', 'format=long'), ENT_COMPAT, 'UTF-8')
					)
				);

			// Send mail
			$mail = new emailManager();
			$mail	->useDefaultTemplate()
					->addMessageHTML($user_message)
					->addTo($email)
					->setSubject(LANG_ADMIN_COM_USER_LIST_ACTIVATE_SEND_MAIL_SUBJECT)
					->setFrom($system_email);

			$user_result = $mail->send();

			admin_informResult($user_result, '', LANG_ADMIN_COM_USER_LIST_ACTIVATE_SEND_MAIL_FAILED);
		}
		else {
			admin_informResult($activation_result);
		}
	}
}
if ($activated)
{
	$user = $db->select("user, *, where: id=$activated");
	if ($user)
	{
		// User infos
		$user = $user[0];
		$username 		= $user['username'	];
		$email 			= $user['email'		];
		$user_activated = $user['activated'	]; // careful : $activated is the user_id

		$user['activation_code'] != "" ? $checkbox_default = false: $checkbox_default = true;

		if($activated != $logged_admin_id)
		{
			// Block user
			if ($user_activated == 1)
			{
				$db->update("user; activated=0; where: id=$activated");
			}
			// Activate user
			else
			{
				if (!$email)
				{
					$db->update("user; activated=1; where: id=$activated");
				}
				else
				{
					$start_view = false;

					// Title
					echo '<h2>'.LANG_ADMIN_COM_USER_LIST_ACTIVATE.'</h2>';

					$html = '';
					$form = new formManager();
					$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'activated_');

					$html .= str_replace('{username}'	, $username	, LANG_ADMIN_COM_USER_LIST_ACTIVATE_USERNAME).'<br />';
					$html .= str_replace('{email}'		, $email	, LANG_ADMIN_COM_USER_LIST_ACTIVATE_EMAIL).'<br /><br />';

					if ($checkbox_default)
					{
						$code_exist = LANG_ADMIN_COM_USER_LIST_ACTIVATE_CODE_EXIST_NO;
						$button 	= LANG_ADMIN_COM_USER_LIST_ACTIVATE_BUTTON_NORMAL;
					} else {
						$code_exist = LANG_ADMIN_COM_USER_LIST_ACTIVATE_CODE_EXIST_YES;
						$button 	= LANG_ADMIN_COM_USER_LIST_ACTIVATE_BUTTON_SPECIAL;
					}
					$html .= str_replace('{code_exist}', $code_exist, LANG_ADMIN_COM_USER_LIST_ACTIVATE_CODE_EXIST).'<br /><br />';

					$html .= $form->checkbox('mail', $checkbox_default, LANG_ADMIN_COM_USER_LIST_ACTIVATE_INFORM_USER);
					$html .= $form->hidden('id', $activated);
					$html .= '<br /><br />'.$form->submit('submit', $button);

					$html .= $form->end();
					echo $html;
				}
			}
		}
		else {
			admin_message(LANG_ADMIN_COM_USER_LIST_SELF_DEACTIVATE_NOT_ALLOWED, 'error');
		}
	}
}



// (3) Case 'del' # TODO: Missing -> check the user contents or donates before deletion...
if ($del)
{
	if($del != $logged_admin_id)
	{
		$user_db = new comUser_db();
		$result = $user_db->deleteUser($del);

		// Potential record (if exist)
		$db->delete("user_forget; where: user_id=$del");

		admin_informResult($result);
	}
	else
	{
		admin_message(LANG_ADMIN_COM_USER_LIST_SELF_DEL_NOT_ALLOWED, 'error');
	}
}



// Instanciate user form class
$user_form = new comUser_form(1); # 1 = backend
#$user_form->overwriteUserFieldsList(/*$put_here_user_field, $put_here_user_field_required*/); # TODO - modifier avec cette methode les champs obligatoires au minimum...


// (2) Case 'upd'
if ($upd_submit)
{
	$user_form->processForm();
	$upd_submit_validation = $user_form->processData();

	if ($upd_submit_validation)
	{
		$new_password = $user_form->getUserNewPassword(); # only if changed

		admin_informResult(true);
	}
	else
	{
		echo $user_form->getFormErrorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_USER_LIST_UPD.'</h2>';

	// Id
	$user_form->getUserID() ? $upd_id = $user_form->getUserID() : $upd_id = $upd;
	$user_form->setUser($upd_id);

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');


	$data = $user_form->getUserForm('upd_');

	// Add 'activation_code' into $data if exist (read only)
	$activation_code = $db->select("user, activation_code, where: id=$upd_id");
	$activation_code = $activation_code[0]['activation_code'];
	if ($activation_code != "") {
		$data['activation_code'] = str_replace('{activation_code}', $activation_code, LANG_ADMIN_COM_USER_LIST_USER_ACTIVATION_CODE);
	}

	$html .= $user_form->displayUserForm($data, 'default/tmpl_admin_form.html');


	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}




// (1) Case 'new'
if ($new_submit)
{
	$user_form->processForm();
	$new_submit_validation = $user_form->processData();

	if ($new_submit_validation)
	{
		$new_user_id 	= $user_form->getUserID();
		$new_password 	= $user_form->getUserNewPassword();

		admin_informResult(true);
	}
	else
	{
		echo $user_form->getFormErrorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_USER_LIST_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');


	$data = $user_form->getUserForm('new_');
	$html .= $user_form->displayUserForm($data, 'default/tmpl_admin_form.html');


	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}




//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_USER_LIST_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'user_');

	// Access level filter
	$status_options = array(LANG_ADMIN_COM_USER_LIST_ALL_ACCESS_LEVEL);
	$opt = comUser_getStatusOptions($session->setAndGet('select_access_level', $submit ? $filter->requestValue('select_access_level')->getInteger() : false), true);
	foreach ($opt as $k => $v) {
		$status_options[$k] = $v;
	}
	if ($session->get('select_access_level'))
	{
		$select_access_level = ', where: access_level='.$session->get('select_access_level');
	} else {
		$select_access_level = '';
	}

	// Multipage
	$multipage = new simpleMultiPage( $db->selectCount("user $select_access_level") );
	$multipage->setFormID('user_');
	$multipage->updateSession($session->returnVar('multipage'));

	$html .=
		admin_floatingContent(
			array(
				$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),
				$form->select('select_access_level', $status_options, LANG_COM_USER_ACCESS_LEVEL),
				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT) // (0)
			)
		);

	// Select users
	$user_list = $db->select("user, id(desc), username, email, access_level, activated, activation_code, registration_date, last_visit $select_access_level; limit:".$multipage->dbLimit());

	$user_status = comUser_getStatusOptions();

	for ($i=0; $i<count($user_list); $i++)
	{
		// Update & Delete buttons
		$update[$i] = $form->submit('upd_'.$user_list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$user_list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)

		// Access_level (_name instead of _id) (get the access-level-name from user_status table)
		$user_list[$i]['access_level'] = $user_status[$user_list[$i]['access_level']];

		// Activated (<a> tag with checked/unchecked image) & Activation code required
		if ($logged_admin_level == 1) { # None Administrators restrictions
			$user_list[$i]['activated'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;activated='.$user_list[$i]['id'].'">'.admin_replaceTrueByChecked($user_list[$i]['activated']).'</a>'; // (4)
		} else {
			$user_list[$i]['activated'] = admin_replaceTrueByChecked($user_list[$i]['activated'], false);
		}
		if ($user_list[$i]['activation_code'] != "") {
			$user_list[$i]['activated'] .= '<img src="'.WEBSITE_PATH.'/admin/components/com_user/images/activation-code.png" alt="activation_code required" title="'.LANG_ADMIN_COM_USER_LIST_ACTIVATION_CODE_REQUIRED.'" />';
		}

		// Format date
		$user_list[$i]['registration_date'] = getTime($user_list[$i]['registration_date'], 'time=no');
		$user_list[$i]['last_visit'] 		= getTime($user_list[$i]['last_visit']);
	}

	// Table
	$table = new tableManager($user_list);
	$table->delCol(5); # Delete activation_code column (wich in included into the activated column)

	$user_field_alias = comUser_getFieldsAlias();
	$table->header(
		array(
			LANG_COM_USER_ID, $user_field_alias['username'], $user_field_alias['email'],
			LANG_COM_USER_ACCESS_LEVEL, LANG_COM_USER_ACTIVATED,
			LANG_COM_USER_REGISTRATION_DATE, LANG_COM_USER_LAST_VISIT
		)
	);

	if (count($user_list)) {
		if ($logged_admin_level == 1) { # None Administrators restrictions
			$table->addCol($delete, 0);
			$table->addCol($update, 999);
		}
	}

	$html .= $table->html();

	if ($logged_admin_level == 1) { # None Administrators restrictions
		$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE).'<br />'; // (1)
	}

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>