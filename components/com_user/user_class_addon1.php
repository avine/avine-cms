<?php

/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

/**
 * Display and process forms to create and update users
 */

class comUser_form
{
	/**
	 * In backend:
	 *		- All fields of 'user_field' table are selected (not only the 'activated' ones)
	 *		- In $this->displayUserAccess(), the 'activated' and 'access_level' fields have a special process (check inside)
	 *		- We display the form inputs of 'activated' and 'access_level' fields
	 *		- Silent registration is disabled
	 */
	private	$backend;

	private	$user_field 				= array(), # Fields list of 'user_field' table
			$user_field_required 		= array();

	private	$overwrite_user_config		= array(); # Change temporarely the 'registration_silent', 'allow_duplicate_email' and 'activation_method' fields

	private	$user_details 				= NULL;

	public	$form_errors_array_format 	= false; # Boolean (if true then return an `array` of the form errors, instead of an `html` code)

	public	$form_process_result 		= NULL; # After called $this->processForm(), get here some important informations about the process result

	private	$captcha_enabled			= false; # Never change this default !
	const	CAPTCHA_FIELD				= 'captcha';



	public function __construct( $backend = false )
	{
		$backend ? $this->backend = true : $this->backend = false;

		global $db;

		if (!$this->backend)
		{
			$user_field = $db->select('user_field, *, field_order(asc), where: activated=1');
		}  else {
			$user_field = $db->select('user_field, *'); # All fields selected
		}

		for ($i=0; $i<count($user_field); $i++) # Notice: required fields are also required in backend (change this if you need)
		{
			$this->user_field[] = $user_field[$i]['field'];

			$user_field[$i]['required' ] ? $this->user_field_required[] = $user_field[$i]['field'] : '';
		}
	}



	// Usefull in frontend
	public function isRequired( $field )
	{
		if (in_array($field, $this->user_field_required)) # Remember: if we are in backend, then a field can be required, but not activated !
		{
			return true;
		} else {
			return false;
		}
	}



	/*
	 * Notice :
	 * Only some values can be overwritten (registration_silent, allow_duplicate_email, activation_method) and not the other ones (related to the 'user_session').
	 */
	public function overwriteConfig( $key, $value )
	{
		global $db;
		$user_field = $db->select('user_field, activated,required, where: field='.$db->str_encode('email'));
		if (!$user_field[0]['activated'] || !$user_field[0]['required'])
		{
			trigger_error("Limitation: the method ".__METHOD__." is available ONLY when 'email' field (in 'user_field' table) is activated and required in the default config.");
			return false;
		}

		switch($key)
		{
			case 'registration_silent':
				$value ? $this->overwrite_user_config['registration_silent'] = true : $this->overwrite_user_config['registration_silent'] = false;
				break;

			case 'allow_duplicate_email':
				$value ? $this->overwrite_user_config['allow_duplicate_email'] = true : $this->overwrite_user_config['allow_duplicate_email'] = false;
				break;

			case 'activation_method':
				if (in_array($value, array('auto', 'email', 'admin')))
				{
					$this->overwrite_user_config['activation_method'] = $value;
				} else {
					trigger_error("Invalid value for activation method: $value<br />Authorized values: auto, email, admin");
				}
				break;

			default:
				trigger_error("Invalid key: $key<br />Authorized keys: registration_silent, allow_duplicate_email, activation_method");
				break;
		}
		return true;
	}



	/**
	 * $strict : if true, then this method  check that the 'username', 'password' (and 'email' when required) fields are included into the parameters
	 * Notice when creating a user: if $strict=false and the 'username' or the 'password' are not available in the final $data, then the comUser_db::insertUser($data) method will failed!
	 *
	 * Tips: You should call this method just after the constructor (and after the overwriteConfig() method, if you use it)
	 */
	public function overwriteUserFieldsList( $user_field = false, $user_field_required = false, $strict = true )
	{
		// List of 'field' fields of 'user_field' table
		$all_user_info = array();
		global $db;
		$temp = $db->select('user_field, field');
		for ($i=0; $i<count($temp); $i++) {
			$all_user_info[] = $temp[$i]['field'];
		}

		$compatible = true;

		// Check $user_field parameter
		if (is_array($user_field)) {
			for ($i=0; $i<count($user_field); $i++) {
				!in_array($user_field[$i], $all_user_info) ? $compatible = false : '';
			}

			if ($strict) {
				!in_array('username', $user_field) || !in_array('password', $user_field) ? $compatible = false : '';
			}
		}

		// Check $user_field_required parameter
		if (is_array($user_field_required)) {
			for ($i=0; $i<count($user_field_required); $i++) {
				!in_array($user_field_required[$i], $all_user_info) ? $compatible = false : '';
			}

			if ($strict) {
				!in_array('username', $user_field_required) || !in_array('password', $user_field_required) ? $compatible = false : '';
			}
		}

		// Add fields : 'email' might be required
		if ($strict)
		{
			$new_account = $this->newAccountActivationInfos();
			if ($new_account['activation_method'] == 'email' || $new_account['activation_method'] == 'admin' || $this->isRegistrationSilent())
			{
				is_array($user_field) && !in_array('email', $user_field) || is_array($user_field_required) && !in_array('email', $user_field_required) ? $compatible = false : '';
			}
		}

		// Exit!
		if (!$compatible) {
			trigger_error("The parameters of the method ".__METHOD__." are not compatible.");
			return;
		}

		// Overwrite the config
		is_array($user_field			) ? $this->user_field = $user_field : '';
		is_array($user_field_required	) ? $this->user_field_required = $user_field_required : '';
	}



