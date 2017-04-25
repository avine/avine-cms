<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


define( 'LANG_ADMIN_TITLE'									, "Administration");
define( 'LANG_ADMIN_COPYRIGHT'								, '&copy; <a href="http://avine.io/" title="http://avine.io">avine.io</a>');


define( 'LANG_ADMIN_ADMIN_MENU_CONFIG_ERROR_WITH_ADMIN_MENU_TABLE', 
		"<span style=\"color:red;\">Une erreur est survenue lors d'une invocation de la méthode: <b>admin_menuManager->includeTarget()</b> dans: <b>/admin/global/functions.php</b><br />" .
		"Le système de navigation de l'Administration est <u>mal configuré!</u><br />" .
		"Problèmes possibles :<br />" .
		"1) La table 'admin_menu' n'est tout simplement pas installée dans la base de données.<br />" .
		"2) Un script crèe un instance de la classe admin_menuManager('[nom de menu]'), où [nom de menu] n'est pas défini dans la table 'admin_menu'.<br />" .
		"3) La cible à inclure dans la page est bien définie dans la table 'admin_menu', mais le fichier correspondant ne se trouve pas sur le serveur.<br /></span>");
define( 'LANG_ADMIN_ADMIN_MENU_TARGET_NOT_FOUND', "La page n'existe pas.");


define( 'LANG_ADMIN_ROOT_WELCOME'							, "Bienvenue dans l'interface d'administration de : ");


define( 'LANG_ADMIN_BUTTON_CREATE'							, "Nouveau");
define( 'LANG_ADMIN_BUTTON_UPDATE'							, "Modifier");
define( 'LANG_ADMIN_BUTTON_DELETE'							, "Effacer");
define( 'LANG_ADMIN_BUTTON_SUBMIT'							, "Valider");
define( 'LANG_ADMIN_BUTTON_RECORD'							, "Enregistrer les modifications");
define( 'LANG_ADMIN_BUTTON_CONTINUE'						, "Continuer");
define( 'LANG_ADMIN_BUTTON_REFRESH' 						, '<img src="'.WEBSITE_PATH.'/admin/images/refresh.png" alt="Rafraichir la page" title="Rafraichir la page" />');

define( 'LANG_ADMIN_PROCESS_NEW_DATA_DEFAULT_NAME'			, "Nouveau "); # When creating a new record in the database, give it a default name

define( 'LANG_ADMIN_PROCESS_SUCCESS'						, "L'opération a réussi !");
define( 'LANG_ADMIN_PROCESS_FAILURE'						, "L'opération a échoué !");


define( 'LANG_ADMIN_WELCOME_INSTALL_DIR_WARNING'			, "Faille de sécurité : le répertoire '<strong>/installation</strong>' n'a pas été effacé !");


define( 'LANG_ADMIN_SIMPLE_MULTI_PAGE_NUM_PER_PAGE'			, "Lignes/Pages");
define( 'LANG_ADMIN_SIMPLE_MULTI_PAGE_CURRENT_PAGE'			, "Pages : ");


define( 'LANG_ADMIN_SWITCH_MENU'							, "Position du menu principal");
define( 'LANG_ADMIN_GO_TO_FRONTEND'							, "Accès public");


define( 'LANG_ADMIN_PERMISSIONS_CAN_NOT_CREATE'				, "Les <b>{user_status}</b> ne peuvent pas créer.");
define( 'LANG_ADMIN_PERMISSIONS_CAN_NOT_PUBLISH'			, "Les <b>{user_status}</b> ne peuvent pas publier/dépublier.");
define( 'LANG_ADMIN_PERMISSIONS_CAN_NOT_DELETE'				, "Les <b>{user_status}</b> ne peuvent pas effacer.");
define( 'LANG_ADMIN_PERMISSIONS_CAN_NOT_ARCHIVE'			, "Les <b>{user_status}</b> ne peuvent pas archiver.");
define( 'LANG_ADMIN_PERMISSIONS_IS_NOT_THE_AUTHOR'			, "Le contenu ne peut être modifié que par son auteur.");
define( 'LANG_ADMIN_PERMISSIONS_CAN_NOT_UPDATE'				, "Le contenu a été publié et ne peut être modifié que par un éditeur.");

define( 'LANG_ADMIN_DEMO_MODE_TIPS'							, "<strong>Mode démo activé !</strong><br /> Aucune modification prise en compte.<br /> Bonne visite...");
define( 'LANG_ADMIN_DEMO_MODE_TIPS_SUPER_ADMIN'				, "<strong>Mode démo activé !</strong><br /> Super administrateur connecté.<br /> Bonnes modifications...");
define( 'LANG_ADMIN_DEMO_MODE_LOGIN'						, "<strong>Mode démo activé !</strong><br /> Nom d'utilisateur : demo <br /> Mot de passe : demo");



?>