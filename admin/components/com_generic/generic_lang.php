<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_GENERIC_INDEX_TITLE'							, "Gestion du composant générique <span class=\"red\">(tests)</span>" );


/* ------
   Global
   ------ */


// Fields for 'config' table



// Fields for 'node' & 'element' tables
define( 'LANG_ADMIN_COM_GENERIC_ID'										, "ID" );
define( 'LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL'							, "Niveau d'accès" );
define( 'LANG_ADMIN_COM_GENERIC_PUBLISHED'								, "Publié" );
define( 'LANG_ADMIN_COM_GENERIC_ARCHIVED'								, "Archivé" );
define( 'LANG_ADMIN_COM_GENERIC_LIST_ORDER'								, "Ordre" );

define( 'LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION'						, "Date de création" );
define( 'LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED'						, "Date de mise à jour" );
define( 'LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID'							, "Auteur" );
define( 'LANG_ADMIN_COM_GENERIC_SHOW_HITS'								, "Nombre de clics" );



// Fields for 'node' table
define( 'LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS'							, "Alias du {node}" );
define( 'LANG_ADMIN_COM_GENERIC_NODE_PARENT_ID'							, "{node} parent" );
define( 'LANG_ADMIN_COM_GENERIC_NODE_LEVEL'								, "Niveau" );

define( 'LANG_ADMIN_COM_GENERIC_NODE_NODE_FIELDSET'						, "Configuration du {node}" );
define( 'LANG_ADMIN_COM_GENERIC_NODE_ELEMENT_FIELDSET'					, "Configuration de l'affichage de ses {element}s" );



// Fields for 'element' table
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS'						, "Alias de l'{element}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_NODE_ID'						, "{node}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_CREATION'					, "Date de création" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_MODIFIED'					, "Dernière mise à jour" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE'					, "Date de mise en ligne" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_OFFLINE'					, "Date de retrait" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR'							, "Auteur" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_META_KEY'						, "Mots clés" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_META_DESC'						, "Description" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_HITS'							, "Nombre de clics" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_TEXT_EDITOR'					, "Utiliser un éditeur de texte" );

define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET1'						, "Configuration de l'{element}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET2'						, "Détails de l'{element}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET3'						, "Configuration de l'affichage" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_FIELDSET4'						, "Statut de l'{element}" );



// Fields for 'home_nde' table
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_ID_ALIAS'						, "Alias de la page" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_NODES_ID'						, "Liste des {node}s accessibles" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_DEFAULT'						, "Page d'accueil par défaut" );



// Fields for 'home_elm' table
#define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_'								, "" );



// *.php
define( 'LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC_EMPTY'					, "<p style=\"color:#BBB;text-align:center;font-style:italic;\">Rien à traiter...</p>" );
define( 'LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL'						, "Informations générales" );
define( 'LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC'						, "Informations spécifiques" );



