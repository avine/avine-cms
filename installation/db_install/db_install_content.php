<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/*
 * The 'contents' component is using the 'generic' component engine (for details see db_install_generic.php)
 */


//////////////////////////////////
// Component setting (first part)

// Component (middle-) prefix name
define( 'COM_CONTENT_TABLE_PREFIX'	, 'content_' );

$com_node_name 		= 'section';
$com_element_name 	= 'item';

// End of setting (first part)
//////////////////////////////



/* ------
   Global
   ------ */

//////////
// config
$table_suffix = COM_CONTENT_TABLE_PREFIX.'config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				com_node_name		TINYTEXT		NOT NULL,
				com_element_name	TINYTEXT		NOT NULL,

				elements_per_step	TINYINT(4)		NOT NULL DEFAULT 10,
				elements_per_row	TINYINT(4)		NOT NULL DEFAULT 1,
				elements_wrapper	TINYINT(4)		NOT NULL DEFAULT 1,

				subnodes_per_row	TINYINT(4)		NOT NULL DEFAULT 2,
				subnodes_wrapper	TINYINT(4)		NOT NULL DEFAULT 1,
				subnodes_ontop		TINYINT(4)		NOT NULL DEFAULT 0,

				show_date_creation	TINYINT(4)		NOT NULL DEFAULT 0,
				show_date_modified	TINYINT(4)		NOT NULL DEFAULT 1,
				show_author_id		TINYINT(4)		NOT NULL DEFAULT 0,
				show_hits			TINYINT(4)		NOT NULL DEFAULT 1,

				selector_node		TINYINT(4)		NOT NULL DEFAULT 1,
				selector_node_rel	TINYINT(4)		NOT NULL DEFAULT 0,
				selector_archive	TINYINT(4)		NOT NULL DEFAULT 1,

				debug				TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                ( com_node_name, com_element_name ) VALUES ( '$com_node_name', '$com_element_name' ) ";


db_install::process($table_suffix, $db_create, $db_insert);



////////
// node
$table_suffix = COM_CONTENT_TABLE_PREFIX.'node';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				id_alias			VARCHAR(50)		NOT NULL,

				parent_id			INT(11)			NOT NULL,
				level				TINYINT(4)		NOT NULL DEFAULT 0,

				access_level		TINYINT(4)		NOT NULL DEFAULT 6,
				published			TINYINT(4)		NOT NULL DEFAULT 1,

				list_order			INT(11)			NOT NULL,

				show_date_creation	TINYINT(4)		NOT NULL DEFAULT 0,
				show_date_modified	TINYINT(4)		NOT NULL DEFAULT 0,
				show_author_id		TINYINT(4)		NOT NULL DEFAULT 0,
				show_hits			TINYINT(4)		NOT NULL DEFAULT 0,

				archived			TINYINT(4)		NOT NULL DEFAULT 0,

				INDEX parent_id_alias (parent_id, id_alias(10))

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////
// element
$table_suffix = COM_CONTENT_TABLE_PREFIX.'element';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				id_alias			VARCHAR(50)		NOT NULL,

				node_id				INT(11)			NOT NULL,

				access_level		TINYINT(4)		NOT NULL DEFAULT 6,
				published			TINYINT(4)		NOT NULL DEFAULT 1,

				list_order			INT(11)			NOT NULL,

				date_creation		INT(11)			NOT NULL,
				date_modified		INT(11)			NOT NULL,

				date_online			INT(11),
				date_offline		INT(11),

				author_id			INT(11)			NOT NULL,

				meta_key			TEXT,
				meta_desc			TEXT,

				hits				INT(11),

				show_date_creation	TINYINT(4)	NOT NULL DEFAULT 0,
				show_date_modified	TINYINT(4)	NOT NULL DEFAULT 0,
				show_author_id		TINYINT(4)	NOT NULL DEFAULT 0,
				show_hits			TINYINT(4)	NOT NULL DEFAULT 0,

				archived			TINYINT(4)	NOT NULL DEFAULT 0,

				INDEX node_id_alias (node_id, id_alias(10))

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



