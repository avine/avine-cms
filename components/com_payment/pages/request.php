<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/**
 * This script is designed for comPayment component debugging
 * First switch comPayment in debug mode (in backend)
 * Then this page will generate a payment request of 1EURO
 * and will insert a new record into:
 *		- 'payment' table
 *		- 'payment_x' table (depends of the selected payment method: 'payment_sips' table for example)
 */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// New Payment
$payment = new comPayment_( LANG_COM_PAYMENT_GENERIC_ORIGIN );

if ($payment->getConfig('debug'))
{
	// If you need customize each $payment object in a different way
	$payment->customize('sips', array('template' => 'tmpl_sips_form.html')); # Display the payment-form using a template (comment this line to disable)

	// Required infos
	$amount 		= '100';
	$currency_code 	= '978'; # = 1EURO

	// Optionals infos
	$normal_return_url 		= str_replace('&amp;', '&', comMenu_rewrite('com=payment&page=response')); # ampersand SIPS limitation
	$cancel_return_url 		= str_replace('&amp;', '&', comMenu_rewrite('com=payment&page=response')); # ampersand SIPS limitation
	$automatic_response_url = siteUrl().'/components/com_payment/autoresponse.php';
	$caddie 				= '';

	$payment_details = array(
		'normal_return_url'			=>	$normal_return_url,
		'cancel_return_url'			=>	$cancel_return_url,
		'automatic_response_url'	=>	$automatic_response_url,
		'caddie'					=>	$caddie
	);

	// Set payment details
	$payment->paymentDetails( $amount, $currency_code, $payment_details );

	// Try to generate payment request
	$payment_id = $payment->getPaymentID($data);
	$tmpl_name = false; # false to disable or use (for example) the default template: 'tmpl_methods_form.html'
	echo $payment->displayPaymentMethodsForm($data, $tmpl_name);

	// Process request result
	if ($payment_id)
	{
		echo "<p style=\"color:green;\">New ID successfully generated in 'payment' table : <b>$payment_id</b></p>";
	} else {
		echo "<p style=\"color:red;\">No ID generated in 'payment' table.<br />This is a failure, unless you are waiting for something (like a payment method selection)</p>";
	}
}
else {
	echo 'Restricted access (switch comPayment component to dedug mode to run this page)';
}


?>