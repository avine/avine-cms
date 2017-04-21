<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


# FIXME - si registration_silent=1 dans user_config et ici dans donate_config ça vaut 0
# on aurait attentdu à ce que la registration soit silent ; or ce n'est pas ce qui se produit.....
# Il faut voir ou mettre : $db->selectOne('user_config, registration_silent', 'registration_silent') dans le code ....


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////
// Class

class comDonate_
{
	// Config
	private	$config = array();

	// Session
	public	$session;
	const	SESSION_DONATE_ID 		= '_donate_id_';	# Current record of 'donate' table we are dealing with

	// Filter
	private	$filter;									# Instance of formManager_filter()

	// Form
	private	$form_id 				= '';				# ID attribut of the form
	private	$form_validated;							# Boolean
	private	$form_errors;								# Form errors (html) after process

	const	GET_DESIGN_ID			= 'id';				# Display only the requested designation (key of query string)

	const	FORM_ERRORS_CUSTOMIZED 	= false;			# Boolean

	const	CONTRIBUTOR_SEP			= ';';				# Infos Separator of the 'contributor' data
	const	CONTRIBUTOR_SEP_REP		= '.';				# Separator replacement (must be different of CONTRIBUTOR_SEP)

	private	$overwrite_com_user_config =				# Overwrite the config of 'user_config' table inside the com_donate component
		array(
			'registration_silent'	=> false,			# Values: none (depends of the configuration of the component)
			'allow_duplicate_email'	=> false,			# Values: 0,1 to overwrite the config (or false to disable this feature and use the default config)
			'activation_method'		=> false			# Values: 0,1 to overwrite the config (or false to disable this feature and use the default config)
		);

	private	$allow_anonymous		= true;				# Allow anonymous donation # TODO - allow the configuration of this in the Admin...

	// Result
	public	$check_donation			= false;			# Find here informations about the last checked donation (after call the checkDonation() method)

	const	INVOICE_PATH			= '/components/com_donate/invoice/';					# Relative path to the invoices directory
	const	INVOICE_TMPL_HTML		= '/components/com_donate/tmpl/tmpl_invoice_html.html';	# Relative path to the html version of the invoice template
	const	INVOICE_TMPL_PDF		= '/components/com_donate/tmpl/tmpl_invoice_pdf.html';	# Relative path to the pdf version of the invoice template



	public function __construct()
	{
		// Config
		global $db;
		$this->config = $db->selectOne('donate_config, *');

		// Session
		$this->session = new sessionManager(sessionManager::FRONTEND, 'donate');

		// Filter
		$this->filter = new formManager_filter();
		$this->filter->requestVariable('post');

		// Check the contributor separator
		if (self::CONTRIBUTOR_SEP_REP == self::CONTRIBUTOR_SEP) {
			trigger_error('<p style="color:red;">Invalid configuration of comDonate_::CONTRIBUTOR_SEP and/or comDonate_::CONTRIBUTOR_SEP_REP</p>');
			exit;
		}

		// Force the 'registration_silent' ?
		if ($this->config['registration_silent']) {
			$this->overwrite_com_user_config['registration_silent'] = 1;
		}
	}



	/**
	 * Config
	 */

	public function currencyCode()
	{
		return $this->config['currency_code'];
	}



	public function currencyName()
	{
		$currency_name = money::currencyCodeOptionsPlural();
		return mb_strtolower($currency_name[$this->config['currency_code']]);
	}



	public function amountMin()
	{
		return $this->config['amount_min'];
	}



	/**
	 * Session
	 */

	public function sessionBegin() # Note : cette méthode était privée, mais j'en ai eu besoin pour la class wcal
	{
		if (!$this->session->get(self::SESSION_DONATE_ID))
		{
			$this->sessionReset();

			// Recording date
			$col = 'recording_date';
			$val = time();

			// User ID
			global $g_user_login;
			if ($user_id = $g_user_login->userID()) {
				$col .= ', user_id';
				$val .= ", $user_id";
			}

			// Insert a new record into 'donate' table
			global $db;
			$db->insert("donate; col: $col; $val");

			// Session, set donate_id
			$this->session->set(self::SESSION_DONATE_ID, $db->insertID());
		}
	}



	public function sessionGetDonateID()
	{
		return $this->session->get(self::SESSION_DONATE_ID);
	}



	public function sessionCheck( $preserve_session = true )
	{
		$debug = false;
		if ($debug) echo "<p style=\"color:blue;\"><b>DEBUG ".__METHOD__."</b></p>";

		// Session founded
		if ($donate_id = $this->session->get(self::SESSION_DONATE_ID))
		{
			if ($debug) echo "<p style=\"color:blue;\">Oh ! We have a donate_id= $donate_id</p>";

			global $db;
			$donate = $db->selectOne("donate, user_id,payment_id, where: id=$donate_id");

			// Donate payment_id
			if ($donate['payment_id'])
			{
				if ($debug) echo "<p style=\"color:blue;\">This donation is finished : PAYMENT_ID= {$donate['payment_id']}</p>";

				$this->sessionReset(); # This donation is finished !
				return;
			}

			// Donate user_id
			$donate_user_id = $donate['user_id'];

			// Logged user_id
			global $g_user_login;
			$logged_user_id = $g_user_login->userID();

			///////////////////////////
			// Let's go for some tests

			// (0-0)
			if (!$donate_user_id && !$logged_user_id)
			{
				# Nothing to do...
			}
			// (1-0)
			elseif ($donate_user_id && !$logged_user_id)
			{
				if (!$this->getNewUserNotLogged())
				{
					// The user just log out !
					$this->sessionReset();
					if ($debug) echo "<p style=\"color:blue;\">Log out detected ! The session has been deleted.</p>";
				}
			}
			// (0-1)
			elseif (!$donate_user_id && $logged_user_id)
			{
				// The user just log in !
				if (!$preserve_session)
				{
					$this->sessionReset();
					if ($debug) echo "<p style=\"color:blue;\">Log in detected ! The session has been deleted (\$preserve=0).</p>";
				} else {
					$db->update("donate; user_id=$logged_user_id; where: id=$donate_id");
					if ($debug) echo "<p style=\"color:blue;\">Log in detected ! The session has been conserved (\$preserve=1).</p>";
				}
			}
			// (1-1)
			elseif ($donate_user_id && $logged_user_id)
			{
				if ($donate_user_id == $logged_user_id)
				{
					if ($this->getNewUserNotLogged())
					{
						$this->resetNewUserNotLogged(); # Important !
						if ($debug) echo "<p style=\"color:blue;\">The new creating user just successfully log in !</p>";
					}
					else
					{
						# So fare so good...
						if ($debug) echo "<p style=\"color:blue;\">The donate_user_id match the logged_user_id ! Let's continue.</p>";
					}
				}
				else
				{
					$this->sessionReset();
					if ($debug) echo "<p style=\"color:blue;\">The donate_user_id <b>not</b> match the logged_user_id ! The session has been deleted.</p>";
				}
			}
		}
		else
		{
			# No donation recorded yet...
			if ($debug) echo "<p style=\"color:blue;\">No donation recorded yet...</p>";
		}
	}



