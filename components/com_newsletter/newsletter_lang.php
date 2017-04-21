<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language

// subscribe.php
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_TITLE'							, "S'abonner à la Newsletter" );

define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_FIELDSET'						, "Formulaire" );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_TIPS'							, "Je souhaite m'abonner à la Newsletter, et recevoir ainsi par email des informations sur l'actualité de <strong>\"{site_name}\"</strong>." );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_EMAIL'							, "Email" );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_SUBMIT'							, "S'abonner" );

define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_CONFIRM_WAITING_FOR'				, "<strong>{email}</strong>, votre demande est enregistrée.<br /> Vous allez recevoir un email de <strong>\"demande de confirmation\"</strong>.<br /> Pour valider votre abonnement, cliquez dans le lien d'activation qu'il contient." );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_CONFIRM_SUCCESS'					, "<strong>{email}</strong>, votre abonnement à la Newsletter est confirmé." );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_CONFIRM_ALREADY'					, "<strong>{email}</strong>, vous êtes déjà abonné(e) à la Newsletter !" );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_CONFIRM_INVALID_REQUEST_CODE'	, "Votre demande ne peut être traitée." );

define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_STATUS_OK'						, "<strong>{email}</strong>, vous êtes abonné(e) à la Newsletter." );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_STATUS_NOT_EXACT_SUBSCRIBER'		, "A noter : l'abonnement est associé à un autre de vos comptes, ayant le mail email." );



/*
 * Emails
 */ 

define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_SEND_EMAIL_SUBJECT', "Demande de confirmation" );
define( 'LANG_COM_NEWSLETTER_SUBSCRIBE_SEND_EMAIL_REQUEST',
"
<p>Bonjour,</p>
<p>Vous avez effectu&eacute; une demande d&#39;abonnement &agrave; la Newsletter de <b>{site_name}</b>.</p>
<p>Si vous &ecirc;tes bien l&#39;auteur de cette demande, cliquez dans le lien suivant, pour confirmer votre abonnement :<br />
{request_link}</p>
<p>Sinon, vous pouvez ignorer ce message.</p>
<p>Pour &ecirc;tre s&ucirc;r de recevoir nos Newsletters, pensez &agrave; ajouter notre adresse mail &agrave; votre carnet d&#39;adresses.</p>
"
);



// unsubscribe.php
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_TITLE'							, "Se désabonner de la Newsletter" );

define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_FIELDSET'						, "Formulaire" );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_TIPS'							, "Je souhaite me désabonner de la Newsletter de <strong>\"{site_name}\"</strong>." );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_EMAIL'							, "Email" );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_SUBMIT'						, "Se désabonner" );

define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_WAITING_FOR'			, "<strong>{email}</strong>, votre demande est enregistré.<br /><br /> Vous allez recevoir un email de <strong>\"demande de confirmation\"</strong>.<br /> Pour vous désabonner, cliquez dans le lien qu'il contient." );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_SUCCESS'				, "<strong>{email}</strong>, votre abonnement à la Newsletter est supprimé." );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_ALREADY'				, "<strong>{email}</strong>, vous n'êtes pas abonné(e) à la Newsletter." );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_CONFIRM_INVALID_REQUEST_CODE'	, "Votre demande ne peut être traitée." );

/*
 * Emails
 */ 

define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_SEND_EMAIL_SUBJECT', "Demande de confirmation" );
define( 'LANG_COM_NEWSLETTER_UNSUBSCRIBE_SEND_EMAIL_REQUEST',
"
<p>Bonjour,</p>
<p>Vous avez demand&eacute; la suppression de votre abonnement &agrave; la Newsletter de <b>{site_name}</b>.</p>
<p>Si vous &ecirc;tes bien l&#39;auteur de cette demande, cliquez dans le lien suivant, pour confirmer votre d&eacute;sabonnement :<br />
{request_link}</p>
<p>Sinon, vous pouvez ignorer ce message.</p>
"
);



// archived.php
define( 'LANG_COM_NEWSLETTER_ARCHIVED_TITLE'							, "Newsletters archivées" );

define( 'LANG_COM_NEWSLETTER_ARCHIVED_NOT_FOUNDED'						, "Désolé, la Newsletter que vous avez demandé est introuvable !" );
define( 'LANG_COM_NEWSLETTER_ARCHIVED_EMPTY'							, "Il n'y a aucune Newsletter archivée..." );


?>