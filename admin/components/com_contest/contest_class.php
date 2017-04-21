<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

function admin_comContest_yearOptions( $first, $last, $selected = false )
{
	// Check $selected
	if ($selected) {
		$first	<= $selected or $first	= $selected;
		$last	>= $selected or $last	= $selected;
	}

	$options[''] = LANG_SELECT_OPTION_ROOT;
	for ($i=$first; $i<=$last; $i++) {
		$options[$i] = $i;
	}

	if ($selected) {
		$options = formManager::selectOption($options, $selected);
	}
	return $options;
}



function admin_comContest_configYearOptions( $selected_year = '' )
{
	$options = array();

	global $db;
	if ($config_year = $db->select('contest_project, [config_year(desc)]'))
	{
		$config_year = array_keys($config_year);
		for ($i=0; $i<count($config_year); $i++)
		{
			$options[ $config_year[$i] ] = $config_year[$i];
		}
	}

	if ($selected_year) {
		$options = formManager::selectOption($options, $selected_year);
	}

	return $options;
}



function admin_comContest_projectOptions( $selected_project = '', $config_year = '' )
{
	$options = array('' => LANG_SELECT_OPTION_ROOT);

	if ($config_year) {
		$config_year = ", where: config_year=$config_year";
	}

	global $db;
	if ($project = $db->select("contest_project, id,compagny,title, config_year(desc),project_order(asc)$config_year"))
	{
		$current_year = false;
		for ($i=0; $i<count($project); $i++)
		{
			if (!$config_year && ($project[$i]['config_year'] != $current_year))
			{
				$current_year = $project[$i]['config_year'];
				$options[ "$current_year(optgroup)" ] = $current_year;
			}

			$options[ $project[$i]['id'] ] = $project[$i]['compagny']." « {$project[$i]['title']} »";
		}
	}

	if ($selected_project) {
		$options = formManager::selectOption($options, $selected_project);
	}

	return $options;
}



function admin_comContest_projectAlias( $project_id )
{
	global $db;
	$project = $db->selectOne("contest_project, compagny,title, where: id=$project_id");
	return $project['compagny']." &laquo; {$project['title']} &raquo;";
}



?>