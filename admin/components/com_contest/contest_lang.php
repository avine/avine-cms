<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_CONTEST_INDEX_TITLE'							, "Gestionnaire du Prix" );



// Fields of 'contest_config' tale
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_YEAR'							, "Année de participation" );
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_DEADLINE'						, "Date limite de dépôt des projets" );
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH'					, "Répertoire des ressources" );
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_JURY_PASSWORD'					, "Mot de passe du jury" );



// config.php
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_TITLE_START'						, "Configuration" );

define( 'LANG_ADMIN_COM_CONTEST_CONFIG_EXPIRED'							, "Expirée" );
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH_MISSING'			, "Non disponible" );

define( 'LANG_ADMIN_COM_CONTEST_CONFIG_EMPTY_RESOURCE_PATH'				, "Non renseigné" );
define( 'LANG_ADMIN_COM_CONTEST_CONFIG_INVALID_RESOURCE_PATH'			, "Le répertoire {input} n'existe pas sur le serveur" );



// project.php
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_TITLE_START'					, "Liste des projets" );
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_TITLE_UPD'						, "Mise à jour du projet" );
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_TITLE_NEW'						, "Nouveau projet" );

define( 'LANG_ADMIN_COM_CONTEST_PROJECT_EMPTY'							, "Aucun projet" );

define( 'LANG_ADMIN_COM_CONTEST_PROJECT_UPD_FIELDSET_USER'				, "Informations de l'auteur" );
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_UPD_FIELDSET_ADMIN'				, "Informations de l'éditeur" );
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_UPD_MISSING_RESOURCE_RECEIVED'	, "Les pièces manquantes ont été reçues" );

define( 'LANG_ADMIN_COM_CONTEST_PROJECT_DEL_ERROR'						, "Le projet contient des ressources. Il ne peut être effacé." );

define( 'LANG_ADMIN_COM_CONTEST_PROJECT_BUTTON_NEW_SELECT'				, "Ajouter un projet" );

define( 'LANG_ADMIN_COM_CONTEST_PROJECT_NEW_SELECT_USER'				, "Associer le projet à un utilisateur" );
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_NEW_NO_USER'					, "Aucun utilisateur disponible !" );
define( 'LANG_ADMIN_COM_CONTEST_PROJECT_NEW_DEFAULT_TITLE'				, "NOUVEAU PROJET" );



// resource.php
define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_START'					, "Liste des ressources" );
define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_NEW'						, "Nouvelle ressource" );
define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_UPD'						, "Mise à jour de la ressource" );
define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_ASSOCIATE'				, "Associer une ressource existante au projet" );

define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_USE_CURRENT_CONFIG_YEAR'		, "Filtrer les projets du Prix" );
define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_SELECT_PROJECT'				, "Projet" );

define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_DOWNLOAD_FILE'					, "Télécharger la ressource" );

define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_FILE_CONSERVED_ON_SERVER'		, "La ressource est tout de même conservée sur le serveur." );

define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_ASSOCIATE'						, "Associer une pièce jointe existante" );
define( 'LANG_ADMIN_COM_CONTEST_RESOURCE_ASSOCIATE_NO_FILE'				, "Aucune ressource disponible !" );



// winner.php
define( 'LANG_ADMIN_COM_CONTEST_WINNER_TITLE_START'						, "Liste des lauréats" );
define( 'LANG_ADMIN_COM_CONTEST_WINNER_TITLE_NEW'						, "Nouveau lauréat" );

define( 'LANG_ADMIN_COM_CONTEST_WINNER_ORDER'							, "Ordre" );
define( 'LANG_ADMIN_COM_CONTEST_WINNER_SELECT_PROJET'					, "Sélectionner un projet" );

?>