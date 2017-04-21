<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////////
// Configuration

// comGeneric_ class object
global $com_gen;
is_subclass_of($com_gen, 'comGeneric_') or trigger_error(LANG_COM_GENERIC_COM_SETUP_MISSING, E_USER_ERROR);

// Main file name (not the static_*.php one)
$com_gen->setPageName('index');


//////////////
// Start view

// Display selector
echo $com_gen->homeNdeSelector(); # the node selector process is included into this method

// Display page content
echo $com_gen->homeContentModel( $com_gen->homeNdeSession() );

?>