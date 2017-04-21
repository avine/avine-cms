<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/**
 * This script 'static_*.php' is a little different : some variables must be defined as 'global' !
 * Because, we need here to require a file wich his called 'volatile_*.php' (by using $com_gen->volatileFilePath() method).
 * And this required script should access to important variables of this main script.
 */
global	$filter;

global	# Posted forms possibilities (with prefix: '$volatile_*')
		$volatile_submit,
		$volatile_start_view;

global	$submit_validation; # Sub-Posted forms possibilities (without prefix)

global	$volatile_result;	# To tell back the main script, the result of $db process

global	$html;				# HTML ouput


// admin_comGeneric class object
global $com_gen;


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('config_', 'post'); // (1)



// (1) Case 'upd'
if ($submit)
{
	$submit_validation = true;
	$filter->reset();

	/**
	 * Global : 'config' table
	 */

	// Global
	$elements_per_step = $filter->requestValue('elements_per_step')->getInteger();
	$elements_per_row = $filter->requestValue('elements_per_row')->getInteger();
	$filter->requestValue('elements_wrapper')->get() ? $elements_wrapper = 1 : $elements_wrapper = 0;

	$elements_per_step === '0' ? $filter->set(false, 'elements_per_step')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_STEP_ERROR)) : '';
	$elements_per_row  === '0' ? $filter->set(false, 'elements_per_row'	)->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_ROW_ERROR )) : '';

	$subnodes_per_row = $filter->requestValue('subnodes_per_row')->getInteger();
	$filter->requestValue('subnodes_wrapper')->get() ? $subnodes_wrapper 	= 1 : $subnodes_wrapper = 0; # 0 = <table> ; 1 = <div>
	$filter->requestValue('subnodes_ontop'	)->get() ? $subnodes_ontop 		= 1 : $subnodes_ontop 	= 0;

	$subnodes_per_row  === '0' ? $filter->set(false, 'subnodes_per_row')->getError($com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_PER_ROW_ERROR )) : '';

	// Selectors
	$filter->requestValue('selector_node'		)->get() ? $selector_node 		= 1 : $selector_node 		= 0;
	$filter->requestValue('selector_node_rel'	)->get() ? $selector_node_rel 	= 1 : $selector_node_rel 	= 0;
	$filter->requestValue('selector_archive'	)->get() ? $selector_archive 	= 1 : $selector_archive 	= 0;

	// Show ...
	$filter->requestValue('show_date_creation'	)->get() ? $show_date_creation 	= 1 : $show_date_creation 	= 0;
	$filter->requestValue('show_date_modified'	)->get() ? $show_date_modified 	= 1 : $show_date_modified 	= 0;
	$filter->requestValue('show_author_id'		)->get() ? $show_author_id 		= 1 : $show_author_id 		= 0;
	$filter->requestValue('show_hits'			)->get() ? $show_hits 			= 1 : $show_hits 			= 0;

	// Debug ...
	$filter->requestValue('debug')->get() ? $debug = 1 : $debug = 0;

	/**
	 *  Specific : 'config_item' table
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_config.php', $volatile_submit);
	if (!$file_exists) {
		$volatile_result = true;
	}
	/* End of : Specific */

	// Database Process
	if ($submit_validation = $filter->validated())
	{
		$result =
			$db->update(
					$com_gen->getTablePrefix()."config; ".
					"elements_per_step=$elements_per_step, elements_per_row=$elements_per_row, elements_wrapper=$elements_wrapper, ".
					"subnodes_per_row=$subnodes_per_row, subnodes_wrapper=$subnodes_wrapper, subnodes_ontop=$subnodes_ontop, ".
					"show_date_creation=$show_date_creation, show_date_modified=$show_date_modified, show_author_id=$show_author_id, show_hits=$show_hits, ".
					"selector_node=$selector_node, selector_node_rel=$selector_node_rel, selector_archive=$selector_archive, ".
					"debug=$debug"
			);

		admin_informResult($result && $volatile_result);
	}
	else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_GENERIC_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Get config
	$config = $db->selectOne($com_gen->getTablePrefix().'config, *');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'config_');

	$wrapper = '';

	/**
	 * Fieldset - Global : Status
	 */
	$fieldset  = '';

	// Debug mode
	$fieldset .= $form->checkbox('debug', $config['debug'], LANG_ADMIN_COM_GENERIC_CONFIG_DEBUG_FIELD);
	if ($config['debug'])
	{
		$fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_DEBUG_Y;
	}  else {
		$fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_DEBUG_N;
	}
	$fieldset .= '<br /><br />';

	// Levels_name : Nodes (readonly)
	$fieldset .= '<h3>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_LEVELS_NAME_NODE).'</h3>';
	$fieldset .= '<p>'.$com_gen->getComNodeName().'</p>';

	// Levels_name : Elements (readonly)
	$fieldset .= '<h3>'.$com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_LEVELS_NAME_ELEMENT).'</h3>';
	$fieldset .= '<p>'.$com_gen->getComElementName().'</p>';

	$wrapper .= admin_fieldset($fieldset, LANG_ADMIN_COM_GENERIC_CONFIG_STATUS_FIELDSET);

	/** 
	 * Fieldset - Global : Component parameters
	 */
	$fieldset = '';

	// Configuration : Elements
	$fieldset .= $form->text('elements_per_step', $config['elements_per_step'], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_STEP, '', 'size=2;wrapper=div.label-fixed');
	$fieldset .= $form->text('elements_per_row' , $config['elements_per_row' ], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEM_PER_ROW , '', 'size=2;wrapper=div.label-fixed');
	$fieldset .= $form->checkbox('elements_wrapper', $config['elements_wrapper'], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_ELEMENTS_WRAPPER);
	$config['elements_wrapper'] ? $fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_WRAPPER_1.'<br />' : $fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_WRAPPER_0.'<br />';

	// Configuration : Subnodes
	$fieldset .= $com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES);
	$fieldset .= $form->text('subnodes_per_row', $config['subnodes_per_row'], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_PER_ROW, '', 'size=2;wrapper=div.label-fixed');

	$fieldset .= $form->checkbox('subnodes_wrapper', $config['subnodes_wrapper'], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_WRAPPER);
	$config['subnodes_wrapper'] ? $fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_WRAPPER_1.'<br />' : $fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_WRAPPER_0.'<br />';

	$fieldset .= $form->checkbox('subnodes_ontop', $config['subnodes_ontop'], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_ONTOP);
	$config['subnodes_ontop'] ? $fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_ONTOP_Y.'<br />' : $fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SUBNODES_ONTOP_N.'<br />';

	// Configuration : Selectors
	$fieldset .= LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTORS;
	$fieldset .= $form->checkbox('selector_node'	, $config['selector_node'    ], $com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTOR_NODE)).'&nbsp; &nbsp;';
	$fieldset .= $form->checkbox('selector_node_rel', $config['selector_node_rel'], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTOR_NODE_RELATIVE).'<br />';
	$fieldset .= $form->checkbox('selector_archive'	, $config['selector_archive' ], LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_SELECTOR_ARCHIVE);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_GLOBAL_FIELDSET));  

	/**
	 * Fieldset - Global : Default values
	 */
	$fieldset = '';

	$fieldset .= $form->checkbox('show_date_creation'	, $config['show_date_creation']	, LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION).'<br />';
	$fieldset .= $form->checkbox('show_date_modified'	, $config['show_date_modified']	, LANG_ADMIN_COM_GENERIC_SHOW_DATE_MODIFIED).'<br />';
	$fieldset .= $form->checkbox('show_author_id'		, $config['show_author_id']		, LANG_ADMIN_COM_GENERIC_SHOW_AUTHOR_ID).'<br />';
	$fieldset .= $form->checkbox('show_hits'			, $config['show_hits']			, LANG_ADMIN_COM_GENERIC_SHOW_HITS);

	$wrapper .= admin_fieldset($fieldset, $com_gen->translate(LANG_ADMIN_COM_GENERIC_CONFIG_DEFAULT_VALUE_FIELDSET));  

	$html .= admin_fieldsetsWrapper($wrapper, LANG_ADMIN_COM_GENERIC_WRAPPER_GENERAL, 'right,49');

	/**
	 * Fieldset - Specific
	 */
	$file_exists = $com_gen->volatileFilePath('volatile_config.php', $volatile_start_view);
	if (!$file_exists) {
		$html .= admin_fieldsetsWrapper(LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC_EMPTY, LANG_ADMIN_COM_GENERIC_WRAPPER_SPECIFIC, 'left,49');
	}
	/* End of : Specific */

	$html .= admin_fieldsetsWrapper('', '', 'clear');

	$html .= '<br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br />'; // (1)

	$html .= $form->end(); // End of Form
  
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>