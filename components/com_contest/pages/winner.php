<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$tmpl_path = sitePath().'/components/com_contest/tmpl';


global $db;

$filter = new formManager_filter();


if (!($project_id = $filter->requestValue('project', 'get')->getInteger()))
{
	if ($project = $db->select('contest_project, [config_year(desc)]'))
	{
		foreach ($project as $config_year => $v )
		{
			if ($winner = $db->select("contest_winner, project_id,winner_order(asc), join: project_id>; contest_project, join: <id, where: config_year=$config_year"))
			{
				// Page title
				echo '<h1>'.LANG_COM_CONTEST_WINNER_TITLE." $config_year</h1>";

				for ($i=0; $i<count($winner); $i++)
				{
					$contest = new comContest($winner[$i]['project_id']);

					$project_addons =
						array(
							'href'	=> comMenu_rewrite('com=contest&amp;page=winner&amp;project='.$winner[$i]['project_id'])
						);

					$template = new templateManager();
					echo $template
							->setTmplPath("$tmpl_path/project_intro_winner.html")
							->setReplacements(array_merge($contest->getProject(), $project_addons))
							->process();
				}
			}
		}
	}
}
else
{
	$contest = new comContest($project_id);

	// Page title
	echo '<h1>'.LANG_COM_CONTEST_WINNER_TITLE.' '.$contest->getConfig('year').'</h1>';

	$project = $contest->getProject();
	$project['address'] = nl2br($project['address']);
	$project['leaders'] = nl2br($project['leaders']);
	$project['year'] = comContest::viewYear($project['year']);

	// Project
	$template = new templateManager();
	echo $template
			->setTmplPath("$tmpl_path/project_main_winner.html")
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

	echo '<p class="content-read-more"><br /><a href="'.comMenu_rewrite('com=contest&amp;page=winner').'" class="pages-navigation">Retour</a></p><br />&nbsp;';
}


?>