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




//////////////
// Start view

if ($start_view)
{
	// Title
	#echo '<h2>'.LANG_ADMIN_COM_PAYMENT_PAYPAL_TITLE_START.'</h2>';
	echo '<h2>Configuration de la méthode de paiement</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'paypal_');

	$html .= '<p style="color:grey;font-style:italic;">not available for now</p>';

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>