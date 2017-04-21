<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/**
 * This script is designed for comPayment_sips component debugging
 * For more details see: ./request.php
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


// Payment Sips
$sips = new comPaymentSips_();
$payment_x_id = $sips->callAutoResponse();


// Use the result to update others DB
if (comPayment_::debugMode())
{
	$infos = $sips->checkPayment_x($payment_x_id);
	if (!$infos['missing_id'])
	{
		if ($infos['validated'])
		{
			$valid_html = 'validated';
		} else {
			$valid_html = 'NOT validated';
		}

		// Some Html output to check the process (but in production, the autoresponse is only a server-server communication)
		echo "<p><b>Call autoresponse result :</b><br />The payment id=$payment_x_id is $valid_html<br />(the 'id' is relative to the index of 'payment_sips' table)</p>";
	}
}



loaderManager::directAccessEnd();

?>