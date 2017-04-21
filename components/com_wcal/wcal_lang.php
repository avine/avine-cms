<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Fields of 'wcal_config' table
define( 'LANG_COM_WCAL_CONFIG_DATE_MAX'								, "Date limite d'anticipation" );
define( 'LANG_COM_WCAL_CONFIG_WDAY_SUNDAY_7'						, "La semaine commence lundi (et non dimanche)" );
define( 'LANG_COM_WCAL_CONFIG_DEDICATE_AMOUNTS'						, "Prix des cours" );
define( 'LANG_COM_WCAL_CONFIG_HANDLE_DESIGNATION_ID'				, "Affectation de don surchargée" );


// Fields of 'wcal_category' table
define( 'LANG_COM_WCAL_CATEGORY_TITLE'								, "Titre" );
define( 'LANG_COM_WCAL_CATEGORY_AUTHOR'								, "Auteur" );
define( 'LANG_COM_WCAL_CATEGORY_COMMENT'							, "Description" );
define( 'LANG_COM_WCAL_CATEGORY_COLOR'								, "Couleur associée" );
define( 'LANG_COM_WCAL_CATEGORY_NODE_ID'							, "Répertoire associé" );
define( 'LANG_COM_WCAL_CATEGORY_ORDER'								, "Ordre" );



// Fields of 'wcal_period' table
define( 'LANG_COM_WCAL_PERIOD_TITLE'								, "Titre" );
define( 'LANG_COM_WCAL_PERIOD_WID_BEGIN'							, "Début" );
define( 'LANG_COM_WCAL_PERIOD_WID_END'								, "Fin" );



// Fields of 'wcal_event' table
define( 'LANG_COM_WCAL_EVENT_WDAY'									, "Jour" );
define( 'LANG_COM_WCAL_EVENT_TIME_BEGIN'							, "Heure de début" );
define( 'LANG_COM_WCAL_EVENT_TIME_END'								, "Heure de fin" );



// Fields of 'wcal_dedicate_type' table
define( 'LANG_COM_WCAL_DEDICATE_TYPE_TITLE'							, "Titre" );
define( 'LANG_COM_WCAL_DEDICATE_TYPE_SAMPLE'						, "Texte d'exemple" );



// Fields of 'wcal_dedicate' table
define( 'LANG_COM_WCAL_DEDICATE_ID'									, "ID de dédicace" );
define( 'LANG_COM_WCAL_DEDICATE_RECORDING_DATE'						, "Date d'enregistrement" );
define( 'LANG_COM_WCAL_DEDICATE_EVENT_DATE'							, "Date de l'événement" );
define( 'LANG_COM_WCAL_DEDICATE_TYPE'								, "Intitulé de la dédicace" );
define( 'LANG_COM_WCAL_DEDICATE_COMMENT'							, "Texte de la dédicace" );
define( 'LANG_COM_WCAL_DEDICATE_DONATE_ID'							, "ID de don" );
define( 'LANG_COM_WCAL_DEDICATE_PAYMENT_STATUS'						, "Validé" );



// Fields of 'wcal_dedicate_details' table
define( 'LANG_COM_WCAL_DEDICATE_DETAILS_NODE_ID'					, "Répertoire du cours" );
define( 'LANG_COM_WCAL_DEDICATE_DETAILS_ELM_DATE_CREATION'			, "Date du cours" );



// wcal class
define( 'LANG_COM_WCAL_CALENDAR'									, "Agenda des cours" );
define( 'LANG_COM_WCAL_WEEK'										, "Semaine" );
define( 'LANG_COM_WCAL_FROM'										, "du" );
define( 'LANG_COM_WCAL_TO'											, "au" );
define( 'LANG_COM_WCAL_DEFAULT_PERIOD_VALIDITY'						, "hors période particulière" );



// index.php
define( 'LANG_COM_WCAL_INDEX_TITLE'									, "Agenda hebdomadaire des cours" );
define( 'LANG_COM_WCAL_INDEX_DATE_MAX_REACHED'						, "Agenda non disponible." );

define( 'LANG_COM_WCAL_INDEX_TODAY'									, "Aujourd'hui" );
define( 'LANG_COM_WCAL_INDEX_VALID'									, "Valable" );

define( 'LANG_COM_WCAL_INDEX_READ_MORE'								, "Accéder aux cours" );
define( 'LANG_COM_WCAL_INDEX_SELECT_PERIOD'							, "Sélectionner une période de l'année" );

define( 'LANG_COM_WCAL_INDEX_MESSAGE_CURRENT_WEEK_YES'				, "Semaine courante" );
define( 'LANG_COM_WCAL_INDEX_MESSAGE_CURRENT_WEEK_NO'				, "Autre semaine" );

define( 'LANG_COM_WCAL_GOTO_INDEX'									, "Agenda hebdomadaire" );
define( 'LANG_COM_WCAL_GOTO_CATEGORY'								, "Agenda thématique" );
define( 'LANG_COM_WCAL_GOTO_DEDICATE'								, "Dédicacer des cours" );



// category.php
define( 'LANG_COM_WCAL_CAT_TITLE'									, "Agenda thématique des cours" );

define( 'LANG_COM_WCAL_CAT_THE'										, "Les" );
define( 'LANG_COM_WCAL_CAT_BETWEEN'									, "de" );
define( 'LANG_COM_WCAL_CAT_AND'										, "à" );

define( 'LANG_COM_WCAL_CAT_SCHEDULES'								, "Horaires" );

define( 'LANG_COM_WCAL_CAT_EMPTY'									, "Aucun cours disponible pour le moment..." );