	public function defaultConfig()
	{
		$this->overwrite_user_config = array();
	}



	public function setFormErrorsArrayFormat( $boolean )
	{
		$boolean ? $this->form_errors_array_format = true : $this->form_errors_array_format = false;
	}



	public function enableCaptcha( $bool )
	{
		$bool ? $this->captcha_enabled = true : $this->captcha_enabled = false;
	}



	/////////////
	// View part

	// Select a user to view/modify his datas (but to create a user, do not call this method)
	public function setUser( $user_id )
	{
		$user_db = new comUser_db();
		$select_user = $user_db->selectUser($user_id);

		if (!$select_user) {
			$this->user_details = NULL; # Reset
			return false;
		}

		$this->user_details = $select_user;
		return true;
	}



	public function getUserView()
	{
		if (!isset($this->user_details)) {
			return false;
		}

		$fields_alias = comUser_getFieldsAlias();

		$data = array();

		// 'user_field' table
		for ($i=0; $i<count($this->user_field); $i++)
		{
			$f = $this->user_field[$i];
			switch ($f)
			{
				case 'password':
					// The password has been encoded by sha1(). There's no way to decrypt it!
					break;

				case 'gender':
					if (comUser_getGenderAlias($this->user_details[$f]))
					{
						$data[$f.'_alias'] = $fields_alias[$f];
						$data[$f] = comUser_getGenderAlias($this->user_details[$f]);
					}
					break;

				default:
					if ($this->user_details[$f])
					{
						$data[$f.'_alias'] = $fields_alias[$f];
						$data[$f] = $this->user_details[$f];
					}
					break;
			}
		}

		// The "others" fields
		$data = array_merge($data, $this->getUserAccess());

		return $data;
	}



	// Get the "others" fields, wich are not a part of 'user_field' table
	private function getUserAccess() # Notice: this is a private method! Because the informations are available from: $this->getUserView();
	{
		if (!isset($this->user_details)) {
			return false;
		}

		$data = array();

		// activated
		$this->user_details['activated'] ? $activated = LANG_COM_USER_ACTIVATED_YES : $activated = LANG_COM_USER_ACTIVATED_NO;
		$data['activated'.'_alias'			] = LANG_COM_USER_ACTIVATED;
		$data['activated'					] = $activated;

		// access_level
		$user_status = comUser_getStatusOptions();
		$data['access_level'.'_alias'		] = LANG_COM_USER_ACCESS_LEVEL;
		$data['access_level'				] = $user_status[$this->user_details['access_level']];

		// registration_date
		$data['registration_date'.'_alias'	] = LANG_COM_USER_REGISTRATION_DATE;
		$data['registration_date'			] = getTime($this->user_details['registration_date'], 'format=long;time=no');

		// last_visit
		$data['last_visit'.'_alias'			] = LANG_COM_USER_LAST_VISIT;
		$data['last_visit'					] = getTime($this->user_details['last_visit'], 'format=long');

		return $data;
	}



	public function displayUserView( $data, $tmpl_name = false ) # The expected $data parameter is : $data = $this->getUserView();
	{
		if (!$data) {
			return '';
		}

		if (!$tmpl_name)
		{
			// Default view
			$html = "\n<table class=\"comUser_view\"><tbody>";
			for ($i=0; $i<count($this->user_field); $i++)
			{
				$f = $this->user_field[$i];
				if (isset($data[$f]))
				{
					switch ($f)
					{
						case 'password':
							// The password has been encoded by sha1. There's no way to decrypt it!
							break;
	
						case 'gender':
						default:
							$html .= "\t<tr><th>{$data[$f.'_alias']} : </th><td>{$data[$f]}</td></tr>\n";
							break;
					}
				}
			}
			$html .= "</tbody></table>\n";
		}
		else
		{
			$html = $this->applyTemplate($tmpl_name, $data);
		}

		return $html;
	}



