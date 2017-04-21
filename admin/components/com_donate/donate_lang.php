<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define('LANG_ADMIN_COM_DONATE_INDEX_TITLE'						, "Gestion des dons");

// Fields for 'donate_config' table
define('LANG_ADMIN_COM_DONATE_CONFIG_CURRENCY_CODE'				, "Devise");
define('LANG_ADMIN_COM_DONATE_CONFIG_AMOUNT_MIN'				, "Montant minimum");
define('LANG_ADMIN_COM_DONATE_CONFIG_REGISTRATION_SILENT'		, "Forcer l'inscription silencieuse");
define('LANG_ADMIN_COM_DONATE_CONFIG_ACCOUNTANT_EMAIL'			, "Email du comptable");
define('LANG_ADMIN_COM_DONATE_CONFIG_INVOICE_NUM'				, "Numéro du premier reçu Cerfa");
define('LANG_ADMIN_COM_DONATE_CONFIG_RECIPENT_NAME'				, "Nom du bénéficiaire");
define('LANG_ADMIN_COM_DONATE_CONFIG_RECIPENT_ADRESS'			, "Adresse du bénéficiaire");

// Fields for 'donate_designation' table
define('LANG_ADMIN_COM_DONATE_DESIGNATION_ID'					, "Identifiant");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE'				, "Titre");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_COMMENT'				, "Description");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_LINK'					, "Lien");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_IMAGE'				, "Image");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT'				, "Montant fixe");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_DESIGN_ORDER'			, "Ordre");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_PUBLISHED'			, "Publié");

// Fields for 'donate' table
define('LANG_ADMIN_COM_DONATE_ID'								, "ID");
define('LANG_ADMIN_COM_DONATE_RECORDING_DATE'					, "Date d'enregistrement");
define('LANG_ADMIN_COM_DONATE_CONTRIBUTOR'						, "Donateur");
define('LANG_ADMIN_COM_DONATE_USER_ID'							, "Utilisateur");
# The following constant has been replaced by : 'LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID'
#define('LANG_ADMIN_COM_DONATE_PAYMENT_ID'						, "payment_ID"); # !Do not change this!

// Fields for 'donate_details' table
define('LANG_ADMIN_COM_DONATE_DETAILS_ID'						, "Identifiant");
define('LANG_ADMIN_COM_DONATE_DETAILS_DESIGN_ID'				, "Affectation");
define('LANG_ADMIN_COM_DONATE_DETAILS_AMOUNT'					, "Montant");
define('LANG_ADMIN_COM_DONATE_DETAILS_CURRENCY_CODE'			, "Devise");

// Fields for 'donate_invoice' table
define('LANG_ADMIN_COM_DONATE_INVOICE_ID'						, "N° de Cerfa");
define('LANG_ADMIN_COM_DONATE_INVOICE_DONATE_ID'				, "ID de don");
define('LANG_ADMIN_COM_DONATE_INVOICE_TITLE'					, "Reçu Cerfa");



// For all scripts
define('LANG_ADMIN_COM_DONATE_ALL_RECEIPT_ADDRESS'				, "Adresse Cerfa");
define('LANG_ADMIN_COM_DONATE_ALL_AMOUNT_TOTAL'					, "Montant total");
define('LANG_ADMIN_COM_DONATE_ALL_DETAILS'						, "Détails du don");
define('LANG_ADMIN_COM_DONATE_ALL_DONATE_ANONYMOUS'				, "anonyme");
define('LANG_ADMIN_COM_DONATE_ALL_USER_GUEST'					, "invité");



// config.php
define('LANG_ADMIN_COM_DONATE_CONFIG_TITLE_START'				, "Configuration des dons");

define('LANG_ADMIN_COM_DONATE_CONFIG_FIELDSET_AMOUNT'			, "Montant des dons");
define('LANG_ADMIN_COM_DONATE_CONFIG_FIELDSET_INVOICE'			, "Ordre de reçu Cerfa");
define('LANG_ADMIN_COM_DONATE_CONFIG_FIELDSET_OTHER'			, "Divers");

define('LANG_ADMIN_COM_DONATE_CONFIG_RECIPENT_ADRESS_TIPS'		, "Eventuellement sur plusieurs lignes.");

