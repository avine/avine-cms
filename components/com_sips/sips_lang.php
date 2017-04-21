<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


define('LANG_COM_PAYMENT_SIPS_MINIMUM_AMOUNT_NOT_REACHED'		, "Paiements inférieurs à 1 {currency_name} non autorisés avec cette méthode.");
define('LANG_COM_PAYMENT_SIPS_CURENCY_CODE_NOT_AVAILABLE'		, "La devise '<b>{currency_name}</b>' n'est actuellement pas disponible avec cette méthode de paiement.");



define('LANG_COM_PAYMENT_SIPS_RESQUEST_DB_FAILED'				, "Nous sommes désolés, mais un problème technique est survenu.<br />Notre base de données est momentanément indisponible.<br /><b>L'opération a été annulée.</b><br />Merci de rééssayer ultérieurement.");
define('LANG_COM_PAYMENT_SIPS_REQUEST_AMOUNT'					, "Montant du paiement : ");

define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUCCESS'					, "Votre paiement a bien été enregistré.");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_FAILED'					, "Nous sommes désolés, mais votre paiement n'a pas été accepté.");

define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_TITLE'			, "Résumé de la transaction");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_PAYMENT_ID'		, "Référence de dossier");		# The reference of the transaction in the website ('id' field in the 'payment' table)
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_PAYMENT_X_ID'	, "N° de transaction");			# The transaction number wich appear on the payment-server pages
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_AMOUNT'			, "Montant");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_PAYMENT_MEANS'	, "Moyen de paiement");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_CARD_NUMBER'		, "N° de carte");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_DATE'			, "Date de paiement");

define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_REF_SYMBOL'		, "<span style=\"color:red;font-weight:bold;\">&nbsp;<sup>(*)</sup>&nbsp;</span>");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_REF_TIPS_ID'		, "Référence à indiquer pour toute demande de renseignements complémentaires.<br />(Différent du <i>numéro de transaction</i> indiqué par le serveur de paiement sécurisé de la banque)");
define('LANG_COM_PAYMENT_SIPS_RESPONSE_SUMMARY_REF_TIPS_X_ID'	, "Référence à indiquer pour toute demande de renseignements complémentaires.<br />(Identique au numéro de transaction indiqué par le serveur de paiement sécurisé de la banque)");


define('LANG_COM_PAYMENT_SIPS_IP_ADDR'							, "Pour des raisons de sécurité, nous enregistrons votre adresse IP : ");

?>