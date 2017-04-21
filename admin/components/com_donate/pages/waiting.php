<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'donate_waiting');


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('waiting_');
if ($submit)
{
	$purge_now = $filter->requestValue('purge_now')->get();
} else {
	$purge_now = false;
}


// Purge config
$purge_delay = 60*60*24*3; # 3 days
$purge_time = time() - $purge_delay;
$purge_color = 'grey';
$purge_trash_img = '<img src="'.WEBSITE_PATH.'/admin/components/com_donate/images/trash.png" alt="Trash" border="0" />';


// Purge now
if ($purge_now)
{
	$donate_id_list = array();
	$donate = $db->select("donate, id, where: payment_id IS NULL AND recording_date < $purge_time");
	for ($i=0; $i<count($donate); $i++) {
		$donate_id_list[] = $donate[$i]['id'];
	}

	admin_comDonate_purgeDatabase($donate_id_list);
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_WAITING_TITLE_START.'</h2>';

	$html = '';

	$purge_now_button = false;

	if ($donate_number = $db->selectCount('donate, where: payment_id IS NULL'))
	{
		// Form
		$form = new formManager(0);
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'waiting_');

		// Multipage
		$multipage = new simpleMultiPage($donate_number);
		$multipage->setFormID('waiting_');
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
		$username = $db->select('user, [id],username');
		$com_donate = new comDonate_();
		$donate_id = $db->select('donate, id(desc), where: payment_id IS NULL; limit:'.$multipage->dbLimit());
		for ($i=0; $i<count($donate_id); $i++)
		{
			// donate_id
			$donate_list[$i]['id'] = '<span class="grey">'. $donate_id[$i]['id'] .'</span>';

			$com_donate->checkDonation($donate_id[$i]['id']);

			// Old record ?
			if ($com_donate->checkDonation_get('recording_date') < $purge_time)
			{
				$span_l = "<span style=\"color:$purge_color;\">";
				$span_r = '</span>';
				$trash[$i] = $purge_trash_img;
				$purge_now_button = true;
			}
			else
			{
				$span_l = '';
				$span_r = '';
				$trash[$i] = '';
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
		}

		// Headers
		$header_donate =
			array(
				'ID',
				LANG_ADMIN_COM_DONATE_RECORDING_DATE,
				LANG_ADMIN_COM_DONATE_USER_ID,
				LANG_ADMIN_COM_DONATE_ALL_RECEIPT_ADDRESS,
				LANG_ADMIN_COM_DONATE_ALL_AMOUNT_TOTAL,
				LANG_ADMIN_COM_DONATE_ALL_DETAILS
			);

		// Table
		$table = new tableManager($donate_list, $header_donate);

		if ($purge_now_button) {
			$table->addCol($trash, 0, '');
		}
		$html .= $table->html();

		if ($purge_now_button) {
			$html .= $form->submit('purge_now', LANG_ADMIN_COM_DONATE_WAITING_PURGE_BUTTON); // (1)
		}

		$html .= $form->end();
	}
	else
	{
		$html .= '<p style="color:grey;">'.LANG_ADMIN_COM_DONATE_WAITING_NO_PAYMENT_IS_NULL.'</p>';
	}

	echo $html;

	// admin_message limitation : should be after : echo $html;
	if ($purge_now_button) {
		admin_message(str_replace('{number}', sprintf('%.2f', $purge_delay/(60*60*24)), LANG_ADMIN_COM_DONATE_WAITING_PURGE_HELP), 'help'); 
	}
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>