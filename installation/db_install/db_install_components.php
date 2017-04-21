<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Langage
define( 'LANG_COMPONENTS_USER_LOGIN' 				, "Compte : S\'identifier" );
define( 'LANG_COMPONENTS_USER_CREATE' 				, "Compte : S\'inscrire" );
define( 'LANG_COMPONENTS_USER_LOGIN_CREATE'			, "Compte : S\'identifier / S\'inscrire" );
define( 'LANG_COMPONENTS_USER_ACCOUNT' 				, "Compte : Informations" );
define( 'LANG_COMPONENTS_USER_FORGET' 				, "Compte : Nom d\'utilisateur ou mot de passe oublié ?" );

define( 'LANG_COMPONENTS_SITEMAP_INDEX' 			, "Plan du site" );

define( 'LANG_COMPONENTS_GENERIC_INDEX' 			, "Composant générique (test)" );
define( 'LANG_COMPONENTS_GENERIC_HOME' 				, "Composant générique (test) : Page d\'accueil" );

define( 'LANG_COMPONENTS_CONTENT_INDEX' 			, "Contenus" );
define( 'LANG_COMPONENTS_CONTENT_HOME' 				, "Contenus : Page d\'accueil" );
define( 'LANG_COMPONENTS_CONTENT_LAST' 				, "Contenus : Nouveautés" );

define( 'LANG_COMPONENTS_NEWSLETTER_SUBSCRIBE' 		, "Newsletter : S\'abonner" );
define( 'LANG_COMPONENTS_NEWSLETTER_UNSUBSCRIBE' 	, "Newsletter : Se désabonner" );
define( 'LANG_COMPONENTS_NEWSLETTER_ARCHIVED' 		, "Newsletters archivées" );

define( 'LANG_COMPONENTS_CONTACT_INDEX' 			, "Nous contacter" );

define( 'LANG_COMPONENTS_SEARCH_INDEX' 				, "Moteur de recherche" );

define( 'LANG_COMPONENTS_DONATE_INDEX' 				, "Don : Formulaire" );
define( 'LANG_COMPONENTS_DONATE_CHECKOUT' 			, "Don : Règlement" );
define( 'LANG_COMPONENTS_DONATE_THANKYOU' 			, "Don : Résultat de paiement" );
define( 'LANG_COMPONENTS_DONATE_LIST' 				, "Don : Liste des dons effectués" );

define( 'LANG_COMPONENTS_PAYMENT_INDEX_REQUEST' 	, "Paiement : Formulaire (mode deboggage uniquement)" );
define( 'LANG_COMPONENTS_PAYMENT_INDEX_RESPONSE'	, "Paiement : Résultat" );

define( 'LANG_COMPONENTS_SIPS_REQUEST' 				, "SIPS : Formulaire (mode deboggage uniquement)" );
define( 'LANG_COMPONENTS_SIPS_RESPONSE' 			, "SIPS : Résultat de paiement" );

define( 'LANG_COMPONENTS_WCAL_INDEX' 				, "Agenda hebdomadaire des cours" );
define( 'LANG_COMPONENTS_WCAL_CATEGORY' 			, "Agenda thématique des cours" );
define( 'LANG_COMPONENTS_WCAL_DEDICATE' 			, "Dédicacer des cours" );
define( 'LANG_COMPONENTS_WCAL_DEDICATE_SUMMARY' 	, "Récapitulatif des dédicaces" );
define( 'LANG_COMPONENTS_WCAL_LIST' 				, "Liste des dédicaces" );

define( 'LANG_COMPONENTS_ADDRBOOK_INDEX' 			, "Annuaire" );

define( 'LANG_COMPONENTS_SCHEDULE_INDEX' 			, "Horaires" );

define( 'LANG_COMPONENTS_CONTEST_REGISTER'			, "Prix : Inscription" );
define( 'LANG_COMPONENTS_CONTEST_PROJECT' 			, "Prix : Description du projet" );
define( 'LANG_COMPONENTS_CONTEST_RESOURCE' 			, "Prix : Travaux d\'élèves" );
define( 'LANG_COMPONENTS_CONTEST_SUMMARY' 			, "Prix : Validation" );
define( 'LANG_COMPONENTS_CONTEST_JURY'	 			, "Prix : Le Jury" );
define( 'LANG_COMPONENTS_CONTEST_WINNER' 			, "Prix : Les lauréats" );