////////////
// home_nde
$table_suffix = COM_CONTENT_TABLE_PREFIX.'home_nde';

/*
  TODO - Add :
				elm_per_row			TINYINT(4)		NOT NULL DEFAULT 1,
				elm_wrapper			TINYINT(4)		NOT NULL DEFAULT 1,
 */

$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				id_alias			VARCHAR(50)		NOT NULL,

				nodes_id			TEXT,

				show_date_creation	TINYINT(4)		NOT NULL DEFAULT 0,
				show_date_modified	TINYINT(4)		NOT NULL DEFAULT 1,
				show_author_id		TINYINT(4)		NOT NULL DEFAULT 0,
				show_hits			TINYINT(4)		NOT NULL DEFAULT 1,

				default_nde			TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                ( id, id_alias, default_nde ) VALUES ( 1, 'default', 1 ) ";


db_install::process($table_suffix, $db_create, $db_insert);



////////////
// home_elm
$table_suffix = COM_CONTENT_TABLE_PREFIX.'home_elm';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				home_nde_id			INT(11)			NOT NULL,

				elm_id				INT(11)			NOT NULL,
				elm_order			INT(11)			NOT NULL	DEFAULT 0,
				elm_published		TINYINT(4)		NOT NULL 	DEFAULT 0,

				PRIMARY KEY (home_nde_id, elm_id)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////////////////////
// Component setting (second part)

$lang_home_page_title = "Page d\'accueil";

/* --------
   Specific
   -------- */

///////////////
// config_item
$table_suffix = COM_CONTENT_TABLE_PREFIX.'config_item';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				nod_view_title			TINYINT(4)		NOT NULL DEFAULT 1,
				nod_show_image_thumb	TINYINT(4)		NOT NULL DEFAULT 1,
				nod_show_medias			TINYINT(4)		NOT NULL DEFAULT 1,

				elm_show_text_intro		TINYINT(4)		NOT NULL DEFAULT 1,
				elm_use_text_editor		TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	() ";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////
// node_item
$table_suffix = COM_CONTENT_TABLE_PREFIX.'node_item';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				node_id				INT(11)			NOT NULL PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,
				title_alias			VARCHAR(100),

				text				MEDIUMTEXT,
				image				TEXT,

				view_title			TINYINT(4)		NOT NULL DEFAULT 1,

				show_image_thumb	TINYINT(4)		NOT NULL DEFAULT 1,
				show_medias			TINYINT(4)		NOT NULL DEFAULT 1

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



////////////////
// element_item
$table_suffix = COM_CONTENT_TABLE_PREFIX.'element_item';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				element_id			INT(11)			NOT NULL PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,
				title_alias			VARCHAR(100),
				title_quote			VARCHAR(500),

				text_intro			MEDIUMTEXT,
				text_main			MEDIUMTEXT,

				image_thumb			TEXT,
				image				TEXT,

				medias				TEXT,

				show_text_intro		TINYINT(4)		NOT NULL DEFAULT 1,
				disable_medias		TINYINT(4)		NOT NULL DEFAULT 0,
				use_text_editor		TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



/////////////////
// home_nde_item
$table_suffix = COM_CONTENT_TABLE_PREFIX.'home_nde_item';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				home_nde_id			INT(11)			NOT NULL PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,

				header				MEDIUMTEXT,
				footer				MEDIUMTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

                VALUES	(1, '$lang_home_page_title', '', '') ";


db_install::process($table_suffix, $db_create, $db_insert);



# Notice : the table 'home_elm_item' is not necessary



// End of setting (second part)
///////////////////////////////



//////////////
// header_msg (special addon)
$table_suffix = COM_CONTENT_TABLE_PREFIX.'header_msg';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
(
	id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

	date_creation		INT(11)			NOT NULL,
	node_id				INT(11)			NOT NULL,

	message				MEDIUMTEXT		NOT NULL

) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);


?>