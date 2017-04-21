<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Database
$config = $db->selectOne('payment_sips_config, *');
$cgi_bin_path = $config['cgi_bin_path'];

// CGI manager
$cgi = new admin_comPaymentSips_cgi($cgi_bin_path, 'sips_cgi_');


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

if (formManager::isSubmitedForm('sips_cgi_', 'post'))
{
	$file_path = '/param/pathfile';
	$cgi->process($file_path, 'pathfile');

	$file_path = '/param/parmcom.'.$config['parmcom_bank_name'];
	$cgi->process($file_path, 'parmcom_bank');

	$file_path = '/param/parmcom.'.$config['merchant_id'];
	$cgi->process($file_path, 'parmcom_merchant');

	$file_path = '/log/logfile.txt';
	$cgi->process($file_path, 'logfile');
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_SIPS_CGI_TITLE_START.'</h2>';

	$html = '';

	clearstatcache();

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'sips_cgi_');

	if (!$cgi_bin_path)
	{
		admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_BIN_PATH.' : '.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UNKNOWN, 'error');
	}
	elseif (!is_dir($cgi_bin_path))
	{
		admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_CGI, 'error');
	}
	else
	{
		echo '<p style="color:grey;">'.LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_BIN_PATH.' : <b>'.$cgi_bin_path.'</b></p>';


		/**
		 * param directory
		 */
		if (!is_dir($cgi_bin_path.'/param'))
		{
			admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_PARAM, 'error');
		}
		else
		{
			// pathfile
			$file_path = '/param/pathfile';
			$html .=
				admin_fieldset(
					$cgi->form($file_path, 'pathfile'),
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PATHFILE
				);

			// parmcom.bank_name
			$file_path = '/param/parmcom.'.$config['parmcom_bank_name'];
			$html .=
				admin_fieldset(
					$cgi->form($file_path, 'parmcom_bank'),
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PARCOM_BANK
				);

			// parmcom.merchant_id
			$file_path = '/param/parmcom.'.$config['merchant_id'];
			$html .=
				admin_fieldset(
					$cgi->form($file_path, 'parmcom_merchant'),
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PARCOM_MERCHANT
				);

			// certif.merchant_country.merchant_id
			$file_path = '/param/certif.'.$config['merchant_country'].'.'.$config['merchant_id'];
			$html .=
				admin_fieldset(
					$cgi->form($file_path, '', 'no-area'),
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_CERTIF
				);
		}


		/**
		 * bin directory
		 */
		if (!is_dir($cgi_bin_path.'/bin'))
		{
			admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_BIN, 'error');
		}
		else
		{
			// request
			$file_path = '/bin/request';
			chmod($cgi_bin_path.$file_path, 0505); # File permissions (read and execute)
			$html .=
				admin_fieldset(
					$cgi->form($file_path, '', 'no-area').LANG_ADMIN_COM_PAYMENT_SIPS_CGI_BINARY_TIPS,
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_REQUEST
				);

			// response
			$file_path = '/bin/response';
			chmod($cgi_bin_path.$file_path, 0505); # File permissions (read and execute)
			$html .=
				admin_fieldset(
					$cgi->form($file_path, '', 'no-area').LANG_ADMIN_COM_PAYMENT_SIPS_CGI_BINARY_TIPS,
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_RESPONSE
				);
		}


		/**
		 * log directory
		 */
		$ftp = new ftpManager($cgi_bin_path);
		$ftp->isDir('/log') or $ftp->mkdir('/log');
		$ftp->isFile('/log/logfile.txt') or $ftp->write('/log/logfile.txt');

		if (!is_dir($cgi_bin_path.'/log'))
		{
			admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_LOG, 'error');
		}
		else
		{
			// logfile
			$file_path = '/log/logfile.txt';
			$html .=
				admin_fieldset(
					$cgi->form($file_path, 'logfile', 'no-update'),
					LANG_ADMIN_COM_PAYMENT_SIPS_CGI_LOGFILE
				);
		}

	}

	$html .= $form->end(); // End of Form
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>