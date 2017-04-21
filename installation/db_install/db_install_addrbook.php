<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



////////////
// addrbook
$table_suffix = 'addrbook';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				name				VARCHAR(150)	NOT NULL,

				address				VARCHAR(300),
				city				VARCHAR(100),
				state				VARCHAR(100),
				country				VARCHAR(100),
				zip					VARCHAR(100),

				phone				VARCHAR(100),
				fax					VARCHAR(100),

				email				VARCHAR(100),
				web					VARCHAR(300),

				comment				TEXT,

				search				TEXT,

				FULLTEXT (search)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////
// addrbook_filter
$table_suffix = 'addrbook_filter';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				id_alias			VARCHAR(50)		NOT NULL,
				name				VARCHAR(150)	NOT NULL,

				published			TINYINT(4)		NOT NULL DEFAULT 0,
				filter_order		INT(11)			NOT NULL DEFAULT 999

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////////////
// addrbook_filter_option
$table_suffix = 'addrbook_filter_option';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				filter_id			INT(11)			NOT NULL,

				name				VARCHAR(150)	NOT NULL

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////////////
// addrbook_filter_search
$table_suffix = 'addrbook_filter_search';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				addrbook_id			INT(11)			NOT NULL,
				option_id			INT(11)			NOT NULL,

				PRIMARY KEY (addrbook_id, option_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



?>