<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Donate class
$donate = new comDonate_();
$checkout = $donate->checkoutInfos($amount_cents, $currency_code); # $amount_cents, $currency_code are passed by reference


echo '<h1>'.LANG_COM_DONATE_CHECKOUT_TITLE.'</h1>';

if (!$checkout)
{
	$html  = LANG_COM_DONATE_CHECKOUT_NO_PENDING_DONATION;
	$html .= '<br /><a href="'.comMenu_rewrite('com=donate&amp;page=index').'">'.LANG_COM_DONATE_CHECKOUT_GO_TO_INDEX.'</a>';
	echo $html;
}
else
{
	// Begin : New Payment
	$payment = new comPayment_( LANG_COM_DONATE_PAYMENT_ORIGIN );

	// Optionals infos
	$normal_return_url 		= str_replace('&amp;', '&', comMenu_rewrite('com=donate&page=thankyou')); # ampersand SIPS limitation
	$cancel_return_url 		= str_replace('&amp;', '&', comMenu_rewrite('com=donate&page=thankyou')); # ampersand SIPS limitation
	$automatic_response_url = siteUrl().'/components/com_donate/autoresponse.php';

	$payment_details =
		array(
			'normal_return_url'			=>	$normal_return_url,
			'cancel_return_url'			=>	$cancel_return_url,
			'automatic_response_url'	=>	$automatic_response_url,
			'caddie'					=>	LANG_COM_DONATE_PAYMENT_ORIGIN
		);

	// Set payment details
	$payment->paymentDetails( $amount_cents, $currency_code, $payment_details );

	// Try to generate payment request
	$payment_id = $payment->getPaymentID($data);
	$tmpl_name = false; # false to disable or use (for example) the default template: 'tmpl_methods_form.html'
	echo $payment->displayPaymentMethodsForm($data, $tmpl_name);
	// End of : New Payment

	echo '<p>'.LANG_COM_DONATE_CHECKOUT_SSL_TIPS.'</p>';

	if ($payment_id)
	{
		$donate->setPaymentID($payment_id);

		// If you need, you can force the logout of user (if logged)
		#global $g_user_login;
		#$g_user_login->autoLogout();
	}

}

?>