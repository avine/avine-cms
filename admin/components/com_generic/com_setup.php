<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////////////
// Component setting

global $init;
$init = array(
	'table_prefix' 				=>		'generic_',

	'node_item_field' 			=>		'',
	'element_item_field' 		=>		'',

	'lang_node_item_field' 		=>		'',
	'lang_element_item_field' 	=>		'',

	'com_name' 					=>		'generic',

	'lang_node' 				=>		LANG_COM_GENERIC_LANG_NODE,
	'lang_element' 				=>		LANG_COM_GENERIC_LANG_ELEMENT
);


?>