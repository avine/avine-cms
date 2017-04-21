<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Config
$system_email = '';


// Language
define( 'LANG_CONFIG_SITE_NAME' 		, "Démo : système de gestion du contenu" );
define( 'LANG_CONFIG_META_KEYWORDS' 	, "content managing system, CMS, web agency, webmastering, infographie, developpement internet, web design, web development, site web" );
define( 'LANG_CONFIG_META_DESC' 		, "Système de gestion du contenu" );
define( 'LANG_CONFIG_META_AUTHOR' 		, "Stéphane Francel" );
define( 'LANG_CONFIG_OFFLINE_MESSAGE'	, "Le site est en cours de maintenance.\nNous vous prions de nous excuser pour la gêne occasionnée.\nMerci de repasser plus tard." );



//////////
// Config
$table_suffix = 'config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				site_name					VARCHAR(100)	NOT NULL,

				meta_keywords				TEXT,
				meta_desc					TEXT,
				meta_author					TEXT,

				online						TINYINT(4)		NOT NULL DEFAULT 1,
				offline_message				TINYTEXT,

				http_host					VARCHAR(100),
				no_linked_content_access	TINYINT(4)		NOT NULL DEFAULT 1,

				system_email				VARCHAR(100)	NOT NULL,

				debug						TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( '".LANG_CONFIG_SITE_NAME."', '".LANG_CONFIG_META_KEYWORDS."', '".LANG_CONFIG_META_DESC."', '".LANG_CONFIG_META_AUTHOR."', 1, '".LANG_CONFIG_OFFLINE_MESSAGE."', '', 1, '$system_email', 0 ) ";


db_install::process($table_suffix, $db_create, $db_insert);




?>