<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_SYSTEM_INDEX_TITLE'						, "Configuration du système" );

/* ------
   Config */

define( 'LANG_ADMIN_COM_CONFIG_TITLE_START'					, "Paramètres généraux du site" );

// 'config' table fields
define( 'LANG_ADMIN_COM_CONFIG_ONLINE_STATUS'				, "Le site est : " );
define( 'LANG_ADMIN_COM_CONFIG_ONLINE' 						, '<span style="color:green;font-weight:bold;">ONLINE</span>' );
define( 'LANG_ADMIN_COM_CONFIG_OFFLINE'						, '<span style="color:red;font-weight:bold;">OFFLINE</span>' );

define( 'LANG_ADMIN_COM_CONFIG_OFFLINE_MESSAGE'				, "Message à afficher lorsque le site est en cours de maintenance" );

define( 'LANG_ADMIN_COM_CONFIG_DEBUG'						, "Mode débogage" );
define( 'LANG_ADMIN_COM_CONFIG_DEBUG_ON'					, "Activé !" );
define( 'LANG_ADMIN_COM_CONFIG_DEBUG_OFF'					, "Inactif !" );

define( 'LANG_ADMIN_COM_CONFIG_SITE_NAME'					, "Nom du site" );
define( 'LANG_ADMIN_COM_CONFIG_META_DESC'					, "Description" );
define( 'LANG_ADMIN_COM_CONFIG_META_KEYWORDS'				, "Mots clés" );
define( 'LANG_ADMIN_COM_CONFIG_META_AUTHOR'					, "Auteur des contenus" );
define( 'LANG_ADMIN_COM_CONFIG_SYSTEM_EMAIL'				, "Email" );

define( 'LANG_ADMIN_COM_CONFIG_SYSTEM_EMAIL_NOT_DEFINED'	, "Attention! L'Email du système n'est pas défini." );

define( 'LANG_ADMIN_COM_CONFIG_HTTP_HOST'					, "Hôte unique" );
define( 'LANG_ADMIN_COM_CONFIG_HTTP_HOST_IS_OPTIONAL'		, "optionnel" );

define( 'LANG_ADMIN_COM_CONFIG_NO_LINKED_CONTENT_ACCESS'	, "Autoriser l'affichage des contenus n'ayant pas été définis par des liens : " );
define( 'LANG_ADMIN_COM_CONFIG_YES'							, '<span style="color:green;font-weight:bold;">OUI</span>');
define( 'LANG_ADMIN_COM_CONFIG_NO' 							, '<span style="color:red;font-weight:bold;">NON</span>');
// End

define( 'LANG_ADMIN_COM_CONFIG_FIELDSET_STATUS'				, "Etat du site" );
define( 'LANG_ADMIN_COM_CONFIG_FIELDSET_PARAMETERS'			, "Paramètres" );
define( 'LANG_ADMIN_COM_CONFIG_FIELDSET_META'				, "Metas données" );
define( 'LANG_ADMIN_COM_CONFIG_FIELDSET_EMAIL'				, "Email du système" );


/* ----------
   Admin Menu */

define( 'LANG_ADMIN_COM_ADMIN_MENU_TITLE_START'				, "Gestionnaire des menus de l'administration" );

// 'admin_menu' table fields
define( 'LANG_ADMIN_COM_ADMIN_MENU_ID'						, "Identifiant" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_NAME'					, "Menu" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_URL_VALUE'				, "Lien" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_INC_FILE'				, "Fichier" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_LINK_NAME'				, "Nom" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_LINK_ORDER'				, "Ordre" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_ACCESS_LEVEL'			, "Niveau d'accès" );
define( 'LANG_ADMIN_COM_ADMIN_MENU_PUBLISHED'				, "Publié" );

define( 'LANG_ADMIN_COM_ADMIN_MENU_UPDATE_FAILED'			, "La mise à jour suivante a échoué : " );


/* ----------
   Components */

define( 'LANG_ADMIN_COM_COMPONENTS_TITLE_START'				, "Pages des composants accessibles en ligne" );

// 'components' table fields
define( 'LANG_ADMIN_COM_COMPONENTS_ID'						, "Identifiant" );
define( 'LANG_ADMIN_COM_COMPONENTS_COM'						, "Composant" );
define( 'LANG_ADMIN_COM_COMPONENTS_PAGE'					, "Page" );
define( 'LANG_ADMIN_COM_COMPONENTS_TITLE'					, "Nom" );
define( 'LANG_ADMIN_COM_COMPONENTS_ACCESS_LEVEL'			, "Niveau d'accès" );
define( 'LANG_ADMIN_COM_COMPONENTS_PUBLISHED'				, "Publié" );
define( 'LANG_ADMIN_COM_COMPONENTS_PARAMS'					, "Paramètres" );

define( 'LANG_ADMIN_COM_COMPONENTS_URL'						, "URL" );

define( 'LANG_ADMIN_COM_COMPONENTS_UPDATE_FAILED'			, "La mise à jour suivante a échoué : " );

?>