// dedicate.php
define( 'LANG_COM_WCAL_DEDICATE_TITLE'								, "Dédicacer des cours" );

define( 'LANG_COM_WCAL_DEDICATE_FIELDSET_DATE'						, "Indiquez l'occasion à dédicacer" );
define( 'LANG_COM_WCAL_DEDICATE_FIELDSET_EVENTS'					, "Sélectionnez les cours à dédicacer" );
define( 'LANG_COM_WCAL_DEDICATE_FIELDSET_COMMENT'					, "Rédiger le texte de la dédicace" );

define( 'LANG_COM_WCAL_DEDICATE_TIPS_DATE'							, "" ); # "Renseignez la date et l'intitulé correspondants à l'occasion de votre dédicace."
define( 'LANG_COM_WCAL_DEDICATE_TIPS_EVENTS'						, "Les cours donnés à la date événement, sont cochés par défaut.<br /> Vous pouvez également en cocher d'autres.<br /> Les cours n'ayant pas de case à cocher, ne peuvent être dédicacés." );
define( 'LANG_COM_WCAL_DEDICATE_TIPS_COMMENT'						, "Vous pouvez vous inspirer du modèle proposé en remplaçant les pointillés par les termes de votre choix.<br /> Vous pouvez également rédiger un texte à votre convenance." );

define( 'LANG_COM_WCAL_DEDICATE_SELECT_DATE'						, "Date de l'événement" );
define( 'LANG_COM_WCAL_DEDICATE_SELECT_DATE_ERROR1'					, "La date sélectionnée est déjà passée." );
define( 'LANG_COM_WCAL_DEDICATE_SELECT_DATE_ERROR2'					, "La date sélectionnée dépasse la limite prévisionnelle de l'agenda." );

define( 'LANG_COM_WCAL_DEDICATE_SELECT_TYPE'						, "Intitulé de la dédicace" );

define( 'LANG_COM_WCAL_DEDICATE_CHECK_ALL_EVENTS'					, "Tout cocher" );

define( 'LANG_COM_WCAL_DEDICATE_SUBMIT'								, "Valider" ); # "Enregistrer la dédicace"
define( 'LANG_COM_WCAL_DEDICATE_SUBMIT_ERROR_EVENT'					, "Vous n'avez coché aucun cours de l'agenda." );
define( 'LANG_COM_WCAL_DEDICATE_SUBMIT_ERROR_COMMENT'				, "Vous n'avez pas rédigé le texte de la dédicace." );

define( 'LANG_COM_WCAL_DEDICATE_SAMPLE_BUTTON_MANAGE'				, "Effacer/Restaurer le modèle" );
define( 'LANG_COM_WCAL_DEDICATE_SAMPLE_CONFIRM_RESTORE'				, "Attention, restaurer le modèle va effacer votre texte !" );

define( 'LANG_COM_WCAL_DEDICATE_RECORDED'							, "Votre dédicace a bien été enregistrée !" );

define( 'LANG_COM_WCAL_DEDICATE_BUTTON_ADD_DEDICATE'				, "Ajouter une dédicace" );
define( 'LANG_COM_WCAL_DEDICATE_BUTTON_GO_TO_SUMMARY'				, "Vérifier et régler mes dédicaces" );
define( 'LANG_COM_WCAL_DEDICATE_BUTTON_GO_TO_SUMMARY_PENDING'		, "Vérifier et régler mes dédicaces en attente" );

define( 'LANG_COM_WCAL_DEDICATE_TIPS_PENDING'						, "Vous avez des dédicaces en attente de paiement..." );

define( 'LANG_COM_WCAL_DEDICATE_UPDATE_TIPS'						, "Mise à jour de votre dédicace" );
define( 'LANG_COM_WCAL_DEDICATE_UPDATE_TIPS_WARNING'				, "Attention, les cases à cocher ont été réinitialisées !" );

define( 'LANG_COM_WCAL_DEDICATE_AMOUNT_TOTAL'						, "Montant total" );



// summary.php
define( 'LANG_COM_WCAL_SUMMARY_TITLE'								, "Récapitulatif des dédicaces" );

define( 'LANG_COM_WCAL_SUMMARY_NO_PENDING'							, "Vous n'avez pas de dédicaces en attente de réglement." );
define( 'LANG_COM_WCAL_SUMMARY_START_DEDICATE'						, "Dédicacer maintenant" );

define( 'LANG_COM_WCAL_SUMMARY_DEDICATED_EVENTS'					, "Cours dédicacés" );
define( 'LANG_COM_WCAL_SUMMARY_AMOUNT'								, "Montant" );
define( 'LANG_COM_WCAL_SUMMARY_ACTIONS'								, "Actions" );

define( 'LANG_COM_WCAL_SUMMARY_CHECKOUT'							, "Régler maintenant" );



// list.php
define( 'LANG_COM_WCAL_LIST_TITLE'									, "Liste des dédicaces" );
define( 'LANG_COM_WCAL_LIST_TITLE_MONTH'							, "du mois" );
define( 'LANG_COM_WCAL_LIST_TITLE_MONTHS'							, "des {months} derniers mois" );

define( 'LANG_COM_WCAL_LIST_EMPTY'									, "Aucune dédicace enregistrée pour le moment..." );

define( 'LANG_COM_WCAL_LIST_ELEMENT_LINK'							, "Cours dédicacés" );
define( 'LANG_COM_WCAL_LIST_RECORDING_DATE'							, "Créé le" );



// *.php
define( 'LANG_COM_WCAL_THE_DEDICATES'								, "Les dédicaces" );


?>