<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



////////////
// Resource
$table_suffix = 'resource_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				thumb_key		ENUM('width', 'height', 'percent')	NOT NULL	DEFAULT 'width',
				thumb_value		INT(11)								NOT NULL	DEFAULT 90,
				thumb_quality	TINYINT(4)							NOT NULL	DEFAULT 85

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	() ";


db_install::process($table_suffix, $db_create, $db_insert);




?>