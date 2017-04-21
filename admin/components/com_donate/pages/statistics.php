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

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('stats_');

if ($submit)
{
	$multi_design_posted = formManager_filter::arrayOnly($filter->requestValue('multi_design')->get());
} else {
	$multi_design_posted = false;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_STATS_TITLE_START.'</h2>';

	$html = '';

	// Create temporary table ('payment_temp') of payments details
	$payment_class = new comPayment_();  
	$payment_class->createPaymentsTemporaryTable();

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'stats_');


	// Multi-designations selector
	$multi_design_options = array();
	$multi_design_selected = array();
	$multi_design = $db->select("donate_designation, id,title,design_order(asc),published");
	for ($i=0; $i<count($multi_design); $i++)
	{
		$multi_design_options[$multi_design[$i]['id']] = $multi_design[$i]['title'];

		// Default selection
		$multi_design[$i]['published'] ? $multi_design_selected[] = $multi_design[$i]['id'] : '';
	}

	if ($multi_design_posted)
	{
		// Posted selection
		$multi_design_selected = $multi_design_posted;
	}

	$html .= $form->select('multi_design', $form->selectOption($multi_design_options, $multi_design_selected), LANG_ADMIN_COM_DONATE_STATS_DESIGNATIONS_FILTER.'<br />', '', 'multiple');
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br /><br /><br />';


	// Let's go !

	// $d = donations list
	$d =
		$db->select(
			"donate_details, designation_id(asc),amount,currency_code, join: donate_id>; ".
			"donate, id,payment_id, join: <id|payment_id> ;".
			"payment_temp, payment_date, where: payment_date IS NOT NULL AND, where: validated=1, join: <payment_id"
		);

	$designation  = $db->select('donate_designation, [id], title');
	$currencies_code = money::currencyCodeOptionsPlural();

	// $s = statistics
	$s = array();
	// $t = total amount
	$t = array();

	for ($i=0; $i<count($d); $i++)
	{
		if (in_array($d[$i]['designation_id'], $multi_design_selected))
		{
			$curren = $currencies_code[ $d[$i]['currency_code'] ];

			$design = $designation[ $d[$i]['designation_id'] ]['title']; # This code works because we have checked in designation.php that 2 designations can't have the same title !

			$amount = money::convertAmountCentsToUnits($d[$i]['amount']);

			// Init
			!isset($s[$curren][$design]	) ? $s[$curren][$design] 	= 0 : '';
			!isset($t[$curren]			) ? $t[$curren] 			= 0 : '';

			$s[$curren][$design] 	+= $amount;
			$t[$curren] 			+= $amount;
		}
	}

	reset($s);
	reset($t);
	while (list($curren, $stats) = each($s))
	{
		$simple_stats = new simpleStats($stats, $curren, LANG_ADMIN_COM_DONATE_STATS_IN.$curren);
		$html .= $simple_stats->htmlDiagramHorizontal();
	}

	$html .= $form->end(); // End of Form

	echo $html;
}


echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';


?>