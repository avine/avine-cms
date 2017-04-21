<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



////////////
// Function

function admin_addrbook_filterList()
{
	$return = array('0' => LANG_ADMIN_COM_ADDRBOOK_OPTION_SELECT_FILTER_ROOT);

	global $db;
	$filter = $db->select('addrbook_filter, id, name, filter_order(asc)');
	for ($i=0; $i<count($filter); $i++)
	{
		$return[$filter[$i]['id']] = $filter[$i]['name'];
	}

	return $return;
}



function admin_addrbook_getOptionID( $name, $filter_id )
{
	global $db;

	if ($option_id = $db->selectOne("addrbook_filter_option, id, where: filter_id=$filter_id AND, where: name=".$db->str_encode($name), 'id'))
	{
		return $option_id;
	} else {
		return false;
	}
}
