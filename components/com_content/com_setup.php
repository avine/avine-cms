<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////////////
// Component setting

global $init;
$init = array(
	'table_prefix' 				=>		'content_',

	'node_item_field' 			=>		'title',
	'element_item_field' 		=>		'title',

	'lang_node_item_field' 		=>		LANG_COM_CONTENT_LANG_NODE_ITEM_FIELD,
	'lang_element_item_field' 	=>		LANG_COM_CONTENT_LANG_ELEMENT_ITEM_FIELD,

	'com_name' 					=>		'content',

	'lang_node' 				=>		LANG_COM_CONTENT_LANG_NODE,
	'lang_element' 				=>		LANG_COM_CONTENT_LANG_ELEMENT
);


?>