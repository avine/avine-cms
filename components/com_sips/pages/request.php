<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/**
 * This script is designed for comPayment_sips component debugging
 * First switch comPayment_sips in debug mode (in backend)
 * Then this page will generate a payment request of 1EURO
 * and will insert a new record directly only into:
 *		- 'payment_sips' table
 * But will have no effect on 'payment' table (unexpected behaviour in production)
 */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// New Payment
$sips = new comPaymentSips_();


if (comPayment_::debugMode())
{
	// Generate submit form ready to go to the bank server
	$payment_x_id = $sips->getPayment_x_ID(100, 978); # = 1 EURO

	// IP addr. message
	echo '<p id="comSips_ip-addr"><span>'.LANG_COM_PAYMENT_SIPS_IP_ADDR.'<span>'.$_SERVER['REMOTE_ADDR'].'</span></span></p>';

	// Debug info
	if ($payment_x_id)
	{
		echo "<br /><br /><p style=\"border-left:5px solid #8B0000;padding-left:5px;color:#8B0000;\"><b>DEBUG INFO :</b><br />New ID successfully generated in 'payment_sips' table : <b>$payment_x_id</b></p>";
	}
}
else
{
	echo 'Restricted access';
}

?>