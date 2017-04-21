<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();
$session = new sessionManager(sessionManager::BACKEND, 'contest_resource');


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$publish_status		= $filter->requestValue('publish_status'	, 'get')->getInteger();
$verified_status	= $filter->requestValue('verified_status'	, 'get')->getInteger();

$submit = formManager::isSubmitedForm('start_', 'post');
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger();
	$upd = $filter->requestName ('upd_'	)->getInteger();
	$new = $filter->requestValue('new'	)->get();
	$associate = $filter->requestValue('associate')->get();
} else {
	$del = false;
	$upd = false;
	$new = false;
	$associate = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post');
$new_submit = formManager::isSubmitedForm('new_', 'post');
$associate_submit = formManager::isSubmitedForm('associate_', 'post');



// 'year' field of the 'contest_config' table
$current_config_year = $db->selectOne('contest_config, year', 'year');



// Session
$session->init('use_current_config_year', true); # boolean

if ($submit)
{
	$session->set('use_current_config_year', $filter->requestValue('use_current_config_year')->get() ? true : false);
	$session->set('project_id', $filter->requestValue('project_id')->getInteger());
}



// Case 'publish_status'
if ($publish_status)
{
	$db->selectOne("contest_resource, published, where: id=$publish_status", 'published') ? $published = '0' : $published = '1';
	$db->update("contest_resource; published=$published; where: id=$publish_status");
}



// Case 'verified'
if ($verified_status)
{
	$db->selectOne("contest_resource, verified, where: id=$verified_status", 'verified') ? $verified = '0' : $verified = '1';
	$db->update("contest_resource; verified=$verified; where: id=$verified_status");
}



// Case 'del'
if ($del)
{
	$remove_from_ftp = false; # Local configuration

	$contest = new comContest($session->get('project_id'));
	admin_informResult( $contest->delResource($del, $remove_from_ftp) ); # Delete from DB (but conserve on FTP)

	$remove_from_ftp or admin_message(LANG_ADMIN_COM_CONTEST_RESOURCE_FILE_CONSERVED_ON_SERVER, 'info');
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	$resource_id = $filter->requestValue('resource_id')->getInteger();

	$title		= strip_tags($filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_CONTEST_RESOURCE_TITLE));
	$comment	= strip_tags($filter->requestValue('comment')->get());

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult($db->update('contest_resource; title='.$db->str_encode($title).', comment='.$db->str_encode($comment).'; where: id='.$resource_id));
	} else {
		echo $filter->errorMessage();
	}
}
if ($upd || ($upd_submit && !$upd_submit_validation))
{
	$start_view = false;

	$upd or $upd = $resource_id;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_UPD.'<br /><span style="font-weight:normal;">'.admin_comContest_projectAlias($session->get('project_id')).'</span></h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('resource_id', $upd);

	// Current resource
	$current = $db->selectOne("contest_resource, *, where: id=$upd");

	$html .= "<p><b>".LANG_COM_CONTEST_RESOURCE_FILE_NAME." :</b> {$current['file_name']}</p>";

	$html .= $form->text('title', $current['title'], LANG_COM_CONTEST_RESOURCE_TITLE.'<br />', '', 'size=50').'<br /><br />';
	$html .= $form->textarea('comment', $current['comment'], LANG_COM_CONTEST_RESOURCE_COMMENT.'<br />', '', 'cols=45;rwos=4').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$title = strip_tags($filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_CONTEST_RESOURCE_TITLE));
	$file = $filter->requestFile('file')->getUploadedfile(1, '', LANG_COM_CONTEST_RESOURCE_FILE_NAME);

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$contest = new comContest($session->get('project_id'));
		$result = $contest->addResource($file, $title);

		admin_informResult($result['ftp'] && $result['db']);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_NEW.'<br /><span style="font-weight:normal;">'.admin_comContest_projectAlias($session->get('project_id')).'</span></h2>';

	$html = '';
	$form = new formManager();
	$form->addMultipartFormData();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('title', '', LANG_COM_CONTEST_RESOURCE_TITLE.'<br />', '', 'size=50').'<br /><br />';
	$html .= $form->file('file', LANG_COM_CONTEST_RESOURCE_FILE_NAME.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'associate'
if ($associate_submit)
{
	$associate_submit_validation = true;
	$filter->reset();

	$title = strip_tags($filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_CONTEST_RESOURCE_TITLE));
	$file_name = $filter->requestValue('file_name')->getFile(1, '', LANG_COM_CONTEST_RESOURCE_FILE_NAME);

	// Database Process
	if ($associate_submit_validation = $filter->validated())
	{
		admin_informResult(
			$db->insert(
				"contest_resource; col: project_id,file_name,code,title; ".
				$session->get('project_id').', '.$db->str_encode($file_name).', '.$db->str_encode(md5(rand())).', '.$db->str_encode($title)
			)
		);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($associate || ($associate_submit && !$associate_submit_validation))
{
	$start_view = false;

	// FTP : list of all resources project
	$contest = new comContest($session->get('project_id'));
	$ftp = new ftpManager($contest->projectResourcePath());
	if ($ftp->setTree()) {
		$resource_ftp = $ftp->getTree();
		$resource_ftp = $resource_ftp['']; # base
	} else {
		$resource_ftp = array();
	}

	// DB : List of the resources project
	$resource_db = array();
	if ($resource = $contest->getResource()) {
		foreach($resource as $id => $info) {
			$resource_db[] = $info['file_name'];
		}
	}

	// Keep only the none associated resources
	$options = array();
	for ($i=0; $i<count($resource_ftp); $i++) {
		in_array($resource_ftp[$i], $resource_db) or $options[$resource_ftp[$i]] = $resource_ftp[$i];
	}

	if ($options)
	{
		// Title
		echo '<h2>'.LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_ASSOCIATE.'<br /><span style="font-weight:normal;">'.admin_comContest_projectAlias($session->get('project_id')).'</span></h2>';

		$html = '';
		$form = new formManager();
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'associate_');

		$html .= $form->text('title', '', LANG_COM_CONTEST_RESOURCE_TITLE.'<br />', '', 'size=50').'<br /><br />';
		$html .= $form->select('file_name', $options, LANG_COM_CONTEST_RESOURCE_FILE_NAME.'<br />').'<br /><br />';

		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
		$html .= $form->end();
		echo $html;
	}
	else
	{
		admin_message(LANG_ADMIN_COM_CONTEST_RESOURCE_ASSOCIATE_NO_FILE, 'warning');
		$start_view = true;
	}
}



//////////////
// Start view

if ($submit)
{
	// Update the projects order
	$id = formManager_filter::arrayOnly($filter->requestName('resource_order_')->getInteger());
	for ($i=0; $i<count($id); $i++) {
		if ($order = $filter->requestValue('resource_order_'.$id[$i])->getInteger()) {
			$db->update("contest_resource; resource_order=$order; where: id=".$id[$i]);
		}
	}
}

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_RESOURCE_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	/*
	 * Session
	 */

	// Projects selection
	$project_options = admin_comContest_projectOptions($session->get('project_id'), $session->get('use_current_config_year') ? $current_config_year : '');

	// Reset the current project_id ?
	if ($session->get('project_id')) {
		array_key_exists('['.$session->get('project_id').']', $project_options) or $session->reset('project_id');
	}

	// Use the current config year ?
	$html .= $form->checkbox('use_current_config_year', $session->get('use_current_config_year'), LANG_ADMIN_COM_CONTEST_RESOURCE_USE_CURRENT_CONFIG_YEAR." $current_config_year");
	$html .= '<br /><br />';

	// Select a project
	$html .= $form->select('project_id', $project_options, LANG_ADMIN_COM_CONTEST_RESOURCE_SELECT_PROJECT.'<br />');
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_top').'<br /><br />';

	/*
	 * Project
	 */

	if ($session->get('project_id'))
	{
		$html .= '<h3>'.$project_options['['.$session->get('project_id').']'].'</h3>';

		$contest = new comContest($session->get('project_id'));

		$list = array();
		if ($resource = $contest->getResource())
		{
			$i = 0;
			foreach($resource as $id => $info)
			{
				$list[$i]['delete'		] = $form->submit("del_$id", LANG_BUTTON_DELETE, '', 'image=delete');

				$list[$i]['title'		] = $info['title'];

				if ($info['file_exists'])
				{
					$list[$i]['file_name'] = '<a href="'.comContest::resourceHref($id).'" title="'.LANG_ADMIN_COM_CONTEST_RESOURCE_DOWNLOAD_FILE.'">'.$info['file_name'].'</a>';
				} else {
					// File is missing !
					$list[$i]['file_name'] = '<span class="red">'.$info['file_name'].'</span>';
				}

				$list[$i]['order'		] = $form->text("resource_order_$id", $info['resource_order'] != '999' ? $info['resource_order'] : '', '', '', 'size=2');

				// Only administrator can change the verified status !
				$admin_perm = new adminPermissions();
				if ($admin_perm->canAccessLevel(1))
				{
					$list[$i]['verified'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;verified_status='.$id.'">'.admin_replaceTrueByChecked($info['verified']).'</a>';
				} else {
					$list[$i]['verified'] = admin_replaceTrueByChecked($info['verified'], false);
				}

				$list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$id.'">'.admin_replaceTrueByChecked($info['published']).'</a>';

				$list[$i]['update'		] = $form->submit("upd_$id", LANG_BUTTON_UPDATE, '', 'image=update');

				$i++;
			}
		}
		$headers = array(
			'',
			LANG_COM_CONTEST_RESOURCE_TITLE,
			LANG_COM_CONTEST_RESOURCE_FILE_NAME,
			LANG_COM_CONTEST_RESOURCE_ORDER,
			LANG_COM_CONTEST_RESOURCE_VERIFIED,
			LANG_COM_CONTEST_RESOURCE_PUBLISHED,
			''
		);
		$table = new tableManager($list, $headers);
		$html .= $table->html();

		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_bottom');
		$html .= $form->submit('new', LANG_COM_CONTEST_RESOURCE_UPLOAD);
		$html .= $form->submit('associate', LANG_ADMIN_COM_CONTEST_RESOURCE_ASSOCIATE);
	}

	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>