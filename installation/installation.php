<?php //exit; # To allow this script comment this line
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Check system requirement
$version = explode('.', phpversion());
($version[0] >= 5) or die('Sorry ! System requirement : PHP5.');


// Direct access authorized
define('_DIRECT_ACCESS', 1);


session_start();
mb_internal_encoding("UTF-8");
ini_set('arg_separator.output', '&amp;');


// Include global
require('../global/language.php');
require('../global/functions.php');

// Include some libraries
require('../libraries/lib_database/database_class.php');
require('../libraries/lib_database/database_lang.php');

require('../libraries/lib_form/form_class.php');
require('../libraries/lib_form/form_lang.php');

require('../libraries/lib_table/table_class.php');
require('../libraries/lib_table/table_lang.php');

require('../libraries/lib_ftp/ftp_class.php');
require('../libraries/lib_ftp/ftp_lang.php');

require('../libraries/lib_box/box_class.php');
require('../libraries/lib_box/box_lang.php');


// Specific : installation utilities
require('_inc/inc.php');


// Template header
require('_inc/tmpl_header.php');


// Manage the `config.php` script
require('manage_config.php');


if (is_file('../config.php'))
{
	// Database connect
	global $db;
	$db = new databaseManager();
	$db->db_connect();

	// Manage the database
	require('manage_db.php');

	// Manage default values of the tables
	require('manage_default.php');
	
	// Database close
	$db->db_close();
}


// Template footer
require('_inc/tmpl_footer.php');

?>