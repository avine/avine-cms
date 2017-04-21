<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Global
require(sitePath().'/admin/global/language.php');
require(sitePath().'/admin/global/functions.php');


// Libraries and components (admin parts only)
$loader = new loaderManager();
$loader->altBase('/admin', true);

$loader->_class('components');

# TODO - Add multi-languages support here (and make the choice possible in the 'config' table)
$loader->_lang('components');

?>