	public function displayUserAccess( $data, $tmpl_name = false )  # The expected $data parameter is : $data = $this->getUserView();
	{
		if (!$data) {
			return '';
		}

		if (!$tmpl_name)
		{
			// Default view
			$list = array();

			// View 'activated' in backend
			if ($this->backend) {
				$list[] = 'activated';
			}

			// View 'access_level' in backend OR even in frontend if different of basic registered user level
			if (($this->backend) || ($this->user_details['access_level'] < comUser_getLowerStatus(true))) {
				$list[] = 'access_level';
			}

			$list = array_merge($list, array('registration_date', 'last_visit'));

			$html = "\n".'<p class="comUser_view-user-access">';
			for ($i=0; $i<count($list); $i++)
			{
				$html .= "<span>{$data[$list[$i].'_alias']} :</span> {$data[$list[$i]]}<br />";
			}
			$html .= '</p>'."\n";
		}
		else
		{
			$html = $this->applyTemplate($tmpl_name, $data);
		}

		return $html;
	}



	/////////////
	// Form part

	// Validate posted form (controller)
	public function processForm()
	{
		// Filter
		$filter = new formManager_filter();
		$filter->requestVariable('post');
		$this->form_process_result = NULL; # Reset

		// DB
		global $db;

		// User config
		$registration_silent 	= $this->isRegistrationSilent();
		$allow_duplicate_email 	= $this->isAllowDuplicateEmail();

		// All posted fields
		$data = array();

		// Create user or update user ?
		$user_id = $filter->requestValue('user_id')->get();
		if (formManager_filter::isInteger($user_id))
		{
			$data['id'] = $this->form_process_result['user_id'] = $user_id; # Remember it!

			$current = $db->select('user, username, email, access_level, where: id='.$data['id']);
			$current_username 		= $current[0]['username'];
			$current_email 			= $current[0]['email'];
			$current_access_level 	= $current[0]['access_level'];
		} else {
			$data['last_visit'] 	= $data['registration_date'] = time();

			$current_username 		= NULL;
			$current_email 			= NULL;
			$current_access_level 	= NULL;
		}

		if ($this->backend)
		{
			$filter->requestValue('activated')->get() ? $data['activated'] = 1 : $data['activated'] = 0;

			$data['access_level'] = $filter->requestValue('access_level')->getInteger();
		}
		elseif (!isset($data['id']))
		{
			// New user status
			$data['access_level'] = comUser_getLowerStatus(1); # Lower status after the 'public' one

			$infos = $this->newAccountActivationInfos();
			$activation_method 			= $infos['activation_method']; # Usefull to define final message type
			$data['activated'] 			= $infos['activated'];
			$data['activation_code'] 	= $infos['activation_code'];
		}

		// Alias of the required field missing message
		$required_missing_message = str_replace('{star}', LANG_COM_USER_FIELD_REQUIRED_STAR, LANG_COM_USER_CREATE_ACCOUNT_ERROR_REQUIRED);

		$fields_alias = comUser_getFieldsAlias();
		for ($i=0; $i<count($this->user_field); $i++)
		{
			// $_POST[$f] = $f_value;
			$f 			= $this->user_field[$i];
			$f_value 	= $filter->requestValue($f)->get();

			// Is this field required and missing ?
			$this->isRequired($f) && !formManager_filter::isNotEmpty($f_value) ? $required_missing = true : $required_missing = false; # Notice: this test don't work for the case 'gender'

			switch($f)
			{
				case 'username':
					if (!isset($data['id']) && $registration_silent && !$this->backend)
					{
						#$data[$f] = $this->randomString();	# Old code (stable)
						$data[$f] = false;					# New code : wait for $data['email'] value, and try to use it as a username, instead of a simple randomString
					}
					elseif ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					}
					else
					{
						$username = $filter->requestValue($f)->getUserPass(1, '', $fields_alias[$f]);

						// No duplicate username allowed
						if ($username && (!$current_username || $username !== $current_username)) # Careful: admin == AdMiN ; but admin !=== AdMiN
						{
							if ($db->select('user, id, where: username='.$db->str_encode($username))) # Known limitation: admin == AdMiN
							{
								$filter->set(false, $f)->getError(LANG_COM_USER_DUPLICATE_USERNAME, $fields_alias[$f]);
							} else {
								$data[$f] = $username;
							}
						}
					}
					break;

				case 'password':
					$password = NULL;
					if (!isset($data['id']) && $registration_silent && !$this->backend)
					{
						$password = $this->randomString();
						$data[$f] = sha1($password);
					}
					elseif ($required_missing && !isset($data['id'])) # Notice: Here the $required_missing test for the password, wich is required only when creating a user
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					}
					else
					{
						if ( ($f_value && !formManager_filter::isUserPass($f_value)) || (!$f_value && !isset($data['id'])) )
						{
							$filter->requestValue($f)->getUserPass(1, '', $fields_alias[$f]); # Simply record the error
						}
						elseif ($f_value)
						{
							$password = $f_value;
							$data[$f] = sha1($password);
						}
					}
					isset($password) ? $this->form_process_result['new_password'] = $password : ''; # Remember it!
					break;

				case 'email':
					if ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					}
					elseif (!$f_value)
					{
						$data[$f] = ''; # Reset the content field
					}
					else
					{
						$email = $filter->requestValue($f)->getEmail(1, '', $fields_alias[$f]);

						// 'system_email' 
						$system_email = $db->select('config, system_email');
						$system_email = $system_email[0]['system_email'];

						if ($email && $email == $system_email && $current_access_level != 1) # 'system_email' can be used only for Administrators 
						{
							$filter->set(false, $f)->getError(LANG_COM_USER_DUPLICATE_SYSTEM_EMAIL, $fields_alias[$f]);
						}
						elseif ($email && $allow_duplicate_email)
						{
							$data[$f] = $email;
						}
						elseif ($email && !$allow_duplicate_email && ( !$current_email || $email != $current_email))
						{
							if ($db->select('user, id, where: email='.$db->str_encode($email)))
							{
								$duplicate_email = str_replace('{href}', comMenu_rewrite("com=user&page=forget&remember=$email"), LANG_COM_USER_DUPLICATE_EMAIL);
								$filter->set(false, $f)->getError($duplicate_email, $fields_alias[$f]); # Duplicate emails not allowed
							} else {
								$data[$f] = $email;
							}
						}
					}
					break;

