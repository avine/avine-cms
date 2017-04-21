<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language
define( 'LANG_MENU_MAIN'			, "Menu principal" );
define( 'LANG_MENU_NEWS'			, "L\'actualité" );

define( 'LANG_MENU_TYPE_FILE'		, "Lien vers un contenu statique (ftp)" );
define( 'LANG_MENU_TYPE_ELEM'		, "Lien vers un contenu dynamique : {element} (database)" );
define( 'LANG_MENU_TYPE_NODE'		, "Lien vers un contenu dynamique : {node} (database)" );
define( 'LANG_MENU_TYPE_COM'		, "Lien vers un composant" );
define( 'LANG_MENU_TYPE_SEP'		, "Séparateur" );
define( 'LANG_MENU_TYPE_URL'		, "Lien vers une url spécifiée" );

define( 'LANG_LINK_HOME'			, "Accueil" );
define( 'LANG_LINK_SAMPLES'			, "Contenus" );
define( 'LANG_LINK_LOGIN'			, "Login" );
define( 'LANG_LINK_SITEMAP'			, "Plan du site" );
define( 'LANG_LINK_PRIVATE'			, "Secret" );

define( 'LANG_LINK_NEWS1'			, "Article 1" );
define( 'LANG_LINK_NEWS2'			, "Article 2" );
define( 'LANG_LINK_NEWS3'			, "Article 3" );



////////
// Menu
$table_suffix = 'menu';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id		INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				name	VARCHAR(100)	NOT NULL,
				comment	TINYTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1   , 'mainmenu', '".LANG_MENU_MAIN."' ),
                		( NULL, 'newsmenu', '".LANG_MENU_NEWS."' ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// Menu Link type
$table_suffix = 'menu_link_type';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id		TINYINT(4)		NOT NULL AUTO_INCREMENT PRIMARY KEY,

				name	VARCHAR(100)	NOT NULL,
				comment	TINYTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, 'file'				, '".LANG_MENU_TYPE_FILE."' ),
						( 2, 'content_element'	, '".LANG_MENU_TYPE_ELEM."' ),
						( 3, 'content_node'		, '".LANG_MENU_TYPE_NODE."'  ),
						( 5, 'component'		, '".LANG_MENU_TYPE_COM."'  ),
						( 6, 'separator'		, '".LANG_MENU_TYPE_SEP."'  ),
						( 7, 'url' 				, '".LANG_MENU_TYPE_URL."'  ) ";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////
// Menu Link
$table_suffix = 'menu_link';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name			VARCHAR(100)	NOT NULL,
				href			TEXT			NOT NULL,
				unique_id		TINYINT(4)		NOT NULL DEFAULT 0,
				link_type_id	TINYINT(4)		NOT NULL,	

				menu_id			INT(11)			NOT NULL,
				parent_id		INT(11)			NOT NULL DEFAULT 0,
				link_order		INT(11)			NOT NULL,

				access_level	TINYINT(4)		NOT NULL DEFAULT 6,
				published		TINYINT(4)		NOT NULL DEFAULT 1,

				template_id		INT(11),
				params			TEXT,

				INDEX link (link_type_id, href(20)),
				INDEX menu (menu_id, parent_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";



$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, '".LANG_LINK_HOME."'	, 'file=home.php'				, 0, 1, 	1, 0, 1, 	6, 1, 	NULL, '' ),
						( 2, '".LANG_LINK_SAMPLES."', 'file=samples.html'			, 0, 1, 	1, 0, 2, 	6, 1, 	NULL, '' ),
						( 3, '".LANG_LINK_LOGIN."'	, 'com=user&amp;page=login'		, 0, 5, 	1, 0, 3, 	6, 1, 	NULL, '' ),
						( 4, '".LANG_LINK_SITEMAP."', 'com=menu&amp;page=sitemap'	, 0, 5, 	1, 0, 4, 	6, 1, 	NULL, '' ),
						( 5, '".LANG_LINK_PRIVATE."', 'file=secret.php'				, 0, 1, 	1, 0, 5, 	5, 1, 	NULL, '' ),

						( 6, '".LANG_LINK_NEWS1."'	, 'file=news/news1.php'			, 0, 1, 	2, 0, 1, 	6, 1, 	NULL, '' ),
						( 7, '".LANG_LINK_NEWS2."'	, 'file=news/news2.php'			, 0, 1, 	2, 0, 2, 	6, 1, 	NULL, '' ),
						( 8, '".LANG_LINK_NEWS3."'	, 'file=news/news3.php'			, 0, 1, 	2, 0, 3, 	6, 1, 	NULL, '' ) ";


db_install::process($table_suffix, $db_create, $db_insert);



////////////////
// Menu sitemap
$table_suffix = 'menu_sitemap';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id			INT(11)												NOT NULL AUTO_INCREMENT PRIMARY KEY,

				info_type	ENUM('menu_order', 'exclude_menu', 'exclude_link')	NOT NULL,
				info_id		INT(11)												NOT NULL

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



?>