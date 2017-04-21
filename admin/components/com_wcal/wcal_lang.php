<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_WCAL_INDEX_TITLE'							, "Agenda hebdomadaire" );



// config.php
define( 'LANG_ADMIN_COM_WCAL_CONFIG_TITLE_START'					, "Configuration de l'agenda" );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_FIELDSET_CALENDAR'				, "Agenda" );
define( 'LANG_ADMIN_COM_WCAL_CONFIG_FIELDSET_DEDICATE'				, "Dédicace" );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_FIELDSET_DEDICATE_TIPS'			, 
"Renseignez les prix dégressifs des cours, séparés par des points virgules.
Exemple : \"<b>150</b> ; <b>130</b> ; <b>100</b>\" signifie que dédicacer un cours coûte <b>150</b> euros et que dédicacer deux cours coûte <b>150</b> + <b>130</b> = 280 euros.
Ensuite chaque cours ajouté coûte <b>100</b> euros de plus. A noter : la devise utilisée est celle définie dans la configuration des dons." );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_DATE_MAX_TIPS'					, "fin de semaine uniquement" );
define( 'LANG_ADMIN_COM_WCAL_CONFIG_DATE_MAX_EXPIRED'				, "Expirée !" );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_WDAY_SUNDAY_7_UPDATED_TIPS'		, "Le jour par lequel commence la semaine a été modifié. Les bornes des périodes ont été impactées en conséquence." );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_AMOUNTS_ERROR_NOT_NUMBER'		, "Les prix sont invalides" );
define( 'LANG_ADMIN_COM_WCAL_CONFIG_AMOUNTS_ERROR_ZERO_BEGIN'		, "Le prix du premier cours ne peut être égal à zéro." );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_TITLE'			, "Dédicacer des cours" );
define( 'LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_COMMENT'		, "" );

define( 'LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_SUCCESS'		, "<strong>Initialisation du système de dédicaces : </strong><br />Une nouvelle affectation de dons vient d'être crée. <br />C'est elle qui sera surchargée par le composant agenda afin de proposer le règlement des dédicaces." );
define( 'LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_FAILURE'		, "<strong>L'initialisation du système de dédicaces a échoué ! </strong><br />Impossible de créer l'affectation de don à surcharger. <br />Cause de l'erreur : une affectation existe déjà avec le titre suivant :" );



// category.php
define( 'LANG_ADMIN_COM_WCAL_CATEGORY_TITLE_START'					, "Liste des matières" );
define( 'LANG_ADMIN_COM_WCAL_CATEGORY_TITLE_NEW'					, "Nouvelle matière" );
define( 'LANG_ADMIN_COM_WCAL_CATEGORY_TITLE_UPDATE'					, "Mise à jour de la matière" );

define( 'LANG_ADMIN_COM_WCAL_CATEGORY_DUPLICATE_TITLE_AUTHOR'		, "Ce couple 'titre-auteur' existe déjà" );
define( 'LANG_ADMIN_COM_WCAL_CATEGORY_DEL_ERROR'					, "Cette matière est utilisée par certains cours. Elle ne peut être effacée." );

define( 'LANG_ADMIN_COM_WCAL_CATEGORY_NODE_ID_MISSING'				, "Répertoire associé introuvable !" );



// period.php
define( 'LANG_ADMIN_COM_WCAL_PERIOD_TITLE_START'					, "Liste des périodes" );
define( 'LANG_ADMIN_COM_WCAL_PERIOD_TITLE_NEW'						, "Nouvelle période" );
define( 'LANG_ADMIN_COM_WCAL_PERIOD_TITLE_UPDATE'					, "Mise à jour de la période" );

define( 'LANG_ADMIN_COM_WCAL_PERIOD_DUPLICATE_TITLE'				, "Ce titre existe déjà." );
define( 'LANG_ADMIN_COM_WCAL_PERIOD_WID_CHRONOLOGY_ERROR'			, "Les dates ne sont pas chronologiques." );
define( 'LANG_ADMIN_COM_WCAL_PERIOD_WID_COVERING_ERROR'				, "La période temporelle définie recouvre une période existante." );

