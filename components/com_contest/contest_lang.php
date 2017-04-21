<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language

// Fields of 'contest_project' table
define( 'LANG_COM_CONTEST_PROJECT_USER_ID'						, "Utilisateur" );
define( 'LANG_COM_CONTEST_PROJECT_CONFIG_YEAR'					, "Année de participation" );
define( 'LANG_COM_CONTEST_PROJECT_COMPAGNY'						, "Nom de l'établissement" );
define( 'LANG_COM_CONTEST_PROJECT_ADDRESS'						, "Adresse de l'établissement" );
define( 'LANG_COM_CONTEST_PROJECT_LEADERS'						, "Responsable(s) du projet" );
define( 'LANG_COM_CONTEST_PROJECT_CONTRIBUTORS'					, "Classe" );
define( 'LANG_COM_CONTEST_PROJECT_TITLE'						, "Titre du projet" );
define( 'LANG_COM_CONTEST_PROJECT_YEAR'							, "Année scolaire" );
define( 'LANG_COM_CONTEST_PROJECT_USER_COMMENT'					, "Renseignements complémentaires" );
define( 'LANG_COM_CONTEST_PROJECT_USER_VALIDATION'				, "Validé" );
define( 'LANG_COM_CONTEST_PROJECT_ALL_RESOURCE_PROVIDED'		, "Complet" );
define( 'LANG_COM_CONTEST_PROJECT_MISSING_RESOURCE_LIST'		, "Liste des pièces manquantes" );
define( 'LANG_COM_CONTEST_PROJECT_ADMIN_INTRO'					, "Objectifs et descriptif" );
define( 'LANG_COM_CONTEST_PROJECT_ADMIN_MAIN'					, "Travaux d'élèves" );
define( 'LANG_COM_CONTEST_PROJECT_ADMIN_VALIDATION'				, "Publié" );
define( 'LANG_COM_CONTEST_PROJECT_ORDER'						, "N° du projet" );



// Fields of 'contest_resource' table
define( 'LANG_COM_CONTEST_RESOURCE_FILE_NAME'					, "Fichier" );
define( 'LANG_COM_CONTEST_RESOURCE_CODE'						, "Code" );
define( 'LANG_COM_CONTEST_RESOURCE_TITLE'						, "Titre" );
define( 'LANG_COM_CONTEST_RESOURCE_COMMENT'						, "Description" );
define( 'LANG_COM_CONTEST_RESOURCE_VERIFIED'					, "Vérifié" );
define( 'LANG_COM_CONTEST_RESOURCE_PUBLISHED'					, "Publié" );
define( 'LANG_COM_CONTEST_RESOURCE_ORDER'						, "Ordre" );



// Pages titles
define( 'LANG_COM_CONTEST_PAGE_CONTEST_YEAR'					, "Prix" );

define( 'LANG_COM_CONTEST_PAGE_LOGIN_TITLE'						, "Inscription" );
define( 'LANG_COM_CONTEST_PAGE_PROJECT_TITLE'					, "Description du projet" );
define( 'LANG_COM_CONTEST_PAGE_RESOURCE_TITLE'					, "Travaux d'élèves" );
define( 'LANG_COM_CONTEST_PAGE_SUMMARY_TITLE'					, "Validation du projet" );



// Others
define( 'LANG_COM_CONTEST_DEADLINE_EXPIRED'						, "La date limite de dépôt des projets est échue : " );
define( 'LANG_COM_CONTEST_PROJECT_USER_VALIDATED_YES'			, "Le projet a été validé par son auteur." );
define( 'LANG_COM_CONTEST_PROJECT_USER_VALIDATED_NO'			, "Le projet n'a pas été validé par son auteur !" );
define( 'LANG_COM_CONTEST_NO_PROJECT_ID'						, "Vous devez au préalable décrire votre projet !" );
define( 'LANG_COM_CONTEST_BUTTON_BACK'							, "Retour" );
define( 'LANG_COM_CONTEST_FIELD_ERROR_EMPTY'					, "Non renseigné" );



