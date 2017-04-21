<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language


// Fields for 'donate' table
define('LANG_COM_DONATE_ID'													, "Identifiant" );
define('LANG_COM_DONATE_RECORDING_DATE'										, "Date d'enregistrement" );
define('LANG_COM_DONATE_CONTRIBUTOR'										, "Donateur" );
define('LANG_COM_DONATE_USER_ID'											, "Utilisateur" );
define('LANG_COM_DONATE_PAYMENT_ID'											, "Référence de paiement" );

// Fields for 'donate_details' table
define('LANG_COM_DONATE_DETAILS_ID'											, "Identifiant" );
define('LANG_COM_DONATE_DETAILS_DESIGN_ID'									, "Affectation" );
define('LANG_COM_DONATE_DETAILS_AMOUNT'										, "Montant" );
define('LANG_COM_DONATE_DETAILS_CURRENCY_CODE'								, "Devise" );

// About 'designation' table
define('LANG_COM_DONATE_DESIGNATION_LINK_READ_MORE'							, "En savoir plus...");

// About 'invoice' table
define('LANG_COM_DONATE_INVOICE_TITLE'										, "Reçu Cerfa");
define('LANG_COM_DONATE_INVOICE_HTML'										, "Version HTML du reçu Cerfa");
define('LANG_COM_DONATE_INVOICE_PDF'										, "Version PDF du reçu Cerfa");



// index.php
define('LANG_COM_DONATE_TITLE'												, "<span>Votre don en ligne :</span> Formulaire" );
define('LANG_COM_DONATE_SUMMARY_TITLE'										, "<span>Votre don en ligne :</span> Récapitulatif" );
define('LANG_COM_DONATE_LOGIN_TITLE'										, "<span>Votre don en ligne :</span> Identification" );

define('LANG_COM_DONATE_LOGIN_REQUEST'										, "Déjà enregistré ?<br />Identifiez-vous." );
define('LANG_COM_DONATE_LOGIN_BACK_TO_DONATE_FORM'							, "Nouvel utilisateur ?<br />Retournez au formulaire de don." );

define('LANG_COM_DONATE_FIELDSET_DONATE'									, "Montant et affectation" );
define('LANG_COM_DONATE_FIELDSET_DONOR'										, "Informations personnelles" );

define('LANG_COM_DONATE_FORM_SUBMIT'										, "Enregistrer le don" );
define('LANG_COM_DONATE_NO_DESIGNATION_AVAILABLE'							, "Aucune affectation de don actuellement disponible." );

define('LANG_COM_DONATE_FORM_ERROR_TITLE'									, "Formulaire non valide !" );
define('LANG_COM_DONATE_FORM_ERROR_AMOUNT_NULL'								, "Aucun montant de don renseigné" );

define('LANG_COM_DONATE_FORM_DONOR_ANONYMOUS'								, "Je fais un don anonyme" );
define('LANG_COM_DONATE_FORM_DONOR_ANONYMOUS_TIPS'							, "J'ai noté que dans ce cas, je ne recevrai pas de reçu Cerfa." );

define('LANG_COM_DONATE_FORM_DONOR_RECEIPT'									, "Je saisis mes coordonnées" );
#define('LANG_COM_DONATE_FORM_DONOR_RECEIPT_TIPS'							, "Dans ce cas, un reçu Cerfa me sera délivré.<br /> Il sera disponible au téléchargement après avoir cliqué dans le bouton <em>\"Retour à la boutique\"</em>, une fois mon règlement validé." );
define('LANG_COM_DONATE_FORM_DONOR_RECEIPT_TIPS'							, "Le reçu Cerfa sera alors disponible au téléchargement en cliquant sur le bouton <em>\"Retour à la boutique\"</em>, une fois mon règlement validé." );

define('LANG_COM_DONATE_FORM_DONOR_REGISTRATION'							, "Je crée mon compte utilisateur" );
#define('LANG_COM_DONATE_FORM_DONOR_REGISTRATION_TIPS'						, "Dans ce cas, le reçu Cerfa me sera également envoyé par mail.<br /> Autres avantages, j'accède à l'historique de mes dons, et je peux à tout instant consulter mes reçus Cerfa en ligne.<br /> Je simplifie mes demandes de renseignements éventuels. Les prochaines fois, je ne ressaisis pas mes coordonnées." );
define('LANG_COM_DONATE_FORM_DONOR_REGISTRATION_TIPS'						, "Le reçu Cerfa me sera également envoyé par mail. Autres avantages, j'accède à l'historique de mes dons, et je peux à tout instant consulter mes reçus Cerfa en ligne. Je simplifie mes demandes de renseignements éventuels. Les prochaines fois, je ne ressaisis pas mes coordonnées." );

define('LANG_COM_DONATE_FORM_DONOR_UPDATE_USER_ACCOUNT'						, "Mettre à jour mon profil utilisateur" );

define('LANG_COM_DONATE_FORM_DONOR_ANONYM_NOT_COMPATIBLE_WITH_REGIST'		, "Pour créer votre compte utilisateur, vous devez saisir vos coordonnées." );

define('LANG_COM_DONATE_FORM_DONOR_REGISTRATION_COMPLETED'					, "Votre compte utilisateur a bien été créé. Pour plus d'informations, consultez l'email qui vous a été envoyé à l'adresse suivante : <b>{email}</b>." );

define('LANG_COM_DONATE_SUMMARY_RECORDING_DATE'								, "Date d'enregistrement" );
define('LANG_COM_DONATE_SUMMARY_CONTRIBUTOR'								, "Vous" );

