<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

// Payment_sips_config table fields
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_BIN_PATH'					, "Répertoire des CGI");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PARMCOM_BANK_NAME'				, "Extension du fichier : parmcom.[nom_de_la_banque]");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_MERCHANT_ID'						, "Numéro du commercant");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_MERCHANT_COUNTRY'				, "Pays du commercant");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET'			, "N° de la première transaction");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_MODE'					, "Mode"); 				/* "Mode d'envoi des transactions" */
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_DAY'						, "Délai (jours)"); 	/* "Délai avant l'envoi (jours)" */
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CURRENCY_CODE_LIST'				, "Liste des devises");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS'					, "Liste des moyens de paiements");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_ORDER'						, "Ordre des blocs");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_HEADER_FLAG'						, "Entêtes des blocs");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_LANGUAGE'						, "Langue des pages de paiement");

// Payment_sips table fields
# The following constant has been replaced by : 'LANG_ADMIN_COM_PAYMENT_ABS_PAYM_X_ID'
#define('LANG_ADMIN_COM_PAYMENT_SIPS_ID'									, "payment_x_ID"); 		/* !Do not change this! */
define('LANG_ADMIN_COM_PAYMENT_SIPS_TRANSMISSION_DATE'						, "Date d'envoi");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CAPTURE_MODE'							, "Mode"); 				/* "Mode d'envoi des transactions" */
define('LANG_ADMIN_COM_PAYMENT_SIPS_CAPTURE_DAY'							, "Délai (jours)"); 	/* "Délai avant l'envoi (jours)" */
define('LANG_ADMIN_COM_PAYMENT_SIPS_AMOUNT'									, "Montant");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CURRENCY_CODE'							, "Devise");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CARD_NUMBER'							, "N° de carte");
define('LANG_ADMIN_COM_PAYMENT_SIPS_PAYMENT_MEANS'							, "Type"); 				/* "Moyen de paiement" */
define('LANG_ADMIN_COM_PAYMENT_SIPS_PAYMENT_DATE'							, "Date de réception"); /* "Date de paiement" */
define('LANG_ADMIN_COM_PAYMENT_SIPS_VALIDATED'								, "Validé");


// config.php
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TITLE_START'					, "Configuration de la méthode de paiement" );

define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TITLE_UPD_CURRENCY'				, "Mise à jour de la liste des devises" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TITLE_UPD_PAYMENT'				, "Mise à jour de la liste des moyens de paiement" );

define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_TITLE_DEV'			, "(A) Installer un certificat de démonstration :" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_TITLE_PROD'			, "(B) Installer un certificat de production :" );

define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_SELECT'				, "Modules de démonstration" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_CGI_BASEPATH'		, "Chemin d'installation du répertoire '/cgi-bin'" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_SUBMIT'				, "Configurer" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_SOGENACTIF'			, "Société Générale (sogenactif)" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_ELYSNET'			, "HSBC (elysnet)" );
define( 'LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_DEMO_MODULE_TIPS'				, "Ouvrez l'onglet <b>'{bin_path_directory}'</b> avant d'utiliser le module de démonstration." );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UNKNOWN'							, "Non renseigné");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CGI_NO_MORE_AVAILABLE'			, "répertoire introuvable" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_FIELDSET_MERCHANT'				, "Commercant");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_FIELDSET_TRANSACTION'			, "Transaction");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_FIELDSET_PAYMENT'				, "Paiement");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_BELGIUM'					, "Belgique");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_FRANCE'					, "France");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_GERMANY'					, "Allemagne");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_ITALY'					, "Italie");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_SPAIN'					, "Espagne");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_COUNTRY_ENGLAND'					, "Royaume-Uni");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET_TIPS'		, "Modifiable uniquement avant la première transaction");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_REMAINING'			, "({number} transactions restantes)");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_MODE_AUTHOR_CAPTURE'		, "Automatique (AUTHOR_CAPTURE)");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CAPTURE_MODE_VALIDATION'			, "Après validation du commercant (VALIDATION)");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CURRENCY_CODE_NAME'				, "Nom");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_CURRENCY_CODE_ORDER'				, "Ordre");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_CB'				, "Carte Bleue");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_VISA'				, "VISA");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_MASTERCARD'		, "MASTERCARD");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_AMEX'				, "AMEX");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_NAME'				, "Nom");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_MEANS_BLOCK'				, "Entête de bloc");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_NUMBER_1'					, "Choisissez un moyen de paiement ci-dessous :");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_NUMBER_2'					, "Vous utilisez le formulaire sécurisé standard SSL, ...");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_NUMBER_4'					, "Autre(s) moyen(s) de paiement :");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_BLOCK_ORDER_SSL_FIRST'			, "Bloc SSL en premier");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_FR'				, "Français");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_GE'				, "Allemand");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_EN'				, "Englais");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_SP'				, "Espagnol");
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_PAYMENT_LANGUAGE_IT'				, "Italien");

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_CGI_BIN_PATH'			, "Répertoire des CGI invalide" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_MERCHANT_ID'				, "Numéro de commercant invalide" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_TRANSACTION_ID_OFFSET'	, "N° de première transaction invalide" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_TRANSACTION_ID_OFFSET_TOO_HIGH'	, "N° de première transaction trop grand" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_INVALID_CAPTURE_DAY'				, "Délai avant l'envoi invalide" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_CURRENCY_LEGEND'				, "Devises" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_CURRENCY_NO_SELECTION'		, "Aucune devise sélectionnée" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_PAYMENT_LEGEND'				, "Moyens de paiement" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CONFIG_UPD_PAYMENT_NO_SELECTION'		, "Aucun moyen de paiement sélectionné" );

// cgi.php
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_TITLE_START'						, "Gestionnaire du répertoire des CGI" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_CGI'							, "Le répertoire des CGI est introuvable." );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_PARAM'						, "Le sous-répertoire '<b>/param</b>' est introuvable." );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_LOG'							, "Le sous-répertoire '<b>/log</b>' est introuvable." );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_DIR_BIN'							, "Le sous-répertoire '<b>/bin</b>' est introuvable." );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_NO_FILE'							, "Fichier introuvable : '<strong>{file}</strong>'" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PATHFILE'							, "Pathfile" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PARCOM_BANK'						, "Paramètres de la banque" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PARCOM_MERCHANT'					, "Paramètres du commercant" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_CERTIF'								, "Certificat du commercant" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_LOGFILE'							, "Fichier des événements" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_REQUEST'							, "Binaire de la requête" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_RESPONSE'							, "Binaire de la réponse" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_BUTTON_ARCHIVE'						, "Archiver" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_PERMISSIONS'						, "<span style=\"color:grey;\">Permissions du fichier : <b>{permissions}</b></span>" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_BINARY_TIPS'						, "<p style=\"color:grey;font-style:italic;\">Note : les fichiers binaires doivent être <b>permis en exécution</b> pour le compte propriétaire</p>" );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_UPDATE_SUCCESS'						, "La mise à jour du fichier a réussi : " );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_UPDATE_ERROR'						, "La mise à jour du fichier a échoué : " );

define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_ARCHIVE_SUCCESS'					, "L'archivage a réussi (emplacement : {path})" );
define('LANG_ADMIN_COM_PAYMENT_SIPS_CGI_ARCHIVE_DENIED'						, "L'archive {path} existe déjà. Vous ne pouvez archiver qu'une fois par jour." );


// list.php
define('LANG_ADMIN_COM_PAYMENT_SIPS_LIST_TITLE_START'						, "Liste des opérations de paiements" );

?>