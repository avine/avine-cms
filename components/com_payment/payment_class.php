<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



///////////////
// Class Money

class money
{
	private	$currency_code 			= false,
			$currency_name_plural 	= true; 	# Language: use plural (default) or singular for currency_name

	private	$amount_cents 			= false,
			$amount_min_cents 		= 0;



	public function __construct( $currency_code = false, $amount_min_cents = false, $currency_name_form = '???' )
	{
		$currency_code 		!== false ? $this->setCurrencyCode($currency_code) : '';
		$amount_min_cents 	!== false ? $this->setAmountMin($amount_min_cents) : '';

		$currency_name_form !== '???' ? $this->setCurrencyNameForm($currency_name_form) : ''; # 'plural' or 'singular'
	}



	/**
	 * Currency
	 */

	public function setCurrencyCode( $currency_code )
	{
		if (array_key_exists($currency_code, $this->currencyCodeOptions()))
		{
			$this->currency_code = $currency_code;
		} else {
			trigger_error($this->trig_err("invalid <i>\$currency_code=$currency_code</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
		}
	}



	public function currencyCodeOptions( $alt = false )
	{
		if ((!$alt && $this->currency_name_plural) || ($alt && !$this->currency_name_plural))
		{
			return $this->currencyCodeOptionsPlural();
		} else {
			return $this->currencyCodeOptionsSingular();
		}
	}



	static function currencyCodeOptionsPlural()
	{
		return
			array(
				978 => LANG_CLASS_MONEY_CURRENCIES_CODE_978,
				840 => LANG_CLASS_MONEY_CURRENCIES_CODE_840,
				826 => LANG_CLASS_MONEY_CURRENCIES_CODE_826
			);
	}



	static function currencyCodeOptionsSingular()
	{
		return
			array(
				978 => LANG_CLASS_MONEY_CURRENCY_CODE_978,
				840 => LANG_CLASS_MONEY_CURRENCY_CODE_840,
				826 => LANG_CLASS_MONEY_CURRENCY_CODE_826
			);
	}



	public function setCurrencyNameForm( $f )
	{
		if ($f === 'plural'  )
		{
			$this->currency_name_plural = true;
		}
		elseif ($f === 'singular')
		{
			$this->currency_name_plural = false;
		}
		else {
			trigger_error($this->trig_err("invalid <i>\$f=$f</i> parameter in <b>".__METHOD__."</b> method. Expected 'plural' or 'singular'"), E_USER_WARNING);
		}
	}



	static function isCurrencyCode( $currency_code )
	{
		if (array_key_exists($currency_code, money::currencyCodeOptionsPlural()))
		{
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Amount
	 */

	public function setAmountMin( $amount_min_cents )
	{
		if (formManager_filter::isInteger($amount_min_cents))
		{
			$this->amount_min_cents = preg_replace('~^(0)+~', '', $amount_min_cents);
		} else {
			trigger_error($this->trig_err("invalid <i>\$amount_min_cents=$amount_min_cents</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
		}
	}



	public function setAmount( $amount_cents )
	{
		if (formManager_filter::isInteger($amount_cents))
		{
			$this->amount_cents = preg_replace('~^(0)+~', '', $amount_cents);
		} else {
			trigger_error($this->trig_err("invalid <i>\$amount_cents=$amount_cents</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
		}
	}



	public function setAmountUnits( $amount_units = false )
	{
		if ($amount_units === false)
		{
			$this->amount_cents = $this->amount_min_cents; # Minimum amount
		}
		elseif ($this->isAmountUnits($amount_units))
		{
			$this->amount_cents = sprintf('%.2f', $amount_units) *100;
		}
		else {
			return false; # Notice: If the amount format is wrong then return false
		}

		return true;
	}



	static function invalidAmountUnitsMessage()
	{
		return LANG_CLASS_MONEY_INVALID_AMOUNT_UNITS;
	}



	static function isAmountUnits( &$amount_units )
	{
		$amount_units = trim($amount_units);
		if (!formManager_filter::isInteger($amount_units))
		{
			$amount_units = str_replace(',', '.', $amount_units);

			if (!formManager_filter::isReal($amount_units)) {
				return false;
			}
		}
		return true;
	}



	# If the formated amount < amount_min then return false
	public function expectedAmount()
	{
		if ($this->amount_cents !== false)
		{
			if ($this->amount_cents >= $this->amount_min_cents) {
				return true;
			} else {
				return false;
			}
		}
		else {
			trigger_error($this->trig_err('undefined <i>$amount_cents</i> property when <b>'.__METHOD__.'</b> method was called'), E_USER_WARNING);
		}
	}



	public function unexpectedAmountMessage()
	{
		// Amount_min
		$amount_min = $this->convertAmountCentsToUnits($this->amount_min_cents);

		// Currency_name
		$curency_name = '';
		if ($this->currency_code)
		{
			if ($this->amount_min_cents == 100)
			{
				$currencyCodeOptions = $this->currencyCodeOptionsSingular();
			} else {
				$currencyCodeOptions = $this->currencyCodeOptionsPlural();
			}
			$curency_name = ' '.$currencyCodeOptions[$this->currency_code];
		}

		return str_replace('{amount_min}', $amount_min.$curency_name, LANG_CLASS_MONEY_AMOUNT_MIN_NOT_REACHED);
	}



	static function isAmount( $amount_cents )
	{
		if (formManager_filter::isInteger($amount_cents))
		{
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Accessors
	 */

	public function getCurrencyCode()
	{
		if ($this->currency_code !== false)
		{
			return $this->currency_code;
		} else {
			trigger_error($this->trig_err('undefined <i>$currency_code</i> property when <b>'.__METHOD__.'</b> method was called'), E_USER_WARNING);
		}
	}



	public function getCurrencyName( $alt = false )
	{
		if ($this->currency_code)
		{
			$currencyCodeOptions = $this->currencyCodeOptions($alt);
			return $currencyCodeOptions[$this->currency_code];
		}
		else
		{
			trigger_error($this->trig_err('undefined <i>$currency_code</i> property when <b>'.__METHOD__.'</b> method was called'), E_USER_WARNING);
			return 'Undefined currency';
		}
	}



	public function getAmountMin()
	{
		return $this->amount_min_cents;
	}



	public function getAmountMinUnits( $precision = true )
	{
		return $this->convertAmountCentsToUnits($this->amount_min_cents, $precision);
	}



	public function getAmount()
	{
		return $this->amount_cents;
	}



	public function getAmountUnits( $precision = true )
	{
		if ($this->amount_cents !== false)
		{
			return $this->convertAmountCentsToUnits($this->amount_cents, $precision);
		} else {
			trigger_error($this->trig_err('undefined <i>$amount_cents</i> property when <b>'.__METHOD__.'</b> method was called'), E_USER_WARNING);
		}
	}



	/**
	 * Conversions
	 */

	static function convertAmountCentsToUnits( $amount, $precision = true )
	{
		$amount = trim($amount);
		if (!formManager_filter::isInteger($amount))
		{
			trigger_error(money::trig_err("invalid <i>\$amount=$amount</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
			return false;
		}

		if ($precision)
		{
			return sprintf('%.2f', ($amount /100));
		} else {
			return ($amount /100);
		}
	}



	static function convertAmountUnitsToCents( $amount )
	{
		$amount = trim($amount);
		if (!formManager_filter::isInteger($amount))
		{
			$amount = str_replace(',', '.', $amount);

			if (!formManager_filter::isReal($amount))
			{
				trigger_error(money::trig_err("invalid <i>\$amount=$amount</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
				return false;
			}
			else
			{
				$amount = sprintf('%.2f', $amount); # Precision
			}
		}

		return $amount *= 100; # Convertion (units=>cents)
	}



	// Example : The amount '12345.67' will become a formated string '12 345,67'
	static function formatAmountUnits( $amount )
	{
		$amount = explode('.', $amount);
		$amount_units = $amount[0];

		// Format units
		$strlen = strlen($amount_units);
		$intval = intval($strlen/3);
		$units = '';
		for ($i=1; $i<=$intval; $i++) {
			$strlen > 3*$i ? $start = -3*$i : $start = -$strlen;
			$units = substr($amount_units, $start, 3).($units ? ' '.$units : '');
		}
		if ($modulo = $strlen%3) {
			$units = substr($amount_units, 0, $modulo).($units ? ' '.$units : '');
		}

		// Format decimals
		isset($amount[1]) ? $decimals = ','.$amount[1] : $decimals = ''; # The  decimals separator is now ',' instead of the original '.'

		return $units.$decimals;
	}



	/**
	 * Others
	 */

	public function export( $debug = false )
	{
		$money = array();

		if (($this->amount_cents !== false) && ($this->currency_code !== false))
		{
			$money['amount_cents'		] = $this->amount_cents;
			$money['amount_units'		] = $this->convertAmountCentsToUnits($this->amount_cents);

			$money['amount_min_cents'	] = $this->amount_min_cents;
			$money['amount_min_units'	] = $this->convertAmountCentsToUnits($this->amount_min_cents);

			$money['currency_code'		] = $this->currency_code;

			$money['currency_name'		] = $this->getCurrencyName();
			$money['currency_name_alt'	] = $this->getCurrencyName(true);
		}
		else {
			trigger_error($this->trig_err('undefined <i>$amount_cents</i> and/or <i>currency_code</i> properties when <b>'.__METHOD__.'</b> method was called'), E_USER_WARNING);
		}

		if ($debug) {
			$table = new tableManager($money);
			echo '<span style="color:grey;font-weight:bold;">DEBUG : Class: money | method: export</span><br />'.$table->html(1);
		}

		return $money;
	}



	// Each extended class re-define this method (to get the right `__CLASS__` value)
	public static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000;background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}




/////////////////
// Class Payment

class comPayment_
{
	private	$config;

	private	$origin 			= ''; 			# origin field of payment table (examples: donation (for comDonate), shopping (for comShopping), ...)

	private	$amount_cents 		= false,
			$currency_code 		= false; 		# $amount_cents and currency_code will be transmitted to the specific payment_x class

	private	$payment_details 	= 				# Optionals infos about the transaction
				array(
					'normal_return_url'			=>	'',
					'cancel_return_url'			=>	'',
					'automatic_response_url'	=>	'',
					'caddie'					=>	''
				);

	private	$customize 			= array();		# Specifics customizations for the payments objects



	public function __construct( $origin = '' )
	{
		$this->origin = $origin;

		global $db;
		$config = $db->select('payment_config, *');
		$this->config = $config[0];

		// Debug
		/*static $debug_once = false;
		if ((!$debug_once) && ($this->config['debug']))
		{
			$table = new tableManager($this->config);
			echo '<div><span style="color:grey;font-weight:bold;">PAYMENT DEBUG : CONFIG</span><br />'.$table->html(1).'</div>';
			$debug_once = true;
		}*/
	}



	public function getConfig( $field )
	{
		if (array_key_exists($field, $this->config)) {
			return $this->config[$field];
		}

		// Error occured
		trigger_error($this->trig_err("invalid <i>\$field=$field</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
		return false;
	}



	/**
	 * Design for all comPayment_x components and allow them to know if debug mode is required
	 * But inside the comPayment_ class use instead: $this->getConfig('debug');
	 */
	static public function debugMode()
	{
		global $db;
		$debug = $db->select('payment_config, debug');

		if ($debug[0]['debug']) {
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Here a very versatile method wich allow to customize each comPaymentAbstract_ extended class in a different way
	 * For example, you can use it in comDonate component, and customize the request-pages in that case (comPayment_sips, comPayment_paypal, ...)
	 *
	 * To do this, here the structure of the $customize parameter:
	 *		$customize =
	 *			array(
	 *				'sips'				=> array( '{sips_key_1}'	=> '{sips_value_1}'		, '{sips_key_2}'	=> '{sips_value_2}'		, ... ),
	 *				'paypal'			=> array( '{paypal_key_1}'	=> '{paypal_value_1}'	, '{paypal_key_2}'	=> '{paypal_value_2}'	, ... ),
	 *
	 *				'{method_alias}' 	=> array( '{x_key_1}'		=> '{x_value_1}'		, '{x_key_2}'		=> '{x_value_2}'		, ... )
	 *			);
	 */
	public function customize( $method_alias, $array )
	{
		$this->customize[$method_alias] = $array;
	}



	// Set the payment details
	public function paymentDetails( $amount_cents, $currency_code, $payment_details = array() )
	{
		// Amount & Currency
		if ((money::isAmount($amount_cents )) && (money::isCurrencyCode($currency_code)))
		{
			$this->amount_cents = $amount_cents;
			$this->currency_code = $currency_code;
		}
		else {
			trigger_error($this->trig_err("invalid <i>\$amount_cents=$amount_cents</i> and/or <i>\$currency_code=$currency_code</i> parameters in <b>".__METHOD__."</b> method"), E_USER_WARNING);
		}

		// Payment details
		reset($payment_details);
		foreach($payment_details as $key => $value)
		{
			if (array_key_exists($key, $this->payment_details))
			{
				$this->payment_details[$key] = $value;
			} else {
				trigger_error($this->trig_err("invalid key=$key in <i>\$payment_details</i> parameter (type array) in <b>".__METHOD__."</b> method"), E_USER_WARNING);
			}
		}
	}



	/**
	 * Get payment request
	 */

	public function getPaymentID( &$data )
	{
		$data = array(); # Init

		if (($this->currency_code === false) || ($this->amount_cents === false))
		{
			trigger_error($this->trig_err("undefined <i>\$amount_cents</i> and/or <i>\$currency_code</i> properties when <b>".__METHOD__."</b> method was called"), E_USER_WARNING);
			return false;
		}

		$start_view = true;

		// Availables methods
		global $db;
		if (!$this->config['debug'])
		{
			$query_generic = " AND, where: alias!='generic'"; # Simple security
		} else {
			$query_generic = '';
		}
		$method = $db->select('payment_method, [id],alias,name, payment_order(asc), where: activated=1'.$query_generic);

		// Process
		$method_id = false;
		if (!count($method))
		{
			echo LANG_COM_PAYMENT_NO_METHOD_AVAILABLE;
			return false;
		}
		elseif (count($method) == 1)
		{
			$start_view = false;

			reset($method);
			list($id, $info) = each($method);

			$method_id 		= $id;
			$method_alias 	= $info['alias'];
		}
		elseif (formManager::isSubmitedForm('payment_method_', 'post'))
		{
			$filter = new formManager_filter();
			$posted_method_id = $filter->requestValue('id', 'post')->get();

			if (formManager_filter::isInteger($posted_method_id) && array_key_exists($posted_method_id, $method))
			{
				$start_view = false;

				$method_id 		= $posted_method_id;
				$method_alias 	= $method[$method_id]['alias'];
			} else {
				// Classic box error message
				$filter->set(false, 'id')->getError(LANG_COM_PAYMENT_NO_METHOD_SELECTED);
				$data['form_error'] = $filter->errorMessage();

				// (or) Simple string error message (use this, if you want to customize the error message, by using a template)
				#$data['form_error'] = LANG_COM_PAYMENT_NO_METHOD_SELECTED;
			}
		}

		// Ok, then let's go !
		if ($method_id)
		{
			// Instanciate payment object (Sips, Paypal, ...)
			$payment_obj = $this->getPaymentObj($method_alias);

			// Do we have some a specific customization for the current payment object ?
			isset($this->customize[$method_alias]) ? $payment_obj->customize_x($this->customize[$method_alias]) : '';

			// Now, insert a new record into 'payment_x' table and get his ID
			$payment_x_id = $payment_obj->getPayment_x_ID($this->amount_cents, $this->currency_code, $this->payment_details);

			// Finally, insert a new record into 'payment' table
			if ($payment_x_id)
			{
				// First/New transaction ?
				if (!$db->selectCount('payment'))
				{
					$query_payment_id = $this->config['payment_id_offset'];
				} else {
					$query_payment_id = 'NULL';
				}

				$result = $db->insert("payment; $query_payment_id, $method_id, $payment_x_id, ".$db->str_encode($this->origin));
				if ($result)
				{
					$payment_id = $db->insertID();
					return $payment_id; # Return the insert ID
				}
				else {
					echo '<p style="color:red;">'.LANG_COM_PAYMENT_DB_ERROR_OCCURED.'</p>';
					return false;
				}
			}
			else
			{
				return false; # Probably means : wait for  $payment_obj->getPayment_x_ID();  to do his own job...
			}
		}

		// Form
		if ($start_view)
		{
			$form = new formManager();
			$data['form_open'	] = $form->form('post', $form->reloadPage(), 'payment_method_');

			reset($method);
			foreach($method as $id => $info)
			{
				$data['methods'][] = $form->radio('id', $id, $info['name'], 'id_'.$id);
			}

			$data['submit'		] = $form->submit('submit', LANG_BUTTON_SUBMIT);
			$data['form_close'	] = $form->end();

			return false;
		}
	}



	public function displayPaymentMethodsForm( $data, $tmpl_name = false )
	{
		$html = '';

		if (!count($data)) {
			return $html;
		}

		if (!$tmpl_name)
		{
			// Default view
			for ($i=0; $i<count($data['methods']); $i++)
			{
				$html .= $data['methods'][$i].'<br />';
			}
			$html .= '<br />'.$data['submit'];

			$html = '<fieldset><legend>'.LANG_COM_PAYMENT_SELECT_METHOD.'</legend>'.$html.'</fieldset>';

			isset($data['form_error']) ? $html = $data['form_error'].$html : '';
		}
		else
		{
			// Using a template
			$template = new templateManager();
			$html = $template->setTmplPath(sitePath()."/components/com_payment/tmpl/$tmpl_name")->setReplacements($data)->process();
		}

		// Add automatically form_open and form_close
		$html = $data['form_open'].$html.$data['form_close'];
		unset($data['form_open']);
		unset($data['form_close']);

		return $html;
	}



	/**
	 * Get payment response/autoresponse
	 *
	 * Depends of each payment method, 
	 * we have to check the SSL-payment-server return ($_POST or $_GET), 
	 * and determine wich payment method it is !
	 *
	 * For example, Sips return a data like this : $_POST['DATA'];
	 *
	 * After we know the payment method, we process the response (or the autoresponse).
	 *
	 * This powerfull method allow you to load the SSL-payments-servers returns at any URL !
	 * Then, each component (like comDonate_) wich is using the payment component, can have his own response and autoresponse pages.
	 * You can customize the HTML output on the response page, or do some specifics DB updates on the autoresponse page.
	 */

	public function getResponsePaymentID( $normal = true, $html_output = true )
	{
		/**
		 *  Payment return detection
		 */
		$detected = false;

		// Try Sips
		if (isset($_POST['DATA']))
		{
			$payment_obj = new comPaymentSips_();
			$detected = true;
		}
		/*
		// Try Paypal								# TODO - NOT IMPLEMENTED #
		elseif (isset($_POST['...']))
		{
			$payment_obj = new comPaymentPaypal_();
			$detected = true;
		}
		*/
		// Try Generic (tests)
		elseif (isset($_GET['GENERIC']))
		{
			$payment_obj = new comPaymentGeneric_();
			$detected = true;
		}

		if (!$detected) {
			return false; 		# !! No payment-server return !!
		}

		/**
		 *  Get 'payment_x_id' (from 'payment_x' table)
		 */

		if ($normal)
		{
			$payment_x_id = $payment_obj->callResponse($html_output);
		} else {
			$payment_x_id = $payment_obj->callAutoResponse();
		}

		if (!$payment_x_id) {
			return false; 		# !! Invalid payment-server return !!
		}

		/**
		 *  Get 'payment_id' (from 'payment' table)
		 */

		$payment_id = $payment_obj->findPayment_idFromPayment_x_id($payment_x_id);

		if (!$payment_id) {
			return false; 		# !! payment_x_id exist but not payment_id (strange behaviour, unless the payment was generated directly from the payment_x component) !!
		}

		return $payment_id;
	}



	/**
	 *  Check a payment
	 */
	public function checkPayment( $payment_id )
	{
		// Structure of the return 
		$infos = $this->checkPaymentStructure();

		$payment_methods_alias = comPayment_::getPaymentMethodsAlias();

		global $db;
		$payment = $db->select("payment, method_id,payment_x_id, where: id=$payment_id");

		if (!count($payment)) {
			return $infos; # Always return the default structure
		}

		$method_id 		= $payment[0]['method_id'];
		$method_alias 	= $payment_methods_alias[$method_id];
		$payment_x_id 	= $payment[0]['payment_x_id'];

		if (($method_alias) && ($payment_x_id))
		{
			// Instanciate payment object (Sips, Paypal, ...)
			$payment_obj = $this->getPaymentObj($method_alias);

			// Here the infos !
			$infos = $payment_obj->checkPayment_x($payment_x_id);
		}

		return $infos;
	}



	static function checkPaymentStructure()
	{
		return
			array(
				'missing_id' 		=> true, # Default value :  payment_id  or  payment_x_id  not founded !

				'transmission_date' => false,
				'amount' 			=> false,
				'currency_code' 	=> false,
				'payment_date' 		=> false,
				'validated' 		=> false
			);
	}



	static function checkPaymentStructureHeader()
	{
		return
			array(
				LANG_COM_PAYMENT_CHECK_PAYMENT_MISSING_ID,

				LANG_COM_PAYMENT_CHECK_PAYMENT_TRANSMISSION_DATE,
				LANG_COM_PAYMENT_CHECK_PAYMENT_AMOUNT,
				LANG_COM_PAYMENT_CHECK_PAYMENT_CURRENCY_CODE,
				LANG_COM_PAYMENT_CHECK_PAYMENT_PAYMENT_DATE,
				LANG_COM_PAYMENT_CHECK_PAYMENT_VALIDATED
			);  	
	}



	static function getPaymentMethodsAlias( $method_id = NULL )
	{
		static $payment_methods_alias = array();

		/**
		 *  If you need to check a lot of payments, 
		 *  it's better to get once the $payment_methods as a static variable,
		 *  It's reduce the number of querys !
		 */
		if (!count($payment_methods_alias))
		{
			global $db;
			$payment_methods = $db->select("payment_method, id,alias");

			for ($i=0; $i<count($payment_methods); $i++)
			{
				$payment_methods_alias[ $payment_methods[$i]['id'] ] = $payment_methods[$i]['alias'];
			}
		}

		if (isset($method_id))
		{
			if ( array_key_exists($method_id, $payment_methods_alias) )
			{
				return $payment_methods_alias[$method_id];		# Return the requested alias (type string)
			} else {
				trigger_error(comPayment_::trig_err("invalid <i>\$method_id=$method_id</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
				return NULL;
			}
		}

		return $payment_methods_alias;							# Return all alias (type array)
	}



	static function getPayment_x_ref( $payment_id )
	{
		global $db;
		$payment = $db->selectOne("payment, method_id,payment_x_id, where: id=$payment_id");

		$method_alias = comPayment_::getPaymentMethodsAlias($payment['method_id']);
		$payment_x_id = $payment['payment_x_id'];

		return $method_alias.$payment_x_id;
	}



	// Return the payment_x class (defined by method_alias)
	public function getPaymentObj( $method_alias )
	{
		// Each payment method must be included into this switch
		switch($method_alias)
		{
			/* Add a new method...
			case '{new_alias}':
				$payment_obj = new comPayment{new_alias}_();
				break;
			*/

			case 'sips':
				$payment_obj = new comPaymentSips_();
				break;

			case 'paypal':
				$payment_obj = new comPaymentPaypal_();
				break;

			case 'generic':
				$payment_obj = new comPaymentGeneric_();
				break;

			// Error
			default :
				trigger_error($this->trig_err("invalid <i>\$method_alias=$method_alias</i> parameter in <b>".__METHOD__."</b> method"), E_USER_WARNING);
				exit; # Critcal error
				break; 
		}

		return $payment_obj;
	}



	/**
	 *  Here the all-in-one method to check payments
	 *
	 *  Instead of checking one single payment using the checkPayment() method,
	 *  you can now have access to all thoses informations in a temporary table.
	 *
	 *  The table structure is almost the same as the return of the checkPayment() method.
	 *  Except that the table include one more field wich is : payment_id
	 *  (In the checkPayment($payment_id) method, the payment_id informations is the parameter of the method).
	 */
	function createPaymentsTemporaryTable()
	{
		global $db;

		$query = "CREATE TEMPORARY TABLE IF NOT EXISTS {table_prefix}payment_temp
				  (
					payment_id			INT(11)			NOT NULL PRIMARY KEY,
					missing_id			TINYINT(4)		NOT NULL DEFAULT 0,

					transmission_date	INT(11),
					amount				INT(11),
					currency_code		INT(11),

					payment_date		INT(11),
					validated			TINYINT(4)		NOT NULL DEFAULT 0

				  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";

		$result = $db->sendMysqlQuery($query);

		// Delete all records (if the table already exists)
		$db->delete('payment_temp');

		$payment_list = $db->select('payment, method_id(asc), id');
		for ($i=0; $i<count($payment_list); $i++)
		{
			$infos = $this->checkPayment($payment_list[$i]['id']);

			// payment_id
			$col = 'payment_id';
			$value = $payment_list[$i]['id'];

			// missing_id
			$col .= ',missing_id';
			$infos['missing_id'] ? $value .= ',1' : $value .= ',0';

			// Payment details
			if ($infos['transmission_date'])
			{
				$col 	.= ',transmission_date';
				$value 	.= ','.$infos['transmission_date'];
			}
			if ($infos['amount'])
			{
				$col 	.= ',amount';
				$value 	.= ','.$infos['amount'];
			}
			if ($infos['currency_code'])
			{
				$col 	.= ',currency_code';
				$value 	.= ','.$infos['currency_code'];
			}
			if ($infos['payment_date'])
			{
				$col 	.= ',payment_date';
				$value 	.= ','.$infos['payment_date'];
			}
			if ($infos['validated'])
			{
				$col 	.= ',validated';
				$value 	.= ',1';
			}

			$db->insert("payment_temp; col: $col; $value");
		}
	}



	function createPaymentsTemporaryTableHeader()
	{
		return
			array(
				LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_MISSING_ID,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_TRANSMISSION_DATE,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_AMOUNT,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_CURRENCY_CODE,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_PAYMENT_DATE,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_VALIDATED
			);
	}



	// Delete a 'payment' and 'payment_x' record
	function deletePayment( $payment_id )
	{
		global $db;

		// select from 'payment' table
		$payment = $db->select("payment, method_id,payment_x_id, where: id=$payment_id");
		if (count($payment))
		{
			$method_id 	= $payment[0]['method_id'];
			$payment_x_id = $payment[0]['payment_x_id'];

			$method_alias = $db->select("payment_method, alias, where: id=$method_id");
			if (count($method_alias))
			{
				$method_alias = $method_alias[0]['alias'];

				// Instanciate payment object (Sips, Paypal, ...)
				$payment_obj = $this->getPaymentObj($method_alias);

				// First, delete 'payment_x' record
				$result = $payment_obj->deletePayment_x($payment_x_id);

				if ($result)
				{
					// Delete now 'payment' record
					$result = $db->delete("payment; where: id=$payment_id");
					if ($result) {
						return true; # Ok, all done !
					}
				}
			}
		}
		return false;
	}



	// Each extended class re-define this method (to get the right `__CLASS__` value)
	public static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000;background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}




//////////////////////////
// Abstract class Payment 

abstract class comPaymentAbstract_
{
	protected	$method_alias		= '';					# MUST BE the same as defined in 'alias' field of 'payment_method' table
	protected	$amount_min_cents	= 0;					# Minimum transaction amount (required by the payment method specifications)

	protected	$amount_cents		= false;				# Amount of the current transaction
	protected	$currency_code		= false;				# currency_code of the current transaction

	protected	$payment_details 	=
					array(									# Optionals infos about the transaction, the returns URLs
						'normal_return_url'			=>	'',
						'cancel_return_url'			=>	'',
						'automatic_response_url'	=>	'',
						'caddie'					=>	''
					);

	private		$customize_x = NULL;						# Specific customization of the current extended payment object

	public		$debug;										# is debug mode activated in the global comPayment_ component ?



	public function __construct()
	{
		// Debug mode activated in the global comPayment_ component
		comPayment_::debugMode() ? $this->debug = true : $this->debug = false;
	}



	// Alias of the current method
	public function getMethodAlias()
	{
		return $this->method_alias;
	}



	// Utilities
	public function findPayment_idFromPayment_x_id( $payment_x_id )
	{
		// method_id
		$method_id = $this->findMethod_id();

		if ($method_id)
		{
			// payment_id
			global $db;
			if ($payment_id = $db->selectOne("payment, id, where: method_id=$method_id AND, where: payment_x_id=$payment_x_id", 'id'))
			{
				return $payment_id;
			}
		}

		return false;
	}



	public function findMethod_id()
	{
		global $db;
		$method_id = $db->select('payment_method, id, where: alias='.$db->str_encode($this->getMethodAlias()));
		$method_id = $method_id[0]['id'];

		// Critical error
		if (!$method_id)
		{
			echo	'<p style="color:red;">Error occured in one of the extended classes of <b>comPaymentAbstract_()</b> class :'.
					'<br />Unable to find the <i>$method_id</i> variable when <b>findMethod_id()</b> method was called.'.
					'<br />This is a critical error !</p>';
			exit;
			return false;
		}

		return $method_id;
	}



	// Learn details about this method at: comPayment_::customize() method
	abstract public function customize_x( $customize_x );



	/**
	 * (1) Insert a new record into the specific 'payment_x' table
	 * (2) Generate the payment-form request
	 * If (1) & (2) success : return the inserted-ID (or false if failed)
	 */
	abstract public function getPayment_x_ID( $amount_cents, $currency_code, $payment_details = array() );



	// Process the SSL-payment-server response, and return the updated-ID (or false if failed)
	abstract public function callResponse( $html_output = true );



	// Process the SSL-payment-server automatic-response, and return the updated-ID (or false if failed)
	abstract public function callAutoResponse();



	// Get transaction status
	abstract public function checkPayment_x( $id );



	/**
	 * Delete a 'payment_x' record
	 *
	 * Usefull into getPayment_x_ID() method:
	 *		- If there's a problem to generate the payment-form request after the record into the specific 'payment_x' table was already inserted
	 *		- Then, simply delete this new record.
	 */
	abstract public function deletePayment_x( $id );

}



///////////////////////////////////
// Final class for Generic Payment

/**
 *  Use this class to test the 'payment' component hitself, without any 'payment_x' component specifications
 *  (for details see how work here the getPayment_x_ID() method)
 */

final class comPaymentGeneric_ extends comPaymentAbstract_
{
	/**
	 * WARNING: each extended class MUST define his own $method_alias
	 */
	protected	$method_alias = 'generic';



	public function __construct()
	{
		// Do never forget to call the parent __construct
		parent::__construct();
	}



	public function customize_x( $customize_x )
	{
		$this->customize_x = $customize_x;

		if ($this->debug)
		{
			echo '<div><span style="font-weight:bold;color:grey;">GENERIC PAYMENT DEBUG : required customization</span><br />';
			$table = new tableManager($customize_x);
			echo $table->html(1).'</div>';
		}
	}



	public function getPayment_x_ID( $amount_cents, $currency_code, $payment_details = array() )
	{
		# EXAMPLE
		// Simulate a payment_x_id : Find the last payment test in 'payment' table, and do a simple incrementation
		global $db;
		$payment_x_id = $db->selectOne('payment, payment_x_id(desc), where: method_id='.$this->findMethod_id().'; limit: 1', 'payment_x_id');

		if ($payment_x_id)
		{
			$new_payment_x_id = $payment_x_id +1;
		} else {
			$new_payment_x_id = 1;
		}
		# EXAMPLE

		// Simulate the payment-form request : simply go to the _response page with some $_GET[] query string
		echo	'<p><a href="'
				.comMenu_rewrite('com=payment&amp;page=response&amp;GENERIC=yes&amp;PAYMENT_X_ID='.$new_payment_x_id).
				'"> Checkout your payment now</a></p>';

		return $new_payment_x_id;
	}



	public function callResponse( $html_output = true )
	{
		# EXAMPLE
		// Return the updated payment_x_id
		$payment_x_id = false;

		// Here we simulate a server-response
		if (($this->debug) && (isset($_GET['GENERIC'])))
		{
			if ( isset($_GET['PAYMENT_X_ID']) && formManager_filter::isInteger($_GET['PAYMENT_X_ID']) )
			{
				$payment_x_id = $_GET['PAYMENT_X_ID'];

				$html  = '<h4>A payment using the "generic" method has been detected !</h4>';
				$html .= '<p><b>Payment details :</b>';
				$html .= '<br />The payment reference is : payment_x_id (id field of payment_x table) = '.$payment_x_id;

				// In this Generic class the payment is always NOT validated because we don't have a payment_x table
				if (false) {
					$html .= '<br />This payment is : validated !</p>';
				} else {
					$html .= '<br />This payment is : NOT validated !</p>';
				}
				echo $html;
			}
		}
		# EXAMPLE

		return $payment_x_id;
	}



	public function callAutoResponse()
	{
		# EXAMPLE
		// Return the updated payment_x_id
		$payment_x_id = false;

		// Here we simulate a server-response
		if (($this->debug) && ($_GET['GENERIC']))
		{
			if ( $_GET['PAYMENT_X_ID'] && formManager_filter::isInteger($_GET['PAYMENT_X_ID']) )
			{
				echo '<p>You are in the method :<br /><b>comPaymentGeneric_::callAutoResponse();</b></p>';

				// In this Generic class the payment is always NOT validated because we don't have a payment_x table
				if (false)
				{
					# Update here the DB, and set this payment validated
					echo '<p>The payment PAYMENT_X_ID= '.$_GET['PAYMENT_X_ID'].' is validated !</p>';
				} else {
					# Update here the DB, and set this payment NOT validated
					echo '<p>The payment PAYMENT_X_ID= '.$_GET['PAYMENT_X_ID'].' is NOT validated !</p>';
				}
			}
		}
		# EXAMPLE

		return $payment_x_id;
	}



	public function checkPayment_x( $id )
	{
		// Structure of the return 
		$infos = comPayment_::checkPaymentStructure();

		# EXAMPLE
		# Get the following infos about the payment_x where his id=$id from the database ...

		// Here we simulate there's no problem...
		$infos['missing_id'] = false;
		// We confirm the payment is not validated
		$infos['validated'] = false;
		# EXAMPLE

		return $infos;
	}



	public function deletePayment_x( $id )
	{
		# ...
	}

}




//////////////////////////////////
// Final class for Paypal Payment

final class comPaymentPaypal_ extends comPaymentAbstract_								# TODO - NOT IMPLEMENTED #
{
	/**
	 * WARNING: each extended class MUST define his own $method_alias
	 */
	protected $method_alias = 'paypal';



	public function __construct()
	{
		// Do never forget to call the parent __construct
		parent::__construct();
	}



	public function customize_x( $customize_x )
	{
		$this->customize_x = $customize_x;

		if ($this->debug)
		{
			echo '<div><span style="font-weight:bold;color:grey;">PAYPAL DEBUG : required customization</span><br />';
			$table = new tableManager($customize_x);
			echo $table->html(1).'</div>';
		}
	}



	public function getPayment_x_ID( $amount_cents, $currency_code, $payment_details = array() )
	{
		echo 'not available for now';
		exit;

		return false;
	}



	public function callResponse( $html_output = true )
	{
		$payment_x_id = false;

		return $payment_x_id;
	}



	public function callAutoResponse()
	{
		$payment_x_id = false;

		return $payment_x_id;
	}



	public function checkPayment_x( $id )
	{
		// Structure of the return 
		$infos = comPayment_::checkPaymentStructure();

		return $infos;
	}



	public function deletePayment_x( $id )
	{
		# ...
	}

}


?>