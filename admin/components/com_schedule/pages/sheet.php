<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


# TODO - Pouvoir rajouter un lock dans schedule_sheet pour ne plus modifier la table schedule associée...


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Instanciate
$schedule = new comSchedule_();



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)

$submit = formManager::isSubmitedForm('start_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new'	)->get(); // (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// (4) Case 'publish_status'
if ($publish_status)
{
	if ($published = $db->select("schedule_sheet, published, where: id=$publish_status"))
	{
		$published[0]['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("schedule_sheet; published=$published; where: id=$publish_status");
	}
}



// (3) Case 'del'
if ($del)
{
	if (!$db->selectCount("schedule, where: sheet_id=$del"))
	{
		admin_informResult($db->delete("schedule_sheet; where: id=$del"));
	} else {
		admin_message(LANG_ADMIN_COM_SCHEDULE_SHEET_DEL_ERROR, 'error');
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$filter->reset();

	// upd_id
	$upd_id = $filter->requestValue('upd_id')->getInteger();

	// title
	$title = $filter->requestValue('title')->getNotEmpty(1, '', LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE);
	$title_current = $filter->requestValue('title_current')->getNotEmpty();
	if ( ($title !== $title_current) && ($db->selectCount('schedule_sheet, where: title='.$db->str_encode($title))) ) {
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_DUPLICATE, LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE);
	}

	// tmpl_id
	$tmpl_id	= $filter->requestValue('tmpl_id')->getInteger(1, LANG_ADMIN_COM_SCHEDULE_SHEET_ERROR_NO_TMPL_SELECTED);

	// header, footer
	$header = $filter->requestValue('header')->get();
	$footer = $filter->requestValue('footer')->get();

	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update(
			'schedule_sheet; '.
			'title='.$db->str_encode($title).', '.
			"tmpl_id=$tmpl_id, ".
			'header='.$db->str_encode($header).', '.
			'footer='.$db->str_encode($footer)."; where: id=$upd_id"
		);
		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($upd || ($upd_submit && !$upd_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_UPD.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	// upd_id
	$upd ? $upd_id = $upd : '';
	$sheet = $db->selectOne("schedule_sheet, *, where: id=$upd_id");
	$html .= $form->hidden('upd_id', $upd_id);

	// title, tmpl_id
	$fieldset = '';
	$fieldset .= $form->text('title', $sheet['title'], LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE.'<br />').'<br /><br />';
	$fieldset .= $form->hidden('title_current', $sheet['title']);
	$fieldset .= $form->select('tmpl_id', $schedule->getTmplOptions($sheet['tmpl_id']), LANG_ADMIN_COM_SCHEDULE_SHEET_TMPL_ID.'<br />').'<br /><br />';
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_SHEET_FIELDSET_1);

	// header, footer
	$fieldset = '';
	$fieldset .= $form->textarea('header', $sheet['header'], LANG_ADMIN_COM_SCHEDULE_SHEET_HEADER.'<br />', '', 'cols=100').'<br /><br />';
	$fieldset .= $form->textarea('footer', $sheet['footer'], LANG_ADMIN_COM_SCHEDULE_SHEET_FOOTER.'<br />', '', 'cols=100');
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_SHEET_FIELDSET_2);

	// Use HTML editor to edit content
	$my_CKEditor = new loadMyCkeditor();
	$html .= $my_CKEditor->addName("header")->addName("footer");

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$filter->reset();

	// title, tmpl_id, show_year
	$title		= $filter->requestValue('title')->getNotEmpty(1, '', LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE);
	$tmpl_id	= $filter->requestValue('tmpl_id')->getInteger(1, LANG_ADMIN_COM_SCHEDULE_SHEET_ERROR_NO_TMPL_SELECTED);

	// header, footer
	$header = $filter->requestValue('header')->get();
	$footer = $filter->requestValue('footer')->get();

	if ($new_submit_validation = $filter->validated())
	{
		$result = $db->insert(
			"schedule_sheet; NULL, ".
			$db->str_encode($title).', '.
			"$tmpl_id, 9999, 0, ".
			$db->str_encode($header).', '.
			$db->str_encode($footer)
		);
		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	// title, tmpl_id
	$fieldset = '';
	$fieldset .= $form->text('title', '', LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE.'<br />').'<br /><br />';
	$fieldset .= $form->select('tmpl_id', $schedule->getTmplOptions(), LANG_ADMIN_COM_SCHEDULE_SHEET_TMPL_ID.'<br />').'<br /><br />';
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_SHEET_FIELDSET_1);

	// header, footer
	$fieldset = '';
	$fieldset .= $form->textarea('header', '', LANG_ADMIN_COM_SCHEDULE_SHEET_HEADER.'<br />', '', 'cols=100').'<br /><br />';
	$fieldset .= $form->textarea('footer', '', LANG_ADMIN_COM_SCHEDULE_SHEET_FOOTER.'<br />', '', 'cols=100');
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_SHEET_FIELDSET_2);

	// Use HTML editor to edit content
	$my_CKEditor = new loadMyCkeditor();
	$html .= $my_CKEditor->addName("header")->addName("footer");

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



if ($submit)
{
	// Check for sheet_order update (0)
	$id = formManager_filter::arrayOnly($filter->requestName('order_')->getInteger(), false);
	for ($i=0; $i<count($id); $i++)
	{
		$order = $filter->requestValue('order_'.$id[$i])->getInteger();
		if ($order !== false) {
			$db->update("schedule_sheet; sheet_order=$order; where: id=".$id[$i]);
		}
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE_START.'</h2>';

	$html = '';

	// Database
	$sheet = $db->select('schedule_sheet, id, sheet_order(asc), title, tmpl_id, published');

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($sheet); $i++)
	{
		// Sheet order
		$sheet[$i]['sheet_order'] = $form->text('order_'.$sheet[$i]['id'], 2*$i+1, '', '', 'size=2');

		// Template name
		$sheet[$i]['tmpl_id'] = $schedule->getTmplName($sheet[$i]['tmpl_id']);

		// Published button
		$sheet[$i]['published'] =  '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$sheet[$i]['id'].'">'.admin_replaceTrueByChecked($sheet[$i]['published']).'</a>'; // (4)

		$update[$i] = $form->submit('upd_'.$sheet[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$sheet[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
	}

	// Table
	$table = new tableManager($sheet);
	if (count($sheet)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(
		array(
			'',
			'ID',
			LANG_ADMIN_COM_SCHEDULE_SHEET_ORDER,
			LANG_ADMIN_COM_SCHEDULE_SHEET_TITLE,
			LANG_ADMIN_COM_SCHEDULE_SHEET_TMPL_ID,
			LANG_ADMIN_COM_SCHEDULE_SHEET_PUBLISHED,
			''
		)
	);
	$table->delCol(1); # Delete the 'id' column
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT).'&nbsp; &nbsp;'; // (0)
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>