<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/**
 * This script is designed for comPayment component debugging
 * For more details see: ./index.php
 */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Use default Html output
$html_output = false;

// Payment
$payment = new comPayment_();
$payment_id = $payment->getResponsePaymentID(true, $html_output);


// Cutomize the Html-output as you need
if (!$html_output)
{
	$infos = $payment->checkPayment($payment_id);
	if (!$infos['missing_id'])
	{
		echo '<h4>Customized Response</h4>';

		if ($infos['validated'])
		{
			echo '<p style="color:green;">Your payment (id='.$payment_id.' in <b>payment</b> table) is validated.</p>';
		} else {
			echo '<p style="color:red;">Sorry, but your payment is NOT validated !</p>';
		}
	}
}

?>