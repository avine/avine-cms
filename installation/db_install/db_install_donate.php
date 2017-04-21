<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



/////////////////
// Donate_config
$table_suffix = 'donate_config';

# note : 978=euro
$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				currency_code		INT(11)			NOT NULL DEFAULT 978,
				amount_min			INT(11)			NOT NULL DEFAULT 0,

				registration_silent	TINYINT(4)		NOT NULL DEFAULT 1,
				accountant_email	VARCHAR(100),

				invoice_num			INT(11)			NOT NULL DEFAULT 1,
				recipient_name		VARCHAR(100),
				recipient_adress	VARCHAR(300)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////////
// Donate_designation
$table_suffix = 'donate_designation';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				title			VARCHAR(100)	NOT NULL,

				comment			TEXT,
				link			TEXT,
				image			TEXT,

				amount			INT(11),

				design_order	INT(11)			NOT NULL,
				published		TINYINT(4)		NOT NULL DEFAULT 1

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, '-default-', '','','', NULL, 1,1 ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////
// Donate
$table_suffix = 'donate';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				recording_date		INT(11)			NOT NULL,
				form_passed			TINYINT(4)		NOT NULL DEFAULT 0,

				contributor			VARCHAR(500),

				user_id				INT(11),
				payment_id			INT(11)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = '';


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// Donate_details
$table_suffix = 'donate_details';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				designation_id		INT(11)			NOT NULL,
				amount				INT(11)			NOT NULL,
				currency_code		INT(11)			NOT NULL,

				donate_id			INT(11),

				INDEX (donate_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = '';


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// Donate_invoice
$table_suffix = 'donate_invoice';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				donate_id			INT(11),
				filename			VARCHAR(50)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = '';


db_install::process($table_suffix, $db_create, $db_insert);



?>