define('LANG_ADMIN_COM_DONATE_CONFIG_ERROR_AMOUNT'				, "N'entrer que des caractères numériques.");
define('LANG_ADMIN_COM_DONATE_CONFIG_ERROR_ACCOUNTANT_EMAIL'	, "L'adresse mail n'est pas valide.");
define('LANG_ADMIN_COM_DONATE_CONFIG_ERROR_INVOICE_NUM'			, "Entrer un nombre supérieur ou égal à 1.");

define('LANG_ADMIN_COM_DONATE_CONFIG_INVOICE_NUM_TIPS'			, "Modifiable uniquement avant le premier don.");



// designation.php
define('LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE_START'			, "Affectation des dons");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE_NEW'			, "Nouvelle affectation");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE_UPDATE'			, "Mise à jour de l'affectation");

define('LANG_ADMIN_COM_DONATE_DESIGNATION_DUPLICATE_TITLE'		, "Ce titre est déjà utilisé pour une autre affectation.");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_ID_USED'				, "L'affectation est utilisée pour des dons.");

define('LANG_ADMIN_COM_DONATE_DESIGNATION_NO_DESIGN_ENTRY'		, "Attention : aucune affectation définie !");
define('LANG_ADMIN_COM_DONATE_DESIGNATION_NO_DESIGN_PUBLISHED'	, "Attention : aucune affectation publiée !");

define('LANG_ADMIN_COM_DONATE_GET_DESIGN_ID_TITLE'				, "Formulaire de cette affectation");

define('LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT_TIPS'			, "optionnel");



// waiting.php
define('LANG_ADMIN_COM_DONATE_WAITING_TITLE_START'				, "Dons en attente");

define('LANG_ADMIN_COM_DONATE_WAITING_NO_PAYMENT_IS_NULL'		, "Pas de dons en attente.");
define('LANG_ADMIN_COM_DONATE_WAITING_PURGE_BUTTON'				, "Purger maintenant");
define('LANG_ADMIN_COM_DONATE_WAITING_PURGE_HELP'				, "Seront purgés : les dons en attente (paiement non initialisé), vieux de {number} jours.");



// list.php
define('LANG_ADMIN_COM_DONATE_LIST_TITLE_START'					, "Liste des dons");

define('LANG_ADMIN_COM_DONATE_LIST_DESIGNATION_FILTER'			, "Filtre d'affectation");
define('LANG_ADMIN_COM_DONATE_LIST_DESIGNATION_FILTER_ROOT'		, "Aucun");

define('LANG_ADMIN_COM_DONATE_LIST_NO_PAYMENT_IS_NULL'			, "Pas de dons effectués.");
define('LANG_ADMIN_COM_DONATE_LIST_PURGE_BUTTON'				, "Purger maintenant");
define('LANG_ADMIN_COM_DONATE_LIST_PURGE_HELP'					, "Seront purgés : TOUS les dons sans réponse du serveur de paiement, vieux de {number} jours.");

define('LANG_ADMIN_COM_DONATE_LIST_MISSING_ID_CRITICAL_ERROR'	, "<b>Erreur critique :</b><br />Certains dons possèdent un <u>ID de paiement</u> non disponible dans la table des paiements. Il n'est plus possible de déterminer leurs statuts de paiements." );

define('LANG_ADMIN_COM_DONATE_LIST_NO_INVOICE_BY_EMAIL'			, "Le reçu Cerfa n'a pas été envoyé par mail !");



// export.php
define('LANG_ADMIN_COM_DONATE_EXPORT_TITLE_START'				, "Exporter les dons validés");

define('LANG_ADMIN_COM_DONATE_EXPORT_CSV_SITE_NAME_DATE'		, "{site_name} : rapport des dons en ligne (au {date})");
define('LANG_ADMIN_COM_DONATE_EXPORT_CSV_AMOUNT_TOTAL'			, "Cumule des dons");
define('LANG_ADMIN_COM_DONATE_EXPORT_CSV_DATE_INTERVAL'			, "(du {start} au {stop}) :");



// statistics.php
define('LANG_ADMIN_COM_DONATE_STATS_TITLE_START'				, "Statistiques des dons");

define('LANG_ADMIN_COM_DONATE_STATS_DESIGNATIONS_FILTER'		, "Filtre d'affectations");

define('LANG_ADMIN_COM_DONATE_STATS_IN'							, "Dons en "); # in Euro, in Dollar, ...

?>