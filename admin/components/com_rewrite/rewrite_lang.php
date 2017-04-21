<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

// 'rewrite_config' table fields
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_ENABLED'							, "Statut de la réécriture d'URLs" );



// 'rewrite_rules' table fields
define( 'LANG_ADMIN_COM_REWRITE_RULES_ID'								, 'ID' );
define( 'LANG_ADMIN_COM_REWRITE_RULES_POS'								, 'Ordre' );
define( 'LANG_ADMIN_COM_REWRITE_RULES_STATIC'							, 'URL statique' );
define( 'LANG_ADMIN_COM_REWRITE_RULES_TARGET'							, 'URL dynamique' );
define( 'LANG_ADMIN_COM_REWRITE_RULES_S1'								, '$1' );
define( 'LANG_ADMIN_COM_REWRITE_RULES_S2'								, '$2' );
define( 'LANG_ADMIN_COM_REWRITE_RULES_S3'								, '$3' );



define( 'LANG_ADMIN_COM_REWRITE_WARNING_FOR_EXPERTS_LEGEND'				, "Configuration réservée aux experts" );
define( 'LANG_ADMIN_COM_REWRITE_WARNING_FOR_EXPERTS'					, "Attention, ce qui suit est réservé aux experts.<br /> A modifier uniquement si vous savez exactement ce que vous faites." );



// config.php
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_TITLE_START'						, "Configuration de la réécriture d'URLs" );

define( 'LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_STATUS'					, "Statut du module : " );
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_OFF'						, "INACTIF" );
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_ON'						, "ACTIF" );

define( 'LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_ENABLE'					, "Activer la réécriture" );
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_DISABLE'					, "Désactiver la réécriture" );

define( 'LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_UNAVAILABLE'			, "Le fichier <b>.htaccess</b> est introuvable !" );
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_SEPARATOR_MISSING'		, "Le fichier <b>.htaccess</b> est présent sur le serveur mais n'est pas exploitable en l'état (détail technique&nbsp;: le <b>commentaire de séparation</b> délimitant la configuration système de celle de l'utilisateur est <b>manquant</b>)." );
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_RESTORE'				, "Restaurer le fichier .htaccess par défaut" );

define( 'LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_SYSTEM'					, "Section système du fichier .htaccess (non modifiable)" );
define( 'LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_USER'					, "Section utilisateur du fichier .htaccess (modifiable)" );

define( 'LANG_ADMIN_COM_REWRITE_CONFIG_TEST_REWRITE_ENGINE'				, "Tester la réécriture d'URLs" );



// rules.php
define( 'LANG_ADMIN_COM_REWRITE_RULES_TITLE_START'						, "Règles de réécriture d'URLs" );

define( 'LANG_ADMIN_COM_REWRITE_RULES_UNLOCK'							, "Autoriser les modifications" );
define( 'LANG_ADMIN_COM_REWRITE_RULES_LOCK'								, "Interdire les modifications" );

define( 'LANG_ADMIN_COM_REWRITE_RULES_UNLOCK_STATUS'					, "Modifications autorisées" );
define( 'LANG_ADMIN_COM_REWRITE_RULES_LOCK_STATUS'						, "Modifications interdites" );

?>