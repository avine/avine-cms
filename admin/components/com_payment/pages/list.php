<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'payment_list');


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('list_', 'post');
if ($submit)
{
	$delete_tests = $filter->requestValue('delete_tests')->get(); // (0)
} else {
	$delete_tests = false;
}



// Usefull : method_id for generic payments (tests)
$payment_generic = new comPaymentGeneric_();
$generic_method_id = $payment_generic->findMethod_id();



// Case 0 : Delete tests
if ($delete_tests)
{
	$result = $db->delete("payment; where: method_id=$generic_method_id");

	admin_informResult($result);
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_LIST_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'list_');

	// Payment class
	$payment_class = new comPayment_();
	// Create temporary table ('payment_temp') of payments details
	$payment_class->createPaymentsTemporaryTable();

	// Exclude the tests payments ?
	$generic_method_query = '';
	if (!$payment_class->getConfig('debug'))
	{
		$generic_method_id ? $generic_method_query = ", where: method_id!=$generic_method_id" : '';
	} else {
		admin_message(LANG_ADMIN_COM_PAYMENT_LIST_GENERIC_METHOD_ACTIVATED_TIPS, 'tips');
	}


	// Multipage class
	$multipage = new simpleMultiPage( $db->selectCount('payment'.$generic_method_query) );
	$multipage->setFormID('list_');
	$multipage->updateSession($session->returnVar('multipage'));

	$html .=
		admin_floatingContent(
			array(
				$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),
				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_1') // (0)
			)
		);


	// Get payments
	$payment = $db->select('payment, *, id(desc), join: id>'.$generic_method_query.'; payment_temp, join: <payment_id'.'; limit:'.$multipage->dbLimit());

	$currency_code = money::currencyCodeOptionsSingular(); 			# currency_code
	$payment_method = $db->select('payment_method, [id], alias'); 	# method_alias
	$critical_error = false;
	$tests_founded = false;
	for ($i=0; $i<count($payment); $i++)
	{
		if (($generic_method_id) && ($payment[$i]['method_id'] == $generic_method_id))
		{
			$current_is_test = true;
			$tests_founded = true;
		} else {
			$current_is_test = false;
		}

		// method_alias instead of method_id
		$payment[$i]['method_id'] = $payment_method[ $payment[$i]['method_id'] ]['alias'];

		// Payment details
		if ($payment[$i]['transmission_date']) $payment[$i]['transmission_date'	] = getTime($payment[$i]['transmission_date']);
		if ($payment[$i]['amount'			]) $payment[$i]['amount'			] = '<div style="text-align:right;font-weight:bold;">'.money::convertAmountCentsToUnits($payment[$i]['amount']).'</div>';
		if ($payment[$i]['currency_code'	]) $payment[$i]['currency_code'		] = $currency_code[$payment[$i]['currency_code']];
		if ($payment[$i]['payment_date'		]) $payment[$i]['payment_date'		] = getTime($payment[$i]['payment_date']);

		// Is database corrupted ?
		if (!$payment[$i]['missing_id'])
		{
			$payment[$i]['missing_id'] = '';
			$payment[$i]['validated'] = admin_replaceTrueByChecked($payment[$i]['validated'], false);
		}
		else
		{
			$payment[$i]['missing_id'] = '<span style="color:red;font-weight:bold;">!</span>'; /* critical error! */
			$payment[$i]['validated'] = '';
			$critical_error = true;
		}
	}
	if ($critical_error == true) {
		admin_message(LANG_ADMIN_COM_PAYMENT_LIST_MISSING_X_ID_CRITICAL_ERROR, 'warning', '300');
	}

	// 'payment' table header
	#$header_id_field = LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID; 	# long version
	$header_id_field = 'ID'; 									# (or) short version
	$header_payment =
		array(
			$header_id_field,
			LANG_ADMIN_COM_PAYMENT_ABS_METH_ID,
			LANG_ADMIN_COM_PAYMENT_ABS_PAYM_X_ID,
			LANG_ADMIN_COM_PAYMENT_ORIGIN
		);

	// 'payment_temp' table header
	$header_payment_temp = $payment_class->createPaymentsTemporaryTableHeader(); // end of : table headers

	// Table
	$table = new tableManager($payment, array_merge($header_payment, $header_payment_temp));
	$table->delCol(4); # Delete duplicate PAYMENT_ID column
	$html .= $table->html();

	if ($tests_founded)
	{
		$html .= $form->submit('delete_tests', LANG_ADMIN_COM_PAYMENT_LIST_DELETE_TESTS); // (0)
		$html .= '<p style="color:red;">'.LANG_ADMIN_COM_PAYMENT_LIST_DELETE_TESTS_TIPS.'</p>';
	}

	#$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_2').'<br />'; // (1) # Not necessary...
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>