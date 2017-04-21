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
$submit = formManager::isSubmitedForm('start_', 'post');


// case 'submit'
if ($submit)
{
	$filter->reset();

	$filter->requestValue('wday_sunday_7'			)->get() ? $wday_sunday_7			= '1' : $wday_sunday_7			= '0';
	$filter->requestValue('current_wday_sunday_7'	)->get() ? $current_wday_sunday_7	= '1' : $current_wday_sunday_7	= '0';

	$date_max = $filter->requestValue('date_max')->getFormatedDate(0, '', LANG_COM_WCAL_CONFIG_DATE_MAX);

	// Amounts
	$dedicate_amounts = $filter->requestValue('dedicate_amounts')->getNotEmpty();
	if ($dedicate_amounts !== false)
	{
		$dedicate_amounts = explode(';', str_replace(' ', '', $dedicate_amounts));
		for ($i=0; $i<count($dedicate_amounts); $i++)
		{
			if (!isset($once) && (!formManager_filter::isInteger($dedicate_amounts[$i]) && !formManager_filter::isReal($dedicate_amounts[$i])))
			{
				$filter->set(false, 'dedicate_amounts')->getError(LANG_ADMIN_COM_WCAL_CONFIG_AMOUNTS_ERROR_NOT_NUMBER, LANG_COM_WCAL_CONFIG_DEDICATE_AMOUNTS);
				$once = true;
			}
			elseif ($i==0 && $dedicate_amounts[$i]==0)
			{
				$filter->set(false, 'dedicate_amounts')->getError(LANG_ADMIN_COM_WCAL_CONFIG_AMOUNTS_ERROR_ZERO_BEGIN, LANG_COM_WCAL_CONFIG_DEDICATE_AMOUNTS);
			}
			else {
				$dedicate_amounts[$i] = money::convertAmountUnitsToCents($dedicate_amounts[$i]);
			}
		}
		$dedicate_amounts = implode(';', $dedicate_amounts);
	}
	# End of Amounts

	if ($submit_validation = $filter->validated())
	{
		if ($wday_sunday_7 != $current_wday_sunday_7)
		{
			// First update
			if ($result_1 = $db->update("wcal_config; wday_sunday_7=$wday_sunday_7"))
			{
				// Update the index of sunday
				if ($wday_sunday_7)
				{
					$db->update("wcal_event; wday=7; where: wday=0");
				} else {
					$db->update("wcal_event; wday=0; where: wday=7");
				}
			}
		}

		// Second update
		if ($date_max)
		{
			// Get now the $date_max of the 'end_period' (can't be done before the first update)
			$d = wcal::getDate($date_max);
			$date_max = wcal::mkTime($d['mon'], $d['mday'], $d['year']);
			$date_max = wcal::getWeekTime(wcal::getWeekID($date_max), 1);
		} else {
			$date_max = 'NULL';
		}
		$result_2 = $db->update("wcal_config; date_max=$date_max, dedicate_amounts=".$db->str_encode($dedicate_amounts));

		// Result 1 & 2
		admin_informResult( (!isset($result_1) || $result_1) && $result_2 );

		// Result 1 (tips)
		if (isset($result_1) && $result_1) {
			admin_message(LANG_ADMIN_COM_WCAL_CONFIG_WDAY_SUNDAY_7_UPDATED_TIPS, 'info');
		}
	}
	else {
		echo $filter->errorMessage();
	}
}



/*
 * First time ? Init the 'handle_designation_id'
 */

// Status of the conffiguration
if ($handle_designation_id = $db->selectOne('wcal_config, handle_designation_id', 'handle_designation_id'))
{
	if (!$db->selectCount("donate_designation, where: id=$handle_designation_id"))
	{
		$handle_designation_id = false; # Reset
	}
}

// Create or renew the configuration
if (!$handle_designation_id)
{
	if (!$db->selectCount('donate_designation, where: title='.$db->str_encode(LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_TITLE)))
	{
		$result =
			$db->insert(
				'donate_designation; NULL, '.
				$db->str_encode(LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_TITLE	).', '.
				$db->str_encode(LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_COMMENT	).', '.
				$db->str_encode(comMenu_rewrite('com=wcal&page=summary')			).', '.
				"NULL, NULL, 9999,0"
			);

		if ($result && $db->update('wcal_config; handle_designation_id='.$db->insertID()))
		{
			admin_message(LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_SUCCESS, 'info', '350');
		}
	}
	else {
		admin_message(LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_FAILURE.' "<em>'.LANG_ADMIN_COM_WCAL_CONFIG_HANDLE_DESIGN_ID_TITLE.'</em>".', 'error', '350');
		$db->update('wcal_config; handle_designation_id=NULL');
	}
}

# end of init



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// wcal config
	$wcal = new wcal();
	$config = $wcal->getConfig();

	// Form
	$form = new formManager( (isset($submit_validation) && !$submit_validation) ? 1 : 0 );
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	/*
	 * Fieldset calendar
	 */
	$fieldset = '';

	$fieldset .= $form->checkbox('wday_sunday_7', $config['wday_sunday_7'], LANG_COM_WCAL_CONFIG_WDAY_SUNDAY_7).'<br /><br />';
	$fieldset .= $form->hidden('current_wday_sunday_7', $config['wday_sunday_7']);

	$tips = ' <span class="grey">('.LANG_ADMIN_COM_WCAL_CONFIG_DATE_MAX_TIPS.')</span>';
	$fieldset .= $form->text('date_max', ($config['date_max'] ? getTime($config['date_max'], 'time=no') : ''), LANG_COM_WCAL_CONFIG_DATE_MAX."$tips<br />", '', 'size=10');
	if ($wcal->dateMaxReached())
	{
		$fieldset .= '<span class="red">'.LANG_ADMIN_COM_WCAL_CONFIG_DATE_MAX_EXPIRED.'</span>';
	}
	$fieldset .= "\n".'<script type="text/javascript">$(function(){$(\'#start_date_max\').datepicker({inline: true});});</script>'."\n";

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_WCAL_CONFIG_FIELDSET_CALENDAR);

	/*
	 * Fieldset amounts
	 */
	$fieldset = '';

	$amounts = explode(';', $config['dedicate_amounts']);
	for ($i=0; $i<count($amounts); $i++) {
		$amounts[$i] = money::convertAmountCentsToUnits($amounts[$i]);
	}
	$wcal->amountCurrency($currency_code, $currency_name);
	$fieldset .= $form->text('dedicate_amounts', implode(' ; ', $amounts), LANG_COM_WCAL_CONFIG_DEDICATE_AMOUNTS.'<br />', '', 'size=50')." <span class=\"grey\">$currency_name</span><br /><br />";

	$fieldset .= '<p>'.nl2br(LANG_ADMIN_COM_WCAL_CONFIG_FIELDSET_DEDICATE_TIPS).'</p>';

	if ($design_id = $config['handle_designation_id'])
	{
		$fieldset .=
			'<hr /><h3>'.LANG_COM_WCAL_CONFIG_HANDLE_DESIGNATION_ID." :</h3>\n".
			'<p><strong>'.LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE.' :</strong> "'.$db->selectOne("donate_designation, title, where: id=$design_id", 'title').'" '.
			"<span class=\"grey\"> (ID=$design_id)</span></p>\n";
	}

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_WCAL_CONFIG_FIELDSET_DEDICATE);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';


?>