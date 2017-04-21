<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;

$current_period_id = wcal::findPeriodID(time());

// Init session
$session = new sessionManager(sessionManager::BACKEND, 'wcal_event');
$session->init('select_period', $current_period_id);

// In case the period have been deleted...
if (!$db->selectCount('wcal_period, where: id='.$session->get('select_period'))) {
	$session->set('select_period', $current_period_id);
}

$period_options = wcal::periodOptions(false);


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



// Update session
if ($submit)
{
	if ($select_period = $filter->requestValue('select_period')->getInteger())
	{
		$session->set('select_period', $select_period);
	}
}



// Case 'del'
if ($del)
{
	admin_informResult($db->delete("wcal_event; where: id=$del"));
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	// Get id
	$upd_id = $filter->requestValue('id')->getInteger();

	$category_id	= $filter->requestValue('category_id'	)->getInteger(1, LANG_ADMIN_COM_WCAL_EVENT_NOT_SELECTED, LANG_ADMIN_COM_WCAL_EVENT_CATEGORY);
	$wday			= $filter->requestValue('wday'			)->getInteger(1, LANG_ADMIN_COM_WCAL_EVENT_NOT_SELECTED, LANG_COM_WCAL_EVENT_WDAY);

	$time_begin		= $filter->requestValue('time_begin'	)->getNotEmpty(1, '', LANG_COM_WCAL_EVENT_TIME_BEGIN);
	$time_end		= $filter->requestValue('time_end'		)->getNotEmpty(1, '', LANG_COM_WCAL_EVENT_TIME_END	);

	if ($time_begin && $time_end)
	{
		($time_begin= wcal::timeHTMLtoDB($time_begin)) or $filter->set(false, 'time_begin'	)->getError(LANG_ADMIN_COM_WCAL_EVENT_INVALID_TIME_FORMAT, LANG_COM_WCAL_EVENT_TIME_BEGIN);
		($time_end	= wcal::timeHTMLtoDB($time_end	)) or $filter->set(false, 'time_end'	)->getError(LANG_ADMIN_COM_WCAL_EVENT_INVALID_TIME_FORMAT, LANG_COM_WCAL_EVENT_TIME_END);

		if ($time_begin && $time_end)
		{
			if ($time_begin < $time_end)
			{
				$time_begin	= $db->str_encode($time_begin);
				$time_end	= $db->str_encode($time_end);
			} else {
				$filter->set(false, 'time_begin')->getError(LANG_ADMIN_COM_WCAL_EVENT_TIMES_CHRONOLOGY_ERROR, LANG_COM_WCAL_EVENT_TIME_BEGIN.', '.LANG_COM_WCAL_EVENT_TIME_END);
			}
		}
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult(
			$db->update("wcal_event; category_id=$category_id, wday=$wday, time_begin=$time_begin, time_end=$time_end; where: id=$upd_id")
		);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_EVENT_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("wcal_event, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= '<p><strong>'.LANG_ADMIN_COM_WCAL_EVENT_PERIOD.' :</strong> '.$period_options[$session->get('select_period')].'</p>';

	$html .= $form->select('category_id', formManager::selectOption(wcal::categoryOptions(), $current['category_id']), LANG_ADMIN_COM_WCAL_EVENT_CATEGORY.'<br />').'<br /><br />';
	$html .= $form->select('wday', formManager::selectOption(wcal::wdayOptions(), $current['wday']), LANG_COM_WCAL_EVENT_WDAY.'<br />').'<br /><br />';

	$html .= $form->text('time_begin'	, wcal::timeDBtoHTML($current['time_begin'	]), LANG_COM_WCAL_EVENT_TIME_BEGIN	.'<br />', '', 'size=5').'<br /><br />';
	$html .= $form->text('time_end'		, wcal::timeDBtoHTML($current['time_end'	]), LANG_COM_WCAL_EVENT_TIME_END	.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$period_id = $session->get('select_period');

	$category_id	= $filter->requestValue('category_id'	)->getInteger(1, LANG_ADMIN_COM_WCAL_EVENT_NOT_SELECTED, LANG_ADMIN_COM_WCAL_EVENT_CATEGORY);
	$wday			= $filter->requestValue('wday'			)->getInteger(1, LANG_ADMIN_COM_WCAL_EVENT_NOT_SELECTED, LANG_COM_WCAL_EVENT_WDAY);

	$time_begin		= $filter->requestValue('time_begin'	)->getNotEmpty(1, '', LANG_COM_WCAL_EVENT_TIME_BEGIN);
	$time_end		= $filter->requestValue('time_end'		)->getNotEmpty(1, '', LANG_COM_WCAL_EVENT_TIME_END	);

	if ($time_begin && $time_end)
	{
		($time_begin= wcal::timeHTMLtoDB($time_begin)) or $filter->set(false, 'time_begin'	)->getError(LANG_ADMIN_COM_WCAL_EVENT_INVALID_TIME_FORMAT, LANG_COM_WCAL_EVENT_TIME_BEGIN);
		($time_end	= wcal::timeHTMLtoDB($time_end	)) or $filter->set(false, 'time_end'	)->getError(LANG_ADMIN_COM_WCAL_EVENT_INVALID_TIME_FORMAT, LANG_COM_WCAL_EVENT_TIME_END);

		if ($time_begin && $time_end)
		{
			if ($time_begin < $time_end)
			{
				$time_begin	= $db->str_encode($time_begin);
				$time_end	= $db->str_encode($time_end);
			} else {
				$filter->set(false, 'time_begin')->getError(LANG_ADMIN_COM_WCAL_EVENT_TIMES_CHRONOLOGY_ERROR, LANG_COM_WCAL_EVENT_TIME_BEGIN.', '.LANG_COM_WCAL_EVENT_TIME_END);
			}
		}
	}

	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult($db->insert("wcal_event; NULL, $period_id,$category_id, $wday, $time_begin,$time_end"));
	} else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_EVENT_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= '<p><strong>'.LANG_ADMIN_COM_WCAL_EVENT_PERIOD.' :</strong> '.$period_options[$session->get('select_period')].'</p>';

	$html .= $form->select('category_id', wcal::categoryOptions(), LANG_ADMIN_COM_WCAL_EVENT_CATEGORY.'<br />').'<br /><br />';
	$html .= $form->select('wday', wcal::wdayOptions(), LANG_COM_WCAL_EVENT_WDAY.'<br />').'<br /><br />';

	$html .= $form->text('time_begin'	, '', LANG_COM_WCAL_EVENT_TIME_BEGIN.'<br />', '', 'size=5').'<br /><br />';
	$html .= $form->text('time_end'		, '', LANG_COM_WCAL_EVENT_TIME_END	.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_EVENT_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Session form
	$html .= $form->select('select_period', formManager::selectOption($period_options, $session->get('select_period')), LANG_ADMIN_COM_WCAL_EVENT_SELECT_PERIOD);
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'<br /><br />';

	// Database
	$list = $db->select('wcal_event, id, wday(asc), time_begin(asc),time_end, category_id, where: period_id='.$session->get('select_period'));

	$wday_options	= wcal::wdayOptions();
	$cat_options	= wcal::categoryOptions();

	for ($i=0; $i<count($list); $i++)
	{
		$list[$i]['wday'		] = $wday_options	[$list[$i]['wday'		]]; # name of the day
		$list[$i]['category_id'	] = $cat_options	[$list[$i]['category_id']]; # name of the category

		$list[$i]['time_begin'	] = wcal::timeDBtoHTML($list[$i]['time_begin'	]);
		$list[$i]['time_end'	] = wcal::timeDBtoHTML($list[$i]['time_end'		]);

		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');
	}

	// Details about the selected period
	$period_details = $period_options[$session->get('select_period')];
	if (($session->get('select_period') == $current_period_id))
	{
		$period_details .= ' - <span class="green" style="font-size:83%;">'.LANG_ADMIN_COM_WCAL_EVENT_SPECIAL_PERIOD_ACTUALLY.'</span>';
	}
	$html .= "\n<h3>$period_details</h3>\n";

	// Table
	$table = new tableManager($list);
	$table->delCol(0); # Delete the 'id' column
	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(array('', LANG_COM_WCAL_EVENT_WDAY, LANG_ADMIN_COM_WCAL_EVENT_TIME_FROM, LANG_ADMIN_COM_WCAL_EVENT_TIME_TO, LANG_ADMIN_COM_WCAL_EVENT_CATEGORY, ''));
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>