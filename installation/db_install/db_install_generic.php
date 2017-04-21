<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/*
 * Here come the 'generic' component engine (wich can be duplicated and customized for many others components)
 */


//////////////////////////////////
// Component setting (first part)

// Component (middle-) prefix name
define( 'COM_GENERIC_TABLE_PREFIX'	, 'generic_' );

$com_node_name 		= 'node';
$com_element_name 	= 'element';

// End of setting (first part)
//////////////////////////////



/* ------ 
   Global 
   ------ */

//////////
// config
$table_suffix = COM_GENERIC_TABLE_PREFIX.'config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				com_node_name		TINYTEXT		NOT NULL,
				com_element_name	TINYTEXT		NOT NULL,

				elements_per_step	TINYINT(4)		NOT NULL DEFAULT 10,
				elements_per_row	TINYINT(4)		NOT NULL DEFAULT 1,
				elements_wrapper	TINYINT(4)		NOT NULL DEFAULT 1,

				subnodes_per_row	TINYINT(4)		NOT NULL DEFAULT 3,
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
$table_suffix = COM_GENERIC_TABLE_PREFIX.'node';


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
$table_suffix = COM_GENERIC_TABLE_PREFIX.'element';


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
$table_suffix = COM_GENERIC_TABLE_PREFIX.'home_nde';

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
$table_suffix = COM_GENERIC_TABLE_PREFIX.'home_elm';


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

/* --------
   Specific
   -------- */

///////////////
// config_item



/////////////
// node_item



////////////////
// element_item



/////////////////
// home_nde_item




# Notice : the table 'home_elm_item' is not necessary



// End of setting (second part)
///////////////////////////////


?>