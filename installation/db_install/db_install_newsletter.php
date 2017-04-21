<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



/////////////////////
// newsletter_config
$table_suffix = 'newsletter_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				return_path		VARCHAR(100),
				reply_to		VARCHAR(100),

				batch_size		INT(11) NOT NULL DEFAULT 50,
				refresh_time	INT(11) NOT NULL DEFAULT 20

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////
// newsletter_tmpl
$table_suffix = 'newsletter_tmpl';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name			VARCHAR(100)	NOT NULL,

				header			MEDIUMTEXT,
				footer			MEDIUMTEXT,

				item1			TEXT,
				item2			TEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////
// newsletter
$table_suffix = 'newsletter';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				tmpl_id			INT(11)			NOT NULL,

				subject			VARCHAR(200)	NOT NULL,
				message			MEDIUMTEXT		NOT NULL,

				sender			VARCHAR(100)	NOT NULL,
				reply_to		VARCHAR(100)	NOT NULL,

				date_creation	INT(11)			NOT NULL

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////
// newsletter_send
$table_suffix = 'newsletter_send';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				newsletter_id	INT(11)			NOT NULL,

				date_begin		INT(11),
				date_end		INT(11),

				sent_count		INT(11)			NOT NULL DEFAULT 0,
				hits			INT(11)			NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////////////////
// newsletter_subscriber
$table_suffix = 'newsletter_subscriber';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				email			VARCHAR(100),
				user_id			INT(11),

				activated		TINYINT(4)		NOT NULL DEFAULT 0,
				request_code	VARCHAR(100),

				INDEX (email(10)),
				INDEX (user_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



?>