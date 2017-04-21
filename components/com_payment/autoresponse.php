<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/**
 * This script is designed for comPayment component debugging
 * For more details see: ./index.php
 */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin(false);



// Protocol info
global $g_protocol;
$g_protocol = 'http://';


// Payment
$payment = new comPayment_();
$payment_id = $payment->getResponsePaymentID(false);


// Use the result to update others DB
if ($payment->getConfig('debug'))
{
	$infos = $payment->checkPayment($payment_id);
	if (!$infos['missing_id'])
	{
		if ($infos['validated'])
		{
			$valid_html = 'validated';
		} else {
			$valid_html = 'NOT validated';
		}

		// Some Html output to check the process (but in production, the autoresponse is only a server-server communication)
		echo "<p><b>Autoresponse result :</b><br />The payment id=$payment_id is $valid_html</p>";
	}
}



loaderManager::directAccessEnd();

?>