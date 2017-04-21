<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Some Forms prefix : to allow them in the same page !
define('COM_USER_FORM_PREFIX_LOGIN_'	, "user_login_");
define('COM_USER_FORM_PREFIX_CREATE_'	, "user_create_");
define('COM_USER_FORM_PREFIX_MODIFY_'	, "user_modify_"); # in fact, this one can not be on the same page with the others, but it's more academic...



function comUser_getFieldsAlias()
{
	return
		array(
			'username'		=> LANG_COM_USER_USERNAME,
			'password'		=> LANG_COM_USER_PASSWORD,
			'email'			=> LANG_COM_USER_EMAIL,

			'gender'		=> LANG_COM_USER_GENDER,
			'last_name'		=> LANG_COM_USER_LAST_NAME,
			'first_name'	=> LANG_COM_USER_FIRST_NAME,

			'age'			=> LANG_COM_USER_AGE,
			'title'			=> LANG_COM_USER_TITLE,
			'company'		=> LANG_COM_USER_COMPANY,

			'adress_1'		=> LANG_COM_USER_ADRESS_1,
			'adress_2'		=> LANG_COM_USER_ADRESS_2,
			'city'			=> LANG_COM_USER_CITY,
			'state'			=> LANG_COM_USER_STATE,
			'country'		=> LANG_COM_USER_COUNTRY,
			'zip'			=> LANG_COM_USER_ZIP,

			'phone_1'		=> LANG_COM_USER_PHONE_1,
			'phone_2'		=> LANG_COM_USER_PHONE_2,
			'fax'			=> LANG_COM_USER_FAX,

			'extra_field_1'	=> LANG_COM_USER_EXTRA_FIELD_1,
			'extra_field_2'	=> LANG_COM_USER_EXTRA_FIELD_2,
			'extra_field_3'	=> LANG_COM_USER_EXTRA_FIELD_3,
			'extra_field_4'	=> LANG_COM_USER_EXTRA_FIELD_4,
			'extra_field_5'	=> LANG_COM_USER_EXTRA_FIELD_5
	);
}



function comUser_selectGenderOptions( $default = '' )
{
	$options =
		array(
			'null'	=>	LANG_COM_USER_GENDER_NULL, # Do never delete this line!
			'1'		=>	LANG_COM_USER_GENDER_1,
			'0'		=>	LANG_COM_USER_GENDER_0,
		);

	$options = formManager::selectOption($options, $default);

	return $options;
}



function comUser_getGenderAlias( $key )
{
	$options = comUser_selectGenderOptions();

	if (array_key_exists($key, $options) && $key !== 'null') {
		return $options[$key];
	} else {
		return '';
	}
}



function comUser_getLowerStatus( $exclude_public = false ) # Notice: the lower status is the highest number (administrator user status = 1)
{
	global $db;

	$lower_status = $db->selectOne('user_status, id(desc); limit: 1', 'id');

	$exclude_public ? $offset = 1 : $offset = 0; # Exclude the 'public' status from the count

	return ($lower_status - $offset);
}



function comUser_getStatusOptions( $selected_status = false, $exclude_public = false )
{
	$options = array();

	global $db;
	$user_status = $db->select('user_status, *');

	$exclude_public ? $offset = 1 : $offset = 0; # Exclude the 'public' status from the options

	for ($i=0; $i<count($user_status)-$offset; $i++)
	{
		$options[$user_status[$i]['id']] = $user_status[$i]['comment'];
	}
	$options = formManager::selectOption($options, $selected_status);

	return $options;
}



/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

class comUser_login
{
	// Required access_level to login
	private	$required_access_level;

	// Frontend or backend ?
	private	$backend_login;

	// Session variable of the user
	private	$session;

	// Show modify, create and forget links
	public $show_links;

	// Html message on login-error
	public	$login_error_html = '';



	public function __construct( $required_access_level = false, $backend_login = false, $show_links = true )
	{
		// Required access level
		$this->required_access_level = comUser_getLowerStatus(1); # default

		if ($required_access_level >= 1 && $required_access_level < $this->required_access_level)
		{
			$this->required_access_level = $required_access_level;
		}

		// Frontend or backend ?
		$this->backend_login = $backend_login;

		// Session
		$this->session = new sessionManager( $backend_login ? sessionManager::BACKEND : sessionManager::FRONTEND, COM_USER_FORM_PREFIX_LOGIN_ );

		// Links
		$show_links ? $this->show_links = true : $this->show_links = false;

		// Perhaps the user was just deleted...
		global $db;
		if ($this->userID() && !$db->selectCount('user, where: id='.$this->userID()))
		{
			$this->autoLogout();
		}
	}



