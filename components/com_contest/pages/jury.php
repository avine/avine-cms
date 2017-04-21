<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$tmpl_path = sitePath().'/components/com_contest/tmpl';

global $db;

$filter = new formManager_filter();
$filter->requestVariable('post');

$session = new sessionManager(sessionManager::FRONTEND, 'contest');



/*
 * Login/logout
 */

if (formManager::isSubmitedForm('contest_login_', 'post', false))
{
	if ($filter->requestValue('password')->getUserPass() == $db->selectOne('contest_config, jury_password', 'jury_password')) {
		$session->set('login', true);
	}
} elseif (formManager::isSubmitedForm('contest_logout_', 'post', false)) {
	$session->reset('login');
}

if (!$session->get('login'))
{
	$html = '<p>'.LANG_COM_CONTEST_JURY_LOGIN_TIPS.'</p>';
	$form = new formManager(0,0);
	$html .= $form->form('post', comMenu_rewrite('com=contest&amp;page=jury'), 'contest_login_');
	$html .= $form->password('password', '', LANG_COM_CONTEST_JURY_PASSWORD);
	$html .= $form->submit('submit', LANG_COM_USER_BUTTON_LOGIN);
	$html .= $form->end();
	echo "<div id=\"comContest-jury-login\">$html</div>";
} else {
	$html = '';
	$form = new formManager(0,0);
	$html .= $form->form('post', comMenu_rewrite('com=contest&amp;page=jury'), 'contest_logout_');
	$html .= $form->submit('submit', LANG_COM_USER_BUTTON_LOGOUT);
	$html .= $form->end();
	echo "<div id=\"comContest-jury-logout\">$html</div>";
}



if ($session->get('login')) :


/*
 * Projects
 */

if (!($project_id = $filter->requestValue('project', 'get')->getInteger()))
{
	$contest = new comContest();

	$config_year = $contest->getConfig('year');
	$project = $db->select("contest_project, id, project_order(asc), where: config_year=$config_year AND, where: admin_validation=1");

	// Page title
	echo '<h1>'.LANG_COM_CONTEST_JURY_CONTESTANTS." $config_year</h1>";

	for ($i=0; $i<count($project); $i++)
	{
		$contest = new comContest($project[$i]['id']);

		$project_addons =
			array(
				'href'	=> comMenu_rewrite('com=contest&amp;page=jury&amp;project='.$project[$i]['id'])
			);

		$template = new templateManager();
		echo $template
				->setTmplPath("$tmpl_path/project_intro.html")
				->setReplacements(array_merge($contest->getProject(), $project_addons))
				->process();
	}
}
else
{
	$contest = new comContest($project_id);

	// Page title
	echo '<h1>'.LANG_COM_CONTEST_JURY_CONTESTANTS.' '.$contest->getConfig('year').'</h1>';

	$project = $contest->getProject();
	$project['address'] = nl2br($project['address']);
	$project['leaders'] = nl2br($project['leaders']);
	$project['year'] = comContest::viewYear($project['year']);

	// Project
	$template = new templateManager();
	echo $template
			->setTmplPath("$tmpl_path/project_main.html")
			->setReplacements($project)
			->process();

	#echo '<h2>'.LANG_COM_CONTEST_JURY_RESOURCES.'</h2>';

	// Resource
	if ($resource = $contest->getResource(true))
	{
		echo '<hr class="comContest-resource-hr" />';

		foreach($resource as $id => $info)
		{
			if ($info['file_exists'])
			{
				if ($info['comment']) {
					echo "<div class=\"comContest-resource-comment\">{$info['comment']}</div>";
				}

				echo '<div class="comContest-resource">';
				$pathinfo = pathinfo($info['file_name']);
				switch($pathinfo['extension'])
				{
					case 'flv':
						$media = new mediasManager();
						echo $media->display(comContest::resourceHref($id), $info['title']);
						break;

					default:
						echo '<a href="'.comContest::resourceHref($id).'">'.$info['title'].'</a><br />';
						break;
				}
				echo '</div><hr class="comContest-resource-hr" />';
			}
		}
	}

	echo '<p class="content-read-more"><br /><a href="'.comMenu_rewrite('com=contest&amp;page=jury').'" class="pages-navigation">Retour</a></p><br />&nbsp;';
}


endif;


?>