
<!-- Handle the radio buttons to slideUp/slideDown the #comContest_js_slide -->
<script type="text/javascript">
$(document).ready(function(){
	// Init
	if (!$("input[id='confirm_all_resource_no']").is(":checked")){
		$("#comContest_js_slide").hide();
	}
	// On change
	$("input[name='all_resource_provided']").change(function(){
		if ($("input[id='confirm_all_resource_no']:checked").val()){
			$("#comContest_js_slide").slideDown();
		} else {
			$("#comContest_js_slide").slideUp();
		}
		$(this).blur();
	});
});
</script>

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
echo '<h1><span>'.LANG_COM_CONTEST_PAGE_CONTEST_YEAR.' '.$contest->getConfig('year').'</span><br />'.LANG_COM_CONTEST_PAGE_SUMMARY_TITLE.'</h1>';


// Contest status ?
if (!$contest->isFormAuthorized(&$message)) {
	echo $message;
}



if (!$contest->getProject()): # IF
	echo userMessage(LANG_COM_CONTEST_NO_PROJECT_ID, 'warning');
else:



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

if (formManager::isSubmitedForm('confirm_', 'post'))
{
	if ($all_resource_provided = $filter->requestValue('all_resource_provided')->get())
	{
		if ($all_resource_provided === 'no')
		{
			$missing_resource_list = strip_tags($filter->requestValue('missing_resource_list')->getNotEmpty(1, LANG_COM_CONTEST_SUMMARY_ERROR_MISSING_LIST));

			$values = 'all_resource_provided=0, missing_resource_list='.$db->str_encode($missing_resource_list);
		} else {
			$values = 'all_resource_provided=1';
		}
	}
	else {
		$filter->set(false)->getError(LANG_COM_CONTEST_SUMMARY_ERROR_RESOURCE_STATUS);
	}

	if ($filter->validated())
	{
		$project = $contest->getProject();
		$db->update("contest_project; $values, user_validation=1; where: id=".$project['id']);
		$contest = new comContest($project['id']); # Required : renew the object after the database update !

		// Inform
		$contest->isProjectValidated(&$message);
		echo $message;
	} else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view

$form = new formManager();
$html = $form->form('post', $form->reloadPage(), 'confirm_');



$html .= '<div id="comContest-summary">';



// Project
$project = $contest->getProject();

$html .=
	'<h3>'.LANG_COM_CONTEST_PROJECT_COMPAGNY	.' :</h3><p>'.$project['compagny'		]."</p>\n".
	'<h3>'.LANG_COM_CONTEST_PROJECT_ADDRESS		.' :</h3><p>'.nl2br($project['address'	])."</p>\n".

	'<h3>'.LANG_COM_CONTEST_PROJECT_LEADERS		.' :</h3><p>'.nl2br($project['leaders'	])."</p>\n".
	'<h3>'.LANG_COM_CONTEST_PROJECT_CONTRIBUTORS.' :</h3><p>'.$project['contributors'	]."</p>\n".

	'<h3>'.LANG_COM_CONTEST_PROJECT_TITLE		.' :</h3><p>'.$project['title'			]."</p>\n".
	'<h3>'.LANG_COM_CONTEST_PROJECT_YEAR		.' :</h3><p>'.comContest::viewYear($project['year'])."</p>\n";

if ($project['user_comment']) {
	$html .= '<h3>'.LANG_COM_CONTEST_PROJECT_USER_COMMENT.' :</h3><p>'.nl2br($project['user_comment'])."</p>\n";
}



// Resource
$resource = $contest->getResource();

$html .= '<h3>'.LANG_COM_CONTEST_SUMMARY_RESOURCES_LIST_PROVIDED.' :</h3>';

$html .= '<div>'."\n";

if ($resource)
{
	$html .= "<ul>\n";
	foreach($resource as $id => $info)
	{
		if (!$info['file_exists'])
		{
			$info['file_name'] = '<span class="comContest-file-missing">'.$info['file_name'].'</span>';
			$missing = true;
		}
		$html .= "<li><b>{$info['title']}</b> ({$info['file_name']})</li>\n";
	}
	$html .= "</ul>\n";
}
else {
	$html .= '<p class="comContest-no-resource-provided">'.LANG_COM_CONTEST_SUMMARY_NO_RESOURCES_PROVIDED.'</p>';
}

// Inform the user that some resources are missing !
if (isset($missing)) {
	$html .= userMessage(LANG_COM_CONTEST_RESOURCE_MISSING_RESOURCE, 'warning');
}

$html .= "\n</div>\n";



// List of missing resources
if ($contest->isProjectValidated(&$message) && !$project['all_resource_provided'])
{
	$html .= '<h3>'.LANG_COM_CONTEST_SUMMARY_RESOURCES_LIST_PENDING.' :</h3>';
	$html .= '<p>'.nl2br($project['missing_resource_list']).'</p>';
}



$html .= '</div>'; // end of #comContest-summary



// Validate the project !!!
if (!$contest->isDeadlineExpired(&$message) && !$contest->isProjectValidated(&$message))
{
	$html .= "\n<fieldset><legend class=\"comContest-legend-summary\">".LANG_COM_CONTEST_SUMMARY_FIELDSET."</legend>\n"; # Fieldset

	$html .= userMessage(LANG_COM_CONTEST_SUMMARY_USER_VALIDATION_TIPS, 'warning');

	// Confirmation
	$html .= '<p>';
	$html .= $form->radio('all_resource_provided', 'yes', LANG_COM_CONTEST_SUMMARY_ALL_RESOURCE_PROVIDED_YES, 'all_resource_yes').'<br />';
	$html .= $form->radio('all_resource_provided', 'no', LANG_COM_CONTEST_SUMMARY_ALL_RESOURCE_PROVIDED_NO, 'all_resource_no').'<br />';
	$html .= '</p>';

	$html .= '<p id="comContest_js_slide">'.$form->textarea('missing_resource_list', '', LANG_COM_CONTEST_PROJECT_MISSING_RESOURCE_LIST.'<br />', '', 'cols=50;rows=3').'</p>';

	$html .= '<p class="comContest-user-validation">';
	$html .= $form->submit('submit', LANG_COM_CONTEST_SUMMARY_USER_VALIDATION.' '.$project['config_year']);
	$html .= '</p>';

	$html .= "\n</fieldset>\n"; # Fieldset
}



$html .= $form->end();
echo $html;



endif; # ENDIF

?>