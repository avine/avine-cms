<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<h1>'.LANG_COM_WCAL_DEDICATE_TITLE.'</h1>';


$wcal = new wcal();
$wcal->donateMonitor();


global $db;

// Configuration
$start_view = true;


// Alias
$goto_add_dedicate		= '<a class="button" href="'.comMenu_rewrite('com=wcal&page=dedicate').'">'.LANG_COM_WCAL_DEDICATE_BUTTON_ADD_DEDICATE.'</a>';
$goto_summary			= '<a class="button" href="'.comMenu_rewrite('com=wcal&page=summary').'">'.LANG_COM_WCAL_DEDICATE_BUTTON_GO_TO_SUMMARY.'</a>';
$goto_summary_pending	= '<a class="button" href="'.comMenu_rewrite('com=wcal&page=summary').'">'.LANG_COM_WCAL_DEDICATE_BUTTON_GO_TO_SUMMARY_PENDING.'</a>';


///////////
// Process

// Posted form possibilities
$submit_start		= formManager::isSubmitedForm('start_', 'post'		);
$submit_dedicate	= formManager::isSubmitedForm('dedicate_', 'post'	);


$filter = new formManager_filter();
$filter->requestVariable('post');



// Update a recorded dedicate is requested !
if ($current_update = $wcal->dedicateSummaryUpdate())
{
	if (!$submit_dedicate)
	{
		if ($d = $db->selectOne("wcal_dedicate, *, where: id=$current_update"))
		{
			$current_date		= $d['event_date'	];
			$current_type_id	= $d['type_id'		];
			$current_comment	= $d['comment'		];

			$dedicate_type = $db->selectOne("wcal_dedicate_type, title, sample, where: id=$current_type_id");

			$display_dedicate_form	= true;
		}
	}
}



// Process "start"
if ($submit_start)
{
	$filter->reset();

	// Date
	if ($date = $filter->requestValue('date')->getFormatedDate(1, '', LANG_COM_WCAL_DEDICATE_SELECT_DATE))
	{
		if ($date < time() - 60*60*24) {
			$filter->set(false, 'date')->getError(LANG_COM_WCAL_DEDICATE_SELECT_DATE_ERROR1);
		}
		elseif ($wcal->dateMaxReached($date)) {
			$filter->set(false, 'date')->getError(LANG_COM_WCAL_DEDICATE_SELECT_DATE_ERROR2);
		}
	}

	// Dedicate type
	if ($type_id = $filter->requestValue('type_id')->getInteger(1, '', LANG_COM_WCAL_DEDICATE_SELECT_TYPE))
	{
		if (!($dedicate_type = $db->selectOne("wcal_dedicate_type, title, sample, where: id=$type_id"))) {
			$filter->set(false, 'type_id')->getError('Invalid dedicate_type_id');
		}
	}

	if ($filter->validated())
	{
		$current_date		= $date;
		$current_type_id	= $type_id;

		$display_dedicate_form = true;
	} else {
		echo $filter->errorMessage();
	}
}



