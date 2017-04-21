<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_SCHEDULE_INDEX_TITLE'					, "Tableaux d'horaires" );



// Fields of 'schedule_tmpl' table
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_NAME'						, "Nom" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_KEY'					, "Titre de la colonne entête" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_VAL'					, "Liste des valeurs de la colonne entête" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_SHOW_YEAR'				, "Afficher l'année" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS'					, "Colonnes de données" ); # generic for all s0, s1, ... fields


// Fields of 'schedule_sheet' table
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE'					, "Titre" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_TMPL_ID'					, "Modèle utilisé" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_ORDER'					, "Ordre" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_PUBLISHED'				, "Publié" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_HEADER'					, "Entête de page" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_FOOTER'					, "Pied de page" );


// Fields of 'schedule' table
define( 'LANG_ADMIN_COM_SCHEDULE_SCHEDULE_ROW_TITLE'			, "Entête de ligne" );
define( 'LANG_ADMIN_COM_SCHEDULE_SCHEDULE_TIME'					, "Date" );
define( 'LANG_ADMIN_COM_SCHEDULE_SCHEDULE_SN'					, "n° colonne" ); # generic for all s0, s1, ... fields



// Fields of 'schedule_addon' table
define( 'LANG_ADMIN_COM_SCHEDULE_ADDON_SN'						, "n° colonne" );
define( 'LANG_ADMIN_COM_SCHEDULE_ADDON_TYPE'					, "Type" );
define( 'LANG_ADMIN_COM_SCHEDULE_ADDON_CONTENT'					, "Contenu" );



// Fields of 'schedule_config' table
#define( 'LANG_ADMIN_COM_SCHEDULE_CONFIG_'						, "" );



// Fields of 'schedule_module' table
define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_ALIAS'					, "Alias du module" );
define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_SHEET_ID'				, "Feuille utilisée" );
define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_SCHEDULE_LIST'			, "Colonnes utilisées" );



// tmpl.php
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_TITLE_START'				, "Liste des modèles" );

define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_FIELDSET'				, "Colonne entête de ligne" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_TIPS'					, "Partie facultative. Laissez vide pour désactiver la colonne entête de ligne." );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_ROW_VAL_TIPS'			, "Inscrivez une valeur par ligne" );

define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_FIELDSET'			, "Titres des colonnes de données" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_TIPS'				, "Remplissez dans l'ordre le nombre de colonnes dont vous avez besoins." );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_ERROR'			, "Il faut définir au moins une colonne de données." );

define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_NAME_DUPLICATE'			, "Ce nom de modèle est déjà utilisé." );

define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_TITLE_NEW'				, "Nouveau modèle" );
define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_TITLE_UPD'				, "Mise à jour du modèle" );

define( 'LANG_ADMIN_COM_SCHEDULE_TMPL_DEL_ERROR'				, "Le modèle est utilisé par des feuilles d'horaires. Il ne peut être effacé." );



// sheet.php
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_START'				, "Liste des feuilles" );

define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_FIELDSET_1'				, "Général" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_FIELDSET_2'				, "Complément" );

define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_NEW'				, "Nouvelle feuille" );
define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_UPD'				, "Mise à jour de la feuille" );

define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_ERROR_NO_TMPL_SELECTED'	, "Aucun modèle sélectionné." );

define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_DUPLICATE'			, "Ce nom de feuille est déjà utilisé." );

define( 'LANG_ADMIN_COM_SCHEDULE_SHEET_DEL_ERROR'				, "La feuille contient des enregistrements. Elle ne peut être effacée." );



// schedule.php
define( 'LANG_ADMIN_COM_SCHEDULE_SCHEDULE_TITLE_START'			, "Horaires des feuilles" );

define( 'LANG_ADMIN_COM_SCHEDULE_SCHEDULE_NO_SHEET_AVAILABLE'	, "Créer au moins une feuille pour pouvoir y rajouter des horaires." );

define( 'LANG_ADMIN_COM_SCHEDULE_SCHEDULE_BUTTON_UPDATE'		, "Mettre à jour" );



// display.php
define( 'LANG_ADMIN_COM_SCHEDULE_CONFIG_TITLE_START'			, "Options d'affichage" );

define( 'LANG_ADMIN_COM_SCHEDULE_CONFIG_USE_MENU'				, "Sélectionner les feuilles à l'aide d'un menu déroulant" );
define( 'LANG_ADMIN_COM_SCHEDULE_CONFIG_DISPLAY_ALL'			, "Afficher directement les feuilles dans la page" );



// schedule_mod.php
define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_TITLE_START'			, "Générateur de modules" );

define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_SUFFIX'					, "Suffixe du module" );
define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_SHEET'					, "Feuille à utiliser" );

define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_SHEET_ERROR'			, "Aucune feuille sélectionnée" );

define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_SUFFIX_ALREADY_EXISTS'	, "Le module {input} existe déjà." );

define( 'LANG_ADMIN_COM_SCHEDULE_MODULE_NEW_SUCCESS'			, "Le module <b>{filename}</b> a bien été créé." );



?>