				case 'gender':
					$gender_options_keys = array_keys(comUser_selectGenderOptions());
					$gender_not_selected_key = $gender_options_keys[0];

					if ($this->isRequired($f) && $f_value == $gender_not_selected_key) # Notice: this time, the test of $required_missing is not right, because the value of a not selected gender is the string 'null' and not an empty string ''
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					}
					elseif (in_array($f_value, $gender_options_keys))
					{
						if ($f_value == $gender_not_selected_key) 
						{
							$data[$f] = 'NULL'; # Reset the content field (TINYINT)
						} else {
							$data[$f] = $filter->requestValue($f)->getInteger(1, LANG_COM_USER_GENDER_NOT_SELECTED, $fields_alias[$f]);
						}
					}
					break;

				case 'age':
					if ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					}
					elseif (!$f_value)
					{
						$data[$f] = 'NULL'; # Reset the content field (TINYINT)
					}
					else
					{
						$data[$f] = $filter->requestValue($f)->getInteger(1, LANG_COM_USER_NUMERCAL_ONLY, $fields_alias[$f]);
					}
					break;

				case 'zip':
				case 'phone_1':
				case 'phone_2':
				case 'fax':
					if ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					}
					elseif (!$f_value)
					{
						$data[$f] = ''; # Reset the content field (VARCHAR)
					}
					else
					{
						$data[$f] = $filter->requestValue($f)->getInteger(1, LANG_COM_USER_NUMERCAL_ONLY, $fields_alias[$f]);
					}
					break;

				// Fields Format: ucwords
				case 'last_name':
				case 'first_name':
				case 'adress_1':
				case 'adress_2':
				case 'city':
				case 'state':
					if ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					} else {
						$data[$f] = upperCaseWords($filter->requestValue($f)->get());
					}
					break;

				// Fields Format: strtoupper
				case 'title':
				case 'company':
				case 'country':
					if ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					} else {
						//$data[$f] = mb_strtoupper($filter->requestValue($f)->get()); # strtoupper() fonctionne mal avec les lettres accentuées
						$data[$f] = upperCaseWords($filter->requestValue($f)->get());
					}
					break;

				default:
					if ($required_missing)
					{
						//$filter->set(false, $f)->getError($required_missing_message, $fields_alias[$f]);
						$filter->set(false, $f)->getError($required_missing_message, '', true); # New code !
					} else {
						$data[$f] = $filter->requestValue($f)->get();
					}
					break;
			}
		}

		// New code : try now to use $data['email'] value to generate the username (only when silent registration is required)
		if (isset($data['username']) && $data['username'] === false)
		{
			if (isset($data['email'])) {
				$wish = $data['email'];											# Use email
			}
			elseif (isset($data['first_name']) && isset($data['last_name'])) {	# Use first_name and/or last_name
				$wish = mb_strtolower($data['first_name'].'.'.$data['last_name']);
			}
			elseif (isset($data['first_name'])) {
				$wish = mb_strtolower($data['first_name']);
			}
			elseif (isset($data['last_name'])) {
				$wish = mb_strtolower($data['last_name']);
			}
			else {
				$wish = '';														# No wish !
			}

			$data['username'] = comUser_form::findAvailableUsername($wish);
		}

		// Check captcha code
		if (!$this->backend && $this->captcha_enabled)
		{
			if (!captcha::checkCode( $filter->requestValue(self::CAPTCHA_FIELD)->get()) )
			{
				$filter->set(false, self::CAPTCHA_FIELD)->getError(LANG_COM_USER_CAPTCHA_ERROR.'<br /><span style="font-size:83%;color:#333;">'.LANG_COM_USER_CAPTCHA_ERROR_TIPS.'</span>', LANG_COM_USER_CAPTCHA);
			}
		}

		if ($filter->validated())
		{
			$this->form_process_result['data'] = $data; # Remember it!
			return true;
		}
		else
		{
			!$this->form_errors_array_format ? $error_message = $filter->errorMessage() : $error_message = $filter->errorMessageArray();

			$this->form_process_result['error_message'] = $error_message; # Remember it!
			return false;
		}
	}



	// Insert/update database (Model)
	public function processData()
	{
		if ($data = $this->getUserData())
		{
			$user_db = new comUser_db();

			if (!isset($data['id']))
			{
				// Create user
				if ($new_user_id = $user_db->insertUser($data))
				{
					$this->form_process_result['user_id'] = $new_user_id; # Remember it!

					// Send Emails
					if ($this->sendEmailsAfterCreate()) {
						return true;
					} else {
						$user_db->deleteUser($new_user_id); # Just created and just deleted - So young to die !
						return false;
					}
				} else {
					return false;
				}
			}
			else
			{
				// Update user
				return $user_db->updateUser($data); # Return true or false
			}
		}
		else
		{
			return false;
		}
	}



	/**
	 * Emails tasks after the creation of a new user account in frontend
	 *
	 * (Use $new_user_id and $new_password parameters to send Emails for a specific user)
	 */
	public function sendEmailsAfterCreate( $new_user_id = NULL, $new_password = NULL )
	{
		if ($this->backend) {
			return true; # No emails tasks in backend
		}

		// $new_user_id & $new_password
		if (!isset($new_user_id))
		{
			$new_user_id = $this->getUserID();					# Here the new ID (available after $this->processForm() method)
		}
		elseif (!formManager_filter::isInteger($new_user_id))	# Here a specific user ID
		{
			trigger_error('Critical error in '.__METHOD__." : Invalid \$new_user_id=$new_user_id parameter");
			exit;
		}

		if (!isset($new_password))
		{
			$new_password = $this->getUserNewPassword();		# Here the new password (available after $this->processForm() method)
		}
		elseif (!formManager_filter::isUserPass($new_password))	# Here the password of the specific user
		{
			trigger_error('Critical error in '.__METHOD__." : Invalid \$new_password=$new_password parameter");
			exit;
		}

		// Security
		if (!$new_user_id || !$new_password) {
			return false; # Warning: this test return true not only when a user was created but also when a user was simply updated !
		}

		$user_db = new comUser_db();
		$select_user = $user_db->selectUser($new_user_id);

		// Config
		comConfig_getInfos($site_name, $system_email); # passed by reference

		// Activation_method
		$activation_method = $this->newAccountActivationInfos();
		$activation_method = $activation_method['activation_method'];

		// New-user Message
		$user_message =
			searchAndReplace( LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER,
				array(
					'{site_name}'			=> htmlentities($site_name, ENT_COMPAT, 'UTF-8'),
					'{username}'			=> $select_user['username'],
					'{password}'			=> $new_password,
					'{registration_date}'	=> htmlentities(getTime($select_user['registration_date'], 'format=long'), ENT_COMPAT, 'UTF-8')
				)
			);

		switch($activation_method)
		{
			case 'auto' :
				$user_message .= LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER_AUTO;
				break;

			case 'email':
				$activation_link = comMenu_rewrite('com=user&amp;page=create&amp;id='.$new_user_id.'&amp;activation_code='.$select_user['activation_code']);
				$activation_link = "<a href=\"$activation_link\">$activation_link</a>";

				$user_message .= str_replace('{activation_link}', $activation_link, LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER_EMAIL);
				break;

			case 'admin':
				$user_message .= LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER_ADMIN;
				break;
		}

		// Send mail
		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageHTML($user_message)
				->addTo($select_user['email'])
				->setSubject(LANG_COM_USER_CREATE_SEND_MAIL_SUBJECT)
				->setFrom($system_email/*, $site_name*/); # Notice : Don't use $site_name as a recipient name, because it can contain some characters like `:` that can make the email invalid !

		$user_result = $mail->send(); # That's it !

		// Display warning, if the email was strongly required to activate account !
		if ((!$user_result) && ($activation_method == 'email')) {
			echo userMessage(LANG_COM_USER_CREATE_ACCOUNT_CREATE_FAILED_EMAIL, 'error', '350');
			return false; # Failure !
		}

		// Special feature : if ($activation_method == 'auto') then make this new user automatically logged
		if (($activation_method == 'auto'))
		{
			/* Notice : this will be fully effective only on the next page
			 * (example: if a login-module was already displayed on the page, this module can be still in a not-logged status) */
			global $g_user_login;
			$g_user_login->autoLogin($new_user_id);
		}

		// Admin Message
		$admin_message =
			searchAndReplace( LANG_COM_USER_CREATE_SEND_MAIL_ADMIN,
				array(
					'{site_name}'			=> htmlentities($site_name, ENT_COMPAT, 'UTF-8'),
					'{username}'			=> $select_user['username'],
					'{activation_method}'	=> $activation_method,
					'{registration_date}'	=> htmlentities(getTime($select_user['registration_date'], 'format=long'), ENT_COMPAT, 'UTF-8')
				)
			);

		// Send mail
		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageHTML($admin_message)
				->addTo($system_email)
				->setSubject(LANG_COM_USER_CREATE_SEND_MAIL_SUBJECT)
				->setFrom($system_email/*, $site_name*/); # Notice : Don't use $site_name as a recipient name, because it can contain some characters like `:` that can make the email invalid !

		$admin_result = $mail->send();

		return true; # Success !
	}



	public function isRegistrationSilent()
	{
		// Overwrite config
		if (isset($this->overwrite_user_config['registration_silent']))
		{
			return $this->overwrite_user_config['registration_silent'];
		}

		// Default config
		global $db;
		$config = $db->select('user_config, registration_silent');
	
		$config[0]['registration_silent'] ? $return = true : $return = false;
		return $return;
	}



	public function isAllowDuplicateEmail()
	{
		// Overwrite config
		if (isset($this->overwrite_user_config['allow_duplicate_email']))
		{
			return $this->overwrite_user_config['allow_duplicate_email'];
		}

		// Default config
		global $db;
		$config = $db->select('user_config, allow_duplicate_email');
	
		$config[0]['allow_duplicate_email'] ? $return = true : $return = false;
		return $return;
	}



	public function newAccountActivationInfos()
	{
		$return = array();

		// Overwrite config
		if (isset($this->overwrite_user_config['activation_method']))
		{
			$return['activation_method'] = $this->overwrite_user_config['activation_method'];
		}
		else
		{
			// Default config
			global $db;
			$config = $db->select('user_config, activation_method');
			$return['activation_method'] = $config[0]['activation_method'];
		}

		switch($return['activation_method'])
		{
			case 'auto':
				$return['activated'] = 1;
				$return['activation_code'] = '';
				break;

			case 'email':
				$return['activated'] = 0;
				$return['activation_code'] = md5(rand());
				break;

			case 'admin':
				$return['activated'] = 0;
				$return['activation_code'] = '';
				break;
			}

		return $return;
	}



	// Random string for the username field using a wish
	public static function findAvailableUsername( $wish = '' )
	{
		global $db;

		// Try to use the wish
		if ($wish) {
			if (!formManager_filter::isUserPass($wish)) {
				$wish = formManager_filter::removeInvalidUserPassChar($wish);
			}

			if (!$db->select('user, id, where: username='.$db->str_encode($wish))) {
				return $wish;
			}
		}

		// Get a random wish... funny !
		do {
			$wish = comUser_form::randomString();
		} while ($db->select('user, id, where: username='.$db->str_encode($wish)));

		return  $wish;
	}



	// Random string for registration silent
	public static function randomString( $lengh = 6 )
	{
		$string = '';

		// Characters list (excluded: 'l', o', 'I', 'O', '0', '1' )
		$char_list = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

		for ($i = 0; $i < $lengh; $i++)
		{
			$string .= substr($char_list, rand(0, strlen($char_list)-1), 1);
		}
		return $string;
	}



	// After processForm() method, get the user_id of the updated/created user
	public function getUserID()
	{
		if (isset($this->form_process_result['user_id']))
		{
			return $this->form_process_result['user_id'];
		}
		return false;
	}



	// After processForm() method, if the password was changed/created, get it!
	public function getUserNewPassword()
	{
		if (isset($this->form_process_result['new_password']))
		{
			return $this->form_process_result['new_password'];
		}
		return false;
	}



	// After processForm() method, if the form validated, get the datas!
	public function getUserData()
	{
		if (isset($this->form_process_result['data']))
		{
			return $this->form_process_result['data'];
		}
		return false;
	}



	// After processForm() method, if the form was not completed properly, get the errors messages
	public function getFormErrorMessage()
	{
		if (isset($this->form_process_result['error_message']))
		{
			return $this->form_process_result['error_message'];
		}
		return false;
	}



	// $overwrite_default_values is allowed for new and old user !
	public function getUserForm( $form_id = '', $overwrite_default_values = array() )
	{
		$data = array();

		// If necessary, set/overwrite the default values of the form inputs
		if (count($overwrite_default_values))
		{
			if (!isset($this->user_details))
			{
				// Take the parameter as it !
				$this->user_details = $overwrite_default_values;							# Set !

				// Set $this->user_details property only inside this method 
				$reset_user_details = true;
			}
			else
			{
				// Use smartly the parameter to overwrite existing values
				foreach($this->user_details as $key => $value)
				{
					if (isset($overwrite_default_values[$key]) && $overwrite_default_values[$key])
					{
						if ($key != 'id' && $key != 'user_id')
						{
							$this->user_details[$key] = $overwrite_default_values[$key];	# Overwrite !
						} else {
							trigger_error('Error occured in '.__METHOD__." : the key '<b>$key</b>' is not allowed for the overwrite_default_values parameter. Use the \$this->setUser(\$user_id) method !");
						}
					}
				}
			}
		}

		$registration_silent = $this->isRegistrationSilent();

		$form = new formManager();
		$form->setForm('post', $form_id);

		// Add hidden field for the user_id when updating a user
		if (isset($this->user_details['id']) && $this->user_details['id'])
		{
			$data['user_id'] = $form->hidden('user_id', $this->user_details['id']);
		}

		$disabled = '';
		if ($this->backend)
		{
			// Self change not allowed : deactivate or change access_level of the current logged-user
			if ($this->user_details['id'])
			{
				global $g_user_login;
				if ($g_user_login->userID() == $this->user_details['id'])
				{
					$disabled = 'disabled';
				}
			}

			// activated
			isset($this->user_details['activated']) ? $activated = $this->user_details['activated'] : $activated = 1; # user or default value
			$data['activated'.'_alias'		] = $form->label('activated', LANG_COM_USER_ACTIVATED);
			$data['activated'				] = $form->checkbox('activated', $activated, '', '', $disabled);

			// access_level
			isset($this->user_details['access_level']) ? $access_level = $this->user_details['access_level'] : $access_level = comUser_getLowerStatus(true); # user or default value
			$user_status = comUser_getStatusOptions($access_level, true);
			$data['access_level'.'_alias'	] = $form->label('access_level', LANG_COM_USER_ACCESS_LEVEL);
			$data['access_level'			] = $form->select('access_level', $user_status, '', '', $disabled);

			// Suite of: Self change not allowed...
			if ($disabled)
			{
				$data['activated'			] .= $form->hidden('activated'		, $this->user_details['activated'		]);
				$data['access_level'		] .= $form->hidden('access_level'	, $this->user_details['access_level'	]);
			}
		}

		// Others fields
		$fields_alias = comUser_getFieldsAlias();
		for ($i=0; $i<count($this->user_field); $i++)
		{
			$f = $this->user_field[$i];

			switch($f)
			{
				case 'username':
					if ($this->user_details['id'] || !$registration_silent || $this->backend)
					{
						// Label
						$data[$f.'_alias'] = $form->label($f, $fields_alias[$f]);

						// Input
						$data[$f] = $form->text($f, $this->user_details[$f], '', '', 'size=default;maxlength=100');

						// Required
						$this->isRequired($f) ? $data[$f] .= LANG_COM_USER_FIELD_REQUIRED_STAR : '';
					}
					break;

				case 'password':
					if ($this->user_details['id'] || !$registration_silent || $this->backend)
					{
						// Label
						if ($this->user_details['id'])
						{
							$data[$f.'_alias'] = $form->label($f, LANG_COM_USER_PASSWORD_NEW); # update
						} else {
							$data[$f.'_alias'] = $form->label($f, $fields_alias[$f]);
						}

						// Input
						$data[$f] = $form->password($f, '', '', '', 'size=default;maxlength=100');

						// Required
						if ($this->isRequired($f)) {
							if ($this->user_details['id'])
							{
								$data[$f] .= LANG_COM_USER_FIELD_IF_NECESSARY; # update
							} else {
								$data[$f] .= LANG_COM_USER_FIELD_REQUIRED_STAR;
							}
						}
					}
					break;

				case 'gender':
					// Label
					$data[$f.'_alias'] = $form->label($f, $fields_alias[$f]);

					// Input
					$gender_options = comUser_selectGenderOptions($this->user_details[$f]);
					$data[$f] = $form->select($f, $gender_options);

					// Required
					$this->isRequired($f) ? $data[$f] .= LANG_COM_USER_FIELD_REQUIRED_STAR : '';
					break;

				case 'age':
					// Label
					$data[$f.'_alias'] = $form->label($f, $fields_alias[$f]);

					// Input
					$data[$f] = $form->text($f, $this->user_details[$f], '', '', 'size=2;maxlength=4');

					// Required
					$this->isRequired($f) ? $data[$f] .= LANG_COM_USER_FIELD_REQUIRED_STAR : '';
					break;

				default:
					// Label
					$data[$f.'_alias'] = $form->label($f, $fields_alias[$f]);

					// Input
					$data[$f] = $form->text($f, $this->user_details[$f], '', '', 'size=default');

					// Required
					$this->isRequired($f) ? $data[$f] .= LANG_COM_USER_FIELD_REQUIRED_STAR : '';
					break;
			}
		}

		// Captcha form
		if (!$this->backend && $this->captcha_enabled)
		{
			// Make a customized captcha instance available in any script.
			captcha::initSession();

			$data['captcha'			] = captcha::showCode().$form->text(self::CAPTCHA_FIELD, '', '', '', 'size=default;update=0').LANG_COM_USER_FIELD_REQUIRED_STAR; # Do not update the captcha field
			$data['captcha'.'_alias'] = $form->label(self::CAPTCHA_FIELD, LANG_COM_USER_CAPTCHA);
		}

		// Reset $this->user_details, wich was temporarily initialized to overwrite the default values
		if (isset($reset_user_details))
		{
			$this->user_details = NULL;
		}

		return $data;
	}



	public function displayUserForm( $data, $tmpl_name = false )
	{
		$html = '';

		// Add manualy the 'user_id' hidden field
		if (isset($data['user_id']))
		{
			$html .= $data['user_id'];
		}

		if (!$tmpl_name)
		{
			// Default view
			$html .= "\n<table class=\"comUser_form\"><tbody>";

			if ($this->backend)
			{
				$html .= "\t<tr><th>{$data['activated'.'_alias']}</th><td>{$data['activated']}</td></tr>\n";
				$html .= "\t<tr><th>{$data['access_level'.'_alias']}</th><td>{$data['access_level']}</td></tr>\n";
			}

			for ($i=0; $i<count($this->user_field); $i++)
			{
				$f = $this->user_field[$i];

				switch ($f)
				{
					// If registration_silent then there's no 'username' and 'password' fields
					case 'username':
					case 'password':
						if (isset($data[$f])) {
							$html .= "\t<tr><th>{$data[$f.'_alias']}</th><td>{$data[$f]}</td></tr>\n";
						}
						break;

					default:
						$html .= "\t<tr><th>{$data[$f.'_alias']}</th><td>{$data[$f]}</td></tr>\n";
						break;
				}
			}

			if (!$this->backend && $this->captcha_enabled)
			{
				$html .= "\t<tr><th>{$data['captcha'.'_alias']}</th><td>{$data['captcha']}</td></tr>\n";
			}

			$html .= "</tbody></table>\n";
		}
		else
		{
			unset($data['user_id']); # Unset the hidden field wich has been already added manualy

			$html .= $this->applyTemplate($tmpl_name, $data);
		}

		return $html;
	}



	private function applyTemplate( $tmpl_name, $data )
	{
		/**
		 * Notice: if you are using a template, the fields order depends entirely of your template !
		 */
		$template = new templateManager();
		$html = $template->setTmplPath(sitePath()."/components/com_user/tmpl/$tmpl_name")->setReplacements($data)->process();

		return $html;
	}



	static function requiredFieldsTips()
	{
		return LANG_COM_USER_FIELD_REQUIRED_STAR.LANG_COM_USER_FIELD_REQUIRED;
	}

}


?>