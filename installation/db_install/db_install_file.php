<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



////////
// File
$table_suffix = 'file';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				path			VARCHAR(300)	NOT NULL PRIMARY KEY,
				title			TINYTEXT,
				access_level	TINYINT(4)		NOT NULL DEFAULT 6,
				published		TINYINT(4)		NOT NULL DEFAULT 1

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



?>