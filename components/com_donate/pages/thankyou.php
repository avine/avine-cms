<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Use default Html output
$html_output = true;

// Payment
$payment = new comPayment_();
$payment_id = $payment->getResponsePaymentID(true, $html_output);


// Add some specific Html-output
if ((!$html_output) || (true)) # TODO - ...
{
	$infos = $payment->checkPayment($payment_id);

	if (!$infos['missing_id'])
	{
		if ($infos['validated'])
		{
			// Invoice links
			$invoice_links = '';
			global $db;
			$donate_id = $db->selectOne("donate, id, where: payment_id=$payment_id", 'id');
			$donate_class = new comDonate_();
			$donate_class->checkInvoice($donate_id);
			$invoice_id = $donate_class->getInvoiceID($donate_id, $invoice_path);
			if (count($invoice_path))
			{
				if (isset($invoice_path['pdf'])) {
					$invoice_links .= '<a href="'.siteUrl().$invoice_path['pdf'].'" class="external">'.LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_PDF.'</a><br />';
				}
				if (isset($invoice_path['html'])) {
					$invoice_links .= '<a href="'.siteUrl().$invoice_path['html'].'" class="external">'.LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_HTML.'</a><br />';
				}
				!$invoice_links or $invoice_links = "<p>$invoice_links</p>";
				echo $invoice_links;
			}

			echo '<p><b>'.LANG_COM_DONATE_THANKYOU_MESSAGE_VALIDATED.'</b></p>';
		} else {
			echo '<p><b>'.LANG_COM_DONATE_THANKYOU_MESSAGE_NOT_VALIDATED.'</b></p>';
		}
	}
}


?>