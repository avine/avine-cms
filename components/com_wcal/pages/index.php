<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo	'<div id="wcal-date-current" style="float:right;"><span class="wcal-date-current"><strong>'.
				LANG_COM_WCAL_INDEX_TODAY.' :</strong> '.getTime('', 'format=long;time=no')."</span></div>\n";

echo '<h1>'.LANG_COM_WCAL_INDEX_TITLE.'</h1>';


$wcal = new wcal();


if ($wcal->dateMaxReached())
{
	echo userMessage(LANG_COM_WCAL_INDEX_DATE_MAX_REACHED, 'warning');
}
else
{
	//////////////////
	// Default values

	// Time and period_id
	$current_time		= time();
	$current_period_id	= $wcal->findPeriodID($current_time);

	// Parameters of the $wcal->getWeekEvents($param, $param_type) method
	$param		= $current_time;
	$param_type	= 'time';

	// Selected period_id
	$period_id	= $current_period_id;



	///////////
	// Process

	if ($submit = formManager::isSubmitedForm('wcal_', 'post'))
	{
		$filter = new formManager_filter();
		if ($period_id = $filter->requestValue('select_period')->getInteger())
		{
			if ($period_id != $current_period_id)
			{
				// Parameters of the $wcal->getWeekEvents($param, $param_type) method
				$param		= $period_id;
				$param_type	= 'period_id';
			}
		}
	}



	//////////////
	// Start view

	$html = '';

	$form = new formManager(0,0);
	$html .= $form->form('post', formManager::reloadPage(), 'wcal_');

	$period_options = $wcal->periodOptions();
	if (count($period_options) >= 2)
	{
		$html .= $form->select('select_period', formManager::selectOption($wcal->periodOptions(), $period_id), LANG_COM_WCAL_INDEX_SELECT_PERIOD);
		$html .= $form->submit('', LANG_BUTTON_SUBMIT, 'submit').'<br /><br />'; # Submit-button : do not give a name attribute (required for javaScript autoSubmit form to work)
	}

	if ($param_type	== 'time')
	{
		$wday = wcal::wdayIndexInDatas($param);
	} else {
		$wday = false;
	}

	if ($period_id == $current_period_id)
	{
		$tips = '<span id="wcal-selected-date-yes">'.LANG_COM_WCAL_INDEX_MESSAGE_CURRENT_WEEK_YES.'</span>';
		$html .= "<h4>$tips". $wcal->getWeekRange($param) ."</h4>\n";
	} else {
		$tips = '<span id="wcal-selected-date-no">'.LANG_COM_WCAL_INDEX_MESSAGE_CURRENT_WEEK_NO.'</span>';
		$html .= "<h4>$tips". LANG_COM_WCAL_INDEX_VALID.' '.$wcal->periodDetails($period_id, 'validity_long') ."</h4>\n";

		// Hide the current-date info
		$html .= '<script type="text/javascript">$(document).ready(function(){$("#wcal-date-current").fadeOut("slow");});</script>';
	}

	$html .= $wcal->showWeekEvents($wcal->getWeekEvents($param, $param_type), false,false, $wday);

	$html .= $form->end();
	echo $html;
}

echo	'<p style="margin-top:2em;">'.
			'<a class="button" href="'.comMenu_rewrite('com=wcal&page=dedicate').'">'.LANG_COM_WCAL_GOTO_DEDICATE	.'</a> &nbsp; '.
			'<a class="button" href="'.comMenu_rewrite('com=wcal&page=category'	).'">'.LANG_COM_WCAL_GOTO_CATEGORY	.'</a> '.
		'</p>';

?>

<!-- wcal : autoSubmit 'select_period' -->
<script type="text/javascript">
$(document).ready(function(){
	$("input#wcal_submit").hide(); // hide submit button
	$("form#wcal_ select").change(function(){$("form#wcal_").submit();});
});
</script>