// Process "dedicate"
if ($submit_dedicate)
{
	$filter->reset();
	$submit_dedicate_validated = true;

	$current_date	 = $filter->requestValue('date')->getInteger();
	$current_type_id = $filter->requestValue('type_id')->getInteger();

	if (!$current_date || !($dedicate_type = $db->selectOne("wcal_dedicate_type, title, sample, where: id=$current_type_id"))) {
		die('current_date and/or current_type_id are missing');
	}

	if ($unique_id = formManager_filter::arrayOnly($filter->requestName(wcal::UID_INPUT_ID_)->get()))
	{
		$dedicate_details = wcal::dedicateDetailsFromUniqueID($unique_id);
		count($dedicate_details) or die('Unable to get the dedicate details');
	} else {
		$filter->set(false)->getError(LANG_COM_WCAL_DEDICATE_SUBMIT_ERROR_EVENT);
	}

	$comment = $db->str_encode(strip_tags($filter->requestValue('comment')->getNotEmpty(1, LANG_COM_WCAL_DEDICATE_SUBMIT_ERROR_COMMENT)));

	if ($submit_dedicate_validated = $filter->validated())
	{
		// Insert/update in 'wcal_dedicate' table
		if (!$current_update)
		{
			$result = $db->insert('wcal_dedicate; col: id,recording_date, event_date,type_id,comment; NULL,'.time().", $current_date,$current_type_id,$comment");
		} else {
			$result = $db->update("wcal_dedicate; comment=$comment; where: id=$current_update");
		}

		if ($result)
		{
			if (!$current_update)
			{
				$dedicate_id = $db->insertID();
				$wcal->sessionAddDedicateID($dedicate_id); # Make this dedicate associated to the user of this session
			} else {
				$dedicate_id = $current_update;
				$db->delete("wcal_dedicate_details; where: dedicate_id=$dedicate_id"); # Remove old records...
			}

			// Insert associated records in 'wcal_dedicate_details' table
			for ($i=0; $i<count($dedicate_details); $i++)
			{
				$db->insert(
						'wcal_dedicate_details; col: id,dedicate_id, node_id,elm_date_creation; '.
						"NULL,$dedicate_id, {$dedicate_details[$i]['node_id']},{$dedicate_details[$i]['elm_date_creation']}"
				);
			}

			// Update the handled designation (donate component)
			$wcal->manageDonateHandledDesignation();

			// Message
			echo '<h3>'.LANG_COM_WCAL_DEDICATE_RECORDED.'</h3>';
			echo "\n<p><br />$goto_add_dedicate &nbsp; $goto_summary</p>\n";

			$start_view = false;

			// Redirect to the summary (comment the following lines to disable this redirection)
			ob_end_clean();
			header('Location: '.comMenu_rewrite('com=wcal&page=summary'));
			die;
		}
		else {
			die('Error occurred while inserting dedication');
		}

	}
	else {
		echo $filter->errorMessage();
	}
}



