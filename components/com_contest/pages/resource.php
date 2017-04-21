<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


global $db;


// User
global $g_user_login;
$user_id = $g_user_login->userID();


// Contest
$contest = new comContest(comContest::findProjectID($user_id));


// Title
echo '<h1><span>'.LANG_COM_CONTEST_PAGE_CONTEST_YEAR.' '.$contest->getConfig('year').'</span><br />'.LANG_COM_CONTEST_PAGE_RESOURCE_TITLE.'</h1>';


// Contest status ?
if (!$contest->isFormAuthorized(&$message)):
	echo $message;
else:


// Config
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

if ($submit = formManager::isSubmitedForm('start_', 'post'))
{
	$new = $filter->requestValue('new')->get();
	$upd = $filter->requestName('upd_')->getInteger();
	$del = $filter->requestName('del_')->getInteger();
} else {
	$upd = false;
	$new = false;
	$del = false;
}

$upd_submit	= formManager::isSubmitedForm('upd_', 'post');
$new_submit	= formManager::isSubmitedForm('new_', 'post');



// Case 'del'
if ($del)
{
	$contest->delResource($del); # Delete from DB and FTP
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	$resource_id = $filter->requestValue('resource_id')->getInteger();
	$title = strip_tags($filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_CONTEST_RESOURCE_TITLE));

	if ($upd_submit_validation = $filter->validated())
	{
		$db->update('contest_resource; title='.$db->str_encode($title).'; where: id='.$resource_id);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($upd || ($upd_submit && !$upd_submit_validation))
{
	$start_view	= false;

	$upd or $upd = $resource_id;

	// Current resource infos
	$resource = $contest->getResource();
	$current = $resource[$upd];

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $form->reloadPage(), 'upd_');

	$html .= '<b>'.LANG_COM_CONTEST_RESOURCE_FILE_NAME.' : </b>'.$current['file_name'].'<br /><br />';

	$html .= $form->hidden('resource_id', $upd);
	$html .= $form->text('title', $current['title'], LANG_COM_CONTEST_RESOURCE_TITLE.'<br />', '', 'size=50');

	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT).'<br /><br />';
	$html .= $form->end();

	echo "\n<fieldset><legend class=\"comContest-legend-resource\">".LANG_COM_CONTEST_RESOURCE_MODIFY."</legend>\n$html\n</fieldset>\n";
	echo '<p><a href="'.$form->reloadPage().'">'.LANG_COM_CONTEST_BUTTON_BACK.'</a></p>';
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$title = strip_tags($filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_CONTEST_RESOURCE_TITLE));
	$file = $filter->requestFile('file')->getUploadedfile(1, '', LANG_COM_CONTEST_RESOURCE_FILE_NAME);

	if ($new_submit_validation = $filter->validated())
	{
		$contest->addResource($file, $title);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view	= false;

	$html = '';
	$form = new formManager();
	$form->addMultipartFormData();
	$html .= $form->form('post', $form->reloadPage(), 'new_');

	$html .= $form->text('title', '', LANG_COM_CONTEST_RESOURCE_TITLE.'<br />', '', 'size=50').'<br /><br />';
	$html .= $form->file('file', LANG_COM_CONTEST_RESOURCE_FILE_NAME.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT).'<br /><br />';
	$html .= $form->end();

	echo "\n<fieldset><legend class=\"comContest-legend-resource\">".LANG_COM_CONTEST_RESOURCE_UPLOAD."</legend>\n$html\n</fieldset>\n";
	echo userMessage(sprintf(LANG_COM_CONTEST_RESOURCE_UPLOAD_MAX_SIZE_TIPS, ftpManager::convertBytes(ini_get('upload_max_filesize'), 'm').'MB'), 'info', '100%');
	echo '<p><a href="'.$form->reloadPage().'">'.LANG_COM_CONTEST_BUTTON_BACK.'</a></p>';
}



//////////////
// Start view

if (!$contest->getProject())
{
	echo userMessage(LANG_COM_CONTEST_NO_PROJECT_ID, 'warning');
}
elseif ($start_view)
{
	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $form->reloadPage(), 'start_');

	$list = array();
	if ($resource = $contest->getResource())
	{
		$i = 0;
		foreach($resource as $id => $info)
		{
			$list[$i]['delete'		] = $form->submit("del_$id", LANG_BUTTON_DELETE, '', 'image=delete');
			$list[$i]['title'		] = $info['title'];
			$list[$i]['file_name'	] = $info['file_name'];
			$list[$i]['update'		] = $form->submit("upd_$id", LANG_BUTTON_UPDATE, '', 'image=update');

			// File is missing !
			if (!$info['file_exists'])
			{
				$list[$i]['file_name'] = '<span class="comContest-file-missing">'.$list[$i]['file_name'].'</span>';
				$missing = true;
			}
			$i++;
		}
	}
	$table = new tableManager($list, array('', LANG_COM_CONTEST_RESOURCE_TITLE, LANG_COM_CONTEST_RESOURCE_FILE_NAME, ''));
	$html .= $table->html();

	$html .= $form->submit('new', LANG_COM_CONTEST_RESOURCE_UPLOAD).'<br /><br />';
	$html .= $form->end();
	echo $html;

	// Inform the user that some resources are missing !
	if (isset($missing)) {
		echo userMessage(LANG_COM_CONTEST_RESOURCE_MISSING_RESOURCE, 'warning');
	}
}


endif;

?>