//////////////
// Components
$table_suffix = 'components';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				com				VARCHAR(100)	NOT NULL,
				page			VARCHAR(100)	NOT NULL,
				title			TINYTEXT,

				access_level	TINYINT(4)		NOT NULL DEFAULT 6,
				published		TINYINT(4)		NOT NULL DEFAULT 1,

				params			TEXT,

				INDEX com_page (com, page)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

				VALUES	(   1, 'user', 'login'				, '".LANG_COMPONENTS_USER_LOGIN."' 					, 6, 1, ''),
						(NULL, 'user', 'create'				, '".LANG_COMPONENTS_USER_CREATE."' 				, 6, 1, ''),
						(NULL, 'user', 'login_create'		, '".LANG_COMPONENTS_USER_LOGIN_CREATE."' 			, 6, 1, ''),
						(NULL, 'user', 'modify'				, '".LANG_COMPONENTS_USER_ACCOUNT."' 				, 5, 1, ''),
						(NULL, 'user', 'forget'				, '".LANG_COMPONENTS_USER_FORGET."' 				, 6, 1, ''),

						(NULL, 'menu', 'sitemap'			, '".LANG_COMPONENTS_SITEMAP_INDEX."' 				, 6, 1, ''),

						(NULL, 'generic', 'index' 			, '".LANG_COMPONENTS_GENERIC_INDEX."'				, 6, 1, ''),
						(NULL, 'generic', 'home'  			, '".LANG_COMPONENTS_GENERIC_HOME."'				, 6, 1, ''),

						(NULL, 'content', 'index' 			, '".LANG_COMPONENTS_CONTENT_INDEX."'				, 6, 1, ''),
						(NULL, 'content', 'home'  			, '".LANG_COMPONENTS_CONTENT_HOME."'				, 6, 1, ''),
						(NULL, 'content', 'last'  			, '".LANG_COMPONENTS_CONTENT_LAST."'				, 6, 1, ''),

						(NULL, 'newsletter', 'subscribe'  	, '".LANG_COMPONENTS_NEWSLETTER_SUBSCRIBE."'		, 6, 1, ''),
						(NULL, 'newsletter', 'unsubscribe'  , '".LANG_COMPONENTS_NEWSLETTER_UNSUBSCRIBE."'		, 6, 1, ''),
						(NULL, 'newsletter', 'archived' 	, '".LANG_COMPONENTS_NEWSLETTER_ARCHIVED."'			, 6, 1, ''),

						(NULL, 'contact', 'index'  			, '".LANG_COMPONENTS_CONTACT_INDEX."'				, 6, 1, ''),

						(NULL, 'search', 'index'  			, '".LANG_COMPONENTS_SEARCH_INDEX."'				, 6, 1, ''),

						(NULL, 'donate', 'index'			, '".LANG_COMPONENTS_DONATE_INDEX."'				, 6, 1, ''),
						(NULL, 'donate', 'checkout'			, '".LANG_COMPONENTS_DONATE_CHECKOUT."'				, 6, 1, ''),
						(NULL, 'donate', 'thankyou'			, '".LANG_COMPONENTS_DONATE_THANKYOU."'				, 6, 1, ''),
						(NULL, 'donate', 'list'				, '".LANG_COMPONENTS_DONATE_LIST."'					, 5, 1, ''),

						(NULL, 'payment', 'request'			, '".LANG_COMPONENTS_PAYMENT_INDEX_REQUEST."'		, 6, 1, ''),
						(NULL, 'payment', 'response'		, '".LANG_COMPONENTS_PAYMENT_INDEX_RESPONSE."'		, 6, 1, ''),

						(NULL, 'sips', 'request'			, '".LANG_COMPONENTS_SIPS_REQUEST."'				, 6, 1, ''),
						(NULL, 'sips', 'response'			, '".LANG_COMPONENTS_SIPS_RESPONSE."'				, 6, 1, ''),

						(NULL, 'wcal', 'index'				, '".LANG_COMPONENTS_WCAL_INDEX."'					, 6, 1, ''),
						(NULL, 'wcal', 'category'			, '".LANG_COMPONENTS_WCAL_CATEGORY."'				, 6, 1, ''),
						(NULL, 'wcal', 'dedicate'			, '".LANG_COMPONENTS_WCAL_DEDICATE."'				, 6, 1, ''),
						(NULL, 'wcal', 'summary'			, '".LANG_COMPONENTS_WCAL_DEDICATE_SUMMARY."'		, 6, 1, ''),
						(NULL, 'wcal', 'list'				, '".LANG_COMPONENTS_WCAL_LIST."'					, 6, 1, ''),

						(NULL, 'addrbook', 'index'			, '".LANG_COMPONENTS_ADDRBOOK_INDEX."'				, 6, 1, ''),

						(NULL, 'schedule', 'index'			, '".LANG_COMPONENTS_SCHEDULE_INDEX."'				, 6, 1, ''),

						(NULL, 'contest', 'register'  		, '".LANG_COMPONENTS_CONTEST_REGISTER."'			, 6, 1, ''),
						(NULL, 'contest', 'project'  		, '".LANG_COMPONENTS_CONTEST_PROJECT."'				, 5, 1, ''),
						(NULL, 'contest', 'resource'  		, '".LANG_COMPONENTS_CONTEST_RESOURCE."'			, 5, 1, ''),
						(NULL, 'contest', 'summary'  		, '".LANG_COMPONENTS_CONTEST_SUMMARY."'				, 5, 1, ''),
						(NULL, 'contest', 'jury'  			, '".LANG_COMPONENTS_CONTEST_JURY."'				, 6, 1, ''),
						(NULL, 'contest', 'winner'  		, '".LANG_COMPONENTS_CONTEST_WINNER."'				, 6, 1, '') ";


db_install::process($table_suffix, $db_create, $db_insert);


?>