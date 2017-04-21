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


///////////
// Process

// Analyse $_GET[]
$com_gen->urlValidator();


//////////////
// Start view

// Display selectors
echo $com_gen->displaySelectors();

// Display page content
echo $com_gen->pageContentModel();

?>