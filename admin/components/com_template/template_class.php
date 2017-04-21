<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Functions

// Search & validate templates into '/templates' directory
function admin_comTemplate_readDir()
{
	$template_ftp = array();

	clearstatcache();

	$dir = opendir(sitePath().'/templates');
	while ($tmpl_dir = readdir($dir))
	{
		if ($tmpl_dir != '.' && $tmpl_dir != '..' && is_dir(sitePath()."/templates/$tmpl_dir"))
		{
			$i = count($template_ftp);

			// Template directory
			$template_ftp[$i]['dir'] = $tmpl_dir;

			// Template expected files
			$checklist = 
				array(
					'php'	=>	'index.php',
					'css'	=>	'index.css',
				);

			$ftp = new ftpManager(sitePath()."/templates/$tmpl_dir/");
			foreach ($checklist as $key => $file) {
				$template_ftp[$i][$key] = $ftp->isFile($file);
			}
		}
	}
	closedir($dir);

	return $template_ftp;
}



// Re-order the templates list, by using the powerfull usort() function !
function admin_comTemplate_orderByID( $x, $y )
{
	// Sort by name
	$field = 'name'; # Set 'name' or 'id'

	if (strip_tags($x[$field]) == strip_tags($y[$field])) {
		return 0;
	}
	elseif (strip_tags($x[$field]) < strip_tags($y[$field])) {
		return -1;
	}
	else {
		return 1;
	}
}



// Get the list of availables positions files
function admin_comTemplate_positionsFilesOptions( &$template_name_current )
{
	$options = array('' => LANG_SELECT_OPTION_ROOT);

	global $db;
	if ($template_name_current = $db->selectOne('template, name, where: current=1', 'name'))
	{
		$ftp = new ftpManager(sitePath()."/templates/$template_name_current/positions");
		$positions = $ftp->setTree('', false)->getTree();
		$positions = $positions['']; # Relative dir

		for ($i=0; $i<count($positions); $i++)
		{
			if ($positions[$i] !== 'index.html') {
				$pos = preg_replace('~\.html$~', '', $positions[$i]);
				$options[$pos] = $pos;
			}
		}
	}

	return $options;
}



?>