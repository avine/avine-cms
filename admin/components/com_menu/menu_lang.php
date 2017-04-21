<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_MENU_INDEX_TITLE'									, "Gestion des menus" );

// menu.php
define( 'LANG_ADMIN_COM_MENU_TITLE_START'									, "Liste des menus" );
define( 'LANG_ADMIN_COM_MENU_TITLE_NEW'										, "Nouveau menu" );
define( 'LANG_ADMIN_COM_MENU_TITLE_UPDATE'									, "Mise à jour du menu" );
define( 'LANG_ADMIN_COM_MENU_TITLE_DELETE'									, "Suppression du menu" );

define( 'LANG_ADMIN_COM_MENU_ID'											, "ID" );
define( 'LANG_ADMIN_COM_MENU_NAME'											, "Nom" );
define( 'LANG_ADMIN_COM_MENU_DESC'											, "Description" );

define( 'LANG_ADMIN_COM_MENU_MAIN_MENU_REQUIRED'							, "Le menu '<b>mainmenu</b>' est nécessaire au fonctionnement du système, il ne peut être effacé." );
define( 'LANG_ADMIN_COM_MENU_NOT_EMPTY_MENU'								, "Le menu contient des liens, il ne peut être effacé." );
define( 'LANG_ADMIN_COM_MENU_NAME_ALREADY_EXIST'							, "Ce nom est déjà utilisé." );

define( 'LANG_ADMIN_COM_MENU_MENU_FIELD_MENU'								, "Détails du Menu" );
define( 'LANG_ADMIN_COM_MENU_MENU_FIELD_MODULE'								, "Module associé" );

define( 'LANG_ADMIN_COM_MENU_NEW_MOD_FILE_OVERWRITE'						, "Ecraser si nécessaire un module de même nom" );
define( 'LANG_ADMIN_COM_MENU_NEW_MOD_FILE_CONSERVED'						, "Le fichier '<b>{input}</b>' existait déjà, et a bien été conservé." );
define( 'LANG_ADMIN_COM_MENU_NEW_MOD_FILE_OVERWRITTEN'						, "Le fichier '<b>{input}</b>' existait déjà, et a bien été mis à jour." );
define( 'LANG_ADMIN_COM_MENU_NEW_MOD_FILE_CREATED'							, "Le fichier '<b>{input}</b>' a bien été créé." );
define( 'LANG_ADMIN_COM_MENU_NEW_MOD_FILE_NAME'								, "Nom du module associé : <b>/modules/menu_<i>'nom-du-menu'</i>.php</b>" );

define( 'LANG_ADMIN_COM_MENU_NEW_MENU_SUCCESS'								, "Le menu '<b>{input}</b>' a bien été créé." );

define( 'LANG_ADMIN_COM_MENU_UPD_MOD_FILE_ON_FTP'							, "<span style=\"color:green;\">Le module associé '<b>{file}</b>' est présent sur le serveur FTP.</span>" );
define( 'LANG_ADMIN_COM_MENU_UPD_MOD_FILE_OFF_FTP'							, "<span style=\"color:red;\">Le module associé '<b>{file}</b>' est absent du serveur FTP.</span>" );

define( 'LANG_ADMIN_COM_MENU_DEL_MENU_SUCCESS'								, "Le menu '<b>{menu}</b>' a été supprimé de la base de données." );

define( 'LANG_ADMIN_COM_MENU_DEL_MOD_FILE'									, "Supprimer le module associé '<b>{file}</b>' du serveur FTP" );
define( 'LANG_ADMIN_COM_MENU_DEL_MOD_SUCCESS'								, "Le fichier '<b>{file}</b>' a été supprimé du serveur FTP." );
define( 'LANG_ADMIN_COM_MENU_DEL_MOD_FAILED'								, "Le fichier '<b>{file}</b>' n'a pu être supprimé du serveur FTP." );



// link.php
define( 'LANG_ADMIN_COM_MENU_LINK_TITLE_START'								, "Liste des liens" );
define( 'LANG_ADMIN_COM_MENU_LINK_TITLE_NEW'								, "Nouveau Lien" );
define( 'LANG_ADMIN_COM_MENU_LINK_TITLE_UPDATE'								, "Mise à jour du lien" );

define( 'LANG_ADMIN_COM_MENU_LINK_SELECT_MENU'								, "Menus disponibles" );
define( 'LANG_ADMIN_COM_MENU_LINK_MENU_TYPE'								, "Nouveau lien" );

define( 'LANG_ADMIN_COM_MENU_LINK_TEMPLATE_IS_MISSING'						, "Non disponible !" );

define( 'LANG_ADMIN_COM_MENU_LINK_FIELD_GENERAL'							, "Informations générales" );
define( 'LANG_ADMIN_COM_MENU_LINK_FIELD_SPECIFIC'							, "Informations spécifiques" );

define( 'LANG_ADMIN_COM_MENU_LINK_ID'										, "id" );
define( 'LANG_ADMIN_COM_MENU_LINK_NAME'										, "Nom" );
define( 'LANG_ADMIN_COM_MENU_LINK_HREF'										, "Url" );
define( 'LANG_ADMIN_COM_MENU_LINK_UNIQUE_ID'								, "Identifiant unique" );
define( 'LANG_ADMIN_COM_MENU_LINK_TYPE'										, "Type" );
define( 'LANG_ADMIN_COM_MENU_LINK_ORDER'									, "Ordre" );
define( 'LANG_ADMIN_COM_MENU_LINK_ACCESS'									, "Niveau d'accès" );
define( 'LANG_ADMIN_COM_MENU_LINK_PUBLISHED'								, "Publié" );
define( 'LANG_ADMIN_COM_MENU_LINK_TEMPLATE'									, "Modèle" );
define( 'LANG_ADMIN_COM_MENU_LINK_PARAMS'									, "Paramètres" );

