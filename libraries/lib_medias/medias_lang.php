<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language
define( 'LANG_COM_MEDIAS_LABEL_TITLE'						, "Titre" );
define( 'LANG_COM_MEDIAS_LABEL_SRC'							, "Source" );
define( 'LANG_COM_MEDIAS_LABEL_WIDTH'						, "Largeur" );
define( 'LANG_COM_MEDIAS_LABEL_HEIGHT'						, "Hauteur" );
define( 'LANG_COM_MEDIAS_LABEL_PREVIEW'						, "Prévisualisation" );

define( 'LANG_COM_MEDIAS_LABEL_KEEP_ALT'					, "Ajouter/Conserver");
define( 'LANG_COM_MEDIAS_LABEL_KEEP_TITLE'					, "Pour ajouter/conserver un média, cocher la case correspondante");

$admin_media_add = '<img src="'.WEBSITE_PATH.'/libraries/lib_medias/images/admin_media_add.png" alt="'.LANG_COM_MEDIAS_LABEL_KEEP_ALT.'" />';

define( 'LANG_COM_MEDIAS_LABEL_KEEP'						, '<span title="'.LANG_COM_MEDIAS_LABEL_KEEP_TITLE.'" style="cursor:help;">'.$admin_media_add.'</span>' );
define( 'LANG_COM_MEDIAS_LABEL_ORDER'						, "Ordre" );
define( 'LANG_COM_MEDIAS_LABEL_SELECT_OPTION_ROOT'			, "Sélectionner" );

define( 'LANG_COM_MEDIAS_FORM_INPUTS_LAST_LINE_FOR_NEW'		, "<span style=\"color:grey\">(Pour ajouter un média, cocher la case correspondante de la colonne <img src=\"".WEBSITE_PATH."/libraries/lib_medias/images/admin_media_add.png\" alt=\"Ajouter/Conserver\" />)</span>" );

define( 'LANG_COM_MEDIAS_STRING_ERROR_TITLE'				, "<b>Erreurs dans les informations de medias :</b>" );

define( 'LANG_COM_MEDIAS_ERROR_INVALID_EXTENSION'			, "Extension non prise en charge : " );
define( 'LANG_COM_MEDIAS_ERROR_VALID_EXTENSIONS'			, "Liste des extensions autorisées : " );

define( 'LANG_COM_MEDIAS_ERROR_INVALID_SRC'					, "Source du media invalide : " );
define( 'LANG_COM_MEDIAS_ERROR_EMPTY_TITLE'					, "Titre du média non renseigné" );

define( 'LANG_COM_MEDIAS_ERROR_INVALID_WIDTH'				, "Largeur du media invalide : " );
define( 'LANG_COM_MEDIAS_ERROR_INVALID_HEIGHT'				, "Hauteur du media invalide : " );

define( 'LANG_COM_MEDIAS_ERROR_INVALID_PREVIEW'				, "Image de prévisualisation invalide : " );

define( 'LANG_COM_MEDIAS_ERROR_MISSING_SOURCE'				, "Source du média manquante" ); 	# "Argument 'src=' manquant" //accurate translation
define( 'LANG_COM_MEDIAS_ERROR_MISSING_TITLE'				, "Titre du média manquant" );		# "Argument 'title=' manquant" //accurate translation

define( 'LANG_COM_MEDIAS_DOWNLOAD_LINK'						, "Télécharger le média" );
define( 'LANG_COM_MEDIAS_DOWNLOAD_LINK_FILE_SIZE'			, "taille" );

define( 'LANG_COM_MEDIAS_SHOW_PREVIEW_TITLE'				, "" ); # "Ressources : "
define( 'LANG_COM_MEDIAS_SHOW_TITLE'						, "" ); # "Ressources"

define( 'LANG_COM_MEDIAS_LOADING'							, "Chargement..." );

define( 'LANG_COM_MEDIAS_PREF_BUTTON_AUDIO'					, "Voir au format vidéo" );
define( 'LANG_COM_MEDIAS_PREF_BUTTON_VIDEO'					, "Ecouter au format audio" );

?>