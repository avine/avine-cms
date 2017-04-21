<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

// Fields for 'newsletter_config' table
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_RETURN_PATH'					, "Chemin de retour (return-path)" );
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_REPLY_TO'						, "Adresse de réponse par défaut (Reply-to)" );
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_BATCH_SIZE'					, "Taille des paquets" );
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_REFRESH_TIME'					, "Temps de rafraichissement" );

// Fields for 'newsletter_tmpl' table
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_NAME'							, "Description" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_HEADER'							, "Entête" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_FOOTER'							, "Pied de page" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_ITEM1'							, "Article (par défaut)" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_ITEM2'							, "Article (alternatif)" );

// Fields for 'newsletter' table
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBJECT'								, "Objet" );
define( 'LANG_ADMIN_COM_NEWSLETTER_MESSAGE'								, "Message" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SENDER'								, "Expéditeur (From)" );
define( 'LANG_ADMIN_COM_NEWSLETTER_REPLY_TO'								, "Adresse de réponse (Reply-to)" );
define( 'LANG_ADMIN_COM_NEWSLETTER_DATE_CREATION'						, "Date de création" );

// Fields for 'newsletter_send' table
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_DATE_BEGIN'						, "Début" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_DATE_END'						, "Fin" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_SENT_COUNT'						, "Nbre d'emails" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_HITS'							, "Nbre de clics" );



define( 'LANG_ADMIN_COM_NEWSLETTER_INDEX_TITLE'							, "Gestion de la newsletter" );
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_TITLE_START'					, "Configuration de la newsletter" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_TITLE_START'					, "Modèles de la newsletter" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TITLE_START'					, "Listes des newsletters" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_TITLE_START'				, "Liste des abonnements" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_TITLE_START'					, "Envoyer une newsletter" );



// config.php
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_DEFAULT_VALUES_INFO'			, "Les valeurs par défaut ont été initialisé." );
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_EMAILS'						, "emails" );
define( 'LANG_ADMIN_COM_NEWSLETTER_CONFIG_SECONDES'						, "secondes" );



// tmpl.php
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_BUTTON_NEW'						, "Nouveau modèle" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_ERROR_DUPLICATE_NAME'			, "Ce description est déjà utilisée pour un autre modèle" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_DEL_WARNING'					, "Le modèle est utilisé par des newsletters, il ne peut être effacé." );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_TITLE_UPDATE'					, "Mise à jour du modèle" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_LOAD_SAMPLE'					, "Charger le modèle d'exemple" );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_KEYWORDS'						, "Mots clés : " );
define( 'LANG_ADMIN_COM_NEWSLETTER_TMPL_KEYWORDS_NONE'					, "aucun" );



// list.php
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_NO_TMPL_DEFINED'				, "Il faut d'abord créer au moins un modèle de Newsletter." );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_USE_TEXT_EDITOR_Y'				, "Basculer l'édition en mode WYSIWYG" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_USE_TEXT_EDITOR_N'				, "Basculer l'édition en mode texte" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_INCLUDE_TEMPLATE'				, "Inclure le modèle dans la fenêtre de l'éditeur" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_INCLUDE_TEMPLATE_TIPS'			, "A noter : seules modifications apportées au message seront prises en compte.<br /> Pour modifier le modèle, utiliser l'onglet \"Modèle\"." );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TITLE_UPDATE'					, "Mise à jour de la Newsletter" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_UPD_FIELDSET_TMPL'				, "Modèle" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_UPD_FIELDSET_MESSAGE'			, "Message" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_UPD_FIELDSET_SENDER'			, "Expéditeur" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_UPD_FIELDSET_SEND_TEST'			, "Envoyer un message de test" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_SELECT_TMPL'					, "Modèle de Newsletter" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_SELECT_ITEM'					, "Modèle des articles" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_LATEST_CONTENT_TITLE'			, "Concevoir la Newsletter à partir des récents articles" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_LATEST_CONTENT'					, "Articles des 30 derniers jours :" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_LATEST_CONTENT_TIPS'			, "Pour ajouter des articles à la Newsletter, indiquez l'odre d'apparition dans la case correspondante." );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TMPL_CSS_AVAILABLE_Y'			, "Styles détectés" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TMPL_CSS_AVAILABLE_N'			, "Styles non détectés" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TMPL_CKEDITOR_DEFAULT_MESSAGE'	, "Rédigez votre message ici..." );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TMPL_CKEDITOR_SEPARATOR'		, "<!-- SEPARATOR -->" ); # Do not change !
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_TMPL_CKEDITOR_SEP_MISSING'		, "Les commentaires HTML \"&lt;!-- SEPARATOR --&gt;\" ont disparu. Les modifications n'ont pas été prises en compte." );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_DEL_ERROR'						, "Cette newsletter est associée à des envois. Elle ne peut être effacée." );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_SEND_TEST_SUCCESS'				, "Message de test envoyé à : <b>{email}</b>" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_SEND_TEST_FAILURE'				, "Impossible d'envoyer le message de test !" );
define( 'LANG_ADMIN_COM_NEWSLETTER_LIST_SEND_TEST_INVALID_EMAIL'		, "Le message de test n'a pu être envoyé. L'adresse email mentionnée n'est pas valide." );



// send.php
define( 'LANG_ADMIN_COM_NEWSLETTER_BUTTON_PREPARE_SEND'					, "Nouvel envoi" );
define( 'LANG_ADMIN_COM_NEWSLETTER_BUTTON_SEND'							, "Envoyer" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_NOT_FINISHED'					, "Non terminé..." );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_NEW_TITLE'						, "Préparer l'envoi" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_NEW_OPTIONS'					, "Sélectionner la Newsletter à envoyer" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE'					, "Newsletter en cours d'envoi" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_NO_NEWSLETTER_AVAILABLE'		, "Aucune newsletter prête à l'envoi." );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_TASK_START'						, "Date d'envoi" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SEND_TASK_LENGTH'					, "Durée de l'envoi" );



// _batchsend.php
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_SESSION_MISSING'			, "Aucune opération en cours !" );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_CONFIG_MISSING'			, "La configuration des newsletters n'a pas été initialisée !" );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_SUBSCRIBER_MISSING'		, "Aucun abonné à la newsletter !" );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_SUBJECT'					, "Objet : " );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_BATCH'						, "Paquet : " );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_COUNTDOWN'					, "Prochaine étape dans : " );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_EMAILS_LIST'				, "email(s) envoyé(s) : " );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_WAIT'						, "Patience, l'envoi n'est pas terminé !" );
define( 'LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_FINISH'					, "L'envoi est terminé !" );



// subscriber.php
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_EMAIL'					, "Email" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_UID'						, "UID" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_ACTIVATED'				, "Abonnement" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_UID_ANONYMOUS'			, "Anonyme" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_CSV'						, "CSV : Liste des abonnements activés" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_CSV_SUMMARY'				, "Mailing liste des abonnements à la Newsletter" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_TITLE_NEW'				, "Nouvel abonnement" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_NEW_SUBSCRIBED_ALREADY'	, "<b>\"{email}\"</b> est déjà abonné(e) à la Newsletter !" );
define( 'LANG_ADMIN_COM_NEWSLETTER_SUBSCRIBER_NEW_SUBSCRIBED_SUCCESS'	, "<b>\"{email}\"</b> est maintenant abonné(e) à la Newsletter !" );

?>