<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<a name="database"></a>'."\n";
$html = '';


// Config
$db_install_path = $_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/installation/db_install/';
$ftp = new ftpManager($db_install_path);

$html .= '<h3>'.LANG_INSTALL_DATABASE_FILES_PATH."</h3><p>$db_install_path</p>\n";

$box = new boxManager();



///////////
// Process

$result = array();

if ($db_process = formManager::isSubmitedForm('database_'))
{
	$filter = new formManager_filter();
	if ($checked_file = formManager_filter::arrayOnly( $filter->requestName('db_install_')->get() ))
	{
		for ($i=0; $i<count($checked_file); $i++)
		{
			$current_file = "db_install_{$checked_file[$i]}.php";

			if (!$ftp->isFile($current_file)) {
				continue;
			}

			ob_start();
			require($db_install_path.$current_file);
			$result[$current_file] = ob_get_contents();
			ob_end_clean();
		}
	}
}



//////////////
// Start view

$form = new formManager();
$html .= $form->form('post', formManager::reloadPage().'#database', 'database_');



$view = array();
$j = 0;

list($dir, $file) = each($ftp->setTree()->getTree());
for ($i=0; $i<count($file); $i++)
{
	if (!preg_match('~^(db_install_)~', $file[$i])) {
		continue;
	}

	$name = preg_replace('~(\.php)$~', '', $file[$i]);

	if (!$db_process)
	{
		$view[$j]['check'] = $form->checkbox($name, 1);
		$view[$j]['name'] = $form->label($name, $file[$i]);
	}
	else
	{
		if (isset($result[$file[$i]])) {
			$view[$j]['check'	] = '<span style="color:grey;">x</span>';
			$view[$j]['name'	] = $file[$i];
			$view[$j]['result'	] = $result[$file[$i]];
		} else {
			$view[$j]['check'	] = '';
			$view[$j]['name'	] = '<span style="color:grey;font-style:italic;">'.$file[$i].'</span>';
			$view[$j]['result'	] = '';
		}
	}

	$j++;
}



if (!$db_process)
{
	$header = array(
		'<div id="checked_status" title="'.LANG_INSTALL_DATABASE_CHECKED_STATUS.'">'.LANG_INSTALL_DATABASE_CHECKED_STATUS.'</div>', # jQuery : Inverse the checkboxes status
		LANG_INSTALL_DATABASE_FILE_NAME
	);
}
else
{
	$header = array(
		'',
		LANG_INSTALL_DATABASE_FILE_NAME,
		LANG_INSTALL_DATABASE_ASSOCIATED_TABLES
	);

	if (isset($_POST[db_install::INSTALL]))
	{
		$html .= '<h2 class="db-install">'.LANG_INSTALL_DATABASE_PROCESS.'<br /><span>'.LANG_INSTALL_DATABASE_PROCESS_INSTALL."</span></h2>\n";
	} else {
		$html .= '<h2 class="db-uninstall">'.LANG_INSTALL_DATABASE_PROCESS.'<br /><span>'.LANG_INSTALL_DATABASE_PROCESS_UNINSTALL."</span></h2>\n";
	}
}



$table = new tableManager($view, $header);
$html .= $table->html();



if (!$db_process)
{
	$html .= '<p>'.$form->submit(db_install::INSTALL, LANG_INSTALL_DATABASE_BUTTON_INSTALL).' &nbsp; &nbsp; '.$form->submit(db_install::UNINSTALL, LANG_INSTALL_DATABASE_BUTTON_UNINSTALL)."</p>\n";
}
else
{
	isset($_POST[db_install::UNINSTALL]) ? $html .= '<p class="navig"><a href="'.$_SERVER['PHP_SELF'].'">'.LANG_INSTALL_NAVIG_INSTALLATION."</a></p>\n" : '';
	isset($_POST[db_install::INSTALL  ]) ? $html .= '<p><a href="'.$_SERVER['PHP_SELF'].'">'.LANG_INSTALL_NAVIG_DISSATISFID.LANG_INSTALL_NAVIG_INSTALLATION."</a></p>\n" : '';
}

$html .= $form->end();



// Fieldset
echo "<fieldset><legend class=\"database\">".LANG_INSTALL_FIELDSET_DATABASE."</legend>\n$html</fieldset>\n";

?>