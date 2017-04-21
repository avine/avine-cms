<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin(false);



// Protocol info
global $g_protocol;
$g_protocol	= 'http://';


// Database connection
global $db;


// Payment
$payment = new comPayment_();
$payment_id = $payment->getResponsePaymentID(false);


// Pass the payment result on the comDonate_ component
if ($payment_id)
{
	$infos = $payment->checkPayment($payment_id);
	if (!$infos['missing_id'])
	{
		// You have access to the following payment details
		$transmission_date 	= $infos['transmission_date'];
		$amount 			= $infos['amount'];
		$currency_code 		= $infos['currency_code'];
		$payment_date 		= $infos['payment_date'];
		$validated 			= $infos['validated'];

		// Payment details (formating)
		$transmission_date 	= getTime($transmission_date);
		$amount = money::convertAmountCentsToUnits($amount);
		$currency_name = money::currencyCodeOptionsPlural();
		$currency_name = $currency_name[$currency_code];
		$payment_date = getTime($payment_date);

		// Additional payment info (payment_method.payment_x_id) example: sips325
		$payment_x_ref = comPayment_::getPayment_x_ref($payment_id); 

		if ($validated)
		{
			$validated_lang		= LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_VALIDATED;
			$validated_message 	= LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER_VALIDATED;
		} else {
			$validated_lang		= LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_NOT_VALIDATED;
			$validated_message 	= LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER_NOT_VALIDATED;
		}

		// Contributor & user email
		$donate = $db->selectOne("donate, id,contributor,user_id, where: payment_id=$payment_id");

		// Default values
		$all_user_details = LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_ANONYMOUS;
		$user_email = '';

		if ($contributor = $donate['contributor'])
		{
			$donate_class = new comDonate_();
			$all_user_details = $donate_class->donorHTML($donate_class->getDonorFromContributor($contributor), 'tmpl_donor_html.html');

			if ($user_id = $donate['user_id'])
			{
				$user_details = new comUser_details($user_id);
				$user_email = $user_details->get('email');

				$all_user_details = "(UID$user_id)\n".$all_user_details;
			}

			/**
			 * The payment is validated, and it's not an anonymous donation.
			 * Let's create the PDF version of the invoice !
			 */ 
			$invoice_links = '';
			if ($validated)
			{
				$donate_class->checkInvoice($donate['id']);

				$invoice_id = $donate_class->getInvoiceID($donate['id'], $invoice_path);
				if (count($invoice_path))
				{
					$invoice_links = LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_TIPS.'<br />';

					if (isset($invoice_path['pdf'])) {
						$invoice_links .= '<br /><a href="'.siteUrl().$invoice_path['pdf'].'">'.LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_PDF.'</a><br />';
						$invoice_links .= siteUrl().$invoice_path['pdf'].'<br />';
					}
					if (isset($invoice_path['html'])) {
						$invoice_links .= '<br /><a href="'.siteUrl().$invoice_path['html'].'">'.LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_HTML.'</a><br />';
						$invoice_links .= siteUrl().$invoice_path['html'].'<br />';
					}
				}

				!$invoice_links or $invoice_links = "<p>$invoice_links</p>";
			}
		}

		// Config : site_name & system_email
		comConfig_getInfos($site_name, $system_email); # passed by reference

		// accountant_email for donate component
		$accountant_email = $db->selectOne('donate_config, accountant_email', 'accountant_email');

		/**
		 * Let's send emails !
		 */

		// Admin Message
		$admin_message =
			searchAndReplace( LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN,
				array(
					'{site_name}'			=> htmlentities($site_name, ENT_COMPAT, 'UTF-8'),
					'{payment_id}'			=> $payment_id,
					'{payment_x_ref}'		=> $payment_x_ref,
					'{amount}'				=> $amount.' '.htmlentities($currency_name, ENT_COMPAT, 'UTF-8'),
					'{payment_date}'		=> htmlentities($payment_date, ENT_COMPAT, 'UTF-8'),
					'{validated}'			=> $validated_lang,
					'{all_user_details}'	=> $all_user_details,
					'{invoice_links}'		=> $invoice_links
				)
			);
		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageHTML($admin_message)
				->addTo($system_email)
				->addTo($accountant_email)
				->setSubject(LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_SUBJECT)
				->setFrom($system_email);
		if (isset($invoice_path['pdf'])) {
			$mail->addAttachment(sitePath().$invoice_path['pdf']);
		}
		$mail->send();


		// User Message
		$user_message =
			searchAndReplace( LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER,
				array(
					'{site_name}'			=> htmlentities($site_name, ENT_COMPAT, 'UTF-8'),
					'{validated_message}'	=> htmlentities($validated_message, ENT_COMPAT, 'UTF-8'),
					'{payment_id}'			=> $payment_id,
					'{amount}'				=> $amount.' '.htmlentities($currency_name, ENT_COMPAT, 'UTF-8'),
					'{payment_date}'		=> htmlentities($payment_date, ENT_COMPAT, 'UTF-8'),
					'{validated}'			=> $validated_lang,
					'{invoice_links}'		=> $invoice_links
				)
			);
		$mail = new emailManager();
		$mail	->useDefaultTemplate()
				->addMessageHTML($user_message)
				->addTo($user_email)
				->setSubject(LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER_SUBJECT)
				->setFrom($system_email);
		if (isset($invoice_path['pdf'])) {
			$mail->addAttachment(sitePath().$invoice_path['pdf']);
		}
		$mail->send();


		// Update logfile
		# ...
	}
	else
	{
		# Update logfile, that we have a missing ID
		# Perhaps even send an email to the administrator !
	}
}



loaderManager::directAccessEnd();

?>