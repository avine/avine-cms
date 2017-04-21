<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



//////////////////
// contest_config
$table_suffix = 'contest_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				year					INT(11),
				deadline				INT(11),

				resource_path			VARCHAR(400),

				jury_password			VARCHAR(100)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( NULL, NULL, NULL, NULL ) ";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////
// contest_project
$table_suffix = 'contest_project';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id						INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id					INT(11)			NOT NULL,
				config_year				INT(11)			NOT NULL,

				compagny				VARCHAR(400)	NOT NULL,
				address					VARCHAR(400)	NOT NULL,

				leaders					VARCHAR(400)	NOT NULL,
				contributors			VARCHAR(400)	NOT NULL,

				title					VARCHAR(100)	NOT NULL,
				year					INT(11)			NOT NULL,

				user_comment			TEXT,
				user_validation			TINYINT(4)		NOT NULL DEFAULT 0,

				all_resource_provided	TINYINT(4),
				missing_resource_list	TEXT,

				admin_intro				MEDIUMTEXT,
				admin_main				MEDIUMTEXT,
				admin_validation		TINYINT(4)		NOT NULL DEFAULT 0,

				project_order			INT(11)			NOT NULL DEFAULT 999

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



////////////////////
// contest_resource
$table_suffix = 'contest_resource';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id						INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				project_id				INT(11)			NOT NULL,
				file_name				VARCHAR(400)	NOT NULL,

				code					VARCHAR(100)	NOT NULL,

				title					VARCHAR(100)	NOT NULL,
				comment					TEXT,

				verified				TINYINT(4)		NOT NULL DEFAULT 0,
				published				TINYINT(4)		NOT NULL DEFAULT 0,

				resource_order			INT(11)			NOT NULL DEFAULT 999

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// contest_winner
$table_suffix = 'contest_winner';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				project_id				INT(11)			NOT NULL PRIMARY KEY,
				winner_order			INT(11)			NOT NULL DEFAULT 999

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);


?>