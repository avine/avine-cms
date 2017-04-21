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
echo '<h1><span>'.LANG_COM_CONTEST_PAGE_CONTEST_YEAR.' '.$contest->getConfig('year').'</span><br />'.LANG_COM_CONTEST_PAGE_PROJECT_TITLE.'</h1>';


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

$upd		= formManager::isSubmitedForm('start_', 'post');
$upd_submit	= formManager::isSubmitedForm('upd_', 'post');

if (!$contest->getProject())
{
	$start_view	= false;
	$upd		= true;
}



// Case 'update'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	$project = array(
		'compagny'		=> $filter->requestValue('compagny'		)->getNotEmpty(1, LANG_COM_CONTEST_FIELD_ERROR_EMPTY, LANG_COM_CONTEST_PROJECT_COMPAGNY),
		'address'		=> $filter->requestValue('address'		)->getNotEmpty(1, LANG_COM_CONTEST_FIELD_ERROR_EMPTY, LANG_COM_CONTEST_PROJECT_ADDRESS),

		'leaders'		=> $filter->requestValue('leaders'		)->getNotEmpty(1, LANG_COM_CONTEST_FIELD_ERROR_EMPTY, LANG_COM_CONTEST_PROJECT_LEADERS),
		'contributors'	=> $filter->requestValue('contributors'	)->getNotEmpty(1, LANG_COM_CONTEST_FIELD_ERROR_EMPTY, LANG_COM_CONTEST_PROJECT_CONTRIBUTORS),

		'title'			=> $filter->requestValue('title'		)->getNotEmpty(1, LANG_COM_CONTEST_FIELD_ERROR_EMPTY, LANG_COM_CONTEST_PROJECT_TITLE),
		'year'			=> $filter->requestValue('year'			)->getInteger(1, LANG_COM_CONTEST_FIELD_ERROR_EMPTY, LANG_COM_CONTEST_PROJECT_YEAR),
		'user_comment'	=> $filter->requestValue('user_comment'	)->get()
	);

	// Security
	foreach($project as $key => $value) {
		$project[$key] = strip_tags($value);
	}

	if ($upd_submit_validation = $filter->validated())
	{
		$contest->updateProject($project);

		// Go to start view !
		$start_view	= true;
		$upd		= false;
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($upd || ($upd_submit && !$upd_submit_validation))
{
	$start_view	= false;

	$html = '';
	$form = new formManager();

	$html .= $form->form('post', $form->reloadPage(), 'upd_');

	($project = $contest->getProject()) or ($project = comContest::emptyProject());

	$html .= "\n<fieldset><legend class=\"comContest-legend-compagny\">".LANG_COM_CONTEST_PROJECT_FIELDSET_COMPAGNY."</legend><p>\n";
	$html .= $form->text('compagny', $project['compagny'], LANG_COM_CONTEST_PROJECT_COMPAGNY.'<br />', '', 'size=50').'<br /><br />';
	$html .= $form->textarea('address', $project['address'], LANG_COM_CONTEST_PROJECT_ADDRESS.'<br />', '', 'cols=40;rows=5').'<span class="comContest-tips">'.LANG_COM_CONTEST_PROJECT_FIELDSET_COMPAGNY_TIPS.'</span>';
	$html .= "\n</p></fieldset>\n";

	$html .= "\n<fieldset><legend class=\"comContest-legend-contributors\">".LANG_COM_CONTEST_PROJECT_FIELDSET_CONTRIBUTORS."</legend><p>\n";
	$html .= $form->textarea('leaders', $project['leaders'], LANG_COM_CONTEST_PROJECT_LEADERS.'<br />', '', 'cols=40;rows=5').'<span class="comContest-tips">'.LANG_COM_CONTEST_PROJECT_FIELDSET_CONTRIBUTORS_TIPS.'</span>'.'<br /><br />';
	$html .= $form->text('contributors', $project['contributors'], LANG_COM_CONTEST_PROJECT_CONTRIBUTORS.'<br />', '', 'size=50');
	$html .= "\n</p></fieldset>\n";

	$html .= "\n<fieldset><legend class=\"comContest-legend-project\">".LANG_COM_CONTEST_PROJECT_FIELDSET_PROJECT."</legend><p>\n";
	$html .= $form->text('title', $project['title'], LANG_COM_CONTEST_PROJECT_TITLE.'<br />', '', 'size=65').'<br /><br />';
	$html .= $form->select('year', comContest::yearOptions(date('Y')-10, date('Y'), $project['year']), LANG_COM_CONTEST_PROJECT_YEAR.'<br />').'<br /><br />';
	$html .= $form->textarea('user_comment', $project['user_comment'], LANG_COM_CONTEST_PROJECT_USER_COMMENT.'<br />', '', 'cols=55;rows=5').'<span class="comContest-tips">'.LANG_COM_CONTEST_PROJECT_FIELDSET_PROJECT_TIPS.'</span>';
	$html .= "\n</p></fieldset>\n";

	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT).'<br /><br />';
	$html .= $form->end();

	echo $html;
	echo '<p><a href="'.$form->reloadPage().'">'.LANG_COM_CONTEST_BUTTON_BACK.'</a></p>';
}



//////////////
// Start view

if ($start_view)
{
	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $form->reloadPage(), 'start_');

	$project = $contest->getProject();

	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_COMPAGNY		.'</h3>', $project['compagny']);
	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_ADDRESS		.'</h3>', nl2br($project['address']));

	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_LEADERS		.'</h3>', nl2br($project['leaders']));
	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_CONTRIBUTORS	.'</h3>', $project['contributors']);

	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_TITLE		.'</h3>', $project['title']);
	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_YEAR			.'</h3>', comContest::viewYear($project['year']));
	$summary[] = array('<h3>'.LANG_COM_CONTEST_PROJECT_USER_COMMENT	.'</h3>', nl2br($project['user_comment']));

	$table = new tableManager($summary, array(LANG_COM_CONTEST_PROJECT_HEADER_LABEL, LANG_COM_CONTEST_PROJECT_HEADER_INFO));
	$html .= $table->html();

	$html .= $form->submit('update', LANG_BUTTON_UPDATE);
	$html .= $form->end();
	echo $html;
}


endif;

?>