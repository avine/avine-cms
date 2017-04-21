<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


///////////
// Process

// Posted forms possibilities
$submit = formManager::isSubmitedForm('config_', 'post');



// Update
if ($submit)
{
	$filter = new formManager_filter();
	$filter->requestVariable('post');

	// debug
	$filter->requestValue('debug')->get() ? $debug = 1 : $debug = 0;

	// payment_id_offset
	if (!$db->selectCount('payment'))
	{
		$payment_id_offset = $filter->requestValue('payment_id_offset')->getInteger(1, LANG_ADMIN_COM_PAYMENT_CONFIG_INVALID_PAYMENT_ID_OFFSET);
		$query_payment_id_offset = ', payment_id_offset='.$payment_id_offset;
	}
	else
	{
		$query_payment_id_offset = ''; # For high level security reason, don't even touch the 'payment_id_offset' field !
	}

	if ($filter->validated())
	{
		$result = $db->update( "payment_config; debug=$debug $query_payment_id_offset" );
		admin_informResult($result);

		// In DEBUG-MODE, allow the 'generic' method
		if ($result) {
			$db->update("payment_method; activated=$debug; where: alias='generic'");
		}
	}
	else
	{
		echo $filter->errorMessage();
	}
}


//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$config = $db->select('payment_config, *');
	$config = $config[0];

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'config_');

	// debug
	$html .= $form->checkbox('debug', $config['debug'], LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG_FIELD, '');
	if ($config['debug'])
	{
		$html .= LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG_Y;
	}
	else
	{
		$html .= LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG_N;
		$html .= '<br /><span style="color:grey;">'.LANG_ADMIN_COM_PAYMENT_CONFIG_GENERIC_METHOD_ACTIVATED_TIPS.'</span>';
	}
	$html .= '<br /><br />';

	// payment_id_offset (can be initialized ONLY before there's any 'payment' table record)
	if (!$db->selectCount('payment'))
	{
		$html .= $form->text('payment_id_offset', $config['payment_id_offset'], LANG_ADMIN_COM_PAYMENT_CONFIG_PAYMENT_ID_OFFSET.'<br />', '', 'size=8');
		$html .= '<span style="color:red;">'.LANG_ADMIN_COM_PAYMENT_CONFIG_PAYMENT_ID_OFFSET_TIPS.'</span>';
	}
	else
	{
		$html .= $form->text('payment_id_offset', $config['payment_id_offset'], LANG_ADMIN_COM_PAYMENT_CONFIG_PAYMENT_ID_OFFSET.'<br />', '', 'size=8;disabled=yes');
	}
	$html .= '<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>