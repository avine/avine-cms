<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

function admin_comPayment_validatedOptions()
{
	return
		array(
			'root'	=>	LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER_ROOT,
			'1'		=>	LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER_1,
			'0'		=>	LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER_0
		);
}

?>