if (isset($display_dedicate_form) || ($submit_dedicate && !$submit_dedicate_validated))
{
	$html = '';

	$form = new formManager();
	$html .= $form->form('post', formManager::reloadPage(), 'dedicate_');

	$html .= $form->hidden('date'	, $current_date		);
	$html .= $form->hidden('type_id', $current_type_id	);

	if ($current_update) {
		$html .= '<h3 id="wcal-dedicate-update">&middot; '.LANG_COM_WCAL_DEDICATE_UPDATE_TIPS." &middot;</h3>\n";
	}

	// Details about the selected date
	$html .=	'<div id="wcal-date-current" style="margin-bottom:2em;"><span class="wcal-date-current">'.
					"<strong>".LANG_COM_WCAL_DEDICATE_SELECT_DATE.' : </strong>'.getTime($current_date, 'time=no;format=long').
					' &nbsp;&middot;&nbsp; '.
					"<strong>".LANG_COM_WCAL_DEDICATE_SELECT_TYPE.' : </strong>'.$dedicate_type['title'].
					' &nbsp;&nbsp; <a class="wcal-date-current" href="'.comMenu_rewrite('com=wcal&page=dedicate').'">'.LANG_BUTTON_RESET.'</a>'.
				'</span></div>';

	/*
	 * Get the calendar
	 */

	// 1 day in seconds
	$day = 60*60*24;

	// Week range
	$week_id = wcal::getWeekID($current_date);
	$time_begin	= wcal::getWeekTime($week_id,0);
	$time_end	= wcal::getWeekTime($week_id,1);

	$datas = $wcal->getWeekEvents($current_date, 'time');

	$wday = wcal::wdayIndexInDatas($current_date);
	if ($wday == 0)
	{
		// Add 2 days before
		$datas_before = $wcal->getWeekEvents($current_date - 7*$day, 'time');
		$wcal->sliceDatas($datas_before, -2);

		$datas = $wcal->mergeDatas($datas_before, $datas);
		$wday += 2; # Update index

		$time_begin -= 2*$day;
	}
	elseif ($wday == 6)
	{
		// Add 2 days after
		$datas_next = $wcal->getWeekEvents($current_date + 7*$day, 'time');
		$wcal->sliceDatas($datas_next, +2);

		$datas = $wcal->mergeDatas($datas, $datas_next);
		$wday += 0; # No update necessary

		$time_end += 2*$day;
	}

	/*
	 * Fieldset : select events
	 */
	$img = '<img src="'.WEBSITE_PATH.'/components/com_wcal/images/fieldset-events.png" alt="" />';
	$html .= "\n\n\n<fieldset><legend>$img 1.".LANG_COM_WCAL_DEDICATE_FIELDSET_EVENTS."</legend>\n\n";

	if ($current_update) {
		$html .= '<div id="wcal-dedicate-update-warning">'.LANG_COM_WCAL_DEDICATE_UPDATE_TIPS_WARNING."</div>\n\n";
	}

	$wcal->addCheckboxesToDatas($datas, 'dedicate_', $wday);
	$html .= $wcal->showWeekEvents($datas, 'tmpl_event_dedicate.php', 0, $wday);

	$html .=
		'<div class="wcal-dedicate-info">'.LANG_COM_WCAL_CALENDAR.' '.
			LANG_COM_WCAL_FROM	.' '.getTime($time_begin, 'time=no;format=long').' '.
			LANG_COM_WCAL_TO	.' '.getTime($time_end	, 'time=no;format=long').
		'</div>';

	$html .= '<span class="wcal-check-all"></span>'; # jQuery : Check/uncheck all events
	$html .= '<br style="clear:right;" /><br />';

	$html .= '<div class="wcal-events-amounts"></div>';

	!LANG_COM_WCAL_DEDICATE_TIPS_EVENTS or $html .= "\n<p class=\"wcal-help\">".LANG_COM_WCAL_DEDICATE_TIPS_EVENTS."</p>";

	/*
	 * Fieldset : select comment
	 */
	$img = '<img src="'.WEBSITE_PATH.'/components/com_wcal/images/fieldset-comment.png" alt="" />';
	$html .= "\n\n\n</fieldset><fieldset><legend>$img 2.".LANG_COM_WCAL_DEDICATE_FIELDSET_COMMENT."</legend>\n\n";

	if (isset($current_comment))
	{
		$my_comment = $current_comment;
	} else {
		$my_comment = $dedicate_type['sample'];
	}
	$html .= $form->textarea('comment', $my_comment, '', '', 'cols=70;rows=5');
	if ($my_comment) {
		$html .= ' <span class="wcal-reset-sample"></span>'; # jQuery : Reset the sample
	}
	$html .= '<br /><br />';

	!LANG_COM_WCAL_DEDICATE_TIPS_COMMENT or $html .= "\n<p class=\"wcal-help\">".LANG_COM_WCAL_DEDICATE_TIPS_COMMENT."</p>";

	$html .= "\n\n\n</fieldset>\n\n\n"; # End of fieldsets

	$html .= $form->submit('submit', LANG_COM_WCAL_DEDICATE_SUBMIT);
	if ($wcal->sessionGetDedicateID()) {
		// Do not record/update the dedicate but go back to the summary !
		$html .= ' &nbsp; &nbsp; <a href="'.comMenu_rewrite('com=wcal&page=summary').'">'.LANG_COM_WCAL_DEDICATE_BUTTON_GO_TO_SUMMARY_PENDING.'</a>';
	}
	$html .= $form->end();
	echo $html;

	$start_view = false;

	// Amounts details
	$amounts = $wcal->amountEvents(99, true);
	$wcal->amountCurrency($currency_code, $currency_name);
	$amounts_array_js = 'var wcal_amounts = ['.implode(', ', $amounts).'];';
?>

<script type="text/javascript">//<![CDATA[
$(document).ready(function()
{
	// Display events amount
	$(".wcal-events-amounts").append('<'+'div id="wcal-events-amounts"><'+'/div>');
	function wcal_countChecked() {
		var n = $('input[name^="<?php echo wcal::UID_INPUT_ID_; ?>"]:checked').length;
		<?php echo $amounts_array_js; ?>
		$("#wcal-events-amounts").html('<'+'span>' + '<?php echo LANG_COM_WCAL_SUMMARY_AMOUNT; ?>' + ' : <'+'/span>' + wcal_amounts[n] + ' <?php echo $currency_name; ?>');
	}
	wcal_countChecked();
	$('input[name^="<?php echo wcal::UID_INPUT_ID_; ?>"]:checkbox').click(wcal_countChecked);

	// Check/uncheck all events
	$(".wcal-check-all").append('<'+'input type="checkbox" id="wcal-check-all" /> <'+'label for="wcal-check-all"><?php echo LANG_COM_WCAL_DEDICATE_CHECK_ALL_EVENTS; ?><'+'/label>');
	$("#wcal-check-all").click(function(){
		var checked_status = $(this).is(':checked');
		$('input[name^="<?php echo wcal::UID_INPUT_ID_; ?>"]').each(function(){
			this.checked = checked_status;
		});
		wcal_countChecked();
	});

	// Manage the sample
	$(".wcal-reset-sample").append('<'+'img src="<?php echo WEBSITE_PATH; ?>/components/com_wcal/images/refresh.png" alt="" />&nbsp;<'+'a href="#" id="wcal-reset-sample"><?php echo str_replace("'", "\'", LANG_COM_WCAL_DEDICATE_SAMPLE_BUTTON_MANAGE); ?><'+'/a>');
	$("#wcal-reset-sample").click(function(){
		var sample = '<?php echo str_replace("'", "\'", preg_replace("~(\n|\r)+~", '\n', $my_comment)); ?>';
		var comment = $("#dedicate_comment");
		if (!comment.val() || comment.val()==sample || confirm('<?php echo str_replace("'", "\'", LANG_COM_WCAL_DEDICATE_SAMPLE_CONFIRM_RESTORE); ?>')) {
			if (comment.val()==sample) {
				comment.val('');
			} else {
				comment.val(sample);
			}
		}
		$(this).blur();
		return false;
	});
});
//]]></script>

<?php
}


