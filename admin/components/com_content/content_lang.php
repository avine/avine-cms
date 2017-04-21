<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define('LANG_ADMIN_COM_CONTENT_INDEX_TITLE'						, "Contenus dynamiques <span>(base de données)</span>");


/* --------
   Specific
   -------- */


// Fields for 'config_item' table
define ('LANG_ADMIN_COM_CONTENT_CONFIG_FIELDSET_NODE'			, "Valeurs par défaut pour un nouveau {node}");

define ('LANG_ADMIN_COM_CONTENT_CONFIG_NOD_VIEW'				, "Pour le {node}, afficher :");
define ('LANG_ADMIN_COM_CONTENT_CONFIG_NOD_VIEW_TITLE'			, "Titre");

define ('LANG_ADMIN_COM_CONTENT_CONFIG_ELM_VIEW'				, "Pour ses {element}s (résumé), afficher :");
define ('LANG_ADMIN_COM_CONTENT_CONFIG_NOD_SHOW_IMAGE_THUMB'	, "Image miniature");
define ('LANG_ADMIN_COM_CONTENT_CONFIG_NOD_SHOW_MEDIAS'			, "Prévisualisation des medias");

define ('LANG_ADMIN_COM_CONTENT_CONFIG_FIELDSET_ELEMENT'		, "Valeurs par défaut pour un nouvel {element}");

define ('LANG_ADMIN_COM_CONTENT_CONFIG_ELM_SHOW_TEXT_INTRO'		, "Répéter le résumé dans la vue développée");
define ('LANG_ADMIN_COM_CONTENT_CONFIG_ELM_USE_TEXT_EDITOR'		, "Utiliser un éditeur WYSIWYG");


// Fields for 'node_item' table
define ('LANG_ADMIN_COM_CONTENT_NODE_FIELDSET1', "Entête du {node}");
define ('LANG_ADMIN_COM_CONTENT_NODE_ID'						, "Identifiant");
define ('LANG_ADMIN_COM_CONTENT_NODE_TITLE'						, "Titre");
define ('LANG_ADMIN_COM_CONTENT_NODE_TITLE_ALIAS'				, "Sous-titre");
define ('LANG_ADMIN_COM_CONTENT_NODE_TEXT'						, "Texte");
define ('LANG_ADMIN_COM_CONTENT_NODE_IMAGE'						, "Image");
define ('LANG_ADMIN_COM_CONTENT_NODE_VIEW_TITLE'				, "Afficher (titre et sous-titre)");

define ('LANG_ADMIN_COM_CONTENT_NODE_FIELDSET2', "Configuration de l'affichage de ses {element}s (résumé)");
define ('LANG_ADMIN_COM_CONTENT_NODE_SHOW_IMAGE_THUMB'			, "Image miniature");
define ('LANG_ADMIN_COM_CONTENT_NODE_SHOW_MEDIAS'				, "Prévisualisation des medias (sauf si désactivé dans l'{element})");


// Fields for 'element_item' table
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET1', "Entête de l'{element}");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_ID'						, "Identifiant");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE'					, "Titre");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE_ALIAS'			, "Sous-titre");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_TITLE_QUOTE'			, "Citation");

define ('LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET2', "Contenu de l'{element}");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_TEXT_INTRO'				, "Résumé");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_TEXT_MAIN'				, "Développement");

define ('LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET3', "Visuels");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_IMAGE_THUMB'			, "Image miniature");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_IMAGE'					, "Image");

define ('LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET4', "Medias");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_MEDIAS_MODIFY'			, "Modifier les medias");

define ('LANG_ADMIN_COM_CONTENT_ELEMENT_FIELDSET5', "Configuration de l'affichage");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_DISABLE_MEDIAS'			, "Désactiver les medias");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_SHOW_TEXT_INTRO'		, "Répéter le résumé dans la vue développée");
define ('LANG_ADMIN_COM_CONTENT_ELEMENT_USE_TEXT_EDITOR'		, "Utiliser un éditeur WYSIWYG");



// Fields for 'home_nde_item' table
define ('LANG_ADMIN_COM_CONTENT_HOME_FIELDSET1', "Titre");
define ('LANG_ADMIN_COM_CONTENT_HOME_TITLE'						, "Titre de la page d'accueil");

define ('LANG_ADMIN_COM_CONTENT_HOME_FIELDSET2', "Contenus");
define ('LANG_ADMIN_COM_CONTENT_HOME_HEADER'					, "Entête");
define ('LANG_ADMIN_COM_CONTENT_HOME_FOOTER'					, "Pied de page");



// Functions
define ('LANG_ADMIN_COM_CONTENT_PENDING_TASKS'					, "Articles récents en attente de publication");
define ('LANG_ADMIN_COM_CONTENT_LAST_PUBLISHED'					, "Articles récemment publiés");
define ('LANG_ADMIN_COM_CONTENT_PREVIEW'						, "Résumé");



// header_msg
define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_TITLE_START'			, "Méssage d'entête commun aux articles d'un répertoire");
define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_TITLE_NEW'			, "Nouveau méssage d'entête");
define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_TITLE_UPDATE'		, "Mise à jour du méssage d'entête");

define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_ID'					, "ID");
define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_DATE_CREATION'		, "Date");
define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID'				, "Répertoire");
define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_MESSAGE'				, "Message");

define ('LANG_ADMIN_COM_CONTENT_HEADER_MSG_NODE_ID_NOT_SELECTED', "Non sélectionné.");

?>