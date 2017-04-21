<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */

 
// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post');

if ($submit)
{
	$filter->reset();

	$year			= $filter->requestValue('year'			)->getInteger		(1, '', LANG_ADMIN_COM_CONTEST_CONFIG_YEAR);
	$deadline		= $filter->requestValue('deadline'		)->getFormatedDate	(1, '', LANG_ADMIN_COM_CONTEST_CONFIG_DEADLINE);
	$resource_path	= $filter->requestValue('resource_path'	)->getPath			(1, '', LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH);
	$jury_password	= $filter->requestValue('jury_password'	)->getUserPass		(1, '', LANG_ADMIN_COM_CONTEST_CONFIG_JURY_PASSWORD);

	if (!$resource_path) {
		$filter->set($resource_path)->getError(LANG_ADMIN_COM_CONTEST_CONFIG_EMPTY_RESOURCE_PATH, LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH);
	}
	elseif (!is_dir($resource_path))
	{
		$filter->set($resource_path)->getError(LANG_ADMIN_COM_CONTEST_CONFIG_INVALID_RESOURCE_PATH, LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH);
	}

	// 'resource_path' should ends with a slash
	preg_match('~/$~', $resource_path) or $resource_path .= '/';

	if ($filter->validated()) {
		admin_informResult(
			$db->update(
				"contest_config; year=$year, deadline=$deadline, ".
				'resource_path='.$db->str_encode($resource_path).', jury_password='.$db->str_encode($jury_password)
			)
		);
	}
	else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_CONFIG_TITLE_START.'</h2>';

	$config = $db->selectOne('contest_config, *');

	// First time ? Set default values
	$config['year'] or $config['year'] = date('Y');

	$config['deadline'] ? $deadline = getTime($config['deadline'], 'time=no') : $deadline = '';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	/*
	 * Year
	 */
	$html .= $form->select('year', admin_comContest_yearOptions(date('Y')-3, date('Y')+3, $config['year']), LANG_ADMIN_COM_CONTEST_CONFIG_YEAR.'<br />');

	if ($config['year'] && ($config['year'] < date('Y')))
	{
		$html .= '&nbsp; <span class="red">'.LANG_ADMIN_COM_CONTEST_CONFIG_EXPIRED.'</span>';
	}
	$html .= '<br /><br />';

	/*
	 * Deadline
	 */
	$html .= '<script type="text/javascript">$(function(){$(\'#start_deadline\').datepicker({inline: true});});</script>'."\n";
	$html .= $form->text('deadline', $deadline, LANG_ADMIN_COM_CONTEST_CONFIG_DEADLINE.'<br />', '', 'size=10');

	if ($config['deadline'] && ($config['deadline'] < time()))
	{
		$html .= '&nbsp; <span class="red">'.LANG_ADMIN_COM_CONTEST_CONFIG_EXPIRED.'</span>';
	}
	$html .= '<br /><br />';

	/*
	 * Resource path
	 */
	if ($db->selectCount('contest_resource')) {
		$readonly = ';readonly';
	} else {
		$readonly = '';
	}
	$html .= $form->text('resource_path', $config['resource_path'], LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH.'<br />', '', "size=70$readonly");

	clearstatcache();
	if ($config['resource_path'] && !is_dir($config['resource_path']))
	{
		$html .= '&nbsp; <span class="red">'.LANG_ADMIN_COM_CONTEST_CONFIG_RESOURCE_PATH_MISSING.'</span>';
	}
	$html .= '<br /><span class="grey"> document_root : '.$_SERVER['DOCUMENT_ROOT'].'</span><br /><br />';

	$html .= $form->text('jury_password', $config['jury_password'], LANG_ADMIN_COM_CONTEST_CONFIG_JURY_PASSWORD.'<br />', '', 'size=default').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>