	public function userID()
	{
		return $this->session->get('id');
	}



	public function accessLevel()
	{
		if ($user_id = $this->userID()) {
			global $db;
			$access_level = $db->selectOne("user, access_level, where: id=$user_id", 'access_level');
		} else {
			$access_level = comUser_getLowerStatus();
		}
		return $access_level;
	}



	public function process()
	{
		if (comUser_login::isSubmited())
		{
			$filter = new formManager_filter();
			$filter->requestVariable('post');

			// Logout process
			if ($filter->requestValue('submit_logout')->get())
			{
				$this->sessionReset();
			}
			// Login process
			else
			{
				self::processResult(false);

				$username = $filter->requestValue('username')->getUserPass();
				$password = $filter->requestValue('password')->getUserPass();

				if ($filter->validated())
				{
					global $db;
					$user = $db->select('user, id, access_level, activated, where: username='.$db->str_encode($username).' AND, where: password='.$db->str_encode(sha1($password)));
					if ($user)
					{
						$user = $user[0];

						if ($user['activated'])
						{
							if ($user['access_level'] <= $this->required_access_level)
							{
								$this->session->set('id', $user['id']);

								$db->update('user; last_visit='.time().'; where: id='.$user['id']);

								// User session : prevent multi-session
								$this->userSession_login();

								self::processResult(true);
							}
							else {
								$this->login_error_html = LANG_COM_USER_NO_ACCESS_LEVEL;
							}
						}
						else {
							$this->login_error_html = LANG_COM_USER_NOT_ACTIVATED;
						}

						if ($filter->requestValue('remember')->get()) # Careful : Cookie must be send before any Html output. Then call this function at the begining of the main script
						{
							setcookie(COM_USER_FORM_PREFIX_LOGIN_.'remember', $username.';'.$password, time()+(60*60*24*365*3)); # Expires : 3 years
						}
					}
					else {
						$this->login_error_html = LANG_COM_USER_INVALID_LOGIN;
					}
				}
				else {
					$this->login_error_html = LANG_COM_USER_INVALID_LOGIN;
				}
			}
		}

		// User session : checkup
		$this->userSession_task();
	}



	public static function isSubmited()
	{
		return formManager::isSubmitedForm(COM_USER_FORM_PREFIX_LOGIN_, 'post', false);
	}



	// This method accept one parameter to set the static variable $result (should not be set from the outside of the class)
	public static function processResult()
	{
		static $result = NULL;
		!func_num_args() or $result = func_get_arg(0);
		return $result;
	}



	// Force a user to be logged without process any form
	public function autoLogin( $user_id )
	{
		global $db;
		$user = $db->select("user, access_level, activated, where: id=$user_id");
		if ($user)
		{
			$user = $user[0];

			if ($user['activated'])
			{
				if ($user['access_level'] <= $this->required_access_level)
				{
					/*
					 * FIXME - J'ai retiré cette ligne car elle ne me semble pas logique.
					 * En effet, lorsque l'utilisateur se connecte (comme ici ou dans la méthode ->process()),
					 * on doit considérer que c'est la même personne qui était en train de naviguer jusqu'à présent.
					 * Or, avec le code suivant, on considère le contraire...
					 * D'ailleurs, je n'ai pas ce code dans la méthode ->process().
					 */
					#$this->sessionReset();

					$this->session->set('id', $user_id);

					$db->update('user; last_visit='.time()."; where: id=$user_id");

					// User session : prevent multi-session
					$this->userSession_login();

					return true; # autoLogin Success
				}
			}
		}

		return false; # autoLogin Failure
	}



	// Force logout without process any form
	public function autoLogout()
	{
		$this->sessionReset();

		$this->userSession_remove();
	}



	protected function sessionReset()
	{
		// Unset the session for the comUser_login area
		$this->session->reset();

		// Unset all of the session variables for backend or frontend area
		$_SESSION[$this->backend_login ? sessionManager::BACKEND : sessionManager::FRONTEND] = array();

		// Delete the session cookie
		setcookie(session_name(), '', time()-3600);
	}