	public function sessionReset()
	{
		$this->session->reset(); # Reset all keys
	}



	public function setNewUserNotLogged()
	{
		$this->session->set('new_user_not_logged', 1);

		/**
		 * But we also need to remember that in this session, the user was created !
		 * This is usefull to inform the user at any time, that his account was successfully created
		 */
		$this->session->set('new_user_created', 1);
	}



	public function getNewUserNotLogged()
	{
		return $this->session->get('new_user_not_logged');
	}



	public function resetNewUserNotLogged()
	{
		$this->session->reset('new_user_not_logged');

		# Notice : 'new_user_created' is still = 1
	}



	public function getNewUserCreated()
	{
		return $this->session->get('new_user_created');
	}



	/**
	 * Filter
	 */

	public function formIsValidated()
	{
		return $this->filter->validated();
	}



	public function formErrors( $alt = true )
	{
		if (!$this->filter->validated())
		{
			if (self::FORM_ERRORS_CUSTOMIZED)
			{
				$form_errors = $this->filter->errorMessageArray();

				if ($alt)
				{
					// Alternative Version
					$message = '<div id="comDonate_form-error"><div>'.LANG_COM_DONATE_FORM_ERROR_TITLE.'</div>';
					for ($i=0; $i<count($form_errors); $i++) {
						$message .= '<p>'.$form_errors[$i].'</p>';
					}
					$message .= '</div>'."\n";
					return $message;
				}
				else
				{
					// Classic version
					$message = '<strong>'.LANG_COM_DONATE_FORM_ERROR_TITLE.'</strong><br />';
					for ($i=0; $i<count($form_errors); $i++) {
						$message .= $form_errors[$i].'<br />';
					}
					return userMessage($message, 'error', '300');
				}
			}
			else
			{
				return $this->filter->errorMessage('400px');
			}
		}
	}



	/**
	 * Form parts
	 */

	public function setFormID( $form_id )
	{
		$this->form_id = $form_id;
	}



	public function allFormProcess()
	{
		$this->donateFormProcess();
		$this->donorFormProcess();

		if ($this->formIsValidated())
		{
			// This donation has passed the validation process !
			$this->updateFormPassed(1);
		}
	}



	//////////
	// Donate

	public function isDonateAvailable()
	{
		global $db;

		if ($db->selectCount('donate_designation, where: published=1')) {
			return true;
		} else {
			return false;
		}
	}



	private function donateFormProcess()
	{
		$amount_list = array();

		// List of handled designation
		if ($handle_designation = $this->session->get('handle_designation', array()))
		{
			$query_id = ' OR, where: id='.implode(' OR, where: id=', array_keys($handle_designation));
		} else {
			$query_id = '';
		}

		// Designations list of ID
		global $db;
		$designation = $db->select("donate_designation, *, design_order(asc), where: published=1$query_id");
		for ($i=0; $i<count($designation); $i++)
		{
			$design_id 		= $designation[$i]['id'];
			$design_title 	= $designation[$i]['title'];

			// Skip fixed amount when unchecked
			if ($this->filter->requestValue('fixed_amount_'.$design_id)->get() && !$this->filter->requestValue('check_amount_'.$design_id)->get()) {
				continue;
			}

			if ( $current_amount = $this->filter->requestValue('amount_'.$design_id)->get() )
			{
				$money = new money($this->config['currency_code'], $this->config['amount_min']);
				if (!$money->setAmountUnits($current_amount))
				{
					$this->filter->set(false, 'amount_'.$design_id)->getError($design_title.' : '.$money->invalidAmountUnitsMessage());
				}
				elseif (!$money->expectedAmount())
				{
					$this->filter->set(false, 'amount_'.$design_id)->getError($design_title.' : '.$money->unexpectedAmountMessage());
				}
				else {
					$amount_list[$design_id] = $money->getAmount();
				}
			}
		}

		// Always delete previous recording
		if ($donate_id = $this->sessionGetDonateID())
		{
			$db->delete("donate_details; where: donate_id=$donate_id");
		}

		if ($this->filter->validated())
		{
			if (!count($amount_list))
			{
				$this->filter->set(false)->getError(LANG_COM_DONATE_FORM_ERROR_AMOUNT_NULL);
			}
			else
			{
				// Others fields
				$currency_code 	= $this->config['currency_code'];

				// 'donate' table & session variable
				$this->sessionBegin();
				$donate_id = $this->sessionGetDonateID();

				// 'donate_details' table
				reset($amount_list);
				foreach($amount_list as $design_id => $amount)
				{
					$db->insert("donate_details; NULL,$design_id,$amount,$currency_code,$donate_id");
				}
			}
		}
	}



