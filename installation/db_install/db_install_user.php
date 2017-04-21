<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Config
$admin_username			= 'admin';
$admin_password			= '';
$admin_email			= '';
$time					= time();
$session_maxlifetime	= 60*30; # 30mn

// Langage
define( 'LANG_USER_STATUS_ADMINISTRATOR'	, "Administrateur" );
define( 'LANG_USER_STATUS_WEBMASTER'		, "Webmaster" );
define( 'LANG_USER_STATUS_EDITOR'			, "Editeur" );
define( 'LANG_USER_STATUS_AUTHOR'			, "Auteur" );
define( 'LANG_USER_STATUS_REGISTERED'		, "Utilisateur" );
define( 'LANG_USER_STATUS_PUBLIC'			, "Public" );



////////
// User
$table_suffix = 'user';

/* ---------------------------------
   Required size for password field:
   any $password is encoded by using
   			sha1($password)
   wich is limited at 40 characters
   --------------------------------- */

$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				username			VARCHAR(100)	NOT NULL,
				password			VARCHAR(100)	NOT NULL,
				email				VARCHAR(100)	NOT NULL,

				access_level		TINYINT(4)		NOT NULL DEFAULT 5,
				activated			TINYINT(4)		NOT NULL DEFAULT 0,
				activation_code		VARCHAR(100),

				registration_date	INT(11),
				last_visit			INT(11),

				INDEX (username(10))

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' "; # TODO : password needs to be case sensitive !


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, '$admin_username', '$admin_password', '$admin_email', 1, 1, '', $time, $time ) ";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////////////
// User informations
$table_suffix = 'user_info';

/* ----------------------------------
   Required field size for encryption
   (based on rijndael-256)

	Original size	Encrypted size
	40				96
	100				176
	200				312
   ---------------------------------- */

$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				user_id			INT(11)			NOT NULL PRIMARY KEY,

				gender			TINYINT(4)		DEFAULT NULL,
				age				SMALLINT(3)		DEFAULT NULL,

				last_name		VARCHAR(100)	DEFAULT '',
				first_name		VARCHAR(100)	DEFAULT '',

				title			VARCHAR(100)	DEFAULT '',
				company			VARCHAR(100)	DEFAULT '',

				adress_1		VARCHAR(180)	DEFAULT '',
				adress_2		VARCHAR(180)	DEFAULT '',
				city			VARCHAR(100)	DEFAULT '',
				state			VARCHAR(100)	DEFAULT '',
				country			VARCHAR(100)	DEFAULT '',
				zip				VARCHAR(100)	DEFAULT '',

				phone_1			VARCHAR(100)	DEFAULT '',
				phone_2			VARCHAR(100)	DEFAULT '',
				fax				VARCHAR(100)	DEFAULT '',

				extra_field_1	VARCHAR(320)	DEFAULT NULL,
				extra_field_2	VARCHAR(320)	DEFAULT NULL,
				extra_field_3	VARCHAR(320)	DEFAULT NULL,
				extra_field_4	VARCHAR(320)	DEFAULT NULL,
				extra_field_5	VARCHAR(320)	DEFAULT NULL

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, NULL, NULL, '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL ) ";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////////////////////////////
// User field (from user & user_info table)
$table_suffix = 'user_field';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				field			VARCHAR(20)		NOT NULL PRIMARY KEY,
				activated		TINYINT(4)		NOT NULL,
				required		TINYINT(4)		NOT NULL,
				field_order		TINYINT(4)		NOT NULL

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 'username'		, 1, 1, 1 ),
						( 'password'		, 1, 1, 2 ),
						( 'email'			, 1, 1, 3 ),

						( 'gender'			, 0, 0, 4 ),
						( 'age'				, 0, 0, 7 ),

						( 'last_name'		, 1, 1, 5 ),
						( 'first_name'		, 1, 1, 6 ),

						( 'title'			, 0, 0, 8 ),
						( 'company'			, 0, 0, 9 ),

						( 'adress_1'		, 1, 0, 10 ),
						( 'adress_2'		, 0, 0, 11 ),
						( 'city'			, 1, 0, 12 ),
						( 'state'			, 0, 0, 13 ),
						( 'country'			, 1, 0, 14 ),
						( 'zip'				, 1, 0, 15 ),

						( 'phone_1'			, 1, 0, 16 ),
						( 'phone_2'			, 0, 0, 17 ),
						( 'fax'				, 0, 0, 18 ),

						( 'extra_field_1'	, 0, 0, 19 ),
						( 'extra_field_2'	, 0, 0, 20 ),
						( 'extra_field_3'	, 0, 0, 21 ),
						( 'extra_field_4'	, 0, 0, 22 ),
						( 'extra_field_5'	, 0, 0, 23 ) ";


db_install::process($table_suffix, $db_create, $db_insert);



////////////////////
// User Acces level
$table_suffix = 'user_status';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id		TINYINT(4)	NOT NULL AUTO_INCREMENT PRIMARY KEY,
				status	VARCHAR(50)	NOT NULL,
				comment	TINYTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 1, 'administrator' , '".LANG_USER_STATUS_ADMINISTRATOR."'	),
						( 2, 'webmaster'	 , '".LANG_USER_STATUS_WEBMASTER."'		),
						( 3, 'editor'		 , '".LANG_USER_STATUS_EDITOR."'		),
						( 4, 'author'		 , '".LANG_USER_STATUS_AUTHOR."'		),
						( 5, 'registered'	 , '".LANG_USER_STATUS_REGISTERED."'	),
						( 6, 'public'		 , '".LANG_USER_STATUS_PUBLIC."'		) ";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////
// User config
$table_suffix = 'user_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				registration_silent		TINYINT(4)						NOT NULL	DEFAULT 0,
				allow_duplicate_email	TINYINT(4)						NOT NULL	DEFAULT 0,
				activation_method		ENUM('auto', 'email', 'admin')	NOT NULL	DEFAULT 'auto',

				crypt_user_info			TINYINT(4)						NOT NULL	DEFAULT 0,

				session_maxlifetime		INT(11),
				visit_counter			INT(11)							NOT NULL	DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( 0,0,1, 0, $session_maxlifetime, 0 ) ";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////
// User forget
$table_suffix = 'user_forget';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				user_id			INT(11)			NOT NULL PRIMARY KEY,
				request_code	VARCHAR(100)	NOT NULL,
				request_date	INT(11)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



////////////////
// User session
$table_suffix = 'user_session';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				session_id		VARCHAR(100)	NOT NULL,
				last_activity	INT(11)			NOT NULL,

				backend			TINYINT(4)		NOT NULL	DEFAULT 0,
				user_id			INT(11),

				INDEX (session_id(16))

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);

?>