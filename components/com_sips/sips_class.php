<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


final class comPaymentSips_ extends comPaymentAbstract_
{
	/**
	 * WARNING: each extended class MUST define his own $method_alias
	 */
	protected	$method_alias 		= 'sips';

	protected	$amount_min_cents 	= 100;

	private		$config;	# Here the SIPS config



	public function __construct()
	{
		// Do never forget to call the parent __construct
		parent::__construct();

		// Config
		global $db;
		$config = $db->select('payment_sips_config, *');
		$this->config = $config[0];

		// Debug
		static $debug_once = false;
		if ((!$debug_once) && ($this->debug))
		{
			$table = new tableManager($this->config);
			echo '<div><span style="font-weight:bold;color:grey;">SIPS DEBUG : Config</span><br />'.$table->html(1).'</div>';
			$debug_once = true;
		}
	}



	public function customize_x( $customize_x )
	{
		$temp = array();
		foreach($customize_x as $key => $value)
		{
			switch($key)
			{
				// Use a template to display the payment-form
				case 'template':
					$temp[$key] = $value;
					break;
			}
		}

		if (count($temp)) {
			$this->customize_x = $temp;

			// Debug
			if ($this->debug) {
				$table = new tableManager($this->customize_x);
				echo '<div><span style="font-weight:bold;color:grey;">SIPS DEBUG : Required customization</span><br />'.$table->html(1).'</div>';
			}
		}
		else {
			// Error occured
			trigger_error($this->trig_err("unknown keys in <i>\$customize_x</i> parameter (type array) in <b>".__METHOD__."</b> method"), E_USER_WARNING);
		}
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



	public function getPayment_x_ID( $amount_cents, $currency_code, $payment_details = array() )
	{
		/**
		 * Parameters validation
		 */

		$error = false;

		// Amount & Currency
		if ((money::isAmount($amount_cents )) && (money::isCurrencyCode($currency_code)))
		{
			$this->amount_cents = $amount_cents;
			$this->currency_code = $currency_code;
		}
		else
		{
			trigger_error($this->trig_err("invalid <i>\$amount_cents=$amount_cents</i> and/or <i>\$currency_code=$currency_code</i> parameters in <b>".__METHOD__."</b> method"), E_USER_WARNING);
			$error = true;
		}

		// Payment details
		reset($payment_details);
		while (list($key, $value) = each($payment_details))
		{
			if (array_key_exists($key, $this->payment_details))
			{
				$this->payment_details[$key] = $value;
			} else {
				trigger_error($this->trig_err("invalid <i>key=$key</i> for \$payment_details parameter (type array) in <b>".__METHOD__."</b> method"), E_USER_WARNING);
				$error = true;
			}
		}

		if ($error) {
			return false; # Exit !
		}

		$currency_name = money::currencyCodeOptionsSingular();
		$currency_name = $currency_name[$this->currency_code];

		// Sips required minimum 100 cents
		if ($this->amount_cents < 100)
		{
			echo '<p>'.str_replace('{currency_name}', $currency_name, LANG_COM_PAYMENT_SIPS_MINIMUM_AMOUNT_NOT_REACHED).'</p>';
			return false;
		}

		// Is the requested currency available ?
		$currency_code_list = explode(',', $this->config['currency_code_list']);
		if (!in_array($this->currency_code, $currency_code_list))
		{
			echo '<p>'.str_replace('{currency_name}', $currency_name, LANG_COM_PAYMENT_SIPS_CURENCY_CODE_NOT_AVAILABLE).'</p>';
			return false;
		}

		/**
		 * Database update
		 */

		global $db;

		// First/New transaction ?
		if (!$db->selectCount('payment_sips'))
		{
			$result =
				$db->insert(
						'payment_sips; col: id, transmission_date, capture_mode,capture_day, amount,currency_code; '.
						$this->config['transaction_id_offset'].', '.time().', '.$db->str_encode($this->config['capture_mode']).','.$this->config['capture_day'].', '.$this->amount_cents.','.$this->currency_code
				);
		}
		else
		{
			$result =
				$db->insert(
					'payment_sips; col: id, transmission_date, capture_mode,capture_day, amount,currency_code; '.
					'NULL, '.time().', '.$db->str_encode($this->config['capture_mode']).','.$this->config['capture_day'].', '.$this->amount_cents.','.$this->currency_code
				);
		}
		if (!$result) {
			echo '<p>'.LANG_COM_PAYMENT_SIPS_RESQUEST_DB_FAILED.'</p>';
			return false;
		}

		// Here the id we should return at the end !
		$payment_x_id = $db->insertID();

		// But... is maximum transaction ID reached ?
		if ($payment_x_id > $this->getMaxTransactionID())
		{
			$db->delete("payment_sips; where: id=$payment_x_id"); # Remove from DB

			if ($this->debug)
			{
				trigger_error($this->trig_err("The maximum transaction_id has been REACHED: ID=".$this->getMaxTransactionID().".<br /><b>SIPS component is unable to generate new transaction_id from now!</b><br />This error occured in <b>".__METHOD__."</b> method"), E_USER_WARNING);
			} else {
				echo '<p style="color:red;">Maximum transaction_id has been reached.<br />Please contact the administrator of the system.</p>';

				## TODO: Send email to inform the administrator of this problem ...
			}

			return false;
		}

		/**
		 * Request form
		 */

		$transaction_id = $payment_x_id; # This is it !

		$call_request_result = $this->callRequest($transaction_id);

		if (!$call_request_result)
		{
			$db->delete("payment_sips; where: id=$payment_x_id");
			return false; # Remove from DB
		}

		/**
		 * Final return
		 */

		return $payment_x_id;
	}



	private function callRequest( $transaction_id )
	{
		$parm = "";

		// Config
		$parm = "$parm pathfile="			.$this->config['cgi_bin_path'].'/param/pathfile';

		$parm = "$parm merchant_id="		.$this->config['merchant_id'];
		$parm = "$parm merchant_country="	.$this->config['merchant_country'];

		$parm = "$parm transaction_id="		.$this->formatTransactionID($transaction_id);
		$parm = "$parm capture_mode="		.$this->config['capture_mode'];
		$parm = "$parm capture_day="		.$this->config['capture_day'];

		$parm = "$parm payment_means="		.$this->config['payment_means'];
		$parm = "$parm block_order="		.$this->config['block_order'];
		$parm = "$parm header_flag="		.$this->config['header_flag'];
		$parm = "$parm language="			.$this->config['language'];

		// Amount
		$parm = "$parm amount="				.$this->amount_cents;
		$parm = "$parm currency_code="		.$this->currency_code;

		// Url
		($this->payment_details['normal_return_url'] 		!= '') 	? 	$normal_return_url 		= $this->payment_details['normal_return_url'] 		:
		$normal_return_url 		= str_replace('&amp;', '&', comMenu_rewrite('com=sips&page=response')); # ampersand SIPS limitation

		($this->payment_details['cancel_return_url'] 		!= '') 	? 	$cancel_return_url 		= $this->payment_details['cancel_return_url'] 		:
		$cancel_return_url 		= str_replace('&amp;', '&', comMenu_rewrite('com=sips&page=response')); # ampersand SIPS limitation

		($this->payment_details['automatic_response_url'] 	!= '') 	? 	$automatic_response_url = $this->payment_details['automatic_response_url'] 	:
		$automatic_response_url = siteUrl().'/components/com_sips/autoresponse.php';

		$parm = "$parm normal_return_url=\""		.$normal_return_url			."\"";
		$parm = "$parm cancel_return_url=\""		.$cancel_return_url			."\"";
		$parm = "$parm automatic_response_url=\""	.$automatic_response_url	."\"";

		// User details
		global $g_user_login;
		if ($g_user_login->userID())
		{
			// User ID
			##
			## TODO - Vérifier dans la documentation de Elysnet :
			## Je crois qu'en cas "d'abonnement", le champ 'customer_id' est utilisé par l'API de SIPS.
			## Je n'ai pas bien saisi si cela pose alors un problème ou non, quant à l'emploi de ce champ ...
			##
			$parm = "$parm customer_id=".$g_user_login->userID();

			// User Email
			global $db;
			$user_email = $db->select('user, email, where: id='.$g_user_login->userID());
			$user_email = $user_email[0]['email'];
			if ($user_email) {
				$parm = "$parm customer_email=$user_email";
			}
		}

		// User IP
		$parm = "$parm customer_ip_address=".$_SERVER['REMOTE_ADDR'];

		// Caddie
		if ($this->payment_details['caddie'] != '')
		{
			$caddie = preg_replace('~(^(_)+|(_)+$)~', '', preg_replace('~[^\.a-zA-Z_0-9\-]~', '_', $this->payment_details['caddie']));
			$parm = "$parm caddie=\"$caddie\"";
		}

		// Request (path)
		$path_bin = $this->config['cgi_bin_path'].'/bin/request';

		// Debug
		if ($this->debug) {
			echo '<div id="comSips_debug"><b>SIPS DEBUG : $path_bin $parm</b><br />'.str_replace(' ', '<br />', "$path_bin $parm").'</div>';
		}

		// Request (bin)
		$exec = exec("$path_bin $parm");

		// Process result
		$resp_details = explode("!", $exec);
		$code 		= @$resp_details[1];
		$error 		= @$resp_details[2];
		$message 	= @$resp_details[3];

		// Request (bin) not founded
		if (( $code == "" ) && ( $error == "" ) )
		{
			if ($this->debug)
			{
				trigger_error($this->trig_err("unable to call <i>binary request</i> in <b>".__METHOD__."</b> method.<br />Possible reason:<br />- Invalid <i>\$path_bin=$path_bin<br />- No rights to read the file</i>"), E_USER_WARNING);
			} else {
				echo '<p style="color:red;">!!!</p>'; # In production : no details infos about the problem (just a sign to the administrator)
			}

			return false;
		}

		// API error
		else if ($code != 0)
		{
			if ($this->debug)
			{
				trigger_error($this->trig_err("<i>API</i> error in <b>".__METHOD__."</b> method:<br />"), E_USER_WARNING);
				echo $error; # The error message from Sips
			} else {
				echo '<p style="color:grey;">...</p>'; # In production : to prevent direct acces to this page
			}
		}

		// OK! Ready to display submit form
		else
		{
			// API error (if DEBUG mode activated in pathfile (cgi-bin))
			echo $error;

			// Amount + Currency HTML
			$amount_units = money::convertAmountCentsToUnits($this->amount_cents);
			$currency_name = money::currencyCodeOptionsPlural();
			$currency_name = $currency_name[$this->currency_code];

			// Submit form
			$data = array();
			$data['amount_units'	] = $amount_units;
			$data['currency_name'	] = $currency_name;
			$data['payment_form'	] = $message;

			if (!isset($this->customize_x['template']))
			{
				// Default view
				$html  = '';
				$html .= "\n\n<!-- SIPS REQUEST -->\n<div id=\"comSips_request\">\n";
				$html .= '<p id="comSips_request-amount">'.LANG_COM_PAYMENT_SIPS_REQUEST_AMOUNT.'<span>'.money::formatAmountUnits($data['amount_units']).'</span>'.
							'<span class="comSips_currency">'.mb_strtolower($data['currency_name']).'</span></p>';
				$html .= $data['payment_form'];
				$html .= "\n<div class=\"comSips_request\"></div></div>\n<!-- End of : SIPS REQUEST -->\n\n";
			}
			else
			{
				// Using a template view
				$template = new templateManager();
				$html = $template->setTmplPath(sitePath()."/components/com_sips/tmpl/".$this->customize_x['template'])->setReplacements($data)->process();

				if (!$html) {
					return false;
				}
			}

			echo $html;
			return true;
		}
	}



	private function getResponse( &$path_bin )
	{
		$response = array();

		// Message (DATA crypted variable)
		$message = 'message='.@$_POST['DATA'];

		/**
		 * ADVANCED DEBUG MODE
		 *
		 * If you need to debug callAutoResponse() method, do this :
		 *		- Use the regular callResponse() method (by returning to the website after finishing the payment on the SSL server)
		 * 		  Then show (html output) and copy the $_POST['DATA'] variable
		 *		- Force the $message to be always using this data (paste the copyed $_POST['DATA'])
		 *		- Go directly to the url of the autoresponse page and see what's appening (but activate before the debug_mode) !
		 */
		#############################################################################
		# BEGIN																		#
		# ------------------------------------------------------------------------- #
		# echo $_POST['DATA']; /*{copy_data}*/	# With callResponse() method		#
		# $fixed_data = {paste_data};			# With callResponse() method		#
		# $message = 'message='.$fixed_data;	# With callAutoResponse() method	#
		# ------------------------------------------------------------------------- #
		# END																		#
		#############################################################################

		// Pathfile
		$pathfile = "pathfile=".$this->config['cgi_bin_path'].'/param/pathfile';

		// Response (path)
		$path_bin = $this->config['cgi_bin_path'].'/bin/response';

		// Response (bin)
		$exec = exec("$path_bin $pathfile $message");

		// Process result
		$resp_details = explode ("!", $exec);

		$response[ 'code'				] 	= $resp_details [ 1  ]; # Important to get errors
		$response[ 'error'				] 	= $resp_details [ 2  ]; # Important to get errors
		$response[ 'merchant_id'		] 	= $resp_details [ 3  ];
		$response[ 'merchant_country'	] 	= $resp_details [ 4  ];
		$response[ 'amount'				] 	= $resp_details [ 5  ];
		$response[ 'transaction_id'		] 	= $resp_details [ 6  ]; # Important to update 'payment_sips' table
		$response[ 'payment_means'		] 	= $resp_details [ 7  ];
		$response[ 'transmission_date'	] 	= $resp_details [ 8  ];
		$response[ 'payment_time'		] 	= $resp_details [ 9  ];
		$response[ 'payment_date'		] 	= $resp_details [ 10 ];
		$response[ 'response_code'		] 	= $resp_details [ 11 ]; # If $response_code='00' then the payment is validated
		$response[ 'payment_certificate'] 	= $resp_details [ 12 ];
		$response[ 'authorisation_id'	] 	= $resp_details [ 13 ];
		$response[ 'currency_code'		] 	= $resp_details [ 14 ];
		$response[ 'card_number'		] 	= $resp_details [ 15 ];
		$response[ 'cvv_flag'			] 	= $resp_details [ 16 ];
		$response[ 'cvv_response_code'	] 	= $resp_details [ 17 ];
		$response[ 'bank_response_code'	] 	= $resp_details [ 18 ];
		$response[ 'complementary_code'	] 	= $resp_details [ 19 ];
		$response[ 'complementary_info'	] 	= $resp_details [ 20 ];
		$response[ 'return_context'		] 	= $resp_details [ 21 ];
		$response[ 'caddie'				] 	= $resp_details [ 22 ];
		$response[ 'receipt_complement'	] 	= $resp_details [ 23 ];
		$response[ 'merchant_language'	] 	= $resp_details [ 24 ];
		$response[ 'language'			] 	= $resp_details [ 25 ];
		$response[ 'customer_id'		] 	= $resp_details [ 26 ];
		$response[ 'order_id'			] 	= $resp_details [ 27 ];
		$response[ 'customer_email'		] 	= $resp_details [ 28 ];
		$response[ 'customer_ip_address'] 	= $resp_details [ 29 ];
		$response[ 'capture_day'		] 	= $resp_details [ 30 ];
		$response[ 'capture_mode'		] 	= $resp_details [ 31 ];
		$response[ 'data'				] 	= $resp_details [ 32 ];

		return $response;
	}



	public function callResponse( $html_output = true )
	{
		// Final return
		$payment_x_id = false;

		// Get response
		$response = $this->getResponse($path_bin); # $path_bin is passed by reference

		// Response (bin) not founded
		if (($response['code'] == "") && ($response['error'] == ""))
		{
			if ($this->debug)
			{
				trigger_error($this->trig_err("unable to call <i>binary response</i> in <b>".__METHOD__."</b> method.<br />Possible reason:<br />- Invalid <i>\$path_bin=$path_bin<br />- No rights to read the file</i>"), E_USER_WARNING);
			} else {
				echo '<p style="color:red;">!!!</p>'; # In production : no details infos about the problem (just a sign to the administrator)
			}
		}

		// API error
		elseif ($response['code'] != 0)
		{
			if ($this->debug)
			{
				trigger_error($this->trig_err("<i>API</i> error in <b>".__METHOD__."</b> method:<br />"), E_USER_WARNING);
				echo $response['error']; # The error message from Sips
			} else {
				echo '<p style="color:grey;">...</p>'; # In production : to prevent direct access to this page
			}
		}

		// OK! Display final message
		else
		{
			// API error (if DEBUG mode activated in pathfile (cgi-bin))
			echo $response['error'];

			// Debug
			if ($this->debug)
			{
				$html  = '';

				reset($response);
				while (list($resp_field, $resp_value) = each($response)) {
					$html .= "<b>$resp_field :</b> $resp_value<br />";
				}

				echo '<div id="comSips_debug"><b>SIPS DEBUG : CALL RESPONSE</b><br /><br />'.$html.'</div>';
			}

			/**
			 * Payment summary (Html output for the user)
			 */

			$summary = array();

			$summary_html = '<h3>'.LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_TITLE.'</h3>';

			// payment_id (from 'payment' table)  OR  payment_x_id (from 'payment_sips' table)  ?
			$payment_x_id = $this->getOriginalTransactionID($response['transaction_id']);
			$payment_id   = $this->findPayment_idFromPayment_x_id($payment_x_id);
			$symbol = LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_REF_SYMBOL;
			if ($payment_id)
			{
				$summary[] = array( LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_PAYMENT_ID.$symbol, $payment_id);											# payment_id

				$ref_tips = LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_REF_TIPS_ID;
			} else {
				$summary[] = array( LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_PAYMENT_X_ID.$symbol, $this->method_alias.$response['transaction_id']);		# payment_x_id

				$ref_tips = LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_REF_TIPS_X_ID;
			}

			$currency_name = money::currencyCodeOptionsPlural(); $currency_name = $currency_name[$response['currency_code']];
			$summary[] = array( LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_AMOUNT, money::convertAmountCentsToUnits($response['amount']).' '.$currency_name);

			$summary[] = array( LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_PAYMENT_MEANS, $response['payment_means']);
			$summary[] = array( LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_CARD_NUMBER  , str_replace('.', '<span style="color:grey;">xxxxxxxxxxxxx</span>', $response['card_number']));

			$yyyy 	= substr($response['payment_date'], 0,4);
			$mm 	= substr($response['payment_date'], 4,2);
			$dd 	= substr($response['payment_date'], 6,2);
			$hh 	= substr($response['payment_time'], 0,2);
			$mn 	= substr($response['payment_time'], 2,2);
			$summary[] = array( LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_DATE, "$dd/$mm/$yyyy - $hh:$mn");
			$summary_table = new tableManager($summary);
			$summary_html .= $summary_table->html();

			$summary_html .= '<p style="color:grey;">'.$symbol.$ref_tips.'</p>';
			// end of :summary


			// Inform User (if there's no payment_id then force the html_output (because this is your last chance to display something about the result !))
			if (($html_output) || (!$payment_id))
			{
				if ($response['response_code'] == '00')
				{
					echo '<h1>'.LANG_COM_PAYMENT_SIPS_RESPONSE_SUCCESS.'</h1>'.$summary_html;
				} else {
					echo '<h1>'.LANG_COM_PAYMENT_SIPS_RESPONSE_FAILED .'</h1>';
				}
			}
		}

		return $payment_x_id;
	}



	public function callAutoResponse()
	{
		// Final return
		$payment_x_id = false;

		// Get response
		$response = $this->getResponse($path_bin); # $path_bin is passed by reference

		// Logfile (content)
		$log_txt = '';

		// Response (bin) not founded
		if (($response['code'] == "") && ($response['error'] == ""))
		{
			if ($this->debug)
			{
				trigger_error($this->trig_err("unable to call <i>binary response</i> in <b>".__METHOD__."</b> method.<br />Possible reason:<br />- Invalid <i>\$path_bin=$path_bin<br />- No rights to read the file</i>"), E_USER_WARNING);
			} else {
				echo '<p style="color:red;">!!!</p>'; # In production : no details infos about the problem
			}
		}

		// API error
		elseif ( $response['code'] != 0 )
		{
			// Update logfile (content)
			$log_txt .= 	"Direct access to call_autoresponse occured !\n".
							"date: ".getTime()."\n".
							"customer_ip_address: ".$_SERVER['REMOTE_ADDR']."\n".
							"-------------------------------------------\n";

			if ($this->debug)
			{
				trigger_error($this->trig_err("<i>API</i> error in <b>".__METHOD__."</b> method:<br />"), E_USER_WARNING);
				echo $response['error']; # The error message from Sips
			}
			// For production : Prevent direct access (2 behaviours availables)
			else
			{
				// Use a Warning page !
				/*echo	'<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'."\n".
						'<html><head>'."\n".
						'<title>WARNING !</title>'."\n".
						'</head><body style="background-color:red;">'."\n".
						'<p style="color:white;font:bold 30px/40px Verdana;margin:100px 0;text-align:center;">WARNING !<br /><br />'."\n".
						'You should not be here !<br /><br />'."\n".
						'Your IP ADRESS has been recorded :<br />'."\n".
						$_SERVER['REMOTE_ADDR'].'</p>'."\n".
						'</body></html>';*/

				// Or use a Simulate '404 Not Found' page !
				header("Status: 404 File not found", false, 404);
				echo	'<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'."\n".
						'<html><head>'."\n".
						'<title>404 Not Found</title>'."\n".
						'</head><body>'."\n".
						'<h1>Not Found</h1>'."\n".
						'<p>The requested URL '.$_SERVER['REQUEST_URI'].' was not found on this server.</p>'."\n".
						'</body></html>';
			}
		}

		// OK! Ready to update database & logfile
		else
		{
			/**
			 * Update database
			 */

			global $db;

			// payment_date
			$mktime = time();

			// validated
			if ($response['response_code'] == '00')
			{
				$validated = 1;
				$query_validated = ', validated='.$validated;
			} else {
				$validated = 0;
				$query_validated = ''; # No query part !
			}

			// transaction_id
			$payment_x_id = $this->getOriginalTransactionID($response['transaction_id']);

			if (count($db->select("payment_sips, id, where: id=$payment_x_id")))
			{
				$result_db =
					$db->update(
							'payment_sips; '.
							'card_number='.$db->str_encode($response['card_number']).
							', payment_means='.$db->str_encode($response['payment_means']).
							", payment_date=$mktime".
							$query_validated.
							"; where: id=$payment_x_id"
					);

				if ($result_db) {
					$result_db_message_fp = 'OK';
				} else {
					$result_db_message_fp = 'FAILURE';
				}
			}
			else {
				$result_db_message_fp = 'UNABLE TO UPDATE (id='.$payment_x_id.' NOT FOUNDED into the index of \'payment_sips\' table)';
			}

			/**
			 * Update logfile (content)
			 */

			$log_txt .= "database_update : $result_db_message_fp\n"; # DB result

			// Find quickly where are located the not-validated payments into the logfile
			if (!$validated) {
				$response['response_code'] = $response['response_code'].' (NOT VALIDATED)';
			}

			reset($response);
			foreach($response as $resp_field => $resp_value)
			{
				$log_txt .= "$resp_field : $resp_value\n";
			}
			$log_txt .= "-------------------------------------------\n";
		}

		// Update logfile
		if ($log_txt)
		{
			$log_dir = $this->config['cgi_bin_path'].'/log/';
			$logfile = 'logfile.txt';

			$ftp = new ftpManager($log_dir);
			$ftp->write($logfile, $log_txt, true); # Append the data to the file

			// No logfile directory
			if ($this->debug && !$ftp->isDir()) {
				trigger_error($this->trig_err("invalid logfile directory = <i>$log_dir</i> in <b>".__METHOD__."</b> method"), E_USER_WARNING);
			}
		}

		return $payment_x_id;
	}



	static public function formatTransactionID( $transaction_id )
	{
		return sprintf('%06u', $transaction_id);				# Example : 58 => 000058
	}



	static public function getOriginalTransactionID( $transaction_id )
	{
		return preg_replace('~^(0)+~', '', $transaction_id);		# Example : 000058 => 58
	}



	static public function getMaxTransactionID()
	{
		return 999999;
	}



	public function checkPayment_x( $id )
	{
		// Structure of the return
		$infos = comPayment_::checkPaymentStructure();

		global $db;
		$payment_x = $db->select("payment_sips, transmission_date,amount,currency_code,payment_date,validated, where: id=$id");

		if (count($payment_x))
		{
			$infos =
				array(
					'missing_id' 		=> false, # OK!

					'transmission_date' => $payment_x[0]['transmission_date'],
					'amount' 			=> $payment_x[0]['amount'			],
					'currency_code' 	=> $payment_x[0]['currency_code'	],
					'payment_date' 		=> $payment_x[0]['payment_date'		],
					'validated' 		=> $payment_x[0]['validated'		]
				);
		}

		return $infos;
	}



	public function deletePayment_x( $id )
	{
		global $db;

		/**
		 * If the payment process was completed, there's no reason to delete such a record !
		 * Then, it's possible to delete a record, only before any response from the ssl-payment-server
		 */
		if ($db->selectCount("payment_sips, where: id=$id AND, where: payment_date IS NULL"))
		{
			$result = $db->delete("payment_sips; where: id=$id AND payment_date IS NULL");
			if ($result) {
				return true;
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


?>