	public function donateForm( $tmpl_name = 'tmpl_form_donate.html', $tmpl_wrapper_name = 'tmpl_form_donate_wrapper.html' )
	{
		// Special case : no designation available for now
		if (!$this->isDonateAvailable()) {
			return '';
		}

		global $db;

		// Form
		$form = new formManager();
		$form->setForm('post', $this->form_id);

		// Currency config
		$currency_name = money::currencyCodeOptionsPlural();
		$currency_name = mb_strtolower($currency_name[$this->config['currency_code']]);

		// List of current recorded designations and amounts
		$amount_per_design = array();
		if ($donate_id = $this->sessionGetDonateID())
		{
			$donate_details = $db->select("donate_details, designation_id,amount, where: donate_id=$donate_id, join: donate_id>; donate, join: <id"); # FIXME : la jointure semble superflue !?!
			for ($i=0; $i<count($donate_details); $i++)
			{
				$money = new money($this->config['currency_code'], $this->config['amount_min']);
				$money->setAmount($donate_details[$i]['amount']);
				$amount_per_design[$donate_details[$i]['designation_id']] = $money->getAmountUnits();
			}
		}

		$query_id = $query_published = ''; # init

		// List of handled designation
		if ($handle_designation = $this->session->get('handle_designation', array()))
		{
			$query_id = ' OR, where: id='.implode(' OR, where: id=', array_keys($handle_designation));
		}

		// Display only the requested designation
		if ($get_design_id = $this->filter->requestValue(self::GET_DESIGN_ID, 'get')->getInteger(0))
		{
			if (!$handle_designation || !in_array($get_design_id, array_keys($handle_designation)))
			{
				$query_published = ', where: published=1 AND';
			}
			$designation = $db->select("donate_designation, *, design_order(asc)$query_published, where: id=$get_design_id");
		}

		// List of available designations
		(isset($designation) && $designation) or $designation = $db->select("donate_designation, *, design_order(asc), where: published=1$query_id");

		$html = '';
		for ($i=0; $i<count($designation); $i++)
		{
			$data = array();

			if (!isset($handle_designation[ $designation[$i]['id'] ]))
			{
				$current = $designation[$i];
			} else {
				$current = $handle_designation[ $designation[$i]['id'] ];
			}

			// Designation
			$data['title'] = $form->label('amount_'.$current['id'], $current['title']);

			if ($current['image'])
			{
				$src = WEBSITE_PATH.RESOURCE_PATH.$current['image'];
				$data['image'] = '<img src="'.$src.'" alt="'.$current['title'].'" />';
			}

			if ($current['comment'])
			{
				$data['comment'] = $current['comment'];
			}

			if ($current['link'])
			{
				$href = comMenu_rewrite($current['link'], false);
				$data['link'] = '<a href="'.$href.'">'.LANG_COM_DONATE_DESIGNATION_LINK_READ_MORE.'</a>';
			}

			// Amount
			if ($current['amount']) {
				$current_amount	= money::convertAmountCentsToUnits($current['amount']);
				$param			= ';update=0;readonly'; # Fixed amount

				// Is the checkbox should be checked ?
				if ( (!count($amount_per_design) && isset($handle_designation[ $designation[$i]['id'] ])) || isset($amount_per_design[ $current['id'] ]) )
				{
					$check = 1;
				} else {
					$check = 0;
				}
				$check_amount	= $form->hidden('fixed_amount_'.$current['id'], 1).$form->checkbox('check_amount_'.$current['id'], $check);
			}
			elseif (isset($amount_per_design[ $current['id'] ]))
			{
				$current_amount	= $amount_per_design[ $current['id'] ];
				$param			= ';update=0'; # Always take the default value
				$check_amount	= '';
			}
			else {
				$current_amount	= '0';
				$param			= '';
				$check_amount	= '';
			}
			$data['amount'] = $check_amount.$form->text('amount_'.$current['id'], $current_amount, '', '', "size=7$param").$currency_name.' &nbsp;';

			// Template
			$html .= $this->applyTemplate($tmpl_name, $data);
		}

		// Template wrapper
		return $this->applyTemplate($tmpl_wrapper_name, array('designations'=>$html));
	}



	/**
	 * Handle a designation to overwrite it's parameters to what you want...
	 * @param array $params The struture of this parameter is an array like this one : $db->selectOne('donate_designation');
	 */
	public function handleDesignation( $params )
	{
		if (!isset($params['id'])) {
			trigger_error('The key "id" is missing in the parameter $params in '.__METHOD__);
			return;
		}
		$designation_id = $params['id'];

		global $db;
		$designation = $db->selectOne("donate_designation, *, where: id=$designation_id");
		if (!$designation) {
			trigger_error('The requested designation "id" doesn\'t exists in '.__METHOD__);
			return;
		}

		// Session
		$this->session->init('handle_designation', array());
		$handle_designation = $this->session->returnVar('handle_designation');

		$handle_designation[$designation_id] =
			array(
				'id'			=> $designation_id,
				'title'			=> isset($params['title'		]) ? $params['title'		] : $designation['title'		],
				'comment'		=> isset($params['comment'		]) ? $params['comment'		] : $designation['comment'		],
				'link'			=> isset($params['link'			]) ? $params['link'			] : $designation['link'			],
				'image'			=> isset($params['image'		]) ? $params['image'		] : $designation['image'		],
				'amount'		=> isset($params['amount'		]) ? $params['amount'		] : $designation['amount'		],
				'design_order'	=> isset($params['design_order'	]) ? $params['design_order'	] : $designation['design_order'	], # FIXME - Known limitation : it's not working, because the order always come from the db !
				'published'		=> 1 # Handled designation is automaticcaly published !
			);

		$this->session->set('handle_designation', $handle_designation);
	}



	public function handleDesignationReset()
	{
		$this->session->set('handle_designation', array());
	}



	/////////
	// Donor

	// Config
	private function getDonorFields()
	{
		// Fields selection of 'user_info' table
		return
			array(
				'last_name',
				'first_name',
				'adress_1',
				'city',
				'state',
				'country',
				'zip',
				'phone_1'
			);
	}

	private function getDonorRequiredFeilds()
	{
		return
			array(
				'last_name',
				'first_name',
				'adress_1',
				'city',
				#'state',		// Optional field
				#'country',		// Optional field
				'zip'#,
				#'phone_1'		// Optional field
			);
	}
	// end of: Config



	public function getDonorHeader()
	{
		$header = array();

		// Fields alias from the com_user component
		$fields_alias = comUser_getFieldsAlias();

		$donor_fields = $this->getDonorFields();
		for ($i=0; $i<count($donor_fields); $i++)
		{
			$header[] = $fields_alias[$donor_fields[$i]];
		}

		return $header;
	}



	public function dbSetDonor( $donor )
	{
		$donate_id = $this->sessionGetDonateID();

		if (!$donate_id) {
			trigger_error('you can not call the '.__METHOD__.' method until you have a valid $donate_id');
			return false;
		}

		global $db;
		return $db->update('donate; contributor='.$db->str_encode($this->getContributorQuery($donor))."; where: id=$donate_id");
	}



