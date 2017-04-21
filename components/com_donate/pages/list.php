<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Global
global $db;


global $g_user_login;
$user_id = $g_user_login->userID();


if (!$user_id)
{
	echo '<p>'.LANG_COM_DONATE_LIST_NO_USER_LOGGED.'</p>';
}
else
{
	echo '<h1>'.LANG_COM_DONATE_LIST_TITLE.'</h1>';

	/**
	 * All donations for this logged-user
	 */
	$donate_list = $db->select("donate, id,payment_id(desc), where: user_id=$user_id AND, where: form_passed=1 AND, where: payment_id IS NOT NULL");
	if (!$donate_list)
	{
		echo '<p>'.LANG_COM_DONATE_LIST_EMPTY_LIST.'</p>';
	}
	else
	{
		$final_amount = 0;
		$final_currency_code = NULL;
		$multi_currency_code = false;

		$list = array();

		$donate = new comDonate_();

		for ($i=0; $i<count($donate_list); $i++)
		{
			$donate_id	= $donate_list[$i]['id'];
			$payment_id	= $donate_list[$i]['payment_id'];

			// Invoice links
			$donate->checkInvoice($donate_id);
			$invoice_id = $donate->getInvoiceID($donate_id, $invoice_path);
			$invoice_links = $donate->getLinksFromInvoicePath($invoice_path);

			$donate->checkDonation($donate_id);
			$validated = $donate->checkDonation_isPaymentValidated();
			if (!$validated) {
				continue; # To view all donations, comment this line
			}
			$list[] =
				array(
					#'validated'			=> replaceTrueByChecked($validated, false),
					'recording_date'	=> $donate->checkDonation_recordingDate(),
					'amount'			=> $donate->checkDonation_amount($amount, $currency_code),
					'details'			=> $donate->checkDonation_details().'<hr /><span style="color:#777;">'.LANG_COM_DONATE_LIST_PAYMENT_ID." : $payment_id</span>",
					'contributor'		=> $donate->checkDonation_contributor('tmpl_donor_html.html'),
					'invoice_links'		=> $invoice_links
				);

			if ($validated)
			{
				$final_amount += $amount;

				// Check for unique currency_code
				!isset($final_currency_code) ? $final_currency_code = $currency_code : '';
				$currency_code != $final_currency_code ? $multi_currency_code = true : '';
			}
		}

		$list_header =
			array(
				#LANG_COM_DONATE_LIST_DONATE_VALIDATED,
				LANG_COM_DONATE_RECORDING_DATE,
				LANG_COM_DONATE_DETAILS_AMOUNT,
				LANG_COM_DONATE_LIST_DONATE_DETAILS,
				LANG_COM_DONATE_LIST_RECEIPT_ADRESS,
				LANG_COM_DONATE_INVOICE_TITLE
			);

		$table = new tableManager($list, $list_header);
		echo $table->html();

		if (!$multi_currency_code && $final_amount != 0)
		{
			// currency_name
			$currency_code_options = money::currencyCodeOptionsPlural();
			$currency_name = mb_strtolower($currency_code_options[$final_currency_code]);

			echo '<br /><h4><span>'.LANG_COM_DONATE_LIST_DONATE_VALIDATED_AMOUNT_TOTAL.' :</span> '.money::convertAmountCentsToUnits($final_amount)." $currency_name</h4>";
		}
	}

}

?>