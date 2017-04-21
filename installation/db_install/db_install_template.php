<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



////////////
// Template
$table_suffix = 'template';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id		INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name	VARCHAR(100)	NOT NULL,
				current	TINYINT(4)		NOT NULL DEFAULT 0,
				comment	TINYTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, 'default', 1, '' ) ";



db_install::process($table_suffix, $db_create, $db_insert);

?>