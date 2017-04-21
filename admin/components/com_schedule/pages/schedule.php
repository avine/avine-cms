<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'schedule');


// Instanciate
$schedule = new comSchedule_();



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_'		)->getInteger(); // (3)
	$upd = $filter->requestValue('update'	)->get(); // (2)
	$new = $filter->requestValue('new'		)->get(); // (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}



// (2) Case 'upd'
if ($del)
{
	$db->delete("schedule; where: id=$del");
}



// (2) Case 'upd'
if ($upd)
{
	// Global infos
	$filter->requestValue('row_key')->get() ? $row_key = true : $row_key = false;
	$cols_count = $filter->requestValue('cols_count')->getInteger();

	$list = $db->select('schedule, id, where: sheet_id='.$session->get('sheet_id_selector'));
	for ($i=0; $i<count($list); $i++)
	{
		$filter->reset();

		$id = $list[$i]['id'];

		// row_title
		if ($row_key) {
			$row_title = $filter->requestValue("row_title_$id")->get();
			$row_title_query = ' row_title='.$db->str_encode($row_title).', ';
		} else {
			$row_title_query = '';
		}

		// time
		$time = $schedule->timeToTimeStamp($filter->requestValue("time_$id")->get(), $session->get('sheet_id_selector'));
		if (!$time) {
			$filter->set(false, "time_$id")->getError('Invalid time');
		}

		// col_*
		$col_query = '';
		for ($j=0; $j<$cols_count; $j++)
		{
			if ($col = $schedule->scheduleHTMLtoDB( $filter->requestValue("col_{$j}_$id")->get() ))
			{
				$col_query .= ", col_$j=".$db->str_encode($col);
			} else {
				$col_query .= ", col_$j=NULL";
			}
		}

		if ($filter->validated()) {
			$db->update("schedule;$row_title_query time=$time{$col_query}; where: id=$id");
		}
	}
}



// (1) Case 'new'
if ($new)
{
	$db->insert('schedule; col: sheet_id; '.$session->get('sheet_id_selector'));
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_SCHEDULE_TITLE_START.'</h2>';

	if ($db->selectCount('schedule_sheet'))
	{
		$html = '';

		// Form
		$form = new formManager(0);
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

		// Session
		$session->init('sheet_id_selector', false);
		if ($submit) {
			$session->set('sheet_id_selector', $filter->requestValue('sheet_id_selector')->getInteger());
		}
		elseif ($session->get('sheet_id_selector') && !$db->selectCount('schedule_sheet, where: id='.$session->get('sheet_id_selector'))) {
			$session->reset(); # This sheet has gone !
		}
		$html .= $form->select('sheet_id_selector', $schedule->sheetsOptions($session->get('sheet_id_selector')));
		$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br /><br />'; // (0)

		$sheet_id_selector = $session->get('sheet_id_selector');
		if ($sheet_infos = $schedule->sheetInfos($sheet_id_selector))
		{
			// Global infos
			$html .= $form->hidden('cols_count', count($sheet_infos['col_list']));
			$html .= $form->hidden('row_key', $sheet_infos['row_key'] ? '1' : '0');

			$table_header = array('', 'ID'); # Header (delete button, id)

			// Add row_key ?
			if ($sheet_infos['row_key']) {
				$table_header[] = $sheet_infos['row_key']; # Header (row_key)
				$row_title_query = " row_title,";
			} else {
				$row_title_query = '';
			}

			// col_* query select
			$col_query = '';
			for ($i=0; $i<count($sheet_infos['col_list']); $i++) {
				$col_query .= "col_$i, ";
			}

			$table_header[] = LANG_ADMIN_COM_SCHEDULE_SCHEDULE_TIME; # Header (time)
			$table_header = array_merge($table_header, $sheet_infos['col_list']); # Header (col_0, col_1, ...)

			// Current schedules list
			$list = $db->select("schedule, id,$row_title_query time(asc),$col_query where: sheet_id=$sheet_id_selector");

			for ($i=0; $i<count($list); $i++)
			{
				$id = $list[$i]['id'];

				if ($sheet_infos['row_key'])
				{
?>
<script type="text/javascript">
	$(function() {
		var availableTags = [<?php echo '"'.implode('","', $sheet_infos['row_val']).'"'; ?>];
		$("#<?php echo "start_row_title_$id"; ?>").autocomplete({source: availableTags});
	});
</script>
<?php
					$list[$i]['row_title'] = $form->text("row_title_$id", $list[$i]['row_title'], '', '', 'size=default');
				}

				if ($sheet_infos['show_year']) {
					$html .= '<script type="text/javascript">$(function(){$(\'#start_'."time_$id".'\').datepicker({inline: true});});</script>'."\n";
				}
				$time = $schedule->timeStampToTime($list[$i]['time'], $sheet_id_selector);
				$list[$i]['time'] = $form->text("time_$id", $time, '', '', 'size=10;maxlength=10');

				for ($j=0; $j<count($sheet_infos['col_list']); $j++) {
					$list[$i]["col_$j"] = $form->text("col_{$j}_$id", $schedule->scheduleDBtoHTML($list[$i]["col_$j"]), '', '', 'size=5;maxlength=5');
				}

				$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
			}

			// Table
			$table = new tableManager($list);
			if (count($list)) {
				$table->addCol($delete, 0);
			}
			$table->header($table_header);
			$table->delCol(1); # Delete the 'id' column
			$html .= $table->html();

			$html .= $form->submit('update', LANG_ADMIN_COM_SCHEDULE_SCHEDULE_BUTTON_UPDATE).'&nbsp; &nbsp;'; // (2)
			$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)
		}

		$html .= $form->end(); // End of Form

		echo $html;
	}
	else {
		admin_message(LANG_ADMIN_COM_SCHEDULE_SCHEDULE_NO_SHEET_AVAILABLE, 'warning');
	}
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>