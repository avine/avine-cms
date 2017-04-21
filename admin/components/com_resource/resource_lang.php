<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_RESOURCE_INDEX_TITLE'							, "Gestion des ressources" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_TITLE_START'						, "Liste des ressources" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_TITLE_NEW_DIR'					, "Nouveau répertoire" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_TITLE_NEW_FILE'					, "Nouvelle ressource" );

define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_TITLE_START'					, "Gestionnaire des miniatures" );



// 'resource_config' table
define( 'LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_VALUE'					, "Dimensions" );
define( 'LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_KEY'						, "Unité" );
define( 'LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_QUALITY'					, "Qualité" );



// list.php
define( 'LANG_ADMIN_COM_RESOURCE_LIST_TITLE_UPDATE'						, "Mise à jour de la ressource" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_UPD_DIR'							, "Répertoire : " );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_UPD_FILE'							, "Fichier : " );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_UPD_ERROR_NAME_MISSING'			, "Aucun nom spécifié" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_UPD_CURRENT_IMAGE_SIZE'			, "Dimensions actuelles : " );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_UPD_INVALID_DIRNAME'				, "Nom de répertoire invalide" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_UPD_INVALID_FILENAME'				, "Nom de fichier invalide" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE'						, "Redimensionner l'image (facultatif)" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE_CREATE_COPY'			, "Créer une copie <span style=\"color:grey;\">(ex. : 'sample.jpg' -&gt; 'sample_resized.jpg')</span>" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE_ERROR'				, "L'image n'a pu être redimensionnée" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE_COPY_PATH'			, "Adresse de l'image redimensionnée : " );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_THUMBS_LABEL'				, "Rép. des miniatures" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_DIR_LABEL'					, "Répertoire" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_LABEL'					, "Extension" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_ALL'					, "Toute" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_IMAGE'					, "Image" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_VIDEO'					, "Son, vidéo" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_TEXT'					, "Document" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_PREVIEW_LABEL'				, "Prévisualiser" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_PREVIEW_NO'				, "Non" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_SELECT_PREVIEW_YES'				, "Oui" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_PATH'					, "Ressource" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_INFOS'					, "Infos" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_LINK'					, "Lien" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_PREVIEW'				, "Prévisualisation" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_BUTTON_NEW_DIR'					, "Nouveau répertoire" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_BUTTON_NEW_FILE'					, "Nouvelle ressource" );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_DATA_CORRUPTED'					, "Impossible d'accéder à la ressource : " );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_DEL_RESULT_DIR'					, "Le répertoire suivant a bien été effacé : " );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_DEL_RESULT_FILE'					, "Le fichier suivant a bien été effacé : " );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_NEW_DIR_PATH'						, "Chemin et nom du nouveau répertoire" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_NEW_DIR_NAME_ERROR'				, "Nom du nouveau répertoire non renseigné." );

define( 'LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_PATH'					, "Sélectionner un répertoire" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_UPLOAD'					, "Sélectionner la resource à télécharger" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_OVERWRITE'				, "Ecraser éventuellement une ressource de même nom" );
define( 'LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_UPLOAD_SUCCESS'			, "Adresse de la ressource : " );



// thumbs.php
define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_FIELDSET1'						, "Configuration" );
define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_FIELDSET2'						, "Actions" );

define( 'LANG_ADMIN_COM_RESOURCE_THUMB_VALUE_AND_KEY_LABEL'				, "Dimensions et unité" );

define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_ACTION_SELECT_DIR_LABEL'		, "Répertoire de départ" );

define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_BUTTON_UPDATE'					, "Mettre à jour les miniatures" );
define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_BUTTON_DELETE'					, "Effacer les miniatures" );

define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_TITLE'					, "Résultat de la mise à jour" );
define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_DIR'						, "<b>Répertoire de départ :</b> {directory}" );

define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_NOTHING_TO_DO'		, "<b>{directory}</b> : tout est en ordre, il n'y avait rien à faire !" );
define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_NEW_THUMB_AVAILABLE'	, "<span style=\"color:#228B22;\">Nouvelle miniature disponible</span>" );
define( 'LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_DELETED_THUMB'		, "<span style=\"color:#B22222;\">Miniature effacée</span>" );



?>