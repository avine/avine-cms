<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Default values for sips method
$sips_merchant_country 		= 'fr';
$sips_capture_mode 			= 'AUTHOR_CAPTURE';
$sips_currency_code_list 	= '978,840,826';					# 978=Euro, 840=Dollar, 826=Livre Sterling
$sips_payment_means 		= 'CB,1,VISA,1,MASTERCARD,1';
$sips_block_order 			= '1,2,3,4,5,6,7,8,9';
$sips_language 				= 'fr';



///////////////////////
// Payment_sips_config
$table_suffix = 'payment_sips_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				cgi_bin_path			VARCHAR(400)	NOT NULL DEFAULT '',
				parmcom_bank_name		VARCHAR(30)		NOT NULL DEFAULT '',

				merchant_id				VARCHAR(15)		NOT NULL DEFAULT '',
				merchant_country		VARCHAR(2)		NOT NULL DEFAULT '".$sips_merchant_country."',

				transaction_id_offset	INT(11)			NOT NULL DEFAULT 0,
				capture_mode			VARCHAR(20)		NOT NULL DEFAULT '".$sips_capture_mode."',
				capture_day				INT(4)			NOT NULL DEFAULT 0,

				currency_code_list		VARCHAR(100)	NOT NULL DEFAULT '".$sips_currency_code_list."',
				payment_means			VARCHAR(128)	NOT NULL DEFAULT '".$sips_payment_means."',
				block_order				VARCHAR(32)		NOT NULL DEFAULT '".$sips_block_order."',
				header_flag				VARCHAR(3)		NOT NULL DEFAULT 'yes',
				language				VARCHAR(2)		NOT NULL DEFAULT '".$sips_language."'

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix VALUES ( ) ";


db_install::process($table_suffix, $db_create, $db_insert);



////////////////
// Payment_sips
$table_suffix = 'payment_sips';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				transmission_date	INT(11)			NOT NULL,

				capture_mode		VARCHAR(25)		NOT NULL,
				capture_day			INT(4)			NOT NULL,

				amount				INT(11)			NOT NULL,
				currency_code		INT(11)			NOT NULL,

				card_number			VARCHAR(25),
				payment_means		VARCHAR(25),

				payment_date		INT(11),
				validated			TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = '';


db_install::process($table_suffix, $db_create, $db_insert);



?>