// config.php
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_TITLE_START'						, "Configuration du composant" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_STATUS_FIELDSET'					, "Statuts" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_DEBUG_FIELD'						, "Le mode \"déboggage\" est : " );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_DEBUG_N'							, "<span style=\"color:green;font-weight:bold;\">INACTIF</span>" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_DEBUG_Y'							, "<span style=\"color:red;font-weight:bold;\">ACTIF</span>" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_LEVELS_NAME_NODE'				, "Nom de la composante <u>{node}</u> de la requête (url) :" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_LEVELS_NAME_ELEMENT'				, "Nom de la composante <u>{element}</u> de la requête (url) :" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_FIELDSET'					, "Paramètres d'affichage" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM'						, "<h3>Affichage des {element}s :</h3>" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_STEP'			, "Nombre par page" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_ROW'				, "Nombre de colonnes" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES'					, "<br /><h3>Affichage des sous-{node}s :</h3>" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_ONTOP'			, "Barre de navigation : " );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_ONTOP_N'			, "<span style=\"color:grey;font-weight:bold;\">en bas de page</span>" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_ONTOP_Y'			, "<span style=\"color:grey;font-weight:bold;\">en haut de page</span>" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEMENTS_WRAPPER'	 		, "Type d'enveloppe : " );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_WRAPPER'	 		, "Type d'enveloppe : " );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_WRAPPER_0'				, "<span style=\"color:red;font-weight:bold;\">&lt;table&gt;</span> <span style=\"color:grey;\">(non recommandé)</span>" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_WRAPPER_1'				, "<span style=\"color:green;font-weight:bold;\">&lt;div&gt;</span> <span style=\"color:grey;\">(recommandé)</span>" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_PER_ROW'			, "Nombre de colonnes" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_STEP_ERROR'		, "Le nombre d'{element}s par page, doit être d'au moins 1." );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_ROW_ERROR'		, "Le nombre de colonnes pour l'affichage des {element}s, doit être d'au moins 1." );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_PER_ROW_ERROR'	, "Le nombre de colonnes pour l'affichage des sous-{node}s, doit être d'au moins 1." );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTORS'				, "<br /><h3>Affichage des sélecteurs :</h3>" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTOR_NODE'			, "Sélecteur des {node}s" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTOR_NODE_RELATIVE'	, "Relatif" );
define( 'LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTOR_ARCHIVE'			, "Sélecteur des archives" );

define( 'LANG_ADMIN_COM_GENERIC_CONFIG_DEFAULT_VALUE_FIELDSET'			, "Valeurs par défaut (nouveau {node} ou {element})" );



// node.php & element.php
define( 'LANG_ADMIN_COM_GENERIC_NODE_SELECTION'							, "Sélectionner un {node}" );
define( 'LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION'						, "Ressources" );
define( 'LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_0'					, "en ligne" );
define( 'LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_1'					, "archives" );
define( 'LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_2'					, "en ligne + archives" );
define( 'LANG_ADMIN_COM_GENERIC_ARCHIVE_IMG_TITLE'						, "Ressource archivée" );
define( 'LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED_TEXT'					, "Champ obligatoire" );
define( 'LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED'							, '<img src="'.WEBSITE_PATH.'/admin/components/com_generic/images/required-field.gif" alt="Required field" title="'.LANG_ADMIN_COM_GENERIC_FIELD_REQUIRED_TEXT.'" style="vertical-align:middle;cursor:help;" />' );



// node.php
define( 'LANG_ADMIN_COM_GENERIC_NODE_TITLE_START'						, "Liste des {node}s" );
define( 'LANG_ADMIN_COM_GENERIC_NODE_TITLE_NEW'							, "Nouveau {node}" );
define( 'LANG_ADMIN_COM_GENERIC_NODE_TITLE_UPDATE'						, "Mise à jour le {node}" );

define( 'LANG_ADMIN_COM_GENERIC_NODE_NODE_SELECTION_ROOT'				, "(Racine)" );
define( 'LANG_ADMIN_COM_GENERIC_NODE_DEL_NODE_HAVE_NODE'				, "Le {node} a des sous-{node}s." );
define( 'LANG_ADMIN_COM_GENERIC_NODE_DEL_NODE_HAVE_ELEMENT'				, "Le {node} a des {element}s associés." );
define( 'LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS_ALREADY_EXIST'			, "L'alias du {node} existe déjà dans ce branchement." );
define( 'LANG_ADMIN_COM_GENERIC_NODE_ARCHIVE_WARNING'					, " <span style=\"color:grey;\">(*) Attention ! Les sous-{node}s et tous les {element}s asscociés sont concernés.</span>" );


// element.php
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_START'					, "Liste des {element}s" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_NEW'						, "Nouvel {element}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_UPDATE'					, "Mise à jour de l'{element}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_TITLE_AUTOARCHIVE'				, "Archivage automatique des {element}s" );

