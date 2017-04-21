<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

$submit = formManager::isSubmitedForm('crypt_', 'post');



// List of 'user_info' fields that needs to be encrypted/decrypted
$user_db = new comUser_db();
$user_info_string_fields = $user_db->getUserInfoStringfields();



// (1) Case 'encryption'
if ($submit && $filter->requestValue('encryption')->get())
{
	// Check config status
	$crypt_user_info = $db->selectOne('user_config, crypt_user_info', 'crypt_user_info');

	if ($crypt_user_info == 0)
	{
		// Update 'config' table
		$result = $db->update('user_config; crypt_user_info=1');
	} else {
		$result = false;
	}

	if ($result)
	{
		$crypt = new cryptManager();

		// Get 'user_info'
		$user_info = $db->select('user_info, *');

		for ($i=0; $i<count($user_info); $i++)
		{
			$user_info_query = '';
			reset($user_info[$i]);
			foreach($user_info[$i] as $key => $value)
			{
				if (in_array($key, $user_info_string_fields))
				{
					$user_info_query .= " $key=".$db->str_encode( $crypt->encrypt($value) ).',';
				}
			}
			$user_info_query = preg_replace('~,$~', '', $user_info_query);

			// Update database
			$result_one_user = $db->update("user_info; $user_info_query; where: user_id={$user_info[$i]['user_id']}");

			!$result_one_user ? $result = false : '';
		}

		$crypt->close();

		admin_informResult($result, '', LANG_ADMIN_COM_USER_CRYPT_ERROR_SYSTEM_FAILURE);
	}
	else
	{
		admin_message(LANG_ADMIN_COM_USER_CRYPT_ERROR_NOTHING_DONE, 'warning');
	}
}



// (2) Case 'decryption'
if ($submit && $filter->requestValue('decryption')->get())
{
	// Check config status
	$crypt_user_info = $db->selectOne('user_config, crypt_user_info', 'crypt_user_info');

	if ($crypt_user_info == 1)
	{
		// Update 'config' table
		$result = $db->update("user_config; crypt_user_info=0");
	} else {
		$result = false;
	}

	if ($result)
	{
		$crypt = new cryptManager();

		// Get 'user_info'
		$user_info = $db->select('user_info, *');

		for ($i=0; $i<count($user_info); $i++)
		{
			$user_info_query = '';
			reset($user_info[$i]);
			foreach($user_info[$i] as $key => $value)
			{
				if (in_array($key, $user_info_string_fields))
				{
					$user_info_query .= " $key=".$db->str_encode( $crypt->decrypt($value) ).',';
				}
			}
			$user_info_query = preg_replace('~,$~', '', $user_info_query);

			// Update database
			$result_one_user = $db->update("user_info; $user_info_query; where: user_id={$user_info[$i]['user_id']}");

			!$result_one_user ? $result = false : '';
		}

		$crypt->close();

		admin_informResult($result, '', LANG_ADMIN_COM_USER_CRYPT_ERROR_SYSTEM_FAILURE);
	}
	else
	{
		admin_message(LANG_ADMIN_COM_USER_CRYPT_ERROR_NOTHING_DONE, 'warning');
	}

}



//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_USER_CRYPT_TITLE_START.'</h2>';

	admin_message(LANG_ADMIN_COM_USER_CRYPT_SUBMIT_TIPS, 'warning', '380');

	$html = '';

	// Database
	$crypt_user_info = $db->selectOne('user_config, crypt_user_info', 'crypt_user_info');

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'crypt_');

	$html .= '<p>';
	if (!$crypt_user_info)
	{
		$html .= '<img src="'.WEBSITE_PATH.'/admin/components/com_user/images/db_not_crypted.png" alt="" style="vertical-align:top;" /> &nbsp;';

		$html .= LANG_ADMIN_COM_USER_CRYPT_STATUS.LANG_ADMIN_COM_USER_CRYPT_STATUS_NOT_CRYPTED;

		$html .= '<br /><br />'.$form->submit('encryption', LANG_ADMIN_COM_USER_CRYPT_SUBMIT_ENCRYPTION); // (1)
	} else {
		$html .= '<img src="'.WEBSITE_PATH.'/admin/components/com_user/images/db_crypted.png" alt="" style="vertical-align:top;" /> &nbsp;';

		$html .= LANG_ADMIN_COM_USER_CRYPT_STATUS.LANG_ADMIN_COM_USER_CRYPT_STATUS_CRYPTED;

		$html .= '<br /><br />'.$form->submit('decryption', LANG_ADMIN_COM_USER_CRYPT_SUBMIT_DECRYPTION); // (2)		
	}
	$html .= '</p>';

	$html .= $form->end();

	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>