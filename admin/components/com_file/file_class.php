<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

function admin_comFile_cssClass( $content, $class = '' )
{
	if ($class) {
		$content = "<span class=\"$class\">$content</span>";
	}
	return $content;
}



// Re-order the files list by 'path_for_order' using the powerfull usort() function
function admin_comFile_orderByPath( $x, $y )
{
	$debug = true;

	// Path details
	$x = explode('/', $x['path_for_order']);
	$y = explode('/', $y['path_for_order']);

	// Remove first cell wich is empty
	array_shift($x);
	array_shift($y);

	// Count levels of subdirectories
	if (count($x) < count($y)) {
		$level_min = count($x)-1;
		$level_max = count($y)-1;
	} else {
		$level_min = count($y)-1;
		$level_max = count($x)-1;
	}

	// Compare directories
	for ($i=0; $i<$level_min; $i++)
	{
		if ($x[$i] < $y[$i]) {
			return -1;
		}
		elseif ($x[$i] > $y[$i]) {
			return 1;
		}
	}

	// One directory is the child of the other
	if ($level_min != $level_max)
	{
		if (count($x) < count($y)) {
			return -1;
		} else {
			return 1;
		}
	}

	// Compare files ($level_min == $level_max wich is the index of the file)
	if ($x[$level_max] < $y[$level_max]) {
		return -1;
	} else {
		return 1;
	}

	trigger_error('Oups ! The comparaison has failded in admin_comFile_orderByPath() function');
}



/**
 * This function is used by '/admin/components/com_menu/link.php' script.
 *
 * In this script, we need a select form of availables files (into the '/contents/' directory).
 * This function return an array of the select options.
 */

// Search files into '/contents' directory (and sub-directories) - used in case: link_type_id=1 (file)
function admin_comFile_selectFileOptions( $current_dir = '' )
{
	static $file_list  = array();
	if ($current_dir == '')
	{
		$file_list = array(); # Reset whe begining
		$file_list[] = '/(optgroup)';
	}

	$dir = opendir(sitePath().'/contents'.$current_dir);

	$here_dir = array();
	$here_file = array();
	while ($data = readdir($dir))
	{
		if (($data != '.') && ($data != '..') && ($data != 'index.html'))
		{
			if ((is_dir(sitePath().'/contents'.$current_dir.'/'.$data)) && (formManager_filter::isPath($data)))
			{
				$here_dir[] = $current_dir.'/'.$data;
			}
			elseif (formManager_filter::isFile($data)) # File founded
			{
				$here_file[] = $current_dir.'/'.$data;
			}
		}
	}
	sort($here_dir);
	sort($here_file);

	for ($i=0; $i<count($here_file); $i++)
	{
		$file_list[$here_file[$i]] = $here_file[$i];
	}
	for ($i=0; $i<count($here_dir); $i++)
	{
		$file_list[] = $here_dir[$i].'(optgroup)';
		admin_comFile_selectFileOptions($here_dir[$i]);
	}
	closedir($dir);

	return $file_list;
}


?>