define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS_ALREADY_EXIST'			, "L'alias de l'{element} existe déjà dans ce {node}." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_NODE_ID_NOT_SELECTED'			, "Aucun {node} parent séléctionné." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE_OFFLINE_ERROR'		, "La date de mise en ligne est postérieure à celle du retrait." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_NO_ONE_NODE_AVAILABLE'			, "Il faut créer au moins un {node}, pour y ajouter des {element}s." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR_ID_NOT_SELECTED'			, "Aucun auteur sélectionné." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_CREATION_POSTDATED'		, "L'{element} est postdaté !" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_CREATION_POSTDATED_WAITING', "Il ne sera affiché qu'une fois cette date attente." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE_OVERWRITTEN'		, "La date de mise en ligne a été fixée en fonction de la date de création, car cette dernière est postdatée." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_UNARCHIVE_ERROR'				, "Impossible de désarchiver l'{element} ! Tous ses {node}s parents doivent l'être aussi." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_BUTTON_AUTO_ARCHIVE'			, "Archivage automatique" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_AUTO_ARCHIVE_HELP'				, "Sont archivés : les {element}s dont la date de retrait (offline) est expirée. Sont concernés : tous les {node}s.<br />Note: Pour être consultable en ligne depuis les archives, les {element}s archivés doivent avoir le statut \"Publié\"." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_PUBLISH_WHEN_ARCHIVE'			, "Forcer le statut \"Publié\" des {element}s à archiver" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_NOTHING_TO_ARCHIVE'				, "Aucun {element} à archiver !" );

define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_MODIFY_DATE_CREATION'			, "Modifier la date" );

define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS'				, "Effacer tous les {element}s du {node}" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS_CONFIRM'		, "Attention, l'opération est définitive !" );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS_EMPTY'		, "Le {node} ne contient aucun {element}." );
define( 'LANG_ADMIN_COM_GENERIC_ELEMENT_DEL_ALL_NODE_ELEMS_COUNT'		, "{count} {element}(s) supprimé(s)." );



// home_nde.php
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_TITLE_START'					, "Liste des pages d'accueil" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_TITLE_NEW'						, "Nouvelle page d'accueil" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_TITLE_UPD'						, "Mise à jour de la page d'accueil" );

define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_DEFAULT_NDE_HEADER'			, "Défaut" );

define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_CONFIG_FIELDSET'				, "Configuration de la page d'accueil" );

define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_ALIAS_ALREADY_EXIST'			, "Cet alias existe déjà." );
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_NODES_ID_TIPS'					, "Laisser vide pour rendre tous les {node}s accessibles." );

define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_DEL_ERROR_DEFAULT_NDE'			, "La page d'accueil par défaut ne peut être effacée." );
define( 'LANG_ADMIN_COM_GENERIC_HOME_NDE_DEL_ERROR_HAVE_ELM'			, "La page d'acceuil contient des {element}s." );



// home_elm.php
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_TITLE_START'					, "{element}s en page d'accueil" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_TITLE_NEW'						, "Ajouter un {element}" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_TITLE_UPD_ALL'					, "Modifier l'ensemble des {element}s" );


define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_NODES_ID_HAVE_BEEN_UPDATED'	, "La configuration des noeuds accessibles à cette page d'accueil a changé. Certains {element}s ont du être retirés." );

define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_BUTTON_UPDATE'					, "Modifier l'ensemble" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_NOT_VISIBLE_ELEMENT'			, "Note: les {element}s grisés ne sont pas visibles (dépubliés, date de retrait expirée, ...)" );

define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_SELECT'					, "Sélectionner un {element}" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_FAILED'					, "Aucun {element} sélectionné." );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_NO_NEW_AVAILABLE'			, "Tous les {element}s disponibles sont déjà en page d'accueil." );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_NEW_NO_ELM_AVAILABLE'			, "Aucun {element} disponible !" );

define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_UPD_SELECT'					, "Sélectionner des {element}s" );
define( 'LANG_ADMIN_COM_GENERIC_HOME_ELM_UPD_PUBLISH_NEW_ONES'			, "Publier les nouveaux {element}s" );

?>