	public function getform( $form_action = '' )
	{
		$data = array();

		// Logout form
		if ($user_id = $this->userID())
		{
			// Links
			$this->isLinkAvailable('modify') ? $data['modify'] = '<a href="'.comMenu_rewrite('com=user&amp;page=modify').'" title="'.LANG_COM_USER_MODIFY_ACCOUNT_LINK_TIPS.'">'.LANG_COM_USER_MODIFY_ACCOUNT_LINK.'</a>' : '';

			// Form fields
			$form = new formManager();
			!$form_action ? $form_action = $form->reloadPage() : '';
			$data['form_open'] = $form->form('post', $form_action, COM_USER_FORM_PREFIX_LOGIN_);

			// Username
			global $db;
			$username = $db->select("user, username, where: id=$user_id");
			$username = $username[0]['username'];
			$data['username'] = $username;

			$data['submit'] = $form->submit('submit_logout', LANG_COM_USER_BUTTON_LOGOUT);

			$data['form_close'] = $form->end();
		}
		// Login form
		else
		{
			// Links
			$this->isLinkAvailable('forget') ? $data['forget'] = '<a href="'.comMenu_rewrite('com=user&amp;page=forget').'" title="'.LANG_COM_USER_FORGET_ACCOUNT_LINK_TIPS.'">'.LANG_COM_USER_FORGET_ACCOUNT_LINK.'</a>' : '';
			$this->isLinkAvailable('create') ? $data['create'] = '<a href="'.comMenu_rewrite('com=user&amp;page=create').'" title="'.LANG_COM_USER_CREATE_ACCOUNT_LINK_TIPS.'">'.LANG_COM_USER_CREATE_ACCOUNT_LINK.'</a>' : '';

			// Cookie
			if (isset($_COOKIE[COM_USER_FORM_PREFIX_LOGIN_.'remember']))
			{
				$username_password = explode(';', $_COOKIE[COM_USER_FORM_PREFIX_LOGIN_.'remember']);
				$remember_username = $username_password[0];
				$remember_password = $username_password[1];
			}
			else {
				$remember_username = '';
				$remember_password = '';
			}

			// Form fields
			$form = new formManager();
			$form->setRandomId(true); # Be sure that the random_id feature is enabled in backend (even if the constant formManager::RANDOM_ID_DISABLED_IN_BACKEND == true)
			!$form_action ? $form_action = $form->reloadPage() : '';
			$data['form_open'] = $form->form('post', $form_action, COM_USER_FORM_PREFIX_LOGIN_);

			$data[ 'username'.'_alias'	] = $form->label('username', LANG_COM_USER_USERNAME);
			$data[ 'password'.'_alias'	] = $form->label('password', LANG_COM_USER_PASSWORD);
			$data[ 'remember'.'_alias'	] = $form->label('remember', LANG_COM_USER_REMEMBER_ME);

			$data[ 'username'			] = $form->text		('username', $remember_username, '', '', "size=20;maxlength=100");
			$data[ 'password'			] = $form->password	('password', $remember_password, '', '', "size=20;maxlength=100");
			$data[ 'remember'			] = $form->checkbox	('remember', isset($_COOKIE[COM_USER_FORM_PREFIX_LOGIN_.'remember']), '', '', 'update=0');

			// Message
			$data['message'] = $this->login_error_html;

			$data['submit'] = $form->submit('submit_login', LANG_COM_USER_BUTTON_LOGIN);

			$data['form_close'] = $form->end();
		}

		return $data;
	}



	public function isLinkAvailable( $page )
	{
		global $db;
		$page_info = $db->selectOne("components, access_level,published, where: com='user' AND, where: page=".$db->str_encode($page));

		if (!$page_info) {
			return false;
		}

		if ($this->show_links && $page_info['published'] && $this->accessLevel() <= $page_info['access_level'])
		{
			return true;
		} else {
			return false;
		}
	}



