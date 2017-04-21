<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



//////////
// config
$table_suffix = 'rewrite_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				enabled				TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( ) ";


db_install::process($table_suffix, $db_create, $db_insert);



///////////
// Rewrite
$table_suffix = 'rewrite_rules';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id			INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				pos			INT(11)			NOT NULL,

				static		VARCHAR(400)	NOT NULL,
				dynamic		VARCHAR(400)	NOT NULL,

				s1			VARCHAR(400),
				s2			VARCHAR(400),
				s3			VARCHAR(400)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";

/*
 * LONG VERSION : allow you to customize each link
 */
/*
// Note : the regular expressions must be PCRE compatible (the php scripts will use the functions : preg_replace, preg_match, ...)
$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	( NULL,  1, '/file/$1',						'/index.php?file=$1',									'([^&\\\?]+)', NULL, NULL ),

						( NULL,  3, '/content/index/$1/$2.html',	'/index.php?com=content&page=index&section=$1&item=$2',	'([^&\\\?]+)', '([^&\\\?]+)', NULL ),
						( NULL,  5, '/content/index/$1/',			'/index.php?com=content&page=index&section=$1',			'([^&\\\?]+)', NULL, NULL ),

						( NULL,  7, '/user/login_create/',			'/index.php?com=user&page=login_create',				NULL, NULL, NULL ),
						( NULL,  9, '/user/login/',					'/index.php?com=user&page=login',						NULL, NULL, NULL ),
						( NULL, 11, '/user/create/',				'/index.php?com=user&page=create',						NULL, NULL, NULL ),
						( NULL, 13, '/user/modify/',				'/index.php?com=user&page=modify',						NULL, NULL, NULL ),
						( NULL, 15, '/user/forget/',				'/index.php?com=user&page=forget',						NULL, NULL, NULL ),

						( NULL, 17, '/sitemap/index/',				'/index.php?com=sitemap&page=index',					NULL, NULL, NULL ),

						( NULL, 19, '/generic/index/',				'/index.php?com=generic&page=index',					NULL, NULL, NULL ),
						( NULL, 21, '/generic/home/',				'/index.php?com=generic&page=home',						NULL, NULL, NULL ),

						( NULL, 23, '/content/index/',				'/index.php?com=content&page=index',					NULL, NULL, NULL ),
						( NULL, 25, '/content/home/',				'/index.php?com=content&page=home',						NULL, NULL, NULL ),

						( NULL, 27, '/newsletter/subscribe/',		'/index.php?com=newsletter&page=subscribe',				NULL, NULL, NULL ),
						( NULL, 29, '/newsletter/unsubscribe/',		'/index.php?com=newsletter&page=unsubscribe',			NULL, NULL, NULL ),

						( NULL, 31, '/contact/index/',				'/index.php?com=contact&page=index',					NULL, NULL, NULL ),

						( NULL, 33, '/search/index/',				'/index.php?com=search&page=index',						NULL, NULL, NULL ),

						( NULL, 35, '/donate/index/',				'/index.php?com=donate&page=index',						NULL, NULL, NULL ),
						( NULL, 37, '/donate/checkout/',			'/index.php?com=donate&page=checkout',					NULL, NULL, NULL ),
						( NULL, 39, '/donate/thankyou/',			'/index.php?com=donate&page=thankyou',					NULL, NULL, NULL ),
						( NULL, 41, '/donate/list/',				'/index.php?com=donate&page=list',						NULL, NULL, NULL ),

						( NULL, 43, '/payment/index/',				'/index.php?com=payment&page=index',					NULL, NULL, NULL ),
						( NULL, 45, '/payment/request/',			'/index.php?com=payment&page=request',					NULL, NULL, NULL ),
						( NULL, 47, '/payment/response/',			'/index.php?com=payment&page=response',					NULL, NULL, NULL ),
						( NULL, 49, '/payment/list/',				'/index.php?com=payment&page=list',						NULL, NULL, NULL ),

						( NULL, 51, '/sips/request/',				'/index.php?com=sips&page=request',						NULL, NULL, NULL ),
						( NULL, 53, '/sips/response/',				'/index.php?com=sips&page=response',					NULL, NULL, NULL ),

						( NULL, 55, '/schedule/index/',				'/index.php?com=schedule&page=index',					NULL, NULL, NULL ) ";
*/

/*
 * SHORT VERSION
 */

// Note : the regular expressions must be PCRE compatible (the php scripts will use the functions : preg_replace, preg_match, ...)
$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

				VALUES	( NULL,  1, '/file/$1',						'/index.php?file=$1',									'([^&\\\?]+)',	NULL, NULL ),

						( NULL,  3, '/content/index/$1/$2.html',	'/index.php?com=content&page=index&section=$1&item=$2',	'([^&\\\?]+)', '([^&\\\?/]+)', NULL ),
						( NULL,  5, '/content/index/$1/',			'/index.php?com=content&page=index&section=$1',			'([^&\\\?]+)', NULL, NULL ),
						( NULL,  7, '/content/index/',				'/index.php?com=content&page=index',					NULL, NULL, NULL ),

						( NULL,  9, '/$1/$2/',						'/index.php?com=$1&page=$2',							'([^&\\\?/]+)', '([^&\\\?/]+)', NULL ) ";


db_install::process($table_suffix, $db_create, $db_insert);


?>