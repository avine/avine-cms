<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

/**
 * config.php
 */

function admin_comPaymentSips_merchantCountryOptions()
{
	return
		array(
			'be' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_BELGIUM,
			'fr' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_FRANCE,
			'de' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_GERMANY,
			'it' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_ITALY,
			'es' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_SPAIN,
			'en' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_ENGLAND
		);
}



function admin_comPaymentSips_captureModeOptions()
{
	return
		array(
			'AUTHOR_CAPTURE' 	=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_MODE_AUTHOR_CAPTURE,
			'VALIDATION' 		=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_MODE_VALIDATION
		);
}



function admin_comPaymentSips_paymentMeansOptions()
{
	return
		array(
			'CB' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_CB,
			'VISA' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_VISA,
			'MASTERCARD' 		=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_MASTERCARD,
			'AMEX' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_AMEX
		);
}



function admin_comPaymentSips_blockNumberOptions()
{
	return
		array(
			'1' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_NUMBER_1,
			'2' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_NUMBER_2,
		  	'4' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_NUMBER_4,
		);
}



function admin_comPaymentSips_paymentLanguageOptions()
{
	return
		array(
			'fr' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_FR,
			'ge' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_GE,
			'en' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_EN,
			'sp' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_SP,
			'it' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_IT
	);
}



function admin_comPaymentSips_demoModuleOptions()
{
	return
		array(
			'sogenactif' 			=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_SOGENACTIF,
			'elysnet' 				=> LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_ELYSNET
	);
}



/**
 * cgi.php
 */

class admin_comPaymentSips_cgi
{
	private	$cgi_bin_path;

	private	$form_id;



	public function __construct( $cgi_bin_path, $form_id = '' )
	{
		$this->cgi_bin_path = $cgi_bin_path;
		$this->form_id 		= $form_id;
	}



	public function form( $file_path, $textarea_name, $param = '' )
	{
		$html = '';
		$form = new formManager(0);
		$form->setForm('post', $this->form_id);

		if (is_file($this->cgi_bin_path.$file_path))
		{
			$ftp = new ftpManager($this->cgi_bin_path);

			// file infos
			$alt_stat = $ftp->stat($file_path);
			#$ftp->stat_view($alt_stat); # Use this to learn more about ftpManager::stat() method
			$file_permissions = $alt_stat['perms']['octal1'];

			// file content
			$file_content = $ftp->read($file_path);

			// param
			$param = explode(',', $param);

			// html output
			if (!in_array('no-area', $param))
			{
				if (!in_array('no-update', $param))
				{
					$html .= $form->textarea($textarea_name, $file_content, $file_path.'<br />', '', 'cols=100;rows=12').'<br />';
					$html .= $form->submit($textarea_name.'_update', LANG_ADMIN_BUTTON_RECORD);
				} else {
					$html .= $form->textarea($textarea_name, $file_content, $file_path.'<br />', '', 'cols=100;rows=12;readonly').'<br />';
					$html .= $form->submit($textarea_name.'_archive', LANG_ADMIN_COM_PAYMENT_SIPS_CGI_BUTTON_ARCHIVE);
				}
			}
			else {
				$html .= $file_path.' - ';
			}

			$html .= str_replace('{permissions}', $file_permissions, LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PERMISSIONS);
		}
		else {
			$html .= '<p style="color:red;">'.str_replace('{file}', $file_path, LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_FILE).'</p>';
		}

		return $html;
	}



	public function process( $file_path, $textarea_name )
	{
		$filter = new formManager_filter();
		$filter->requestVariable('post');

		$ftp = new ftpManager($this->cgi_bin_path);

		if ($filter->requestValue($textarea_name.'_update')->get())
		{
			if (is_file($this->cgi_bin_path.$file_path))
			{
				$content = $filter->requestValue($textarea_name)->get();

				$result = $ftp->write($file_path, "\n".trim($content)."\n");

				if ($result === false)
				{
					admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CGI_UPDATE_ERROR.$file_path, 'error');
				} else {
					admin_message(LANG_ADMIN_COM_PAYMENT_SIPS_CGI_UPDATE_SUCCESS.$file_path, 'ok');
				}
			}
			else {
				echo '<p style="color:red;">File not found : '.$file_path.'</p>';
			}
		}
		elseif ($filter->requestValue($textarea_name.'_archive')->get())
		{
			// File name
			$file_name = explode('/', $file_path);
			$file_name = $file_name[count($file_name)-1];

			// Archive path
			$archive_path = preg_replace('~'.pregQuote($file_name).'$~', date('Y.m.d_').$file_name, $file_path);

			// Rename current file and create a new empty file
			if (!$ftp->isFile($archive_path))
			{
				$result_rename = $ftp->rename($file_path, $archive_path);
				$result_create = $ftp->write($file_path);

				admin_informResult( $result_rename && $result_create, str_replace('{path}', "\"$archive_path\"", LANG_ADMIN_COM_PAYMENT_SIPS_CGI_ARCHIVE_SUCCESS) );
			} else {
				admin_message( str_replace('{path}', "\"$archive_path\"", LANG_ADMIN_COM_PAYMENT_SIPS_CGI_ARCHIVE_DENIED), 'error');
			}
		}
	}

}

?>