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

// Posted forms possibilities
$submit = formManager::isSubmitedForm('config_', 'post'); // (0)

// Update
if ($submit)
{
	$currency_code = $filter->requestValue('currency_code')->getInteger();
	if (!array_key_exists($currency_code, money::currencyCodeOptionsSingular()))
	{
		$filter->set(false, 'currency_code')->getError('Invalid data !', LANG_ADMIN_COM_DONATE_CONFIG_CURRENCY_CODE);
	}

	$amount_min = $filter->requestValue('amount_min')->get();
	if (money::isAmountUnits($amount_min))
	{
		$amount_min = money::convertAmountUnitsToCents($amount_min);
	} else {
		$filter->set(false, 'amount_min')->getError(LANG_ADMIN_COM_DONATE_CONFIG_ERROR_AMOUNT, LANG_ADMIN_COM_DONATE_CONFIG_AMOUNT_MIN);
	}

	if ($filter->requestValue('registration_silent')->get())
	{
		$registration_silent = 1;
	} else {
		$registration_silent = 0;
	}

	$accountant_email = $filter->requestValue('accountant_email')->getEmail(0, LANG_ADMIN_COM_DONATE_CONFIG_ERROR_ACCOUNTANT_EMAIL, LANG_ADMIN_COM_DONATE_CONFIG_ACCOUNTANT_EMAIL);

	$invoice_num = $filter->requestValue('invoice_num')->getInteger(0, LANG_ADMIN_COM_DONATE_CONFIG_ERROR_INVOICE_NUM, LANG_ADMIN_COM_DONATE_CONFIG_INVOICE_NUM);
	if ($invoice_num !== false && $invoice_num == 0)
	{
		$filter->set(false, 'invoice_num')->getError(LANG_ADMIN_COM_DONATE_CONFIG_ERROR_INVOICE_NUM, LANG_ADMIN_COM_DONATE_CONFIG_INVOICE_NUM);
	}

	$recipient_name		= strip_tags($filter->requestValue('recipient_name'		)->get());
	$recipient_adress	= strip_tags($filter->requestValue('recipient_adress'	)->get());

	if ($filter->validated())
	{
		$accountant_email	? $accountant_email	= $db->str_encode($accountant_email	) : $accountant_email	= 'NULL';
		$recipient_name		? $recipient_name	= $db->str_encode($recipient_name	) : $recipient_name		= 'NULL';
		$recipient_adress	? $recipient_adress	= $db->str_encode($recipient_adress	) : $recipient_adress	= 'NULL';

		$invoice_num ? $invoice_num = ", invoice_num=$invoice_num" : $invoice_num = ''; # Available only before the first donation

		$result =
			$db->update(
				"donate_config; ".
				"currency_code=$currency_code, amount_min=$amount_min, ".
				"registration_silent=$registration_silent, accountant_email=$accountant_email, ".
				"recipient_name=$recipient_name, recipient_adress=$recipient_adress".
				$invoice_num
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
	echo '<h2>'.LANG_ADMIN_COM_DONATE_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$config = $db->selectOne('donate_config, *');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'config_');

	// Fieldset : Amount
	$fieldset = '';
	$fieldset .= $form->select('currency_code', formManager::selectOption(money::currencyCodeOptionsSingular(), $config['currency_code']), LANG_ADMIN_COM_DONATE_CONFIG_CURRENCY_CODE.'<br />').'<br /><br />';
	$fieldset .= $form->text('amount_min', money::convertAmountCentsToUnits($config['amount_min']), LANG_ADMIN_COM_DONATE_CONFIG_AMOUNT_MIN.'<br />', '', 'size=6');
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_DONATE_CONFIG_FIELDSET_AMOUNT);

	// Fieldset : Other
	$fieldset = '';
	$fieldset .= $form->checkbox('registration_silent', $config['registration_silent'], LANG_ADMIN_COM_DONATE_CONFIG_REGISTRATION_SILENT).'<br /><br />';
	$fieldset .= $form->text('accountant_email', $config['accountant_email'], LANG_ADMIN_COM_DONATE_CONFIG_ACCOUNTANT_EMAIL.'<br />', '', 'maxlength=100;size=35');
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_DONATE_CONFIG_FIELDSET_OTHER);

	// Fieldset : Invoice
	$fieldset = '';
	if (!$db->selectCount('donate')) # Available only before the first donation
	{
		$disabled	= '';
		$message	= ' <span class="red">'.LANG_ADMIN_COM_DONATE_CONFIG_INVOICE_NUM_TIPS.'</span>';
	} else {
		$disabled	= ';disabled';
		$message	= '';
	}
	$fieldset .= $form->text('invoice_num', $config['invoice_num'], LANG_ADMIN_COM_DONATE_CONFIG_INVOICE_NUM.'<br />', '', "maxlength=6;size=6$disabled").$message.'<br /><br />';
	$fieldset .= $form->text('recipient_name', $config['recipient_name'], LANG_ADMIN_COM_DONATE_CONFIG_RECIPENT_NAME.'<br />', '', 'size=40').'<br /><br />';
	$fieldset .= $form->textarea('recipient_adress', $config['recipient_adress'], LANG_ADMIN_COM_DONATE_CONFIG_RECIPENT_ADRESS.'<br />', '', 'rows=3').'<br /><span class="grey">'.LANG_ADMIN_COM_DONATE_CONFIG_RECIPENT_ADRESS_TIPS.'</span>';
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_DONATE_CONFIG_FIELDSET_INVOICE);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (0)
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>