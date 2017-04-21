<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'donate_list');


// Configuration
$start_view = true;


$invoice_by_letter = '<img src="'.siteUrl().'/admin/components/com_donate/images/no-invoice-by-email.png" alt="'.LANG_ADMIN_COM_DONATE_LIST_NO_INVOICE_BY_EMAIL.'" title="'.LANG_ADMIN_COM_DONATE_LIST_NO_INVOICE_BY_EMAIL.'" />';


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('list_');
if ($submit)
{
	$purge_now = $filter->requestValue('purge_now')->get();
} else {
	$purge_now = false;
}


// Purge config
$purge_delay = 60*60*24*30; # 30 days
$purge_time = time() - $purge_delay;
$purge_color = 'grey';
$purge_trash_img = '<img src="'.WEBSITE_PATH.'/admin/components/com_donate/images/trash.png" alt="Trash" border="0" />';



// Purge now
if ($purge_now)
{
	// Create temporary table ('payment_temp') of payments details
	$payment_class = new comPayment_();
	$payment_class->createPaymentsTemporaryTable();

	// Let's go !
	$donate =
		$db->select(
				"donate, id,payment_id, where: recording_date < $purge_time AND, join: payment_id>; ".
				"payment_temp, where: payment_date IS NULL, join: <payment_id"
		);

	$donate_id_list = array();
	for ($i=0; $i<count($donate); $i++)
	{
		// Delete 'payment' and 'payment_x' records
		if ($payment_class->deletePayment($donate[$i]['payment_id']))
		{
			$donate_id_list[] = $donate[$i]['id'];
		} else {
			# TODO - Informer : Impossible d'effacer le paiement id=xxx ...
		}
	}

	// Delete 'donate' and 'donate_details' records
	admin_comDonate_purgeDatabase($donate_id_list);
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_LIST_TITLE_START.'</h2>';

	$html = '';

	$purge_now_button = false;

	if ($donate_number = $db->selectCount('donate, where: payment_id IS NOT NULL'))
	{
		// Create temporary table ('payment_temp') of payments details (available fields : payment_id,missing_id, transmission_date,amount,currency_code, payment_date,validated)
		$payment_class = new comPayment_();
		$payment_class->createPaymentsTemporaryTable();

		// 'username' list
		$username = $db->select('user, [id],username');

		// Form
		$form = new formManager(0);
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'list_');

		// Multipage
		$multipage = new simpleMultiPage($donate_number);
		$multipage->setFormID('list_');
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
		$critical_error 	= false;
		$donate_list 		= array();
		$trash 				= array();
		$com_donate = new comDonate_();
		$donate_id = $db->select('donate, id(desc), where: payment_id IS NOT NULL; limit:'.$multipage->dbLimit());
		for ($i=0; $i<count($donate_id); $i++)
		{
			// donate_id
			$donate_list[$i]['id'] = '<span class="grey">'. $donate_id[$i]['id'] .'</span>';

			$com_donate->checkDonation($donate_id[$i]['id']);

			// payment infos : critical error ?
			if ( ($payment_temp = $db->selectOne('payment_temp, *, where: payment_id='.$com_donate->checkDonation_get('payment_id'))) && (!$payment_temp['missing_id']) )
			{
				$current_error = false;
			} else {
				$current_error = true;
				$critical_error = true;
			}

			// Should be purged ?
			$span_l = '';
			$span_r = '';
			$trash[$i] = '';
			if ( (!$current_error) && (!$payment_temp['payment_date'] && ($com_donate->checkDonation_get('recording_date') < $purge_time)) )
			{
				$span_l = "<span style=\"color:$purge_color;\">";
				$span_r = '</span>';
				$trash[$i] = $purge_trash_img;
				$purge_now_button = true;
			}

			// recording_date
			$donate_list[$i]['recording_date'] = $span_l. $com_donate->checkDonation_recordingDate() .$span_r;

			// username
			if ($user_id = $com_donate->checkDonation_get('user_id'))
			{
				$donate_list[$i]['username'	] = $username[$user_id]['username'];
			} else {
				$donate_list[$i]['username'	] = '<span style="color:#CCC;">'.LANG_ADMIN_COM_DONATE_ALL_USER_GUEST.'</span>';
			}

			// contributor
			$donate_list[$i]['contributor'	] = $com_donate->checkDonation_contributor('tmpl_donor_html.html');
			!$donate_list[$i]['contributor'	] ? $donate_list[$i]['contributor'] = '<span style="color:#CCC;">'.LANG_ADMIN_COM_DONATE_ALL_DONATE_ANONYMOUS.'</span>' : '';

			// amount_total
			$donate_list[$i]['amount'		] = $com_donate->checkDonation_amount($amount_total, $currency_code);

			// details
			$donate_list[$i]['details'		] = $com_donate->checkDonation_details();

			// payment_id
			$donate_list[$i]['payment_id'	] = $com_donate->checkDonation_get('payment_id');

			if (!$current_error)
			{
				// amount & currency_code are the same in donate datas and in payment datas ?
				if ( ($amount_total != $payment_temp['amount']) || ($currency_code != $payment_temp['currency_code']) )
				{
					$donate_list[$i]['amount'] = '<span style="color:red;">'.$donate_list[$i]['amount'].'</span>'; # critical error !
				}

				// payment_date
				$payment_temp['payment_date'] ? $donate_list[$i]['payment_date'] = getTime($payment_temp['payment_date']) : $donate_list[$i]['payment_date'] = '';

				// validated
				$donate_list[$i]['validated'] = admin_replaceTrueByChecked($payment_temp['validated'], false);
			}
			else
			{
				// payment_id orphan !
				$donate_list[$i]['payment_id'] = '<span style="color:red;">'.$donate_list[$i]['payment_id'].'</span>';

				// payment_date & validated : unknown !
				$donate_list[$i]['payment_date'] = $donate_list[$i]['validated'] = '<span style="color:red;">?</span>'; # critical error!
			}

			// invoice
			$com_donate->checkInvoice($donate_id[$i]['id']);
			$invoice_id = comDonate_::formatInvoiceID($com_donate->getInvoiceID($donate_id[$i]['id'], $invoice_path));
			$invoice_links = $com_donate->getLinksFromInvoicePath($invoice_path);
			$donate_list[$i]['invoice'	] = $invoice_id;
			$donate_list[$i]['download'	] = $invoice_links;

			// Should invoice be send by letter ?
			if ($invoice_links && !$user_id) {
				$donate_list[$i]['download'	] .= $invoice_by_letter;
			}
		}

		// Headers
		$header_donate =
			array(
				'ID',
				LANG_ADMIN_COM_DONATE_RECORDING_DATE,
				LANG_ADMIN_COM_DONATE_USER_ID,
				LANG_ADMIN_COM_DONATE_ALL_RECEIPT_ADDRESS,
				LANG_ADMIN_COM_DONATE_ALL_AMOUNT_TOTAL,
				LANG_ADMIN_COM_DONATE_ALL_DETAILS,
				LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_PAYMENT_DATE,
				LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_VALIDATED,
				LANG_ADMIN_COM_DONATE_INVOICE_ID,
				LANG_ADMIN_COM_DONATE_INVOICE_TITLE
			);

		// Table
		$table = new tableManager($donate_list, $header_donate);

		if ($purge_now_button) {
			$table->addCol($trash, 0, '');
		}
		$html .= $table->html();

		if ($purge_now_button) {
			$html .= $form->submit('purge_now', LANG_ADMIN_COM_DONATE_LIST_PURGE_BUTTON); // (1)
		}

		$html .= $form->end();

		// Critical error message !
		if ($critical_error) {
			admin_message(LANG_ADMIN_COM_DONATE_LIST_MISSING_ID_CRITICAL_ERROR, 'warning', '300');
		}
	}
	else
	{
		$html .= '<p style="color:grey;">'.LANG_ADMIN_COM_DONATE_LIST_NO_PAYMENT_IS_NULL.'</p>';
	}

	echo $html;

	// admin_message limitation : should be after : echo $html;
	if ($purge_now_button) {
		admin_message(str_replace('{number}', sprintf('%.2f', $purge_delay/(60*60*24)), LANG_ADMIN_COM_DONATE_LIST_PURGE_HELP), 'help'); 
	}
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>