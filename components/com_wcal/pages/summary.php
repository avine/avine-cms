<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


///////////////
// Maintenance

# We have chosen almost arbitrarily to perform the global maintenance of the field 'payment_status' here...
wcal::updatePaymentStatus();

// end
//////


echo '<h1>'.LANG_COM_WCAL_SUMMARY_TITLE.'</h1>';


$wcal = new wcal();
$wcal->donateMonitor();


// Alias
$goto_checkout			= '<a class="button" href="'.$wcal->checkoutUrl(false).'">'.LANG_COM_WCAL_SUMMARY_CHECKOUT.'</a>';
$goto_add_dedicate		= '<a class="button" href="'.comMenu_rewrite('com=wcal&page=dedicate').'">'.LANG_COM_WCAL_DEDICATE_BUTTON_ADD_DEDICATE.'</a>';
$goto_start_dedicate	= '<a class="button" href="'.comMenu_rewrite('com=wcal&page=dedicate').'">'.LANG_COM_WCAL_SUMMARY_START_DEDICATE.'</a>';


// First check for delete request...
$wcal->dedicateSummaryDelete();

// Get summary
$summary = $wcal->dedicateSummary();

// Update the handled designation (donate component)
$wcal->manageDonateHandledDesignation();


if ($wcal->sessionGetDedicateID())
{
	echo $summary;

	echo "<p>$goto_add_dedicate &nbsp; $goto_checkout</p>\n";
}
else
{
	echo	'<p>'.LANG_COM_WCAL_SUMMARY_NO_PENDING."</p>\n<p>$goto_start_dedicate</p>\n";
}

?>