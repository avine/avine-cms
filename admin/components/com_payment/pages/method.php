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

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$activated_status = $filter->requestValue('activated_status', 'get')->getInteger(); // (4)
$upd = $filter->requestName('upd_')->getInteger(); // (2)
$submit = formManager::isSubmitedForm('method_', 'post'); // (1)

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)



// (4) Activated status
if ($activated_status)
{
	$activated = $db->select("payment_method, activated, where: id=$activated_status");
	if ($activated)
	{
		$activated[0]['activated'] == 1 ? $new_status = '0' : $new_status = '1';

		$db->update("payment_method; activated=$new_status; where: id=$activated_status");
	}
}



// (2) Update
if ($upd_submit)
{
	$upd_submit_validation = true;

	// Fields validation
	$filter->reset();
	$upd_id = $filter->requestValue('id')->getInteger();
	$name = $filter->requestValue('name')->getNotEmpty();

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update("payment_method; name=".$db->str_encode($name)."; where: id=$upd_id");

		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_METHOD_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : $upd_id = $filter->requestValue('id')->getInteger();

	$current = $db->select("payment_method, *, where: id=$upd_id");
	$current = $current[0];

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= LANG_ADMIN_COM_PAYMENT_METHOD_ALIAS.' : <strong>'.$current['alias'].'</strong>&nbsp; &nbsp;';
	$html .= $form->text('name', $current['name'], LANG_ADMIN_COM_PAYMENT_METHOD_NAME, '', 'maxlength=100');
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	$html .= $form->end().'<br />';
	echo $html;
}



// (1) Submit
if ($submit)
{
	$payment_id = $filter->requestName('payment_order_')->getInteger();
	!is_array($payment_id) ? $payment_id = array($payment_id) : '';
	if ($payment_id)
	{
		for ($i=0; $i<count($payment_id); $i++)
		{
			$order = $filter->requestValue('payment_order_'.$payment_id[$i])->getInteger();
			if ($order !== false) {
				$db->update('payment_method; payment_order='.$order.'; where: id='.$payment_id[$i]);
			}
		}
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_PAYMENT_METHOD_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'method_');

	// Show 'generic' payment method ?
	$class_payment = new comPayment_();
	if (!$class_payment->getConfig('debug'))
	{
		$query_generic = ", where: alias!='generic'";
	}
	else
	{
		$query_generic = '';
		echo admin_message(LANG_ADMIN_COM_PAYMENT_METHOD_GENERIC_METHOD_ACTIVATED_TIPS, 'tips');
	}

	$payment = $db->select('payment_method, *, payment_order(asc)'.$query_generic);
	$one_activated = false;
	for ($i=0; $i<count($payment); $i++)
	{
		$payment[$i]['payment_order'] = $form->text('payment_order_'.$payment[$i]['id'], (2*$i +1), '', '', 'size=1;update=no'); // (1)

		if ($payment[$i]['activated']) $one_activated = true;
		$payment[$i]['activated'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;activated_status='.$payment[$i]['id'].'">'.admin_replaceTrueByChecked($payment[$i]['activated']).'</a>'; // (4)

		$update[$i] = $form->submit('upd_'.$payment[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');	 // (2)
	}
	if (!$one_activated) {
		admin_message(LANG_ADMIN_COM_PAYMENT_METHOD_NO_METHOD_ACTIVATED, 'warning');
	}

	// Table
	$payment_header = 
		array(
			LANG_ADMIN_COM_PAYMENT_METHOD_ID,
			LANG_ADMIN_COM_PAYMENT_METHOD_ALIAS,
			LANG_ADMIN_COM_PAYMENT_METHOD_NAME,
			LANG_ADMIN_COM_PAYMENT_METHOD_ORDER,
			LANG_ADMIN_COM_PAYMENT_METHOD_ACTIVATED
		);
	$table = new tableManager($payment, $payment_header);

	if (count($payment)) {
		$table->addCol($update, 999, '');
	}
	$table->delCol('0'); // Delete id column

	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (1)
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>