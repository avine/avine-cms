<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language for payment table 
$generic_name 				= 'Générique (test)';
$sips_name   				= 'Carte de crédit';
$paypal_name 				= 'Paypal';



//////////////////
// Payment_config
$table_suffix = 'payment_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				payment_id_offset	INT(11)			NOT NULL DEFAULT 0,

				debug				TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix VALUES ( ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// Payment_method
$table_suffix = 'payment_method';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				alias			VARCHAR(25)		NOT NULL,

				name			VARCHAR(100)	NOT NULL,

				payment_order	TINYINT(4)		NOT NULL,
				activated		TINYINT(4)		NOT NULL DEFAULT 1

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

				VALUES	( 1,'generic', 	'$generic_name'	, 1,1 ),
						( 2,'sips', 	'$sips_name'	, 2,1 ),
                		( 3,'paypal', 	'$paypal_name'	, 3,0 ) ";


db_install::process($table_suffix, $db_create, $db_insert);



///////////
// Payment
$table_suffix = 'payment';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				method_id		INT(11)			NOT NULL,
				payment_x_id	INT(11)			NOT NULL,

				origin			VARCHAR(20),

				INDEX (payment_x_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";

 
$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



?>