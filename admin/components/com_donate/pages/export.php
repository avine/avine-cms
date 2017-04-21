<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'donate_export');


// Configuration
$start_view = true;


///////////
// Process



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_EXPORT_TITLE_START.'</h2>';

	$html = '';

	// Create temporary table ('payment_temp') of payments details (available fields : payment_id,missing_id, transmission_date,amount,currency_code, payment_date,validated)
	$payment_class = new comPayment_();
	$payment_class->createPaymentsTemporaryTable();

	// Database
	if ($donate_number = $db->selectCount('donate, join: payment_id>; payment_temp, join: <payment_id, where: validated=1'))
	{
		// 'username' and 'email' list
		$user_infos = $db->select('user, [id],username,email');

		// currency name
		$currency_code_sing = money::currencyCodeOptionsSingular();
		$currency_code_plur = money::currencyCodeOptionsPlural();

		// designation (list)
		$designation = $db->select('donate_designation, [id],title');

		// Form
		$form = new formManager(0);
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'export_');

		// Multipage
		$multipage = new simpleMultiPage($donate_number);
		$multipage->setFormID('export_');
		$multipage->updateSession($session->returnVar('multipage'));
		$html .=
			admin_floatingContent(
				array(
					$multipage->numPerPageForm(),
					$multipage->navigationTool(false, 'admin_'),
					$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT) // (0)
				)
			);

		// Let's go !
		$donate_list = array();
		$global_infos = array('amount_total' => array(), 'date_start' => (time() + 60*60*24*30), 'date_stop' => 0);
		$com_donate = new comDonate_();
		$donate_id = $db->select('donate, id(desc), join: payment_id>; payment_temp, join: <payment_id, where: validated=1; limit:'.$multipage->dbLimit());
		for ($i=0; $i<count($donate_id); $i++)
		{
			$com_donate->checkDonation($donate_id[$i]['id']);

			$payment_temp = $db->selectOne('payment_temp, *, where: payment_id='.$com_donate->checkDonation_get('payment_id'));

			// payment_id
			$donate_list[$i]['payment_id'] = $com_donate->checkDonation_get('payment_id');

			// payment_date
			$donate_list[$i]['payment_date'] = getTime($payment_temp['payment_date']);

			// amount_total and currency_code (from 'donate_details' table)
			$com_donate->checkDonation_amount($amount_total, $currency_code);

			// amount_total (from 'payment_temp' table)
			$donate_list[$i]['amount'] = money::convertAmountCentsToUnits($payment_temp['amount']);
			if ($amount_total != $payment_temp['amount'])
			{
				$donate_list[$i]['amount'] .= '(!!!)'; # critical error !
			}

			// currency_code (from 'payment_temp' table)
			$donate_list[$i]['currency_code'] = $currency_code_sing[$payment_temp['currency_code']];
			if ($currency_code != $payment_temp['currency_code'])
			{
				$donate_list[$i]['currency_code'] .= '(!!!)'; # critical error !
			}

			// invoice
			$com_donate->checkInvoice($donate_id[$i]['id']);
			$donate_list[$i]['invoice'] = comDonate_::formatInvoiceID($com_donate->getInvoiceID($donate_id[$i]['id'], $invoice_path));

			// username
			if ($user_id = $com_donate->checkDonation_get('user_id'))
			{
				$donate_list[$i]['username'	] = $user_infos[$user_id]['username'];
				$donate_list[$i]['email'	] = $user_infos[$user_id]['email'];
			} else {
				$donate_list[$i]['username'	] = '';
				$donate_list[$i]['email'	] = '';
			}

			// contributor
			$donate_list[$i] = array_merge($donate_list[$i], $com_donate->getDonorFromContributor($com_donate->checkDonation_get('contributor')));

			// details
			$temp = array();
			$details = $com_donate->checkDonation_get('details');
			for ($j=0; $j<count($details); $j++)
			{
				$temp[ $details[$j]['designation_id'] ] = money::convertAmountCentsToUnits($details[$j]['amount']).' '.mb_strtolower($currency_code_plur[$payment_temp['currency_code']]);
			}
			reset($designation);
			foreach ($designation as $id => $title)
			{
				isset($temp[$id]) ? $donate_list[$i][] = $temp[$id] : $donate_list[$i][] = '';
			}

			// Global infos
			!isset($global_infos['amount_total'][$payment_temp['currency_code']]) ? $global_infos['amount_total'][$payment_temp['currency_code']] = 0 : '';
			$global_infos['amount_total'][$payment_temp['currency_code']] += $payment_temp['amount'];
			$payment_temp['payment_date'] < $global_infos['date_start'] ? $global_infos['date_start'] = $payment_temp['payment_date'] : '';
			$payment_temp['payment_date'] > $global_infos['date_stop' ] ? $global_infos['date_stop' ] = $payment_temp['payment_date'] : '';
		}

		// Header (part 1)
		$header_donate =
			array(
				LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_PAYMENT_DATE,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_AMOUNT,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_CURRENCY_CODE,
				LANG_ADMIN_COM_DONATE_INVOICE_ID,
				LANG_COM_USER_USERNAME,
				LANG_COM_USER_EMAIL
			);

		// Header (part 2)
		$header_contributor = $com_donate->getDonorHeader();

		// Header (part 3)
		$header_details = array();
		reset($designation);
		foreach ($designation as $id => $title) {
			$header_details[] = $title['title'];
		}

		// Headers (final)
		$header = array_merge($header_donate, $header_contributor, $header_details);

		// Table
		$table = new tableManager($donate_list, $header);
		$html .= $table->html();

		$html .= $form->end();

		// Global infos : date interval
		$date_start_stop =
			searchAndReplace( LANG_ADMIN_COM_DONATE_EXPORT_CSV_DATE_INTERVAL,
				array(
					'{start}'		=> getTime($global_infos['date_start']),
					'{stop}'		=> getTime($global_infos['date_stop'])
				)
			);

		// Global infos : total amount per currency
		$amount_per_currency = array();
		$amounts = $global_infos['amount_total'];
		reset($amounts);
		foreach ($amounts as $curr => $amon) {
			$amount_per_currency[] = money::convertAmountCentsToUnits($amon).' '.mb_strtolower($currency_code_plur[$curr]);
		}

		$html .= '<h3>'.LANG_ADMIN_COM_DONATE_EXPORT_CSV_AMOUNT_TOTAL." $date_start_stop</h3>";
		$table = new tableManager($amount_per_currency);
		$html .= $table->html();

		// CSV
		$csv = new simpleCSV();
		$csv->set($donate_list, $header);

		// Add summary
		$csv->addSummaryBefore(
			searchAndReplace( LANG_ADMIN_COM_DONATE_EXPORT_CSV_SITE_NAME_DATE,
				array(
					'{site_name}'	=> $db->selectOne('config, site_name', 'site_name'),
					'{date}'		=> getTime()
				)
			)
		);

		// Add summary
		$csv->addSummaryBefore(LANG_ADMIN_COM_DONATE_EXPORT_CSV_AMOUNT_TOTAL, 0);

		// Add summary
		$csv->addSummaryBefore($date_start_stop, 1);

		// Add summary
		$amounts = $global_infos['amount_total'];
		reset($amounts);
		foreach ($amounts as $curr => $amon) {
			$csv->addSummaryBefore( money::convertAmountCentsToUnits($amon).' '.mb_strtolower($currency_code_plur[$curr]), 1);
		}

		$html .= '<br />'.$csv->textareaBox().'<br />';
	}
	else
	{
		$html .= '<p style="color:grey;">'.LANG_ADMIN_COM_DONATE_LIST_NO_PAYMENT_IS_NULL.'</p>';
	}

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';


?>