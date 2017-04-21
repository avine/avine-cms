<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Demo module configuration : default location of the '/cgi-bin' directory
$demo_module_cgi_basepath_default = $_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('sips_', 'post');
if ($submit)
{
	$submit_config	= $filter->requestValue('submit'		)->get();
	$upd_currency	= $filter->requestValue('upd_currency'	)->get();
	$upd_payment	= $filter->requestValue('upd_payment'	)->get();
	$demo_module	= $filter->requestValue('demo_module'	)->get();
} else {
	$submit_config	= false;
	$upd_currency	= false;
	$upd_payment	= false;
	$demo_module	= false;
}

$upd_currency_submit = formManager::isSubmitedForm('upd_currency_', 'post');
$upd_payment_submit  = formManager::isSubmitedForm('upd_payment_' , 'post');



if ($demo_module)
{
	$demo_module_select			= $filter->requestValue('demo_module_select')->get();
	$demo_module_cgi_basepath	= $filter->requestValue('demo_module_cgi_basepath')->getPath();

	if ($demo_module_cgi_basepath != $demo_module_cgi_basepath_default)
	{
		/* TODO - Autorize the installation of the demo module, in a specified directory...
		 * (For now, the 'demo_module_cgi_basepath' is restricted to 'readonly'. So, the directory is not editable) */ 	

		# TODO - Move the '/cgi-bin' directory to it's new location...
	}

	if (array_key_exists($demo_module_select, admin_comPaymentSips_demoModuleOptions()))
	{
		$result = false;

		switch($demo_module_select)
		{
			case 'sogenactif':
				$parmcom_bank_name	= 'sogenactif';
				$merchant_id		= '014213245611111';
				break;

			case 'elysnet':
				$parmcom_bank_name	= 'elysnet';
				$merchant_id		= '014102450311111';
				break;
		}
		$cgi_bin_path				= $demo_module_cgi_basepath."/cgi-bin/$parmcom_bank_name";

		// Check and customize the module files
		$ftp = new ftpManager($cgi_bin_path);
		if ($ftp->isDir())
		{
			// Check '/bin'
			if ($ftp->isFile('/bin/request') && $ftp->isFile('/bin/response'))
			{
				// Check '/param'
				if (
					$ftp->isFile('/param/pathfile') &&
					$ftp->isFile("/param/parmcom.$parmcom_bank_name") &&
					$ftp->isFile("/param/parmcom.$merchant_id") &&
					$ftp->isFile("/param/certif.fr.$merchant_id")
				) {
					// Customize 'pathfile'
					$pathfile = $ftp->read('/param/pathfile');
					$pathfile = searchAndReplace($pathfile, array('{WEBSITE_PATH}'=>WEBSITE_PATH, '{DOCUMENT_ROOT}'=>$_SERVER['DOCUMENT_ROOT']));
					if ($ftp->write('/param/pathfile', $pathfile))
					{
						$result = $db->update("payment_sips_config; cgi_bin_path='$cgi_bin_path', parmcom_bank_name='$parmcom_bank_name', merchant_id='$merchant_id'");
					}
				}
			}
		} # TODO - Il manque ici les messages d'erreurs éventuels pour chaque cas...
		admin_informResult($result);
		if ($result) {
			admin_message(str_replace('{bin_path_directory}', LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_BIN_PATH, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_TIPS), 'tips');
		}
	}
}