	// Return an expected structure and cleaned values of contributor, using the $array parameter
	public function getContributorQuery( $array )
	{
		$donor = array();

		// Fill the donor
		$donor_fields = $this->getDonorFields();
		for ($i=0; $i<count($donor_fields); $i++)
		{
			$field = $donor_fields[$i];

			isset($array[$field]) ? $donor[$field] = $array[$field] : $donor[$field] = '';
		}

		// Clean the donor
		array_walk($donor, __CLASS__.'::cleanDonor');

		// Query field : contributor
		$contributor = implode(self::CONTRIBUTOR_SEP, $donor);
		return $contributor;
	}



	public static function cleanDonor( &$array_value, $array_key )
	{
		// Basic com_donate task : Replace the contributor separator
		$array_value = str_replace(self::CONTRIBUTOR_SEP, self::CONTRIBUTOR_SEP_REP, $array_value);

		// Advanced com_user task : replace all `NULL` values (this is even a tricky trick...)
		/*
		 * Explanation : in the `user_info` table, some fields such as `gender` are TINYINT (instead of VARCHAR).
		 * So, after processing the user form, an empty input will be interpreted like this : NULL (instead of an empty string).
		 * The reason is that the user form process is designed to prepare a query ready to database insertion.
		 *
		 * So, we assume that the string 'NULL' is always come from the user form process, and need to be replaced by ''.
		 */
		$array_value === 'NULL' ? $array_value = '' : '';
	}



	// Return the contributor of the current session, if set!
	public function dbGetDonor()
	{
		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			$contributor = $db->selectOne("donate, contributor, where: id=$donate_id", 'contributor');
		} else {
			$contributor = '';
		}

