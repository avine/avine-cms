<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Config
$default_period_title = 'Agenda général';



///////////////
// wcal_config

$table_suffix	= 'wcal_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				date_max				INT(11),
				wday_sunday_7			TINYINT(4)		NOT NULL DEFAULT 1,

				dedicate_amounts		VARCHAR(200)	NOT NULL,
				handle_designation_id	INT(11)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( NULL, 1, '5000;2500', NULL ) ";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////////
// wcal_category
$table_suffix = 'wcal_category';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,
				author				VARCHAR(100),
				comment				MEDIUMTEXT,

				color				VARCHAR(12),

				node_id				INT(11),

				category_order		INT(11)			NOT NULL DEFAULT 999

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////
// wcal_period
$table_suffix = 'wcal_period';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,

				wid_begin			INT(11),
				wid_end				INT(11)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, '$default_period_title', NULL,NULL ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////
// wcal_event
$table_suffix = 'wcal_event';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				period_id			INT(11)			NOT NULL,
				category_id			INT(11)			NOT NULL,

				wday				TINYINT(4),

				time_begin			VARCHAR(4),
				time_end			VARCHAR(4),

				INDEX (period_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////////
// wcal_dedicate_type
$table_suffix = 'wcal_dedicate_type';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,
				sample				MEDIUMTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////////
// wcal_dedicate
$table_suffix = 'wcal_dedicate';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				recording_date		INT(11)			NOT NULL,

				event_date			INT(11),
				type_id				INT(11),
				comment				MEDIUMTEXT,

				donate_id			INT(11),
				payment_status		INT(11),

				INDEX (payment_status)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////////////////
// wcal_dedicate_details
$table_suffix = 'wcal_dedicate_details';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				dedicate_id			INT(11)			NOT NULL,

				node_id				INT(11)			NOT NULL,
				elm_date_creation	INT(11)			NOT NULL,

				INDEX dedicate_elm (dedicate_id, elm_date_creation)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



?>