// Availables currency_code_list
if ($upd_currency_submit)
{
	$upd_currency_submit_validation = true;

	// Fields validation
	if ($posted_currency = formManager_filter::arrayOnly( $filter->requestValue('code_list')->getInteger(1, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_CURRENCY_NO_SELECTION) ))
	{
		$currency_code_list = '';
		for ($i=0; $i<count($posted_currency); $i++)
		{
			$currency_code_list .= $posted_currency[$i].',';
		}
		$currency_code_list = preg_replace('~,$~', '', $currency_code_list);
	}

	// Database Process
	if ($upd_currency_submit_validation = $filter->validated())
	{
		$result = $db->update("payment_sips_config; currency_code_list=".$db->str_encode($currency_code_list));
		admin_informResult($result);
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd_currency) || (($upd_currency_submit) && (!$upd_currency_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TITLE_UPD_CURRENCY.'</h2>';

	$html = '';

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_currency_');

	$upd_currency ? $posted_currency = formManager_filter::arrayOnly( $filter->requestName('currency_code_order_')->getInteger() ) : $posted_currency = false;
	$currency_code_list = formManager::selectOption(money::currencyCodeOptionsSingular(), $posted_currency);

	$html .= $form->select('code_list', $currency_code_list, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_CURRENCY_LEGEND.'<br />', '', 'multiple').'<br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();

	echo $html;
}



// Availables payment_means
if ($upd_payment_submit)
{
	$upd_payment_submit_validation = true;

	// Fields validation
	if ($posted_payment = formManager_filter::arrayOnly( $filter->requestValue('means')->getVar(1, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_PAYMENT_NO_SELECTION) ))
	{
		$payment_means = '';
		for ($i=0; $i<count($posted_payment); $i++)
		{
			$payment_means .= $posted_payment[$i].',1,';
		}
		$payment_means = preg_replace('~,$~', '', $payment_means);
	}

	// Database Process
	if ($upd_payment_submit_validation = $filter->validated())
	{
		$result = $db->update("payment_sips_config; payment_means=".$db->str_encode($payment_means));
		admin_informResult($result);
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd_payment) || (($upd_payment_submit) && (!$upd_payment_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TITLE_UPD_PAYMENT.'</h2>';

	$html = '';

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_payment_');

	$upd_payment ? $posted_payment = formManager_filter::arrayOnly( $filter->requestName('payment_means_block_')->getVar() ) : $posted_payment = false;
	$payment_means = formManager::selectOption(admin_comPaymentSips_paymentMeansOptions(), $posted_payment);

	$html .= $form->select('means', $payment_means, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_PAYMENT_LEGEND.'<br />', '', 'multiple').'<br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();

	echo $html;
}



// Update
if ($submit_config)
{
	$upd_submit_validation = true;


	// Fieldset : Merchant //

	// cgi_bin_path
	$cgi_bin_path = $filter->requestValue('cgi_bin_path')->getPath();
	if ($cgi_bin_path)
	{
		(strlen($cgi_bin_path) > 1) ? $cgi_bin_path = preg_replace('~(/)+$~', '', $cgi_bin_path) : '';

		!is_dir($cgi_bin_path) ? $filter->set(false, 'cgi_bin_path')->getError(LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_CGI_BIN_PATH) : '';
	}

	// parmcom_bank_name
	$parmcom_bank_name = $filter->requestValue('parmcom_bank_name')->get();

	// merchant_id
	$merchant_id = $filter->requestValue('merchant_id')->getInteger(1, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_MERCHANT_ID);

	// merchant_country
	$merchant_country = $filter->requestValue('merchant_country')->get();


	// Fieldset : Transaction //

	// transaction_id_offset
	if (!$db->selectCount('payment_sips'))
	{
		$transaction_id_offset = $filter->requestValue('transaction_id_offset')->getInteger(1, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_TRANSACTION_ID_OFFSET);

		($transaction_id_offset && ($transaction_id_offset > comPaymentSips_::getMaxTransactionID())) ? $filter->set(false, 'transaction_id_offset')->getError(LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET_TOO_HIGH) : '';

		$query_trans_id_offset = ', transaction_id_offset='.$transaction_id_offset;
	}
	else {
		$query_trans_id_offset = ''; # For high security reason, don't even touch the 'transaction_id_offset' field !
	}

	// capture_mode
	$capture_mode = $filter->requestValue('capture_mode')->get();

	// capture_day
	$capture_day = $filter->requestValue('capture_day')->getInteger(1, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_CAPTURE_DAY);


	// Fieldset : Payment //

	// currency_code_list
	$currency_code_list = '';
	$currency_position = array();
	$posted_currency = formManager_filter::arrayOnly( $filter->requestName('currency_code_order_')->getInteger(), false );
	for ($i=0; $i<count($posted_currency); $i++)
	{
		$position = $filter->requestValue('currency_code_order_'.$posted_currency[$i])->getInteger();
		$position === false ? $position = 0 : '';
		$currency_position[$posted_currency[$i]] = $position;
	}
	asort($currency_position);
	foreach($currency_position as $code => $position) {
		$currency_code_list .= $code.',';
	}
	$currency_code_list = preg_replace('~,$~', '', $currency_code_list);

	// payment_means
	$payment_means = '';
	$payment_block = array();
	$posted_payment = formManager_filter::arrayOnly( $filter->requestName('payment_means_block_')->getVar(), false );
	for ($i=0; $i<count($posted_payment); $i++)
	{
		$block = $filter->requestValue('payment_means_block_'.$posted_payment[$i])->getInteger();
		$payment_means .= $posted_payment[$i].','.$block.',';
	}
	$payment_means = preg_replace('~,$~', '', $payment_means);

	// header_flag
	$filter->requestValue('header_flag')->get() ? $header_flag = 'yes' : $header_flag = 'no';

	// ssl_first
	$filter->requestValue('ssl_first')->get() ? $block_order = '2,1,3,4,5,6,7,8,9' : $block_order = '1,2,3,4,5,6,7,8,9';

	// language
	$language = $filter->requestValue('language')->get();


	if ($upd_submit_validation = $filter->validated())
	{
		$result =
			$db->update(
					'payment_sips_config; '.
					'cgi_bin_path='				.$db->str_encode($cgi_bin_path).
					', parmcom_bank_name='		.$db->str_encode($parmcom_bank_name).

					', merchant_id='			.$db->str_encode($merchant_id).
					', merchant_country='		.$db->str_encode($merchant_country).

					$query_trans_id_offset.
					', capture_mode='			.$db->str_encode($capture_mode).
					', capture_day='			.$capture_day.

					', currency_code_list='		.$db->str_encode($currency_code_list).
					', payment_means='			.$db->str_encode($payment_means).
					', block_order='			.$db->str_encode($block_order).
					', header_flag='			.$db->str_encode($header_flag).
					', language='				.$db->str_encode($language)
			);

		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$config = $db->selectOne('payment_sips_config, *');

	// Form
	$form = new formManager($demo_module ? 0 : 1);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'sips_');



	// Fieldset : Merchant //
	$fieldset  = '';

	// Test demo module
	if (!$config['cgi_bin_path'])
	{
		$fieldset .= "\n<h3>".LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_TITLE_DEV."</h3><br />\n";
		$fieldset .= $form->select('demo_module_select', admin_comPaymentSips_demoModuleOptions(), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_SELECT.'<br />').'<br /><br />';
		$fieldset .= $form->text('demo_module_cgi_basepath', $demo_module_cgi_basepath_default, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_CGI_BASEPATH.'<br />', '', 'size=70;readonly').'<br /><br />';
		$fieldset .= $form->submit('demo_module', LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_SUBMIT).'<hr />';
		$fieldset .= "\n<h3>".LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_TITLE_PROD."</h3><br />\n";
	}

	// cgi_bin_path
	$fieldset .= $form->text('cgi_bin_path', $config['cgi_bin_path'], LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_BIN_PATH.'<br />', '', 'size=70');
	if (!$config['cgi_bin_path'])
	{
		$fieldset .= '<span style="color:red;">'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UNKNOWN.'</span>';
	}
	elseif (!file_exists($config['cgi_bin_path']))
	{
		$fieldset .= '<span style="color:red;">'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_NO_MORE_AVAILABLE.'</span>';
	}
	$fieldset .= '<br /><span style="color:grey;">(document_root : </span>'.$_SERVER['DOCUMENT_ROOT'].'<span style="color:grey;">)</span>';

	$fieldset .= '<br /><br />';

	// parmcom_bank_name
	$fieldset .= $form->text('parmcom_bank_name', $config['parmcom_bank_name'], LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PARMCOM_BANK_NAME.'<br />', '', 'size=17');
	if (!$config['parmcom_bank_name'])
	{
		$fieldset .= '<span style="color:red;">'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UNKNOWN.'</span>';
	}
	$fieldset .= '<br /><br />';

	// merchant_id
	$fieldset .= $form->text('merchant_id', $config['merchant_id'], LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_MERCHANT_ID.'<br />', '', 'size=17;maxlength=15');
	if (!$config['merchant_id'])
	{
		$fieldset .= '<span style="color:red;">'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UNKNOWN.'</span>';
	}
	$fieldset .= '<br /><br />';

	// merchant_country
	$fieldset .= $form->select('merchant_country', formManager::selectOption(admin_comPaymentSips_merchantCountryOptions(), $config['merchant_country']), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_MERCHANT_COUNTRY.'<br />');

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_FIELDSET_MERCHANT);



	// Fieldset : Transaction //
	$fieldset  = '';

	// transaction_id_offset
	/**
	 * The index of the FIRST transaction is : payment_x_id=transaction_id_offset
	 * This offset can be initialized ONLY before there's any 'payment_sips' table record
	 */
	if (!$db->selectCount('payment_sips'))
	{
		$fieldset .= $form->text('transaction_id_offset', comPaymentSips_::formatTransactionID($config['transaction_id_offset']), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET.'<br />', '', 'size=8;maxlength=6');
		$fieldset .= '<span style="color:red;">'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET_TIPS.'</span><br />';
	}
	else
	{
		$fieldset .= $form->text('transaction_id_offset', comPaymentSips_::formatTransactionID($config['transaction_id_offset']), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET.'<br />', '', 'size=8;maxlength=6;disabled=yes');
	}
	// Number of remaining transactions
	$next_index = $db->select('payment_sips, id(desc); limit: 1');
	if (count($next_index))
	{
		$next_index = $next_index[0]['id'] +1;
	} else {
		$next_index = $config['transaction_id_offset'];
	}
	$fieldset .= '<span style="color:grey;">'.str_replace('{number}', (comPaymentSips_::getMaxTransactionID() - $next_index), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_REMAINING).'</span><br />';
	$fieldset .= '<br />';

	// capture_mode
	$fieldset .= $form->select('capture_mode', formManager::selectOption(admin_comPaymentSips_captureModeOptions(), $config['capture_mode']), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_MODE.'<br />');
	$fieldset .= '<br /><br />';

	// capture_day
	$fieldset .= $form->text('capture_day', $config['capture_day'], LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_DAY.'<br />', '', 'size=4;maxlength=2');
	$fieldset .= '<br /><br />';

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_FIELDSET_TRANSACTION);



	// Fieldset : Payment //
	$fieldset  = '';

	// currency_code
	$fieldset .= '<h3>'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CURRENCY_CODE_LIST.$form->submit('upd_currency', LANG_ADMIN_BUTTON_UPDATE).'</h3>';
	$currency_info = array();
	$currency_code_options = money::currencyCodeOptionsSingular();
	$current_currencies = explode(',', $config['currency_code_list']);
	for ($i=0; $i<count($current_currencies); $i++)
	{
		$currency_info[$i]['name' ] = $currency_code_options[$current_currencies[$i]]; 
		$currency_info[$i]['order'] = $form->text('currency_code_order_'.$current_currencies[$i], (2*$i +1), '', '', 'size=2;maxlength=2;update=no'); 
	}
	$currency_info_obj = new tableManager($currency_info, array(LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CURRENCY_CODE_NAME, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CURRENCY_CODE_ORDER));
	$fieldset .= $currency_info_obj->html().'<br />';

	// payment_means
	$fieldset .= '<h3>'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS.$form->submit('upd_payment', LANG_ADMIN_BUTTON_UPDATE).'</h3>';
	$payment_means_options = admin_comPaymentSips_paymentMeansOptions();
	$current_payments = explode(',', $config['payment_means']);
	for ($i=0; $i<count($current_payments); $i++)
	{
		$payment_info[$i]['name' ] = $payment_means_options[$current_payments[$i]];
		$payment_info[$i]['block'] = $form->select('payment_means_block_'.$current_payments[$i], formManager::selectOption(admin_comPaymentSips_blockNumberOptions(), $current_payments[$i+1]), '', '', 'update=no');
		$i++;
	}
	$payment_info_obj = new tableManager($payment_info, array(LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_NAME, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_BLOCK));
	$fieldset .= $payment_info_obj->html().'<br />';

	// ssl_first
	$config['block_order'] == '2,1,3,4,5,6,7,8,9' ? $ssl_first = 1 : $ssl_first = 0;
	$fieldset .= $form->checkbox('ssl_first', $ssl_first, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_ORDER_SSL_FIRST).'<br />';

	// header_flag
	$config['header_flag'] == 'yes' ? $header_flag = 1 : $header_flag = 0;
	$fieldset .= $form->checkbox('header_flag', $header_flag, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_HEADER_FLAG).'<br /><br />';

	// language
	$fieldset .= $form->select('language', formManager::selectOption(admin_comPaymentSips_paymentLanguageOptions(), $config['language']), LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_LANGUAGE.'<br />');

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_FIELDSET_PAYMENT);


	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>