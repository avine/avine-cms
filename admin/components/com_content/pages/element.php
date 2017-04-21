<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Get component setting
global $init;
!isset($init) or trigger_error(LANG_COM_GENERIC_INIT_OVERWRITTEN, E_USER_WARNING);
require(comGeneric_::comSetupPath(__FILE__));


// Instanciate class object
global $com_gen;
$com_gen = new admin_comGeneric($init);


// Unset temporary variable
$init = NULL;


// Include file
require(sitePath().'/admin/components/com_generic/static_element.php'); # Use generic !

?>