		$donor = $this->getDonorFromContributor($contributor);
		return $donor;
	}



	/**
	 * $contributor : is the field of donate table
	 * $donor : is the $contributor transformed into an array
	 */
	public function getDonorFromContributor( $contributor )
	{
		$donor = array();

		// Init
		$donor_fields = $this->getDonorFields();
		for ($i=0; $i<count($donor_fields); $i++) {
			$donor[$donor_fields[$i]] = '';
		}

		if ($contributor)
		{
			$contributor = explode(self::CONTRIBUTOR_SEP, $contributor);

			$i = 0;
			reset($donor);
			foreach($donor as $field => $value) {
				$donor[$field] = $contributor[$i++];
			}
		}

		return $donor;
	}



	// Format an Html output from the $donor parameter
	public function donorHTML( $donor, $tmpl_name = '' )
	{
		/**
		 * Security : this method is used by the autoresponse.php script, to send email to the administrator wich contain the full name and adress of the donor.
		 * This is a very important information. So, this time we check the avaibility of the template to prevent a template-path errror !
		 */
		$tmpl_name && !is_file(sitePath()."/components/com_donate/tmpl/$tmpl_name") ? $tmpl_name = '' : '';

		if (!$tmpl_name)
		{
			$html = '';

			// Fields alias from the com_user component
			$fields_alias = comUser_getFieldsAlias();

			reset($donor);
			foreach($donor as $field => $value)
			{
				if ($value)
				{
					$html .= "<span>{$fields_alias[$field]} :</span> $value<br />\n";
				}
			}
			$html = "<p id=\"comDonate_donorHTML\">$html</p>\n";

			return $html;
		}
		else
		{
			return $this->applyTemplate($tmpl_name, $donor);
		}
	}



	// Get quickly smart infos about the donor in array format
	public function donorParts( $donor )
	{
		// Full name
		if ($full_name = $this->mergeParts(@$donor['first_name'], @$donor['last_name'])) {
			$parts['full_name'] = $full_name;
		}

		// Full adress (sub-array). Like a french adress.
		if ($adress = @$donor['adress_1']) {
			$parts['full_adress'][] = $adress;
		}
		if ($adress = $this->mergeParts(@$donor['zip'], @$donor['city'])) {
			$parts['full_adress'][] = $adress;
		}
		if ($adress = $this->mergeParts(@$donor['state'], @$donor['country'])) {
			$parts['full_adress'][] = $adress;
		}

		if ($phone = @$donor['phone_1']) {
			$parts['phone'] = $phone;
		}

		return $parts;
	}



	private function mergeParts()
	{
		$args = func_get_args();

		$parts = array();
		for ($i=0; $i<count($args); $i++) {
			if ($args[$i]) {
				$parts[] = $args[$i];
			}
		}

		return implode(' ', $parts);
	}



	// Check the return of dbGetDonor() method  (or the getDonorFromContributor($contributor) method)
	private function currentDonorIsEmpty( $donor )
	{
		foreach($donor as $field => $value)
		{
			if ($value) {
				return false;
			}
		}
		return true;
	}



	public function getDonateUserID()
	{
		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			return $db->selectOne("donate, user_id, where: id=$donate_id", 'user_id');
		}
		return false;
	}



	private function donorFormProcess()
	{
		global $g_user_login;
		$user_id = $g_user_login->userID();

		if (!$user_id && !$this->getDonateUserID())
		{
			// anonymous/receipt ?
			$this->filter->requestValue('opt_radio')->get() == 'anonymous' ? $anonymous = true : $anonymous = false;

			// registration ?
			$this->filter->requestValue('opt_registration')->get() ? $registration = true : $registration = false;
		}
		else
		{
			$anonymous = false;
			$registration = false;
		}

		global $db;

		if ($anonymous)
		{
			if ($donate_id = $this->sessionGetDonateID())
			{
				// Always reset the contributor value
				$db->update('donate; contributor='.$db->str_encode('')."; where: id=$donate_id");
			}
		}
		else
		{
			// Contributor
			$user_form = $this->instanciateUserForm();
			$user_form->processForm();
			if ($user_data = $user_form->getUserData())
			{
				$contributor = $this->getContributorQuery($user_data);

				// 'donate' table & session variable
				$this->sessionBegin();
				$donate_id = $this->sessionGetDonateID();

				$db->update('donate; contributor='.$db->str_encode($contributor)."; where: id=$donate_id");

				// Update user account ?
				if ($user_id && $this->filter->requestValue('opt_update_user_account')->get())
				{
					$user_data['id'] = $user_id; # Add the user ID info

					$user_db = new comUser_db();
					$user_db->updateUser($user_data);
				}
			}
			else
			{
				$this->filter->set(false)->getError($user_form->getFormErrorMessage()); # Notice : the parameter of getError() can be an array !
			}
		}

		if ($registration)
		{
			if ($anonymous)
			{
				$this->filter->set(false)->getError(LANG_COM_DONATE_FORM_DONOR_ANONYM_NOT_COMPATIBLE_WITH_REGIST);
			}
			elseif (isset($donate_id) && !$this->getDonateUserID())
			{
				###
				### TODO - Tout ce bloc de code n'est pas assez mutualisé ! Il aurait du être généré par une méthode issue de la classe comUser_form ...
				###
				if (!$this->overwrite_com_user_config['registration_silent'])
				{
					// Username
					if ($username = $this->filter->requestValue('opt_registration_username')->getUserPass(1, LANG_COM_USER_CREATE_ACCOUNT_ERROR_USERNAME))
					{
						if ($db->select('user, id, where: username='.$db->str_encode($username)))
						{
							$this->filter->set(false, 'opt_registration_username')->getError(LANG_COM_USER_DUPLICATE_USERNAME, LANG_COM_USER_USERNAME); # No duplicate username allowed
						}
					}

					// Password
					$password = $this->filter->requestValue('opt_registration_password')->getUserPass(1, LANG_COM_USER_CREATE_ACCOUNT_ERROR_PASSWORD);
				}
				else
				{
					// Username & Password
					$username = comUser_form::findAvailableUsername($this->filter->requestValue('opt_registration_email')->get());
					$password = $user_form->randomString();
				}

				// Email
				if ($email = $this->filter->requestValue('opt_registration_email')->getEmail(1, LANG_COM_USER_CREATE_ACCOUNT_ERROR_EMAIL))
				{
					if (!$user_form->isAllowDuplicateEmail() && $db->select('user, id, where: email='.$db->str_encode($email)))
					{
						$duplicate_email = str_replace('{href}', comMenu_rewrite("com=user&page=forget&remember=$email"), LANG_COM_USER_DUPLICATE_EMAIL);
						$this->filter->set(false, 'opt_registration_email')->getError($duplicate_email, LANG_COM_USER_EMAIL); # No duplicate email allowed
					}
				}

				if ($this->filter->validated() && ($username && $password && $email))
				{
					// Complete the new user datas
					$user_data['username'	] = $username;
					$user_data['password'	] = sha1($password);
					$user_data['email'		] = $email;

					// Create a new user account
					$user_db = new comUser_db();
					$new_user_id = $user_db->insertUser($user_data);

					if ($new_user_id)
					{
						// Send Emails task (and autoLogin when possible)
						$user_form->sendEmailsAfterCreate($new_user_id, $password);

						// Update 'donate' table
						$db->update("donate; user_id=$new_user_id; where: id=$donate_id");
						$this->setNewUserNotLogged();
					}
				}
				###
				### TODO - Fin du bloc pas assez mutualisé ...
				###
			}
		}
	}



	public function donorForm( $tmpl_name = 'default/tmpl_donate_form_donor.html' ) # Carefull : the tmpl_name is relative to the base defined by the com_user component
	{
		$data = array();

		global $g_user_login;
		$user_id = $g_user_login->userID();

		// Form
		$form = new formManager();
		$form->setForm('post', $this->form_id);

		// Current donor from 'donate' table
		$current_donor = $this->dbGetDonor();

		// Current donor from 'user' table
		if ($this->currentDonorIsEmpty($current_donor) && $user_id)
		{
			$user_db = new comUser_db();
			$user = $user_db->selectUser($user_id);

			reset($current_donor);
			foreach($current_donor as $key => $value) {
				$current_donor[$key] = $user[$key];
			}
		}

		// Choice fields
		if (!$user_id && !$donate_user_id = $this->getDonateUserID())
		{
			if ($this->allow_anonymous)
			{
				// anonymous/receipt (radios buttons)
				$data['opt_radio_anonymous'			] = $form->radio('opt_radio', '[anonymous]'	, LANG_COM_DONATE_FORM_DONOR_ANONYMOUS	, 'radio_anonymous');
				$data['opt_radio_receipt'			] = $form->radio('opt_radio', 'receipt'		, LANG_COM_DONATE_FORM_DONOR_RECEIPT	, 'radio_receipt');
			} else {
				// receipt only (hidden field)
				$data['opt_radio_receipt'			] = $form->hidden('opt_radio', 'receipt').'<strong class="comDonate_form_donor_reciept">'.LANG_COM_DONATE_FORM_DONOR_RECEIPT.'</strong>';
			}
			$data['opt_radio_anonymous'	.'_tips'] = LANG_COM_DONATE_FORM_DONOR_ANONYMOUS_TIPS;
			$data['opt_radio_receipt'	.'_tips'] = LANG_COM_DONATE_FORM_DONOR_RECEIPT_TIPS;

			// Registration
			$data['opt_registration'			] = $form->checkbox('opt_registration'			, 0, LANG_COM_DONATE_FORM_DONOR_REGISTRATION);
			$data['opt_registration'	.'_tips'] = LANG_COM_DONATE_FORM_DONOR_REGISTRATION_TIPS;
			if (!$this->overwrite_com_user_config['registration_silent'])
			{
				$data['opt_registration_username'	] = $form->text	('opt_registration_username'	, '', LANG_COM_USER_USERNAME).LANG_COM_USER_FIELD_REQUIRED_STAR;
				$data['opt_registration_password'	] = $form->password('opt_registration_password'	, '', LANG_COM_USER_PASSWORD).LANG_COM_USER_FIELD_REQUIRED_STAR;
			}
			$data['opt_registration_email'   	] = $form->text	('opt_registration_email'   	, '', LANG_COM_USER_EMAIL).LANG_COM_USER_FIELD_REQUIRED_STAR;
		}
		else {
			$data['opt_update_user_account'   	] = $form->checkbox('opt_update_user_account', 1, LANG_COM_DONATE_FORM_DONOR_UPDATE_USER_ACCOUNT);
		}

		if ($this->getNewUserCreated()) {
			$data['opt_registration_completed'] = $this->registrationCompletedMessage();
		}

		// If there's at least one required field (or the registration block is available), add the 'required field message'
		if ( $this->donorFieldIsRequired() || (!$user_id && !$donate_user_id) )
		{
			$data['required_field_tips'] = comUser_form::requiredFieldsTips();
		}

		// Donor fields
		$user_form = $this->instanciateUserForm();
		$user_id ? $user_form->setUser($user_id) : ''; # Important : prepare the user form context !
		$data = array_merge($data, $user_form->getUserForm($this->form_id, $current_donor));

		// Template
		return $user_form->displayUserForm($data, $tmpl_name);
	}



	public function registrationCompletedMessage()
	{
		if ($donate_user_id = $this->getDonateUserID())
		{
			$user_db = new comUser_db();
			$user = $user_db->selectUser($donate_user_id);

			return '<p class="comDonate_info">'.str_replace('{email}', $user['email'], LANG_COM_DONATE_FORM_DONOR_REGISTRATION_COMPLETED)."</p>\n";
		}
	}



	private function instanciateUserForm()
	{
		$user_form = new comUser_form();

		// Overwrite some config ?
		$this->overwrite_com_user_config['registration_silent'	] !== false ? $user_form->overwriteConfig('registration_silent'		, $this->overwrite_com_user_config['registration_silent'	]) : '';
		$this->overwrite_com_user_config['allow_duplicate_email'] !== false ? $user_form->overwriteConfig('allow_duplicate_email'	, $this->overwrite_com_user_config['allow_duplicate_email'	]) : '';
		$this->overwrite_com_user_config['activation_method'	] !== false ? $user_form->overwriteConfig('activation_method'		, $this->overwrite_com_user_config['activation_method'		]) : '';

		// Set the requested fields wich needs to be displayed into the form
		$user_form->overwriteUserFieldsList( $this->getDonorFields(), $this->getDonorRequiredFeilds(), false );

		// Get the form errors in array format
		$user_form->setFormErrorsArrayFormat(true);

		// Enable Captcha feature (optional)
		#$user_form->enableCaptcha(true);

		return $user_form;
	}



	// Search after required fields...
	public function donorFieldIsRequired( $field = false )
	{
		$required_fields_list = $this->getDonorRequiredFeilds();

		// Return true if there's at least one required field
		if ($field === false)
		{
			count($required_fields_list) ? $required = true : $required = false;
		}
		// Return true if this particular field is required
		else
		{
			in_array($field, $required_fields_list) ? $required = true : $required = false;
		}
		return $required;
	}



	/**
	 * Summary
	 */

	public function updateFormPassed( $value )
	{
		if ($value !== 0 && $value !== 1) {
			trigger_error("Invalid \$value=$value parameter in ".__METHOD__);
			return;
		}

		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			$db->update("donate; form_passed=$value; where: id=$donate_id");
		}
	}



	public function isFormPassed()
	{
		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			return $db->selectOne("donate, form_passed, where: id=$donate_id", 'form_passed');
		}
		return false;
	}



	// This method is also usefull in backend to check any donatation, by using the optionals parameters
	public function donateSummary( $tmpl_name_contributor = '' )
	{
		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			$donate = $db->select("donate, recording_date,contributor,user_id, where: id=$donate_id AND, where: form_passed=1 AND, where: payment_id IS NULL, join: id>; donate_details, designation_id,amount,currency_code, join: <donate_id");

			$designation = $db->select('donate_designation, [id],title');

			if ($donate)
			{
				/**
				 * Commons datas
				 */
				$data['recording_date'	] = ucfirst(getTime($donate[0]['recording_date'], 'format=long'));
				if ($donate[0]['contributor']) {
					$data['contributor'	] = $this->donorHTML( $this->getDonorFromContributor($donate[0]['contributor']), $tmpl_name_contributor );
				}

				// ID and Email for registered user
				if ($donate[0]['user_id'])
				{
					$data['user_id'		] = $donate[0]['user_id'];

					$user_db = new comUser_db();
					$user = $user_db->selectUser($data['user_id']);
					$data['email'		] = $user['email'];
				}

				$currency_code = money::currencyCodeOptionsPlural();
				$currency_name = mb_strtolower($currency_code[$donate[0]['currency_code']]);

				/**
				 * Donate details
				 */
				$amount_total = 0;
				$donate_details = array();
				for ($i=0; $i<count($donate); $i++)
				{
					$title	= $designation[$donate[$i]['designation_id']]['title'];
					$amount	= $donate[$i]['amount'];
					$donate_details[] = array('title' => $title, 'amount' => money::convertAmountCentsToUnits($amount)." $currency_name");

					$amount_total += $amount;
				}
				$data['donate_details'	] = $donate_details;
				$data['amount_total'	] = money::convertAmountCentsToUnits($amount_total)." $currency_name";

				// Html output

				$recording_date = '<h3>'.LANG_COM_DONATE_SUMMARY_RECORDING_DATE."</h3>\n<p>{$data['recording_date']}</p>\n";

				$contributor = "<h3>".LANG_COM_DONATE_SUMMARY_CONTRIBUTOR."</h3>\n";
				if (isset($data['contributor']))
				{
					$contributor .= $data['contributor'].'<p class="comDonate_info">'.LANG_COM_DONATE_SUMMARY_RECEIPT_TIPS."</p>\n";
				} else {
					$contributor .= '<p class="comDonate_info">'.LANG_COM_DONATE_SUMMARY_ANONYMOUS_TIPS."</p>\n";
				}

				$email = '';
				if (isset($data['email']))
				{
					$email .= "<h3>".LANG_COM_DONATE_SUMMARY_EMAIL."</h3>\n<p>{$data['email']}</p>\n";

					if ($this->getNewUserCreated()) {
						$email .= '<p class="comDonate_info">'.LANG_COM_DONATE_SUMMARY_REGISTRATION_COMPLETED."</p>\n";
					}
				}

				$table = new tableManager($data['donate_details'], array(LANG_COM_DONATE_DETAILS_DESIGN_ID, LANG_COM_DONATE_DETAILS_AMOUNT));
				$donate_details = '<h3>'.LANG_COM_DONATE_SUMMARY_DONATE_DETAILS."</h3>\n".$table->html();

				$amount_total 	= '<p>'.LANG_COM_DONATE_SUMMARY_AMOUNT_TOTAL." : <b>{$data['amount_total']}</b></p>"; # Notice that the $amount_total in already included into the last line of $donate_details

				return "$recording_date{$contributor}$email{$donate_details}$amount_total";
			}
		}
	}



	private function applyTemplate( $tmpl_name, $data )
	{
		$template = new templateManager();
		$html = $template->setTmplPath(sitePath()."/components/com_donate/tmpl/$tmpl_name")->setReplacements($data)->process();

		return $html;
	}



	/**
	 * Checkout
	 */

	public function checkoutInfos( &$amount_cents, &$currency_code ) # Passed by reference
	{
		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			$donate = $db->select("donate, where: id=$donate_id AND, where: form_passed=1 AND, where: payment_id IS NULL, join: id>; donate_details, amount,currency_code, join: <donate_id");

			if ($donate)
			{
				$currency_code = $donate[0]['currency_code'];

				$amount_cents = 0;
				for ($i=0; $i<count($donate); $i++)
				{
					$amount_cents += $donate[$i]['amount'];

					// Simple security
					if ($donate[$i]['currency_code'] != $currency_code) {
						trigger_error('Error occured in '.__METHOD__.' : None unique currency_code !');
						exit;
					}
				}

				return true;
			}
		}

		return false;
	}



	// Only if checkoutInfos() method return true, then call this final method to set the payment ID.
	public function setPaymentID($payment_id)
	{
		if ($donate_id = $this->sessionGetDonateID())
		{
			global $db;
			if ($db->update("donate; payment_id=$payment_id; where: id=$donate_id"))
			{
				$this->sessionReset();
				return true;
			}
		}

		trigger_error('The $donate_id is not set. Only if '.__CLASS__.'::checkoutInfos() method return true, then call the method '.__CLASS__.'::setPaymentID() to set the payment ID.');
		return false;
	}



	/**
	 * Get infos about a donation
	 */

	public function checkDonation( $donate_id )
	{
		global $db;
		if ($donate = $db->select("donate, *, where: id=$donate_id"))
		{
			$check_donation = array();

			// Unique datas
			$check_donation['id'				] = $donate[0]['id'				];
			$check_donation['recording_date'	] = $donate[0]['recording_date'	];
			$check_donation['form_passed'		] = $donate[0]['form_passed'	];
			$check_donation['contributor'		] = $donate[0]['contributor'	];
			$check_donation['user_id'			] = $donate[0]['user_id'		];
			$check_donation['payment_id'		] = $donate[0]['payment_id'		];

			if ($donate_details = $db->select("donate_details, *, where: donate_id=$donate_id"))
			{
				// Unique data
				$check_donation['currency_code'] = $donate_details[0]['currency_code'];

				for ($i=0; $i<count($donate_details); $i++)
				{
					// Multi datas
					$check_donation['details'][] =
						array(
							'designation_id'	=> $donate_details[$i]['designation_id'	],
							'amount'			=> $donate_details[$i]['amount'			]
						);
				}
			}
			else
			{
				# Notice : in that case $check_donation['currency_code'] and $check_donation['details'] are undefined !
			}

			$this->check_donation = $check_donation;
			return true;
		}

		$this->check_donation = false;
		return false;
	}



	public function checkDonation_get( $field )
	{
		if (!is_array($this->check_donation)) {
			return false;
		}

		if (array_key_exists($field, $this->check_donation))
		{
			return $this->check_donation[$field];
		}
	}



	public function checkDonation_recordingDate()
	{
		if (!is_array($this->check_donation)) {
			return false;
		}

		$this->check_donation['recording_date'] ? $return = getTime($this->check_donation['recording_date']) : $return = '';

		return $return;
	}



	public function checkDonation_contributor( $tmpl_name = '' )
	{
		if (!is_array($this->check_donation)) {
			return false;
		}

		$this->check_donation['contributor'] ? $return = $this->donorHTML($this->getDonorFromContributor($this->check_donation['contributor']), $tmpl_name) : $return = '';

		return $return;
	}




	public function checkDonation_isPaymentValidated()
	{
		if (!is_array($this->check_donation)) {
			return false;
		}

		if ($this->check_donation['payment_id'])
		{
			$payment = new comPayment_();
			$payment_infos = $payment->checkPayment($this->check_donation['payment_id']);
			if (!$payment_infos['missing_id'])
			{
				return $payment_infos['validated'];
			} else {
				return NULL; # If the payment_id is defined into 'donate' table, but doesn't exists into 'payment' table (no ID record), then the method return NULL to inform this problem !
			}
		}

		return false;
	}



	public function checkDonation_amount( &$amount_total = false, &$currency_code = false )
	{
		if (!is_array($this->check_donation)) {
			return false;
		}

		if (isset($this->check_donation['details']))
		{
			// currency_code
			$currency_code = $this->check_donation['currency_code'];

			// currency_name
			$currency_code_options = money::currencyCodeOptionsPlural();
			$currency_name = mb_strtolower($currency_code_options[$currency_code]);

			// amount_total
			$amount_total = 0;
			for ($i=0; $i<count($this->check_donation['details']); $i++)
			{
				$amount_total += $this->check_donation['details'][$i]['amount'];
			}

			// Return HTML amount
			return money::convertAmountCentsToUnits($amount_total)." $currency_name";
		}

		return '';
	}



	public function checkDonation_details()
	{
		if (!is_array($this->check_donation)) {
			return false;
		}

		if (isset($this->check_donation['details']))
		{
			$html = '';

			// designation (list)
			global $db;
			$designation = $db->select('donate_designation, [id],title');

			// currency_name
			$currency_code_options = money::currencyCodeOptionsPlural();
			$currency_name = mb_strtolower($currency_code_options[$this->check_donation['currency_code']]);

			for ($i=0; $i<count($this->check_donation['details']); $i++)
			{
				$designation_id = $this->check_donation['details'][$i]['designation_id'];
				$amount 		= $this->check_donation['details'][$i]['amount'];

				$html .= "<span>{$designation[$designation_id]['title']} : </span>".money::convertAmountCentsToUnits($amount)." $currency_name<br />\n";
			}

			return $html;
		}

		return '';
	}



	// Use this the see what you can do with the checkoutDonation_xxx methods
	public function checkDonation_debug( $donate_id )
	{
		if ($this->checkDonation($donate_id))
		{
			$t = array();

			$t["Recording date"	] = $this->checkDonation_recordingDate();
			$t["Contributor"	] = $this->checkDonation_contributor('tmpl_donor_html.html');
			$t["Validated"		] = $this->checkDonation_isPaymentValidated() ? 'YES' : 'NO';
			$t["Amount total"	] = $this->checkDonation_amount($amount_total, $currency_code)." (amount_total=$amount_total, currency_code=$currency_code)";
			$t["Details"		] = $this->checkDonation_details();

			$table = new tableManager($t);
			echo $table->html(1);
		}
	}



	/**
	 * Manage invoice
	 */

	// Add new invoice in the 'donate_invoice' table. Create a file of the invoice on the FTP server
	public function checkInvoice( $donate_id )
	{
		global $db;

		// Find a record in the 'donate' table
		if ($donate = $db->selectOne("donate, *, where: id=$donate_id"))
		{
			// Obviously, there's no invoice for anonymous donation !
			if ($donate['contributor'])
			{
				// Donor details (formating)
				$donor = $this->donorParts( $this->getDonorFromContributor($donate['contributor']) );

				// Check that we have a payment_id for this donation
				if ($donate['payment_id'])
				{
					// Check now the payment status
					$payment = new comPayment_();
					$infos = $payment->checkPayment($donate['payment_id']);
					if (!$infos['missing_id'])
					{
						// Available payment details
						$transmission_date 	= $infos['transmission_date'];
						$amount 			= $infos['amount'];
						$currency_code 		= $infos['currency_code'];
						$payment_date 		= $infos['payment_date'];
						$validated 			= $infos['validated'];

						// Payment details (formating)
						$transmission_date 	= getTime($transmission_date, 'time=no');
						$amount = money::convertAmountCentsToUnits($amount);
						$currency_name = money::currencyCodeOptionsPlural();
						$currency_name = mb_strtolower($currency_name[$currency_code]);
						$payment_date = getTime($payment_date, 'time=no');

						// Check that the payment is validated
						if ($validated)
						{
							// Check for an existing invoice for this donation !
							$donate_invoice = $db->selectOne("donate_invoice, *, where: donate_id=$donate_id");

							// DB : Add a new invoice in the 'donate_invoice' table
							if (!$donate_invoice)
							{
								// First invoice ?
								if (!$db->selectCount('donate_invoice'))
								{
									$invoice_num = $db->selectOne('donate_config, invoice_num', 'invoice_num');
								} else {
									$invoice_num = 'NULL';
								}

								$db->insert("donate_invoice; $invoice_num, $donate_id, ".$db->str_encode(md5(rand())));
								$donate_invoice = $db->selectOne('donate_invoice, *, where: id='.$db->insertID());
							}

							// FTP : Create or renew the .html and .pdf versions of the invoice
							if ($donate_invoice)
							{
								$replacements = array(
									'site_url'			=>	siteUrl(),
									'title'				=>	LANG_COM_DONATE_INVOICE_TITLE,
									'recipient_name'	=>	$this->config['recipient_name'],
									'recipient_adress'	=>	nl2br($this->config['recipient_adress']), # HTML break line
									'donor_name'		=>	$donor['full_name'],
									'donor_adress'		=>	implode('<br />', $donor['full_adress']), # HTML glue
									'amount'			=>	money::formatAmountUnits($amount).' '.$currency_name,
									'amount_in_words'	=>	$this->nombre_en_lettre($amount),
									'date'				=>	$payment_date,
									'invoice'			=>	self::formatInvoiceID($donate_invoice['id'])
								);

								$ftp = new ftpManager(sitePath().self::INVOICE_PATH);
								$template = new templateManager();

								if (!$ftp->isFile($donate_invoice['filename'].'.html'))
								{
									$invoice_html = $template->setTmplPath(sitePath().self::INVOICE_TMPL_HTML)->setReplacements($replacements)->process();

									// Save HTML version
									$ftp->write($donate_invoice['filename'].'.html', $invoice_html);
								}

								if (!$ftp->isFile($donate_invoice['filename'].'.pdf'))
								{
									$invoice_pdf = $template->setTmplPath(sitePath().self::INVOICE_TMPL_PDF)->setReplacements($replacements)->process();

									// Save PDF version
									require_once(sitePath().'/plugins/php/html2pdf/html2pdf.class.php');
									$html2pdf = new HTML2PDF('L','A5','fr', true, 'UTF-8');
									$html2pdf->WriteHTML($invoice_pdf);
									$html2pdf->Output($ftp->getBase().$donate_invoice['filename'].'.pdf', 'F');
								}
							}
						}
					}
				}
			}
		}
	}



	public function nombre_en_lettre( $montant )
	{
		$fct_path = sitePath().'/plugins/php/nombre_en_lettre/fct_nombre_en_lettre_fr.php';

		if (is_file($fct_path))
		{
			require_once($fct_path);
			return nombre_en_lettre($montant);
		}

		return false;
	}



	public function getInvoiceID( $donate_id, &$invoice_path )
	{
		$invoice_path = array();

		global $db;
		if ($donate_invoice = $db->selectOne("donate_invoice, id,filename, where: donate_id=$donate_id"))
		{
			$filename = $donate_invoice['filename'];
			$ftp = new ftpManager(sitePath().self::INVOICE_PATH);
			$invoice_extension = array('html', 'pdf');

			foreach ($invoice_extension as $ext) {
				if ($ftp->isFile("$filename.$ext")) {
					$invoice_path[$ext] = self::INVOICE_PATH."$filename.$ext";
				}
			}

			return $donate_invoice['id'];
		}

		return false;
	}



	static public function formatInvoiceID( $invoice_id )
	{
		if ($invoice_id)
		{
			return sprintf('%05u', $invoice_id);
		} else {
			return '';
		}
	}



	public function getLinksFromInvoicePath( $invoice_path, $sep = ' &nbsp; ' )
	{
		$links = array();

		$img_dir = siteUrl().'/components/com_donate/images/';
		$img_html	= '<img src="'.$img_dir.'invoice-html.png" alt="HTML" />';
		$img_pdf	= '<img src="'.$img_dir.'invoice-pdf.png" alt="PDF" />';

		if (isset($invoice_path['pdf']))
		{
			$links[] = '<a href="'.siteUrl().$invoice_path['pdf'].'" title="'.LANG_COM_DONATE_INVOICE_PDF.'" class="external no-arrow">'.$img_pdf.'</a>';
		}

		if (isset($invoice_path['html']))
		{
			$links[] = '<a href="'.siteUrl().$invoice_path['html'].'" title="'.LANG_COM_DONATE_INVOICE_HTML.'" class="external no-arrow">'.$img_html.'</a>';
		}

		if (count($links))
		{
			return "\n<div class=\"comDonate_invoice-links\">".implode($sep, $links)."</div>\n";
		}
	}

}

?>