define('LANG_COM_DONATE_SUMMARY_RECEIPT_TIPS'								, "Le reçu Cerfa sera établi à cette adresse." );
define('LANG_COM_DONATE_SUMMARY_ANONYMOUS_TIPS'								, "Votre don est anonyme. Aucun reçu Cerfa ne vous sera délivré." );

define('LANG_COM_DONATE_SUMMARY_EMAIL'										, "Email de contact" );
define('LANG_COM_DONATE_SUMMARY_REGISTRATION_COMPLETED'						, "Votre compte utilisateur a bien été créé. Pour plus d'informations, consultez l'email qui vient de vous être envoyé à votre adresse email de contact." );

define('LANG_COM_DONATE_SUMMARY_DONATE_DETAILS'								, "Votre don" );
define('LANG_COM_DONATE_SUMMARY_AMOUNT_TOTAL'								, "Total" );

define('LANG_COM_DONATE_SUMMARY_RELOAD_FORM'								, "Modifier" );
define('LANG_COM_DONATE_SUMMARY_CHECKOUT'									, "Régler maintenant" );



// checkout.php
define('LANG_COM_DONATE_CHECKOUT_TITLE'										, "<span>Votre don en ligne :</span> Règlement" );

define('LANG_COM_DONATE_CHECKOUT_SSL_TIPS'									, "<b>Votre paiement est réalisé en toute sécurité !</b> Après avoir cliqué sur le mode de paiement de votre choix, vous allez être redirigé vers le serveur de paiement sécurisé de la banque. Cela signifie que toutes les informations bancaires que vous allez échanger avec la banque sont protégées par une connexion chiffrée de haut niveau, garantissant leur confidentialité." );

define('LANG_COM_DONATE_CHECKOUT_NO_PENDING_DONATION'						, "Pas de dons en attente...");
define('LANG_COM_DONATE_CHECKOUT_GO_TO_INDEX'								, "Faire un don maintenant");

define('LANG_COM_DONATE_PAYMENT_ORIGIN'										, "Donation");



// thankyou.php
define('LANG_COM_DONATE_THANKYOU_MESSAGE_VALIDATED'							, "Merci de votre générosité et de votre confiance.");
define('LANG_COM_DONATE_THANKYOU_MESSAGE_NOT_VALIDATED'						, "");



// list.php
define('LANG_COM_DONATE_LIST_TITLE'											, "Liste des dons effectués" );
define('LANG_COM_DONATE_LIST_NO_USER_LOGGED'								, "Vous devez vous connecter pour accéder à cette page." );
define('LANG_COM_DONATE_LIST_EMPTY_LIST'									, "Vous n'avez pas de don enregistré." );

define('LANG_COM_DONATE_LIST_PAYMENT_ID'									, "Réf. de paiement");
define('LANG_COM_DONATE_LIST_DONATE_DETAILS'								, "Détails du don");
define('LANG_COM_DONATE_LIST_RECEIPT_ADRESS'								, "Adresse Cerfa");
define('LANG_COM_DONATE_LIST_DONATE_VALIDATED'								, "Validé");

define('LANG_COM_DONATE_LIST_DONATE_VALIDATED_AMOUNT_TOTAL'					, "Montant total des dons validés");



// autoresponse.php

/* Emails messages */
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_SUBJECT'				, "Don en ligne");
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN',
"<p>Bonjour,<br />Administrateur/Comptable de <b>{site_name}</b>,</p>
<p>Un nouveau don en ligne &agrave; &eacute;t&eacute; r&eacute;alis&eacute;.</p>
<p>Don : <b>PID{payment_id}</b> (PxREF{payment_x_ref})<br />
Montant : <b>{amount}</b><br />
Date de r&egrave;glement : <b>{payment_date}</b><br />
Valid&eacute; : <b>{validated}</b></p>
<div>Donateur :<br />
{all_user_details}</div>
{invoice_links}"
);
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_VALIDATED'				, "OUI");
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_NOT_VALIDATED'			, "NON");
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_ADMIN_ANONYMOUS'				, "Anonyme");

define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_TIPS'		, "Le re&ccedil;u Cerfa de votre don se trouve en pièce jointe de ce mail.<br /> Si besoin, vous pouvez également le t&eacute;l&eacute;charger à tout moment gr&acirc;ce aux liens ci-dessous.");

define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_PDF'		, "T&eacute;l&eacute;charger le re&ccedil;u Cerfa au format PDF");
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_DOWNLOAD_INVOICE_HTML'		, "T&eacute;l&eacute;charger le re&ccedil;u Cerfa au format HTML");

define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER_SUBJECT'				, "Votre don en ligne");
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER',
"<p>Bonjour,</p>
<p><b>{site_name}</b> vous informe.</p>
<p>{validated_message}</p>
<p>D&eacute;tails concernant votre don :<br />
R&eacute;f&eacute;rence de dossier : <b>PID{payment_id}</b><br />
Montant : <b>{amount}</b><br />
Date de r&egrave;glement : <b>{payment_date}</b><br />
Valid&eacute; : <b>{validated}</b></p>
{invoice_links}"
);
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER_VALIDATED'				, "Votre don en ligne a bien été enregistré.\nMerci de votre générosité et de votre confiance.");
define('LANG_COM_DONATE_AUTORESPONSE_SEND_MAIL_USER_NOT_VALIDATED'			, "Nous sommes désolés, mais le règlement de votre don en ligne n'a pas été validé par votre banque.\nVotre compte n'a pas été débité.");
/* end of : emails */


?>