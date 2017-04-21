<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<a name="config"></a>'."\n";
$html = '';



///////////
// Process

$filter = new formManager_filter();
$ftp = new ftpManager();
$box = new boxManager();

$update = 1;
$disabled = '';



if (!is_file('../config.php'))
{
	$default =
		array(
			'db_host' 			=>	'',
			'db_user' 			=>	'',
			'db_pass' 			=>	'',
			'db_name' 			=>	'',
			'db_table_prefix' 	=>	'avn_',
			'website_path' 		=>	str_replace('/installation/installation.php', '', $_SERVER['PHP_SELF']),
			'time_zone' 		=>	'Europe/Paris'
		);

	if (formManager::isSubmitedForm('config_'))
	{
		$db_host = $filter->requestValue('db_host')->getNotEmpty(1, '', LANG_INSTALL_DB_HOST);
		$db_user = $filter->requestValue('db_user')->getNotEmpty(1, '', LANG_INSTALL_DB_USER);
		$db_pass = $filter->requestValue('db_pass')->get();
		$db_name = $filter->requestValue('db_name')->getNotEmpty(1, '', LANG_INSTALL_DB_NAME);

		$db_table_prefix = $filter->requestValue('db_table_prefix')->getVar(0, '', LANG_INSTALL_DB_TABLE_PREFIX);
		$website_path = $filter->requestValue('website_path')->getPath(1, '', LANG_INSTALL_WEBSITE_PATH);
		$time_zone = $filter->requestValue('time_zone')->get();

		if ($filter->validated())
		{
			$message = array();

			// Check website_path
			if (!is_file($_SERVER['DOCUMENT_ROOT'].$website_path.'/installation/installation.php')) {
				$message[] = LANG_INSTALL_CONFIG_ERROR_WEBSITE_PATH."<br /><b>{$_SERVER['DOCUMENT_ROOT']}$website_path</b>";
			}

			// Check database connection
			try {
				$dbh =@ new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
				$dbh = NULL; # Close connection
			} catch (PDOException $e) {
				$message[] = LANG_INSTALL_CONFIG_ERROR_DB_CONNECTION;
			}

			// Display errors
			if (count($message)) {
				$box->echoMultiMessage($message, LANG_INSTALL_CONFIG_ERROR_TITLE, 'warning');
			}
			// Create `config.php` script
			else
			{
				$config = $ftp->read('_inc/CONFIG');

				$config = str_replace('{DB_HOST}'			, $db_host, $config);
				$config = str_replace('{DB_USER}'			, $db_user, $config);
				$config = str_replace('{DB_PASS}'			, $db_pass, $config);
				$config = str_replace('{DB_NAME}'			, $db_name, $config);
				$config = str_replace('{DB_TABLE_PREFIX}'	, $db_table_prefix, $config);
				$config = str_replace('{WEBSITE_PATH}'		, $website_path, $config);
				$config = str_replace('{TIME_ZONE}'			, $time_zone, $config);

				$ftp->write('../config.php', $config) ? '' : $box->echoMessage(LANG_INSTALL_CONFIG_FAILED);
			}
		}
		else {
			echo $filter->errorMessage();
		}
	}
}



if (is_file('../config.php'))
{
	require ('../config.php');

	$default =
		array(
			'db_host' 			=>	DB_HOST,
			'db_user' 			=>	DB_USER,
			'db_pass' 			=>	DB_PASS,
			'db_name' 			=>	DB_NAME,
			'db_table_prefix' 	=>	DB_TABLE_PREFIX,
			'website_path' 		=>	WEBSITE_PATH,
			'time_zone' 		=>	TIME_ZONE
		);

	if (formManager::isSubmitedForm('config_') && $filter->requestValue('modify')->get())
	{
		$ftp->delete('../config.php');
		$update = 0;
	} else {
		$disabled = ';disabled';
	}
}



//////////////
// Start view

$form = new formManager($update);
$html .= $form->form('post', formManager::reloadPage().'#config', 'config_');

$html .= $form->text('db_host'			, $default['db_host'		], LANG_INSTALL_DB_HOST			, '', "wrapper=div.label-150px$disabled");
$html .= $form->text('db_user'			, $default['db_user'		], LANG_INSTALL_DB_USER			, '', "wrapper=div.label-150px$disabled");
$html .= $form->text('db_pass'			, $default['db_pass'		], LANG_INSTALL_DB_PASS			, '', "wrapper=div.label-150px$disabled");
$html .= $form->text('db_name'			, $default['db_name'		], LANG_INSTALL_DB_NAME			, '', "wrapper=div.label-150px$disabled");
$html .= $form->text('db_table_prefix'	, $default['db_table_prefix'], LANG_INSTALL_DB_TABLE_PREFIX	, '', "wrapper=div.label-150px$disabled");
$html .= $form->text('website_path'		, $default['website_path'	], LANG_INSTALL_WEBSITE_PATH	, '', "wrapper=div.label-150px$disabled");
$html .= $form->select('time_zone', formManager::selectOption(timeZoneSelection(), $default['time_zone']), LANG_INSTALL_TIME_ZONE, '', "wrapper=div.label-150px$disabled");

!$disabled ? $html .= '<p><br />'.$form->submit('submit', LANG_INSTALL_CONFIG_BUTTON_CREATE)."</p>\n" : $html .= '<p><br />'.$form->submit('modify', LANG_INSTALL_CONFIG_BUTTON_MODIFY)."</p>\n";

$html .= $form->end();



// Fieldset
echo "<fieldset><legend class=\"config\">".LANG_INSTALL_FIELDSET_CONFIG."</legend>\n$html</fieldset>\n";

?>