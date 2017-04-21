<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Function

function admin_comDonate_usedDesignationsOptions()
{
	global $db;

	// All designations
	$all_designation = $db->select('donate_designation, [id], title');

	// Used designations
	$used_designation = $db->select('donate, [designation_id], id');

	$temp = array();
	if ($used_designation)
	{
		reset($used_designation);
		while(list($designation_id, $array) = each($used_designation)) $temp[$designation_id] = $all_designation[$designation_id]['title'];
		asort($temp);
	}

	$options = array();
	$options['0'] = LANG_ADMIN_COM_DONATE_LIST_DESIGNATION_FILTER_ROOT;
	if ($temp)
	{
		reset($temp);
		while(list($id, $title) = each($temp)) $options[$id] = $title;
	}

	return $options;
}



// Delete records from 'donate' table and the associated records of 'donate_details' table
function admin_comDonate_purgeDatabase( $donate_id_list = array() )
{
	$id_error = array();

	global $db;

	for ($i=0; $i<count($donate_id_list); $i++)
	{
		if ($db->delete('donate_details; where: donate_id='.$donate_id_list[$i]))
		{
			$db->delete('donate; where: id='.$donate_id_list[$i]) ? '' : $id_error[] = $donate_id_list[$i];
		} else {
			$id_error[] = $donate_id_list[$i];
		}
	}

	if (count($id_error)) {
		admin_message("The task could not be completed properly. Please, check manually the following records of 'donate' table (and the associated records of 'donate_details' table) : id = ".implode(', ', $id_error), 'error', '400');
		return false;
	}

	admin_informResult(true);
	return true;
}


?>