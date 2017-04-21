<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'contest_project');


// Images Html
$png_new = '<img src="'.WEBSITE_PATH.'/admin/images/new.png" alt="new" />';


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger();

$submit = formManager::isSubmitedForm('start_', 'post');
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger();
	$upd = $filter->requestName ('upd_'	)->getInteger();
	$new = $filter->requestValue('new'	)->get();
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post');
$new_submit = formManager::isSubmitedForm('new_', 'post');



// Init/Update the 'config_year' selection
if (
	($submit && ($config_year = $filter->requestValue('config_year')->getInteger())) # Update
	||
	(!$session->get('config_year') && ($config_year = $db->selectOne('contest_project, config_year(desc)', 'config_year'))) # Init
) {
	$session->set('config_year', $config_year);
}



// Case 'publish_status'
if ($publish_status)
{
	if ($db->selectOne("contest_project, admin_validation, where: id=$publish_status", 'admin_validation')) {
		$admin_validation = '0';
	} else {
		$admin_validation = '1';
	}
	$db->update("contest_project; admin_validation=$admin_validation; where: id=$publish_status");
}



// Case 'del'
if ($del)
{
	if (!$db->select("contest_resource, id, where: project_id=$del"))
	{
		$db->delete("contest_project; where: id=$del");
	} else {
		admin_message(LANG_ADMIN_COM_CONTEST_PROJECT_DEL_ERROR, 'warning');
	}
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$user_id = $filter->requestValue('user_id')->getInteger();

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result =
			$db->insert(
				"contest_project; col: user_id,config_year, compagny,address,leaders,contributors,title,year ; ".
				"$user_id, ".$session->get('config_year').", '', '', '', '', '".LANG_ADMIN_COM_CONTEST_PROJECT_NEW_DEFAULT_TITLE."', 0"
			);

		if ($result) {
			$new_project_id = $db->insertID();
		}

		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// List of availables users
	$options = array();
	if ($user = $db->select('user, [id], username'))
	{
		$user_excluded = $db->select('contest_project, [user_id], where: config_year='.$session->get('config_year'));
		foreach($user as $id => $info) {
			if (!array_key_exists($id, $user_excluded)) {
				$options[$id] = $info['username'];
			}
		}
	}

	if (count($options))
	{
		// Title
		echo '<h2>'.LANG_ADMIN_COM_CONTEST_PROJECT_TITLE_NEW.'</h2>';

		$html = '';
		$form = new formManager();
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

		$html .= $form->select('user_id', $options, LANG_ADMIN_COM_CONTEST_PROJECT_NEW_SELECT_USER);
		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

		$html .= $form->end();
		echo $html;
	}
	else
	{
		admin_message(LANG_ADMIN_COM_CONTEST_PROJECT_NEW_NO_USER, 'warning');
		$start_view = true;
	}
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	$upd_id = $filter->requestValue('id')->getInteger();

	$project = array(
		'config_year'	=> $filter->requestValue('config_year'				)->getInteger(1, '', LANG_COM_CONTEST_PROJECT_CONFIG_YEAR),

		'compagny'		=> strip_tags($filter->requestValue('compagny'		)->getNotEmpty(1, '', LANG_COM_CONTEST_PROJECT_COMPAGNY)),
		'address'		=> strip_tags($filter->requestValue('address'		)->getNotEmpty(1, '', LANG_COM_CONTEST_PROJECT_ADDRESS)),

		'leaders'		=> strip_tags($filter->requestValue('leaders'		)->getNotEmpty(1, '', LANG_COM_CONTEST_PROJECT_LEADERS)),
		'contributors'	=> strip_tags($filter->requestValue('contributors'	)->getNotEmpty(1, '', LANG_COM_CONTEST_PROJECT_CONTRIBUTORS)),

		'title'			=> strip_tags($filter->requestValue('title'			)->getNotEmpty(1, '', LANG_COM_CONTEST_PROJECT_TITLE)),
		'year'			=> $filter->requestValue('year'						)->getInteger(1, '', LANG_COM_CONTEST_PROJECT_YEAR),

		'admin_intro'	=> $filter->requestValue('text_intro'				)->get(),
		'admin_main'	=> $filter->requestValue('text_main'				)->get()
	);

	if ($filter->requestValue('missing_resource_received')->get())
	{
		$project['all_resource_provided'] = '1';
		$project['missing_resource_list'] = '';
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$contest = new comContest($upd_id);
		admin_informResult( $contest->updateProject($project) );
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($upd || ($upd_submit && !$upd_submit_validation))
{
	$start_view = false;

	$upd or $upd = $upd_id;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_PROJECT_TITLE_UPD.'</h2>';

	$form = new formManager();
	$html = $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	// Get project
	$contest = new comContest($upd);
	$project = $contest->getProject();

	$html .= $form->hidden('id', $upd);

	/*
	 * Wrapper right
	 */
	$wrapper = '';

	$wrapper .= $form->select('year', comContest::yearOptions(date('Y')-10, date('Y'), $project['year']), LANG_COM_CONTEST_PROJECT_YEAR.'<br />').'<br /><br />';

	$wrapper .= $form->text('compagny', $project['compagny'], LANG_COM_CONTEST_PROJECT_COMPAGNY.'<br />', '', 'size=50').'<br /><br />';
	$wrapper .= $form->textarea('address', $project['address'], LANG_COM_CONTEST_PROJECT_ADDRESS.'<br />', '', 'cols=40;rows=3').'<br /><br />';

	$wrapper .= $form->textarea('leaders', $project['leaders'], LANG_COM_CONTEST_PROJECT_LEADERS.'<br />').'<br /><br />';
	$wrapper .= $form->text('contributors', $project['contributors'], LANG_COM_CONTEST_PROJECT_CONTRIBUTORS.'<br />').'<br /><br />';

	if ($project['user_comment']) {
		$wrapper .= '<h3>'.LANG_COM_CONTEST_PROJECT_USER_COMMENT.' :</h3><p>'.nl2br($project['user_comment']).'</p>';
	}

	$wrapper .= '<h3>'.LANG_COM_CONTEST_SUMMARY_RESOURCES_LIST_PROVIDED.' :</h3>';
	if ($resource = $contest->getResource())
	{
		$wrapper .= '<p>';
		foreach($resource as $id => $info)
		{
			$info['file_exists'] or $info['file_name'] = '<span class="red">'.$info['file_name'].'</span>';

			$one_resource = "- {$info['title']} ({$info['file_name']})<br />";

			if (!$info['verified'] || !$info['published'])
			{
				$one_resource = '<span class="grey"><i>'.$one_resource.'</i></span>';
			}
			$wrapper .= $one_resource;
		}
		$wrapper .= "</p>\n";
	}
	else {
		$wrapper .= '<p class="comContest-no-resource-provided">'.LANG_COM_CONTEST_SUMMARY_NO_RESOURCES_PROVIDED.'</p>';
	}

	if ($project['user_validation'])
	{
		if ($project['missing_resource_list'])
		{
			$wrapper .=
				'<h3>'.LANG_COM_CONTEST_SUMMARY_RESOURCES_LIST_PENDING.' :</h3>'.
				'<p>'.
				nl2br($project['missing_resource_list']).'<br />'.
				$form->checkbox('missing_resource_received', 0, LANG_ADMIN_COM_CONTEST_PROJECT_UPD_MISSING_RESOURCE_RECEIVED).
				'</p>';
		}

		$wrapper .= '<p class="green">'.admin_replaceTrueByChecked(1, false).' '.LANG_COM_CONTEST_PROJECT_USER_VALIDATED_YES.'</p>';
	} else {
		$wrapper .= '<p class="red">'.admin_replaceTrueByChecked(0, false).' '.LANG_COM_CONTEST_PROJECT_USER_VALIDATED_NO.'</p>';
	}

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_CONTEST_PROJECT_UPD_FIELDSET_USER, 'right,49');

	/*
	 * Wrapper left
	 */
	$wrapper = '';

	$wrapper .= $form->select('config_year', admin_comContest_yearOptions(date('Y'), date('Y'), $project['config_year']), LANG_COM_CONTEST_PROJECT_CONFIG_YEAR.'<br />').'<br /><br />';

	$wrapper .= $form->text('title', $project['title'], LANG_COM_CONTEST_PROJECT_TITLE.'<br />', '', 'size=70').'<br /><br />';

	$wrapper .= $form->textarea('text_intro', $project['admin_intro'], LANG_COM_CONTEST_PROJECT_ADMIN_INTRO.'<br />', '', 'cols=55;rows=7').'<br /><br />';
	$wrapper .= $form->textarea('text_main', $project['admin_main'], LANG_COM_CONTEST_PROJECT_ADMIN_MAIN.'<br />').'<br /><br />';

	$my_CKEditor = new loadMyCkeditor();
	$wrapper .= $my_CKEditor->addName("text_intro")->addName("text_main");

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_CONTEST_PROJECT_UPD_FIELDSET_ADMIN, 'left,49');

	$html .= admin_fieldsetsWrapper('', '', 'clear');

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($submit)
{
	// Update the projects order
	$id = formManager_filter::arrayOnly($filter->requestName('project_order_')->getInteger());
	for ($i=0; $i<count($id); $i++) {
		if ($order = $filter->requestValue('project_order_'.$id[$i])->getInteger()) {
			$db->update("contest_project; project_order=$order; where: id=".$id[$i]);
		}
	}
}

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_PROJECT_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Select year
	if (count($config_year = admin_comContest_configYearOptions($session->get('config_year'))))
	{
		$html .= $form->select('config_year', $config_year, LANG_ADMIN_COM_CONTEST_CONFIG_YEAR);
		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_top').'<br /><br />';
	}

	if ($session->get('config_year'))
	{
		$project = $db->select('contest_project, id,user_id, compagny,title, user_validation,all_resource_provided, project_order(asc),admin_validation, where: config_year='.$session->get('config_year'));

		for ($i=0; $i<count($project); $i++)
		{
			// Username
			$user = new comUser_details($project[$i]['user_id']);
			$project[$i]['user_id'] = $user->get('username');

			// Status
			$project[$i]['user_validation'] = admin_replaceTrueByChecked($project[$i]['user_validation'], false);
			if (isset($project[$i]['all_resource_provided'])) {
				$project[$i]['all_resource_provided'] = admin_replaceTrueByChecked($project[$i]['all_resource_provided'], false);
			}

			// Published
			$project[$i]['admin_validation'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$project[$i]['id'].'">'.admin_replaceTrueByChecked($project[$i]['admin_validation']).'</a>';

			// Order
			$project[$i]['project_order'] != '999' or $project[$i]['project_order'] = ''; # This is the default value of the 'project_order' field
			$project[$i]['project_order'] = $form->text('project_order_'.$project[$i]['id'], $project[$i]['project_order'], '', '', 'size=2');

			// Delete and update buttons
			$delete[$i] = $form->submit('del_'.$project[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');
			$update[$i] = $form->submit('upd_'.$project[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');

			if (isset($new_project_id) && ($new_project_id == $project[$i]['id'])) {
				$project[$i]['title'] = "<span class=\"green\">{$project[$i]['title']}</span> $png_new";
			}

			$project[$i]['id'] = '<span class="grey">'.$project[$i]['id'].'</span>'; # Notice : 'id' info no more available !
		}

		$headers =
			array(
				'ID',
				LANG_COM_CONTEST_PROJECT_USER_ID,
				LANG_COM_CONTEST_PROJECT_COMPAGNY,
				LANG_COM_CONTEST_PROJECT_TITLE,
				LANG_COM_CONTEST_PROJECT_USER_VALIDATION,
				LANG_COM_CONTEST_PROJECT_ALL_RESOURCE_PROVIDED,
				LANG_COM_CONTEST_PROJECT_ORDER,
				LANG_COM_CONTEST_PROJECT_ADMIN_VALIDATION
			);
		$table = new tableManager($project, $headers);
		//$table->delCol(0); # Delete 'id' column

		if (count($project)) {
			$table->addCol($delete, 0);
			$table->addCol($update, 999);
		}
		$html .= $table->html();

		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit_bottom');
	}
	else {
		$html .= '<p class="grey"><i>'.LANG_ADMIN_COM_CONTEST_PROJECT_EMPTY.'</i></p>';
	}

	$html .= $form->submit('new', LANG_ADMIN_COM_CONTEST_PROJECT_BUTTON_NEW_SELECT);
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>