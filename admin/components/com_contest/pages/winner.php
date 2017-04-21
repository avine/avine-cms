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

///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


$submit = formManager::isSubmitedForm('start_', 'post');
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger();
	$new = $filter->requestValue('new'	)->get();
} else {
	$del = false;
	$new = false;
}

$new_submit = formManager::isSubmitedForm('new_', 'post');



// Case 'del'
if ($del)
{
	admin_informResult( $db->delete("contest_winner; where: project_id=$del") );
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$project_id = $filter->requestValue('project_id')->getInteger();

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult( $db->insert("contest_winner; $project_id, 999") );
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_WINNER_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$current_winner = $db->select('contest_winner, [project_id]');

	$project = admin_comContest_projectOptions();

	// Keep only the none winners
	$options = array();
	foreach($project as $id => $title)
	{
		array_key_exists($id, $current_winner) or $options[$id] = $title;
	}
	(count($options) < 15) ? $size = count($options) : $size = 15;

	$html .= $form->select('project_id', $options, LANG_ADMIN_COM_CONTEST_WINNER_SELECT_PROJET.'<br />', '', "size=$size").'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($submit)
{
	// Update the winners order
	$id = formManager_filter::arrayOnly($filter->requestName('order_')->getInteger());
	for ($i=0; $i<count($id); $i++) {
		if ($order = $filter->requestValue('order_'.$id[$i])->getInteger()) {
			$db->update("contest_winner; winner_order=$order; where: project_id=".$id[$i]);
		}
	}
}

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTEST_WINNER_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	$list = array();
	if ($project = $db->select('contest_project, [config_year(desc)]'))
	{
		foreach ($project as $config_year => $v )
		{
			if ($winner = $db->select("contest_winner, project_id,winner_order(asc), join: project_id>; contest_project, compagny,title, join: <id, where: config_year=$config_year"))
			{
				for ($i=0; $i<count($winner); $i++)
				{
					$list[] = array(
						'delete'	=>	$form->submit('del_'.$winner[$i]['project_id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'),
						'year'		=>	$config_year,
						'compagny'	=>	$winner[$i]['compagny'],
						'title'		=>	$winner[$i]['title'],
						'order'		=>	$form->text('order_'.$winner[$i]['project_id'], 2*$i + 1, '', '', 'size=2')
					);
				}
			}
		}
	}

	$headers = array(
		'',
		LANG_COM_CONTEST_PROJECT_CONFIG_YEAR,
		LANG_COM_CONTEST_PROJECT_COMPAGNY,
		LANG_COM_CONTEST_PROJECT_TITLE,
		LANG_ADMIN_COM_CONTEST_WINNER_ORDER
	);

	$table = new tableManager($list, $headers);
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>
