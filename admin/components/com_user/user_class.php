<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

define( 'LANG_ADMIN_COM_USER_USER_OPTION_ROOT_ID'						, '0' );



function admin_comUser_SelectActivationMethod( $default = '' )
{
	$options =
		array(
			'auto'   =>  LANG_ADMIN_COM_USER_ACTIVATION_METHOD_AUTO,
			'email'  =>  LANG_ADMIN_COM_USER_ACTIVATION_METHOD_EMAIL,
			'admin'  =>  LANG_ADMIN_COM_USER_ACTIVATION_METHOD_ADMIN
		);

	$options = formManager::selectOption($options, $default);

	return $options;
}



function admin_comUser_getUserOptions( $access_level_inf = false, $access_level_sup = false, $selected_user_id = false, $add_option_root = true )
{
	$user_options = array();

	if ($add_option_root) {
		$user_options[LANG_ADMIN_COM_USER_USER_OPTION_ROOT_ID] = LANG_SELECT_OPTION_ROOT;
	}

	// Lower status wich is the highest number (administrator user status = 1)
	$lower_status = comUser_getLowerStatus();

	$access_level_inf == false ? $access_level_inf = 1 : '';
	$access_level_sup == false ? $access_level_sup = $lower_status : '';

	if ( ($access_level_inf > $access_level_sup) || ($access_level_inf > $lower_status) || ($access_level_sup > $lower_status) )
	{
		trigger_error("Error occured in admin_comUser_getUserOptions() function : wrong values of \$access_level_inf=$access_level_inf and/or \$access_level_sup=$access_level_sup");
		return $user_options;
	}

	global $db;

	// user_status
	$user_status = comUser_getStatusOptions();

	for ($i=$access_level_inf; $i<=$access_level_sup; $i++)
	{
		$user_list = $db->select("user, id, username, where: access_level=$i");
		if ($user_list)
		{
			$user_options['(optgroup)'.$user_status[$i]] = '';

			for ($j=0; $j<count($user_list); $j++)
			{
				$user_options[$user_list[$j]['id']] = $user_list[$j]['username'];
			}
		}
	}

	if ($selected_user_id) {
		$user_options = formManager::selectOption($user_options, $selected_user_id);
	}

	return $user_options;
}



function admin_comUserSession_details( $exclude_myself = true )
{
	if ($exclude_myself) {
		global $g_user_login;
		$where = ', where: user_id!='.$g_user_login->userID();
	} else {
		$where = '';
	}

	global $db;
	$s = $db->select("user_session, last_activity(desc), backend, user_id$where, join: user_id>; user, username, join: <id");
	if (!count($s)) {
		return;
	}

	$list = array();
	for ($i=0; $i<count($s); $i++)
	{
		$b = $s[$i]['backend'] ? admin_replaceTrueByChecked($s[$i]['backend'], false) : '';

		$t = time() - $s[$i]['last_activity'];
		if ($t < 60) {
			$t = round($t).' sec';
		} else {
			$t = round($t/60).' min '.round($t%60).' sec';
		}

		$list[$i] = array(
			'username'		=> "$b ".$s[$i]['username'],
			'last_activity'	=> $t
		);
	}

	$table = new tableManager($list, array(LANG_ADMIN_COM_USER_SESSION_UID, LANG_ADMIN_COM_USER_SESSION_LAST_ACTIVITY));
	return '<h3>'.LANG_ADMIN_COM_USER_SESSION_TITLE.'</h3>'.$table->html();
}


?>