define( 'LANG_ADMIN_COM_MENU_LINK_CHANGE_ORDER'								, "Lien parent" );
define( 'LANG_ADMIN_COM_MENU_LINK_CHANGE_ORDER_ROOT'						, "( Racine )" );
define( 'LANG_ADMIN_COM_MENU_LINK_ADD_CSS'									, "Ajouter au lien une classe CSS particulière" );
define( 'LANG_ADMIN_COM_MENU_LINK_TMPL_LIST'	 							, "Afficher cette page avec un modèle particulier" );
define( 'LANG_ADMIN_COM_MENU_LINK_USE_DEFAULT_TMPL'							, "( modèle par défaut )" );
define( 'LANG_ADMIN_COM_MENU_LINK_ACCESS_LIST'								, "Niveau d'accès" );

define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE1_PARAMS'							, "Sélectionner un fichier du répertoire '/contents'" );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE2_PARAMS'							, "Sélectionner un {element}" );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE3_PARAMS'							, "Sélectionner un {node}" );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE5_PARAMS'							, "Sélectionner une page de composant" );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE6_PARAMS'							, "Aucun paramètre suplémentaire." );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE7_HREF'							, "URL du lien" );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE7_PARAMS'							, "Nouvelle fenêtre" );

define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE1_NO_FILE_SELECTED'				, "Aucun fichier sélectionné." );
define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE1_FILE_NOT_FOUND'					, "<span style=\"color:red\">Le fichier '<b>{file}</b>' n'est plus disponible sur le serveur FTP. <br />Pour rendre ce lien à nouveau fonctionnel il faut le réinstaller, ou en choisir un autre dans la liste.<br /><br /></span>" );

define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE23_NO_ITEM_SELECTED'				, "La sélection est vide." );

define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE5_NO_COMPONENT_SELECTED'			, "Aucun composant sélectionné." );

define( 'LANG_ADMIN_COM_MENU_LINK_MENUTYPE7_NO_URL'							, "Il manque l'URL." );

define( 'LANG_ADMIN_COM_MENU_LINK_DEL_LINK_HAVE_CHILD'						, "Le lien a des sous-liens." );
define( 'LANG_ADMIN_COM_MENU_LINK_DEL_MODULES_XHREF_TROUBLES'				, "Message interne : La mise à jour de la table 'module_xhref' a posé problème! Mais le fonctionnement du système n'est pas affecté." );

define( 'LANG_ADMIN_COM_MENU_LINK_NO_LINK_TYPE_SELECTED'					, "Aucun type de lien sélectionné." );

define( 'LANG_ADMIN_COM_MENU_LINK_INVALID_FIRST_LINK_HOME_PAGE'				, "Le premier lien du 'mainmenu' est de type : <b>url</b> (ou <b>separateur</b>) et ne peut être utilisé comme page d'accueil.<br />Le système utilisera le premier lien valide de ce menu." );

define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES'								, "Modules à afficher dans la page" );

define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_USE_DEFAULT'					, "Ajouter les modules par défaut" );
define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_USE_DEFAULT_TIPS'			, "<span class=\"grey\">(voir mention : *)</span>" );
define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_BY_DEFAULT'					, "*" );

define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_CURRENT_MENU'				, "[M]" );
define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_CURRENT_MENU_TIPS'			, "La mention [M] indique le module responsable de l'affichage du menu actuellement édité." );

define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_COPY_FROM_LINK'				, "Ajouter les modules qui apparaissent sur une autre page" );

define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_NEW_TIPS'					, "<br /><span class=\"grey\">Note : Si plusieurs liens pointent vers la même  page, seul le premier sera pris en compte pour l'affichage des modules.</span>" );
define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_UPD_TIPS_NOT_PRIMARY_LINK'	, "<br /><span class=\"red\">Attention : Plusieurs liens pointent vers cette page. Seul le lien '<b>{name}</b>' (id={id}) du menu '<b>{menu_name}</b>' est pris en compte pour l'affichage des modules.</span>" );
define( 'LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_PRIMARY_LINK_TIPS'			, "<br /><span class=\"grey\">Conseil : Si nécessaire, cocher l'option <b>\"Identifiant unique\"</b> pour changer ce comportement.</span>" );

define( 'LANG_ADMIN_COM_MENU_LINK_NO_MENU_DEFINED'							, "Aucun menu n'a été défini." );



// sitemap.php
define( 'LANG_ADMIN_COM_MENU_SITEMAP_TITLE_START'							, "Plan du site" );

define( 'LANG_ADMIN_COM_MENU_SITEMAP_MENU_ORDER'							, "Ordre" );
define( 'LANG_ADMIN_COM_MENU_SITEMAP_MENUS_AND_LINKS'						, "Menus / Liens" );
define( 'LANG_ADMIN_COM_MENU_SITEMAP_EXCLUDE'								, "Exclure" );

define( 'LANG_ADMIN_COM_MENU_SITEMAP_BUTTON_EXCLUDE_UNPUBLISHED_LINKS'		, "Exclure les liens dépubliés" );
define( 'LANG_ADMIN_COM_MENU_SITEMAP_RESULT_NEW_LINKS_EXCLUDED'				, "Nombre de liens nouvellement exclus : " );
define( 'LANG_ADMIN_COM_MENU_SITEMAP_RESULT_NO_NEW_LINKS_TO_EXCLUDE'		, "Aucun nouveau lien à exclure." );

?>