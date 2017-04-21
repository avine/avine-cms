<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Instanciate Book
$addrbook = new comAddrbook('addrbook_');



// External configuration
global $addrbook_fixed_options;
for ($i=0; $i<count($addrbook_fixed_options); $i++)
{
	$addrbook->fixedOption($addrbook_fixed_options[$i]);
}



// Default page title
if (!count($addrbook_fixed_options))
{
	$html = '<h1>'.LANG_ADDRBOOK_INDEX_TITLE."</h1>\n";
}



///////////
// Process

$filter = new formManager_filter();

// Process Book filters
if (formManager::isSubmitedForm('addrbook_'))
{
	// Keywords ?
	$addrbook->search($search = $filter->requestValue('search')->get());

	// Filters ?
	$addrbook->processFilters();
	if ($breadcrumb = $addrbook->optionsBreadcrumb())
	{
		$breadcrumb = "<h5 id=\"addrbook-filters-breadcrumb\">".LANG_ADDRBOOK_INDEX_FILTERS_BREADCRUMB."&nbsp; $breadcrumb</h5>";
	}
}



//////////////
// Start view

// Form : begin
$form = new formManager(1,0);
$html .= $form->form('post', formManager::reloadPage(), 'addrbook_');


// Options requested ?
if ($addrbook->getOptions() || (isset($search) && $search))
{
	$toggle_show	= ' show';
	$remove_filters	= ' <a class="addrbook-filter-remove" href="'.formManager::reloadPage().'">'.LANG_ADDRBOOK_INDEX_FILTERS_REMOVE.'</a>';
} else {
	$toggle_show	= '';
	$remove_filters	= '';
}


// Display Book filters
$html .=
	"<div class=\"toggle\" id=\"addrbook-all-filters\">\n".
		"<h3 class=\"toggle-title$toggle_show\">".LANG_ADDRBOOK_INDEX_FILTERS_TITLE."</h3>\n".
		"<div class=\"toggle-content\">\n".
			$addrbook->getAllFilters( array($form->text('search', '', LANG_ADDRBOOK_INDEX_KEYWORDS)) ).
			"\n<div class=\"addrbook-filter-submit\">".
				$form->submit('submit_filters', LANG_ADDRBOOK_INDEX_FILTERS_SUBMIT).
				$remove_filters.
			"</div>\n".
		"\n</div>".
	"\n</div>\n";


!isset($breadcrumb) or $html .= $breadcrumb;


// Display Book addresses
$html .= "\n<div id=\"addrbook-book-wrapper\">\n".$addrbook->getBook()."\n</div>\n";

// Display pagination
$html .= $addrbook->getSteps();

// Form : end
$html .= $form->end();
echo $html;

