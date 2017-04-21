<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



///////////
// Process

// Form process
$string = false;
if (
	formManager::isSubmitedForm('search_top_', 'get') ||
	formManager::isSubmitedForm('search_bot_', 'get') ||
	formManager::isSubmitedForm('search_mod_', 'get')	# Special : to integrate the form into a module
) {
	$filter = new formManager_filter();
	$filter->requestVariable('get');

	$string = $filter->requestValue('string')->getNotEmpty();
}


// Search forms (top & bottom)
$form_top = '<div class="comSearch_form hide-form-submit">'.comSearch_::form('search_top_').'</div>';
$form_bot = '<div class="comSearch_form hide-form-submit">'.comSearch_::form('search_bot_').'</div><br />';


// Search process
$results = '';
$stats = '';
if ($string)
{
	$com_search = new comSearch_(); # If the script terminates here, that means some of your contents contains a php fatal error !

	$results = $com_search->search($string);
	$results .= comSearch_::pagination('search_top_');

	$stats = $com_search->stats();
}


//////////////
// Start view

// Title
echo '<a name="search_goto_top"></a><h1>'.LANG_COM_SEARCH_INDEX_TITLE.'</h1>';

echo "$form_top{$stats}<hr />\n";

if ($results)
{
	echo $results;

	if ($com_search->success() && $com_search->matchesCount() > comSearch_::RESULTS_PER_STEP)
	{
		echo "\n<hr />$form_bot";

		// Goto top anchor
		#echo '<p><br /><a href="#search_goto_top">'.LANG_COM_SEARCH_INDEX_GO_TO_TOP.'</a></p>';
	}
}


?>