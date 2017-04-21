<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../config.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');

loaderManager::directAccessBegin(false);



// Local configuration
$protect_filename = false; # boolean


// Protocol info
global $g_protocol;
$g_protocol = 'http://';


// Database connection
global $db;


if (isset($_GET['code']))
{
	if ($id = comContest::resourceID($_GET['code']))
	{
		$resource = $db->selectOne("contest_resource, project_id,file_name, where: id=$id");

		$contest = new comContest();
		$file_path = $contest->getConfig('resource_path').$resource['project_id'].'/'.$resource['file_name'];

		if (!$protect_filename)
		{
			$filename = $resource['file_name'];
		} else {
			$pathinfo = pathinfo($resource['file_name']);
			$filename = 'contest_project_resource.'.$pathinfo['extension']; # generic name
		}

		if (is_file($file_path))
		{
			// Download file
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment; filename=$filename;");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($file_path));
			readfile($file_path);
		}
	}
}



loaderManager::directAccessEnd();

?>