define( 'LANG_ADMIN_COM_WCAL_PERIOD_DEFAULT_TIPS'					, "Cet agenda est utilisé par défaut, lorsque pour une date donnée, aucune période particulière ne la contenant n'a été définie." );

define( 'LANG_ADMIN_COM_WCAL_PERIOD_DEL_ERROR'						, "Cette période contient des cours. Elle ne peut être effacée." );

define( 'LANG_ADMIN_COM_WCAL_PERIOD_FIELDSET_DATES'					, "Validité temporelle" );

define( 'LANG_ADMIN_COM_WCAL_PERIOD_COPY_EVENT_FROM_DEFAULT_PERIOD'	, "Recopier les cours de la période par défaut" );


// event.php
define( 'LANG_ADMIN_COM_WCAL_EVENT_TITLE_START'						, "Liste des cours" );
define( 'LANG_ADMIN_COM_WCAL_EVENT_TITLE_NEW'						, "Nouveau cours" );
define( 'LANG_ADMIN_COM_WCAL_EVENT_TITLE_UPDATE'					, "Mise à jour du cours" );

define( 'LANG_ADMIN_COM_WCAL_EVENT_SELECT_PERIOD'					, "Sélectionner une période" );

define( 'LANG_ADMIN_COM_WCAL_EVENT_CATEGORY'						, "Matière" );

define( 'LANG_ADMIN_COM_WCAL_EVENT_NOT_SELECTED'					, "Sélection manquante." );

define( 'LANG_ADMIN_COM_WCAL_EVENT_INVALID_TIME_FORMAT'				, "Format invalide.<br /> (exemple : pour 15h renseigner 15:00)." );

define( 'LANG_ADMIN_COM_WCAL_EVENT_TIMES_CHRONOLOGY_ERROR'			, "Les horaires ne sont pas chronologiques." );

define( 'LANG_ADMIN_COM_WCAL_EVENT_TIME_FROM'						, "de" );
define( 'LANG_ADMIN_COM_WCAL_EVENT_TIME_TO'							, "à" );

define( 'LANG_ADMIN_COM_WCAL_EVENT_SPECIAL_PERIOD_ACTUALLY'			, "Période actuelle !" );
define( 'LANG_ADMIN_COM_WCAL_EVENT_PERIOD'							, "Période" );



// dedicate_type.php
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_TITLE_START'				, "Liste des intitulés des dédicaces" );
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_TITLE_NEW'				, "Nouvelle intitulé de dédicace" );
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_TITLE_UPDATE'			, "Mise à jour de l'intitulé de dédicace" );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_DUPLICATE_TITLE'			, "Ce titre existe déjà" );
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_DEL_ERROR'				, "Cet intitulé est utilisé par certaines dédicaces. Il ne peut être effacé." );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TYPE_EMPTY_WARNING'			, "Pour effectuer des dédicaces, il faut définir au moins un intitulé." );



// dedicate.php
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TITLE_START'					, "Liste des dédicaces par occasions" );
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_TITLE_UPDATE'					, "Mise à jour de la dédicace" );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_DEDICATED_EVENTS'				, "Liste des cours dédicacés" );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_ONLY_VALIDATED'				, "Validés uniquement" );


// dedicate_details.php
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_TITLE_START'			, "Liste des dédicaces par cours" );
define( 'LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_TITLE_UPDATE'			, "Mise à jour de la dédicace" );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ASSOCIATED_ELEM'		, "Concordance" );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ELEM_ID_NO_MATCH'		, "Cours associé introuvable !" );

define( 'LANG_ADMIN_COM_WCAL_DEDICATE_DETAILS_ASSOCIATED_ELEMENT'	, "Articles associé" );

?>