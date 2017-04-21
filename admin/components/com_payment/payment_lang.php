<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_PAYMENT_INDEX_TITLE'							, "Gestion des paiements" );



// Payment_config table fields
define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_PAYMENT_ID_OFFSET'				, "N° du premier paiement" );
define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG'							, "Mode déboggage" );

// Payment_method table fields
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_ID'								, "Identifiant" );
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_ALIAS'							, "Alias" );
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_NAME'							, "Nom" );
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_ORDER'							, "Ordre" );
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_ACTIVATED'						, "Activée" );

// Payment table fields
/**
 * Here 3 absolutes constants wich can be used into other components
 *
 *		- Between external components like 'donate' component and 'payment' component
 *		  The reference to the ID field of 'payment' table
 *
 *		- Between 'payment' component and 'payment_x' component (like payment_sips)
 *		  The reference to the ID field of 'payment_x' table
 *
 *		- And also the method_id field.
 *		  Because an absolute reference to a payment_x_id is the couple (method_id,payment_x_id)
 */
define( 'LANG_ADMIN_COM_PAYMENT_ABS_PAYMENT_ID'							, "ID de paiement" ); 
define( 'LANG_ADMIN_COM_PAYMENT_ABS_PAYM_X_ID'							, "N° de transaction" );
define( 'LANG_ADMIN_COM_PAYMENT_ABS_METH_ID'							, "Méthode" );

define( 'LANG_ADMIN_COM_PAYMENT_ORIGIN'									, "Origine" );


// config.php
define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_TITLE_START'						, "Configuration du système de paiements" );

define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG_FIELD'						, "Le mode \"déboggage\" est : " );
define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG_N'							, "<span style=\"color:green;font-weight:bold;\">INACTIF</span>" );
define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_DEBUG_Y'							, "<span style=\"color:red;font-weight:bold;\">ACTIF</span>" );

define( 'LANG_ADMIN_COM_PAYMENT_CONFIG_GENERIC_METHOD_ACTIVATED_TIPS'	, "(pour afficher les paiements utilisant la méthode \"generic\", le mode \"déboggage\" doit être activé)" );

define('LANG_ADMIN_COM_PAYMENT_CONFIG_PAYMENT_ID_OFFSET_TIPS'			, "Modifiable uniquement avant le premier paiement");
define('LANG_ADMIN_COM_PAYMENT_CONFIG_INVALID_PAYMENT_ID_OFFSET'		, "N° du premier paiement invalide");


// method.php
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_TITLE_START'						, "Liste des méthodes de paiements" );
define( 'LANG_ADMIN_COM_PAYMENT_METHOD_TITLE_UPDATE'					, "Mise à jour de la méthode de paiement" );

define( 'LANG_ADMIN_COM_PAYMENT_METHOD_NO_METHOD_ACTIVATED'				, "Attention : aucune méthode activée !" );

define( 'LANG_ADMIN_COM_PAYMENT_METHOD_GENERIC_METHOD_ACTIVATED_TIPS'	, "Mode \"déboggage\" activé : la méthode \"generic\" est mise à disposition, et permet d'effectuer des tests du module de paiement." );


// list.php
define( 'LANG_ADMIN_COM_PAYMENT_LIST_TITLE_START'						, "Liste des paiements" );

define( 'LANG_ADMIN_COM_PAYMENT_LIST_DELETE_TESTS'						, "Effacer les tests de paiements" );
define( 'LANG_ADMIN_COM_PAYMENT_LIST_DELETE_TESTS_TIPS'					, "<b>Attention !</b> Si des tests de paiements ont été générés par des modules autres que le module de paiements,<br />il est fortement recommandé d'effacer au préalable, les enregistrements correspondants dans ces modules.<br />" );

define( 'LANG_ADMIN_COM_PAYMENT_LIST_MISSING_X_ID_CRITICAL_ERROR'		, "<b>Erreur critique :</b><br />Des références invalides ont été trouvé !<br /><u>Détails :</u><br />Champs : <i>'<b>payment_x_id</b>'</i> invalides,<br />dans la table : <i>'<b>payment</b>'</i>,<br />de la Base de données." );

define( 'LANG_ADMIN_COM_PAYMENT_LIST_GENERIC_METHOD_ACTIVATED_TIPS'		, "Mode \"déboggage\" activé : les paiements utilisant la méthode \"generic\" sont affichés." );



// Usefull for the selectFilter class, to get a filter of validated field
define('LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER'		, "Filtre des paiements" );
define('LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER_ROOT'	, "Tous" );
define('LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER_1'		, "Validés" );
define('LANG_ADMIN_COM_PAYMENT_VALIDATED_FILTER_0'		, "Non validés" );

?>