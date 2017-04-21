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
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger();
	$upd = $filter->requestName ('upd_'	)->getInteger();
	$new = $filter->requestValue('new'	)->get();
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post');
$new_submit = formManager::isSubmitedForm('new_', 'post');



// Case 'del'
if ($del)
{
	if ($del != 1)
	{
		if (!$db->selectOne("wcal_event, id, where: period_id=$del", 'id'))
		{
			admin_informResult($db->delete("wcal_period; where: id=$del"));
		} else {
			admin_message(LANG_ADMIN_COM_WCAL_PERIOD_DEL_ERROR, 'error');
		}
	}
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	// Get id
	$upd_id = $filter->requestValue('id')->getInteger();

	$title = $db->str_encode($t = $filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_WCAL_PERIOD_TITLE));

	if ($t && $db->selectOne("wcal_period, id, where: title=$title AND, where: id!=$upd_id", 'id'))
	{
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_WCAL_PERIOD_DUPLICATE_TITLE, LANG_COM_WCAL_PERIOD_TITLE);
	}

	if ($upd_id != 1)
	{
		$wid_begin	= $filter->requestValue('wid_begin'	)->getFormatedDate(1, '', LANG_COM_WCAL_PERIOD_WID_BEGIN);
		$wid_end	= $filter->requestValue('wid_end'	)->getFormatedDate(1, '', LANG_COM_WCAL_PERIOD_WID_END	);

		if ($wid_begin && $wid_end)
		{
			$wid_begin	= wcal::getWeekID($wid_begin);
			$wid_end	= wcal::getWeekID($wid_end );

			if ($wid_begin > $wid_end)
			{
				$filter->set(false, 'wid_begin')->getError(LANG_ADMIN_COM_WCAL_PERIOD_WID_CHRONOLOGY_ERROR, LANG_COM_WCAL_PERIOD_WID_BEGIN.', '.LANG_COM_WCAL_PERIOD_WID_END);
			}
			else
			{
				if (admin_wcal::periodCovering($wid_begin, $wid_end, $upd_id))
				{
					$filter->set(false, 'wid_begin')->getError(LANG_ADMIN_COM_WCAL_PERIOD_WID_COVERING_ERROR, LANG_COM_WCAL_PERIOD_WID_BEGIN.', '.LANG_COM_WCAL_PERIOD_WID_END);
				}
			}
		}
	}
	else
	{
		$wid_begin	=
		$wid_end	= 'NULL';
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult($db->update("wcal_period; title=$title, wid_begin=$wid_begin, wid_end=$wid_end; where: id=$upd_id"));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_PERIOD_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("wcal_period, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= $form->text('title', $current['title'], LANG_COM_WCAL_PERIOD_TITLE);

	$fieldset  = '';
	if ($upd_id != 1)
	{
		$fieldset .= $form->text('wid_begin', getTime(wcal::getWeekTime($current['wid_begin']), 'time=no'), LANG_COM_WCAL_PERIOD_WID_BEGIN	, '', 'size=10').' &nbsp; ';
		$fieldset .= $form->text('wid_end'	, getTime(wcal::getWeekTime($current['wid_end'],1), 'time=no'), LANG_COM_WCAL_PERIOD_WID_END	, '', 'size=10').'<br /><br />';
		$fieldset .= "\n".'<script type="text/javascript">$(function(){$(\'#upd_wid_begin, #upd_wid_end\').datepicker({inline: true});});</script>'."\n";
	} else {
		$fieldset .= LANG_ADMIN_COM_WCAL_PERIOD_DEFAULT_TIPS;
	}
	$html .= '<br /><br />'.admin_fieldset($fieldset, LANG_ADMIN_COM_WCAL_PERIOD_FIELDSET_DATES);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$title = $db->str_encode($t = $filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_WCAL_PERIOD_TITLE));

	if ($t && $db->selectOne("wcal_period, id, where: title=$title", 'id'))
	{
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_WCAL_PERIOD_DUPLICATE_TITLE, LANG_COM_WCAL_PERIOD_TITLE);
	}

	$wid_begin	= $filter->requestValue('wid_begin'	)->getFormatedDate(1, '', LANG_COM_WCAL_PERIOD_WID_BEGIN);
	$wid_end	= $filter->requestValue('wid_end'	)->getFormatedDate(1, '', LANG_COM_WCAL_PERIOD_WID_END	);

	if ($wid_begin && $wid_end)
	{
		$wid_begin	= wcal::getWeekID($wid_begin);
		$wid_end	= wcal::getWeekID($wid_end	);

		if ($wid_begin > $wid_end)
		{
			$filter->set(false, 'wid_begin')->getError(LANG_ADMIN_COM_WCAL_PERIOD_WID_CHRONOLOGY_ERROR, LANG_COM_WCAL_PERIOD_WID_BEGIN.', '.LANG_COM_WCAL_PERIOD_WID_END);
		}
		else
		{
			if (admin_wcal::periodCovering($wid_begin, $wid_end))
			{
				$filter->set(false, 'wid_begin')->getError(LANG_ADMIN_COM_WCAL_PERIOD_WID_COVERING_ERROR, LANG_COM_WCAL_PERIOD_WID_BEGIN.', '.LANG_COM_WCAL_PERIOD_WID_END);
			}
		}
	}

	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult($result = $db->insert("wcal_period; NULL, $title,$wid_begin,$wid_end"));

		if ($result && $filter->requestValue('copy_default_period_events')->get())
		{
			$new_period_id = $db->insertID();
			$event = $db->select('wcal_event, *, where: period_id=1');
			foreach ($event as $e)
			{
				$db->insert(
						'wcal_event; '.
						'col: id, period_id, category_id, wday, time_begin, time_end; '.
						"NULL, $new_period_id, {$e['category_id']}, {$e['wday']}, {$e['time_begin']}, {$e['time_end']}"
				);
			}
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_PERIOD_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('title', '', LANG_COM_WCAL_PERIOD_TITLE);

	/*
	 * Notice : only for the default period, the dates are not available.
	 * And it can not append when creating a new period...
	 */
	$fieldset  = '';

	$fieldset .= $form->text('wid_begin', '', LANG_COM_WCAL_PERIOD_WID_BEGIN, '', 'size=10').' &nbsp; ';
	$fieldset .= $form->text('wid_end'	, '', LANG_COM_WCAL_PERIOD_WID_END	, '', 'size=10').'<br /><br />';
	$fieldset .= "\n".'<script type="text/javascript">$(function(){$(\'#new_wid_begin, #new_wid_end\').datepicker({inline: true});});</script>'."\n";

	$html .= '<br /><br />'.admin_fieldset($fieldset, LANG_ADMIN_COM_WCAL_PERIOD_FIELDSET_DATES);

	$html .= $form->checkbox('copy_default_period_events', 0, LANG_ADMIN_COM_WCAL_PERIOD_COPY_EVENT_FROM_DEFAULT_PERIOD).'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_PERIOD_TITLE_START.'</h2>';

	$html = '';

	// Database
	$list = $db->select('wcal_period, *, wid_begin(asc)');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($list); $i++)
	{
		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

		// Default period
		if ($list[$i]['id'] == 1)
		{
			$delete[$i] = ''; # Remove the delete button for the default period
			$list[$i]['title'] = "<strong>{$list[$i]['title']}</strong>";

			/*$list[$i]['wid_begin'	] =
			$list[$i]['wid_end'		] = '<div class="grey center">-</div>';*/
		}
		// Other period
		else
		{
			$time_begin	= wcal::getWeekTime($list[$i]['wid_begin']);
			$time_end	= wcal::getWeekTime($list[$i]['wid_end'],1);

			$list[$i]['wid_begin'	] = getTime($time_begin	, 'time=no;format=long');
			$list[$i]['wid_end'		] = getTime($time_end	, 'time=no;format=long');

			if ($time_end < time())
			{
				$span_l	= '<span class="grey"><em>';
				$span_r	= '</em></span>';

				// Put text in grey
				$list[$i]['title'		] = $span_l. $list[$i]['title'		] .$span_r;
				$list[$i]['wid_begin'	] = $span_l. $list[$i]['wid_begin'	] .$span_r;
				$list[$i]['wid_end'		] = $span_l. $list[$i]['wid_end'	] .$span_r;
			}
		}
	}

	// Table
	$table = new tableManager($list);
	$table->delCol(0); # Delete the 'id' column

	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(array('', LANG_COM_WCAL_PERIOD_TITLE, LANG_COM_WCAL_PERIOD_WID_BEGIN, LANG_COM_WCAL_PERIOD_WID_END, ''));
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>