<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



///////////
// Contact
$table_suffix = 'contact';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				user_id			INT(11)			NOT NULL PRIMARY KEY,

				contact_order	INT(11)			NOT NULL,

				title			VARCHAR(100)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, 1, NULL ) ";


db_install::process($table_suffix, $db_create, $db_insert);



?>