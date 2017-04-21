<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



define( 'LANG_COM_GENERIC_COM_SETUP_MISSING',
	'<br />Some globals variables, required to run the script, are missing !<br />
	Expected object : <strong>is_subclass_of($com_gen, \'comGeneric_\')</strong>.<br />
	You probably try to access directly to a <strong>"static_*.php"</strong> file.<br />
	To run properly, this file need the <strong>"com_setup.php"</strong> file, wich contain the required variables setting.<br />
	So, you should use a main file wich is including both : "com_setup.php" and the "static_*.php" file.<br />' );

define( 'LANG_COM_GENERIC_INIT_OVERWRITTEN',
	'A script <strong>need to define (tamporarily) $init</strong> as a global variable.<br />
	But <strong>$init have been already defined</strong> somewhere !<br />
	<strong>So, the content of the original $init variable have been overwritten !</strong>' );



// Language
define( 'LANG_COM_GENERIC_LANG_NODE'						, "noeud" );
define( 'LANG_COM_GENERIC_LANG_ELEMENT'						, "élément" );

define( 'LANG_COM_GENERIC_NODE_SELECTION'					, "{node}s" );

define( 'LANG_COM_GENERIC_ARCHIVE_SELECTION'				, "{element}s" );
define( 'LANG_COM_GENERIC_ARCHIVE_SELECTION_0'				, "en ligne" );
define( 'LANG_COM_GENERIC_ARCHIVE_SELECTION_1'				, "archives" );
define( 'LANG_COM_GENERIC_ARCHIVE_SELECTION_2'				, "en ligne + archives" );
define( 'LANG_COM_GENERIC_ARCHIVE_IMG_TITLE'				, "Ressource archivée" );

define( 'LANG_COM_GENERIC_NEW_ELEMENT'						, "Nouveau !" );

define('LANG_COM_GENERIC_AUTHOR'							, "Auteur");
define('LANG_COM_GENERIC_DATE_CREATION'						, "Création");
define('LANG_COM_GENERIC_DATE_MODIFIED'						, "Mise à jour");
define('LANG_COM_GENERIC_HITS'								, "Vues");

?>