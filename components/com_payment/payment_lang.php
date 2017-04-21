<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// class money
define('LANG_CLASS_MONEY_CURRENCY_CODE_978'					, "Euro");
define('LANG_CLASS_MONEY_CURRENCY_CODE_840'					, "Dollar");
define('LANG_CLASS_MONEY_CURRENCY_CODE_826'					, "Livre Sterling");

define('LANG_CLASS_MONEY_CURRENCIES_CODE_978'				, "Euros");
define('LANG_CLASS_MONEY_CURRENCIES_CODE_840'				, "Dollars");
define('LANG_CLASS_MONEY_CURRENCIES_CODE_826'				, "Livres Sterling");

define('LANG_CLASS_MONEY_INVALID_AMOUNT_UNITS'				, "Le montant est invalide");
define('LANG_CLASS_MONEY_AMOUNT_MIN_NOT_REACHED'			, "Le montant minimum est de : {amount_min}");
// end of : class money


define('LANG_COM_PAYMENT_NO_METHOD_AVAILABLE'				, "Nous sommes désolés, mais aucune méthode de paiement n'est actuellement disponible.");
define('LANG_COM_PAYMENT_SELECT_METHOD'						, "Sélectionner une méthode de paiement");
define('LANG_COM_PAYMENT_NO_METHOD_SELECTED'				, "Aucune méthode de paiement sélectionnée");

define('LANG_COM_PAYMENT_DB_ERROR_OCCURED'					, "Une erreur s'est produite ! Notre base de données est momentanément indisponible.<br /><b>Si une interface de paiement est affichée, n'en tenez pas compte.</b><br />Merci de réessayer ultérieurement.");


define('LANG_COM_PAYMENT_GENERIC_ORIGIN'					, "");


define('LANG_COM_PAYMENT_CHECK_PAYMENT_MISSING_ID'			, "!"); # Intégrité des tables
define('LANG_COM_PAYMENT_CHECK_PAYMENT_TRANSMISSION_DATE'	, "Date d'envoi");
define('LANG_COM_PAYMENT_CHECK_PAYMENT_AMOUNT'				, "Montant");
define('LANG_COM_PAYMENT_CHECK_PAYMENT_CURRENCY_CODE'		, "Devise");
define('LANG_COM_PAYMENT_CHECK_PAYMENT_PAYMENT_DATE'		, "Date de réception");
define('LANG_COM_PAYMENT_CHECK_PAYMENT_VALIDATED'			, "Validé");


# The following constant has been replaced by : LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID
#define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_PAYMENT_ID'				, "ID de paiement" );
define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_MISSING_ID'				, "!" );  /* Intégrité des tables */
define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_TRANSMISSION_DATE'			, "Date d'envoi" );
define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_AMOUNT'					, "Montant" );
define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_CURRENCY_CODE'				, "Devise" );
define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_PAYMENT_DATE'				, "Date de réception" );
define( 'LANG_ADMIN_COM_PAYMENT_PAYMENT_TEMP_VALIDATED'					, "Validé" );

?>