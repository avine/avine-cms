<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_MODULE_INDEX_TITLE'						, "Gestion des modules" );



define( 'LANG_ADMIN_COM_MODULE_LIST_TITLE_START'				, "Liste des modules" );
define( 'LANG_ADMIN_COM_MODULE_POS_TITLE_START'					, "Positions des modules" );
define( 'LANG_ADMIN_COM_MODULE_MOD_TITLE_START'					, "Modules en ligne" );
define( 'LANG_ADMIN_COM_MODULE_DEFAULT_TITLE_START'				, "Modules par défaut" );

define( 'LANG_ADMIN_COM_MODULE_NO_POSITION_AVAILABLE'			, "Aucune position définie." );
define( 'LANG_ADMIN_COM_MODULE_POSITION_FILTER'					, "Sélectionner une position" );

define( 'LANG_ADMIN_COM_MODULE_NO_MODULE_AVAILABLE'				, "Aucun module installé." );
define( 'LANG_ADMIN_COM_MODULE_MODULE_FILTER'					, "Sélectionner un module" );

define( 'LANG_ADMIN_COM_MODULE_NO_MENU_AVAILABLE'				, "Aucun menu défini." );
define( 'LANG_ADMIN_COM_MODULE_LINK_FILTER'						, "Pages où le module doit apparaître" );
define( 'LANG_ADMIN_COM_MODULE_LINK_FILTER_TIPS_MODULE_DEFAULT'	, "<span style=\"color:green;\">(module par défaut)</span>" );

define( 'LANG_ADMIN_COM_MODULE_LINK_FILTER_TIPS_PRIMARY_LINK'	, "<br /><span style=\"color:grey;\">Note : Lorsque plusieurs liens pointent vers la même page, seul le premier apparaît dans cette liste.</span>" );



// List.php
define( 'LANG_ADMIN_COM_MODULE_LIST_FILE'						, "Nom" );
define( 'LANG_ADMIN_COM_MODULE_LIST_COMMENT'					, "Description" );
define( 'LANG_ADMIN_COM_MODULE_LIST_MODULE_USED'				, "Utilisé" );
define( 'LANG_ADMIN_COM_MODULE_LIST_NAME_EXTENSION'				, "Nom / extension " );
define( 'LANG_ADMIN_COM_MODULE_LIST_MOD_NAME'					, "Nom du module : " );

define( 'LANG_ADMIN_COM_MODULE_LIST_TITLE_NEW'					, "Nouveau module" );
define( 'LANG_ADMIN_COM_MODULE_LIST_TITLE_UPDATE'				, "Mise à jour du module" );

define( 'LANG_ADMIN_COM_MODULE_INVALID_FILE_NAME'				, "Le fichier est présent sur le serveur ftp, mais son nom est invalide." );
define( 'LANG_ADMIN_COM_MODULE_MISSING_FILE'					, "Le fichier est référencé dans la base de données, mais a disparu du serveur ftp." );

define( 'LANG_ADMIN_COM_MODULE_LIST_ERROR_INVALID_FILENAME'		, "{input} n'est pas valide comme de nom de fichier." );
define( 'LANG_ADMIN_COM_MODULE_LIST_ERROR_FILE_EXISTS'			, "Le fichier {input} existe déjà sur le serveur FTP." );

define( 'LANG_ADMIN_COM_MODULE_LIST_UPD_FILE_CONTENT'			, "Contenu du fichier" );

define( 'LANG_ADMIN_COM_MODULE_LIST_DEL_FAILURE_FTP'			, "L'opération a échoué. Impossible d'effacer le fichier du serveur FTP." );
define( 'LANG_ADMIN_COM_MODULE_LIST_DEL_FAILURE_DB'				, "L'opération a échoué. Impossible d'effacer la référence au fichier dans la base de données." );

define( 'LANG_ADMIN_COM_MODULE_LIST_FIELDSET_CONFIG'			, "Configuration" );
define( 'LANG_ADMIN_COM_MODULE_LIST_USE_HTML_EDITOR'			, "Ouvrir les fichiers HTML avec un éditeur WYSIWYG" );
define( 'LANG_ADMIN_COM_MODULE_LIST_EXLUDE_INVALID_FILE'		, "Ne pas afficher les fichiers problématiques" );



// Position.php
define( 'LANG_ADMIN_COM_MODULE_POSITION_POS'					, "Position" );
define( 'LANG_ADMIN_COM_MODULE_POSITION_DESC'					, "Description" );

define( 'LANG_ADMIN_COM_MODULE_POSITION_TITLE_NEW'				, "Nouvelle position" );
define( 'LANG_ADMIN_COM_MODULE_POSITION_TITLE_UPDATE'			, "Mise à jour de la position" );

define( 'LANG_ADMIN_COM_MODULE_POSITION_POS_ALREADY_EXIST'		, "Cette position existe déjà." );
define( 'LANG_ADMIN_COM_MODULE_POSITION_USED_BY_MODULE'			, "La position est utilisée pour l'affichage de modules. Elle ne peut être effacée." );



// Module.php
define( 'LANG_ADMIN_COM_MODULE_TITLE_NEW'						, "Nouveau module" );
define( 'LANG_ADMIN_COM_MODULE_TITLE_UPDATE'					, "Mise à jour du module" );

define( 'LANG_ADMIN_COM_MODULE_MODULE_NO_TITLE'					, "Titre du module non renseigné." );
define( 'LANG_ADMIN_COM_MODULE_MODULE_POS_NOT_SELECTED'			, "Aucune position sélectionnée." );
define( 'LANG_ADMIN_COM_MODULE_MODULE_FILE_NOT_SELECTED'		, "Aucun module sélectionné." );

define( 'LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_LABEL'			, "Afficher ce module avec le modèle d'une autre position" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_DIRECTORY'		, "Répertoire des positions : " );
define( 'LANG_ADMIN_COM_MODULE_MODULE_HTML_POS_TEMPLATE_ERROR'	, "Modèle par défaut non défini !" );

define( 'LANG_ADMIN_COM_MODULE_MODULE_ID'						, "ID" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_NAME'						, "Titre du module" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_SHOW_NAME'				, "Afficher le titre" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_FILE'						, "Fichier" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_POS'						, "Position" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_ORDER'					, "Ordre" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_ACCESS_LEVEL'			 	, "Niveau d'accès" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_PUBLISHED'				, "Publié" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_HTML_POS'					, "Modèle" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_PARAMS'					, "Paramètres" );
define( 'LANG_ADMIN_COM_MODULE_MODULE_COMMENT'					, "Description" );

define( 'LANG_ADMIN_COM_MODULE_MODULE_XHREF'					, "Pages où le module doit apparaître" );



// default.php
define( 'LANG_ADMIN_COM_MODULE_DEFAULT_UPDATE'					, "Mise à jour des modules par défaut" );

?>