// login.php
define( 'LANG_COM_CONTEST_LOGIN_CLICK_TO_CONTINUE'				, "Continuer &raquo;" );



// project.php
define( 'LANG_COM_CONTEST_PROJECT_FIELDSET_COMPAGNY'			, "Etablissement" );
define( 'LANG_COM_CONTEST_PROJECT_FIELDSET_CONTRIBUTORS'		, "Participants" );
define( 'LANG_COM_CONTEST_PROJECT_FIELDSET_PROJECT'				, "Projet" );

define( 'LANG_COM_CONTEST_PROJECT_FIELDSET_COMPAGNY_TIPS'		, "Addresse, code postal, ville" );
define( 'LANG_COM_CONTEST_PROJECT_FIELDSET_CONTRIBUTORS_TIPS'	, "Un nom par ligne" );
define( 'LANG_COM_CONTEST_PROJECT_FIELDSET_PROJECT_TIPS'		, "" );

define( 'LANG_COM_CONTEST_PROJECT_HEADER_LABEL'					, "Libellés" );
define( 'LANG_COM_CONTEST_PROJECT_HEADER_INFO'					, "Informations" );



// resource.php
define( 'LANG_COM_CONTEST_RESOURCE_UPLOAD'						, "Ajouter une pièce jointe" );
define( 'LANG_COM_CONTEST_RESOURCE_MISSING_RESOURCE'			, "Certaines pièces jointes ont disparues. Merci de nous les transmettre à nouveau." );
define( 'LANG_COM_CONTEST_RESOURCE_MODIFY'						, "Modifier la pièce jointe" );
define( 'LANG_COM_CONTEST_RESOURCE_UPLOAD_MAX_SIZE_TIPS'		, "La taille du fichier ne doit pas dépasser %s. Dans le cas contraire, envoyez-le par courrier." );



// summary.php
define( 'LANG_COM_CONTEST_SUMMARY_RESOURCES_LIST_PROVIDED'		, "Pièces jointes fournies" );
define( 'LANG_COM_CONTEST_SUMMARY_RESOURCES_LIST_PENDING'		, "Pièces en attente" );
define( 'LANG_COM_CONTEST_SUMMARY_NO_RESOURCES_PROVIDED'		, "Aucune" );

define( 'LANG_COM_CONTEST_SUMMARY_ALL_RESOURCE_PROVIDED_YES'	, "Le projet est complet, tous les travaux ont été fournis en pièces jointes" );
define( 'LANG_COM_CONTEST_SUMMARY_ALL_RESOURCE_PROVIDED_NO'		, "Le projet n'est pas complet, des travaux vont être transmis par courrier" );

define( 'LANG_COM_CONTEST_SUMMARY_USER_VALIDATION_TIPS'			, "Attention ! Après validation, vous ne pourrez plus apporter de modifications." );
define( 'LANG_COM_CONTEST_SUMMARY_USER_VALIDATION'				, "Valider pour le Prix" );

define( 'LANG_COM_CONTEST_SUMMARY_FIELDSET'						, "Validation du projet" );
define( 'LANG_COM_CONTEST_SUMMARY_ERROR_RESOURCE_STATUS'		, "Statut des pièces jointes non renseigné." );
define( 'LANG_COM_CONTEST_SUMMARY_ERROR_MISSING_LIST'			, "Liste des pièces manquantes non renseignée." );



// jury.php
define( 'LANG_COM_CONTEST_JURY_LOGIN_TIPS'						, "Pour accéder à l'espace jury, saisisez votre code puis validez" );
define( 'LANG_COM_CONTEST_JURY_PASSWORD'						, "Mot de passe" );
define( 'LANG_COM_CONTEST_JURY_CONTESTANTS'						, "Les candidatures" );
define( 'LANG_COM_CONTEST_JURY_RESOURCES'						, "Ressources" );



// winner.php
define( 'LANG_COM_CONTEST_WINNER_TITLE'							, "Les lauréats" );


?>