	public function displayForm( $data, $tmpl_name_login = false, $tmpl_name_logout = false )
	{
		$html = '';

		// Logout form
		if ($this->userID())
		{
			if (!$tmpl_name_logout)
			{
				// Default view
				$html .= '<p class="comUser_logout">'."\n";

				$html .= '<span class="comUser_logout-hello">'.LANG_COM_USER_HELLO.' <strong>'.$data['username'].'</strong></span><br />'."\n";

				$html .= $data['submit']."<br />\n";

				isset($data['modify']) ? $html .= ' <span class="comUser_login-links">'.$data['modify'].'</span>' : '';

				$html .= "</p>\n";
			}
			else
			{
				$html .= $this->applyTemplate($tmpl_name_logout, $data);
			}
		}
		// Login form
		else
		{
			if (!$tmpl_name_login)
			{
				// Default view
				$html .= "\n<table class=\"comUser_form\"><tbody>";

				$html .= "\t<tr><th>{$data['username'.'_alias']}</th><td>{$data['username']}</td></tr>\n";
				$html .= "\t<tr><th>{$data['password'.'_alias']}</th><td>{$data['password']}</td></tr>\n";

				$html .= "\t<tr class=\"comUser_login-remember\"><th>{$data['remember'.'_alias']}</th><td>{$data['remember']}</td></tr>\n";

				$data['message'] ? $message = "<span>{$data['message']}</span>" : $message = '';
				$html .= "\t<tr><td colspan=\"2\"><div class=\"comUser_login-error\">$message</div></td></tr>\n";

				$links = '';
				isset($data['forget']) ? $links .= $data['forget'] : '';
				isset($data['forget']) && isset($data['create']) ? $links .= ' | ' : '';
				isset($data['create']) ? $links .= $data['create'] : '';

				$html .= "\t<tr><th class=\"comUser_login-links\">$links</th><td class=\"comUser_login-submit\">{$data['submit']}</td></tr>\n";

				$html .= "</tbody></table>\n";
			}
			else
			{
				$html .= $this->applyTemplate($tmpl_name_login, $data);
			}
		}

		// Add automatically form_open and form_close
		$html = $data['form_open'].$html.$data['form_close'];
		unset($data['form_open']);
		unset($data['form_close']);

		return $html;
	}



	private function applyTemplate( $tmpl_name, $data )
	{
		$template = new templateManager();
		$html = $template->setTmplPath(sitePath()."/components/com_user/tmpl/$tmpl_name")->setReplacements($data)->process();

		return $html;
	}



	static function searchUserID( $username, $password = '', $activated = false, $access_level = false )
	{
		$activated 		? $activated_query 		= " AND, where: activated=$activated" 			: $activated_query 		= '';
		$access_level 	? $access_level_query 	= " AND, where: access_level<=$access_level" 	: $access_level_query 	= '';

		global $db;
		$user_id = $db->selectOne('user, id, where: username='.$db->str_encode($username).' AND, where: password='.$db->str_encode(sha1($password)).$activated_query.$access_level_query, 'id');

		return $user_id;
	}



	/**
	 * Use this method to create a website where only logged users can enter !
	 * This script blocks all request_uri except the 'login' page. (The access of the 'create' and 'forget' pages depends of the method parameters
	 */
	public function loginRequiredToEnter( $access_level = 9999, $authorise_create = true, $authorise_forget = true )
	{
		// User is logged, ok !
		if ( ($this->userID() && $this->accessLevel() <= $access_level) ) {
			return true;
		}

		// User request an authorized com_user page, ok !
		$page = array('login');
		$authorise_create ? $page[] = 'create' : '';
		$authorise_forget ? $page[] = 'forget' : '';

		if (@$_GET['com'] == 'user' && in_array(@$_GET['page'], $page)) {
			return true;
		}

		// No !
		return false;
	}



	/**
	 * Manage the user session
	 */

	// Checkup (this method is called in $this->process() method)
	protected function userSession_task()
	{
		$this->userSession_delete();
		$this->userSession_logout();
		$this->userSession_update();
	}



	// Delete older sessions
	private function userSession_delete()
	{
		global $db;

		$maxlifetime = $db->selectOne('user_config, session_maxlifetime', 'session_maxlifetime');
		if (!$maxlifetime) {
			return;
		}

		$offest = time() - $maxlifetime;

		// Find the time each logged user spent on the website
		$user_session = $db->select("user_session, *, where: last_activity < $offest");
		for ($i=0; $i<count($user_session); $i++)
		{
			/*
			 * Notice : it's possible there's 2 records of the same $user_id, in case the user is logged in frontend and backend...
			 */
			if ($user_id = $user_session[$i]['user_id'])
			{
				$user = $db->selectOne("user, *, where: id=$user_id");
				$last_login		= $user['last_visit']; # This field contains the time, the user log in !
				$last_activity	= $user_session[$i]['last_activity'];

				$duration = $last_activity - $last_login;

				// TODO : actually, this interesting information is not stored... So, we need to create a new table 'user_activity' with 'user_id' and 'duration' fields
				#echo "<p style=\"color:red;background-color:yellow;\">The user_id=$user_id spent <b>$duration</b> secondes on the website !</p>";
			}
		}

		$db->delete("user_session; where: last_activity < $offest");
	}



