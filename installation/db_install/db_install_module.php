<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



///////////////
// Module list
$table_suffix = 'module_list';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				file		VARCHAR(100)	NOT NULL PRIMARY KEY,
				comment		TINYTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = '';


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////
// Module position
$table_suffix = 'module_pos';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				pos			VARCHAR(50)		NOT NULL PRIMARY KEY,
				comment		TINYTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 'navbar'		,	'Navigation horizontale' ),
                		( 'slideshow'	,	'Diaporama' ),
						( 'top'			,	'Entête de page' ),
						( 'left'		,	'Colonne de gauche' ),
						( 'right'		,	'Colonne de droite' ),
						( 'bottom'		,	'Pied de page' ),
						( 'search'		,	'Moteur de recherche' ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////
// Module
$table_suffix = 'module';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name			VARCHAR(100)	NOT NULL,
				show_name		TINYINT(4)		NOT NULL DEFAULT 1,

				mod_file		VARCHAR(100)	NOT NULL,
				mod_pos			VARCHAR(50)		NOT NULL,

				mod_order		INT(11)			NOT NULL,
				access_level	TINYINT(4)		NOT NULL DEFAULT 6,
				published		TINYINT(4)		NOT NULL DEFAULT 1,

				html_pos		VARCHAR(50),
				params			TEXT,

				comment			TINYTEXT,
				
				INDEX (mod_pos(10))

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";

	   	 
$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

				VALUES	( 1, 'Menu principal'	, 1, 'menu_mainmenu.php', 'navbar'		, 1, 6, 1, '', '', 'Affichage du menu' ),
						( 2, 'Actualités'		, 1, 'menu_newsmenu.php', 'left'		, 1, 6, 1, '', '', 'Affichage du menu' ),
						( 3, 'Rechercher'		, 0, 'mod_search.php'	, 'search'		, 1, 6, 1, '', '', 'Moteur de recherche' ),
						( 4, 'Identification'	, 1, 'mod_login.php'	, 'right'		, 1, 6, 1, '', '', 'Module d\'identification' ),
						( 5, 'Newsletter'		, 1, 'mod_newsletter.php', 'right'		, 1, 6, 1, '', '', 'S\'abonner à la Newsletter' ),
						( 6, 'Slideshow'		, 0, 'mod_slideshow.php', 'slideshow'	, 1, 6, 1, '', '', 'Diaporama' ) ";
 

db_install::process($table_suffix, $db_create, $db_insert);



////////////////
// Module_xhref
$table_suffix = 'module_xhref';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id			INT(11)		NOT NULL AUTO_INCREMENT PRIMARY KEY,

				mod_id		INT(11)		NOT NULL,

				link_href	TEXT		NOT NULL,
				link_id		INT(11)		NOT NULL,

				INDEX (link_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1,	2, 'file=news/news1.php',		6 ),
						( NULL, 2, 'file=news/news2.php',		7 ),
						( NULL, 2, 'file=news/news3.php',		8 ),

						( NULL, 2, 'file=home.php',				1 ),

						( NULL, 4, 'file=home.php',				1 ),
						( NULL, 5, 'file=home.php',				1 ),
						( NULL, 6, 'file=home.php',				1 ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// Module_default
$table_suffix = 'module_default';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				mod_id		INT(11)		NOT NULL PRIMARY KEY

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1 ),
                		( 3 ) ";


db_install::process($table_suffix, $db_create, $db_insert);


?>