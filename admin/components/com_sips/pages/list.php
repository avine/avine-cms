<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'sips_list');


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('list_', 'post'); // (1)


//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_SIPS_LIST_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'list_');

	// Validated filter
	$validated_filter = $session->setAndGet('validated_filter', $filter->requestValue('validated_filter')->get());
	$form_select = $form->select('validated_filter', formManager::selectOption(admin_comPayment_validatedOptions(), $validated_filter), LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER);
	if (formManager_filter::isInteger($validated_filter))
	{
		$query_validated_filter = ", where: validated=$validated_filter";
	} else {
		$query_validated_filter = ''; # 'root'
	}

	// Multipage class
	$multipage = new simpleMultiPage( $db->selectCount('payment_sips'.$query_validated_filter) );
	$multipage->setFormID('list_');
	$session->atLeastOneUpdatedKeys() ? $multipage->resetCurrentPage() : ''; # Reset currentpage
	$multipage->updateSession($session->returnVar('multipage'));

	$html .=
		admin_floatingContent(
			array(
				$form_select,
				$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),
				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_1') // (1)
			)
		);

	// Let's go !
	$payment = $db->select('payment_sips, *, id(desc)'.$query_validated_filter.'; limit:'.$multipage->dbLimit());

	for ($i=0; $i<count($payment); $i++)
	{
		// Transaction_id
		$payment[$i]['id'] = comPaymentSips_::formatTransactionID($payment[$i]['id']);

		// Transmission_date
		$payment[$i]['transmission_date'] = getTime($payment[$i]['transmission_date']);

		// Amount (converion centimes => euros)
		$payment[$i]['amount'] = '<div style="text-align:right;font-weight:bold;">'.money::convertAmountCentsToUnits($payment[$i]['amount']).'</div>';

		// Currency_code
		$currency_code = money::currencyCodeOptionsSingular();
		$payment[$i]['currency_code'] = $currency_code[$payment[$i]['currency_code']];

		// Card_number
		$payment[$i]['card_number'] = str_replace('.', '<span style="color:grey;">xxxxxxxxxxxxx</span>', $payment[$i]['card_number']);

		// Payment_date
		$payment[$i]['payment_date'] ? $payment[$i]['payment_date'] = getTime($payment[$i]['payment_date']) : '';

		// Validated
		$payment[$i]['validated'] = admin_replaceTrueByChecked($payment[$i]['validated'], false);
	}

	// Table
	$table = new
		tableManager(
			$payment, 
			array(
				LANG_ADMIN_COM_PAYMENT_ABS_PAYM_X_ID,
				LANG_ADMIN_COM_PAYMENT_SIPS_TRANSMISSION_DATE,
				LANG_ADMIN_COM_PAYMENT_SIPS_CAPTURE_MODE,
				LANG_ADMIN_COM_PAYMENT_SIPS_CAPTURE_DAY,
				LANG_ADMIN_COM_PAYMENT_SIPS_AMOUNT,
				LANG_ADMIN_COM_PAYMENT_SIPS_CURRENCY_CODE,
				LANG_ADMIN_COM_PAYMENT_SIPS_CARD_NUMBER,
				LANG_ADMIN_COM_PAYMENT_SIPS_PAYMENT_MEANS,
				LANG_ADMIN_COM_PAYMENT_SIPS_PAYMENT_DATE,
				LANG_ADMIN_COM_PAYMENT_SIPS_VALIDATED
			)
		);

	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_2'); // (1)
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>