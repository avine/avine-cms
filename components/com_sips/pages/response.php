<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/**
 * This script is designed for comPayment_sips component debugging
 * For more details see: ./request.php
 */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Use default Html output
$html_output = true;

// Payment Sips
$sips = new comPaymentSips_();
$payment_x_id = $sips->callResponse($html_output);


// Cutomize the Html-output as you need
if (!$html_output)
{
	$infos = $sips->checkPayment_x($payment_x_id);
	if (!$infos['missing_id'])
	{
		echo '<h4>Customized Sips-Response</h4>';

		if ($infos['validated'])
		{
			echo '<p style="color:green;">Your payment (id='.$payment_x_id.' in <b>payment_sips</b> table) is validated.</p>';
		} else {
			echo '<p style="color:red;">Sorry, but your payment is NOT validated !</p>';
		}
	}
}

?>