	// If the current user have switch to another browser, he needs to be logout from the first one
	private function userSession_logout()
	{
		global $db;

		if ($user_id = $this->userID())
		{
			$this->backend_login ? $backend = '1' : $backend = '0';

			if (!$db->selectCount("user_session, where: user_id=$user_id AND, where: backend=$backend AND, where: session_id=".$db->str_encode(session_id())))
			{
				$this->autoLogout();
			}
		}
	}



	// Update the user session
	private function userSession_update()
	{
		global $db;

		// Alias
		$session_id		= $db->str_encode(session_id());					# This is the browser itself
		$last_activity	= time();
		$this->backend_login ? $backend = '1' : $backend = '0';
		$user_id		= ($this->userID() ? $this->userID() : 'NULL');		# This is the user wich is on that browser

		// Check for an existing session
		if (!$db->selectCount("user_session, where: session_id=$session_id AND, where: backend=$backend"))
		{
			/*
			 * Notices :
			 * -> We don't need to care about a guest in the backend, which doesn't means anything !
			 * -> Here, we assume that this $user_id (if logged) is not logged in another browser.
			 *    This point is checked in $this->userSession_login() method
			 */
			($backend && !$this->userID()) or $db->insert("user_session; col: session_id,last_activity,backend,user_id; $session_id,$last_activity,$backend,$user_id");

			// Update the counter of frontend visitors !
			$backend or $db->update('user_config; visit_counter='.($db->selectOne('user_config, visit_counter', 'visit_counter') + 1));
		}
		else {
			$db->update("user_session; last_activity=$last_activity, user_id=$user_id; where: session_id=$session_id AND backend=$backend");
		}
	}



	// Here we do that a user can not be logged on 2 differents browsers at the same time (this method is called in $this->process() method)
	protected function userSession_login()
	{
		($user_id = $this->userID()) or trigger_error('Invalid call of '.__METHOD__.' : $this->userID() is missing !');
		$this->backend_login ? $backend = '1' : $backend = '0';

		global $db;
		$db->delete("user_session; where: user_id=$user_id AND backend=$backend"); # Remove this user from any browser

		$this->userSession_update(); # Set this user on that browser
	}



	// Remove the session of the current user
	protected function userSession_remove()
	{
		global $db;

		// Alias
		$session_id		= $db->str_encode(session_id());
		$this->backend_login ? $backend = '1' : $backend = '0';

		$db->delete("user_session; where: session_id=$session_id AND backend=$backend");
	}



	static public function userSession_counter( &$counter )
	{
		$html = '';

		global $db;

		// Start with a global count
		$counter =
			array(
				'guest'			=> $db->selectCount	('user_session, where: user_id IS NULL'			),
				'user'			=> $db->selectCount	('user_session, where: user_id IS NOT NULL'		),
				'visit_counter'	=> $db->selectOne	('user_config, visit_counter', 'visit_counter'	) # Additional info : counter of visitors from the begining
			);

		// Get the number of users which are logged in both backend and frontend
		$double_login =
			count(
				$db->fetchMysqlResults(
					$db->sendMysqlQuery(
						"SELECT DISTINCT u1.user_id FROM {table_prefix}user_session AS u1 LEFT JOIN {table_prefix}user_session AS u2 ON u1.user_id = u2.user_id WHERE u1.backend != u2.backend"
					)
				)
			);

		// Get the real count of users
		$counter['user'] -= $double_login;

		// Simple debug info
		#if ($double_login) echo "<p>There's is $double_login user(s) which is(are) logged in both frontend and backend.</p>";

		// Guest counter
		if ($counter['guest']) {
			$lang = str_replace('{s}', ($counter['guest']>1 ? 's':''), LANG_COM_USER_SESSION_COUNTER_GUEST);
			$html .= "<li><strong>{$counter['guest']}</strong> $lang</li>\n";
		}

		// Connected counter
		if ($counter['user']) {
			$lang = str_replace('{s}', ($counter['user']>1 ? 's':''), LANG_COM_USER_SESSION_COUNTER_USER);
			$html .= "<li><strong>{$counter['user']}</strong> $lang</li>\n";
		}

		return "\n<ul class=\"comUser_session-counter\">\n$html</ul>\n";
	}

}



/*
 * Load 'user_class_addon*.php' (just because the scripts are too long to be added here...)
 */
require(sitePath().'/components/com_user/user_class_addon1.php');
require(sitePath().'/components/com_user/user_class_addon2.php');


?>