//////////////
// Start view

if ($start_view)
{
	$html = '';

	$form = new formManager();
	$html .= $form->form('post', formManager::reloadPage(), 'start_');

	$html .= "\n<fieldset><legend>".LANG_COM_WCAL_DEDICATE_FIELDSET_DATE."</legend>\n\n";

	// Date
	$html .= $form->text('date', '', LANG_COM_WCAL_DEDICATE_SELECT_DATE, '', 'size=10;wrapper=div.label-150px').'<br />';
	$html .= "\n".'<script type="text/javascript">$(function(){$(\'#start_date\').datepicker({inline: true});});</script>'."\n";

	// Dedicate type
	$html .= $form->select('type_id', wcal::dedicateTypeOptions(true, true), LANG_COM_WCAL_DEDICATE_SELECT_TYPE, '', 'wrapper=div.label-150px').'<br />';

	!LANG_COM_WCAL_DEDICATE_TIPS_DATE or $html .= "\n<p class=\"wcal-help\">".LANG_COM_WCAL_DEDICATE_TIPS_DATE."</p>";

	$html .= "</fieldset>\n\n";

	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT);

	$html .= $form->end();
	echo $html;

	if ($wcal->sessionGetDedicateID())
	{
		echo '<br /><div id="wcal-date-current">'.
				'<h3>'.LANG_COM_WCAL_DEDICATE_TIPS_PENDING."</h3>\n".
				"<br />$goto_summary_pending<br /><br />".'<img src="'.WEBSITE_PATH.'/components/com_wcal/images/checkout.png" alt="" /></div>';
	}
}


?>