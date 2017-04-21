<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


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



// (3) Case 'del'
if ($del)
{
	if (!$db->selectCount("schedule_sheet, where: tmpl_id=$del"))
	{
		admin_informResult($db->delete("schedule_tmpl; where: id=$del"));
	} else {
		admin_message(LANG_ADMIN_COM_SCHEDULE_TMPL_DEL_ERROR, 'warning');
	}
}



// (1) Case 'upd'
if ($upd_submit)
{
	$filter->reset();

	// upd_id
	$upd_id = $filter->requestValue('upd_id')->getInteger();

	// name
	$name = $filter->requestValue('name')->getNotEmpty(1, '', LANG_ADMIN_COM_SCHEDULE_TMPL_NAME);
	$current_name = $filter->requestValue('current_name')->getNotEmpty();
	if ( ($name !== $current_name) && ($db->selectCount('schedule_tmpl, where: name='.$db->str_encode($name))) ) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_SCHEDULE_TMPL_NAME_DUPLICATE, LANG_ADMIN_COM_SCHEDULE_TMPL_NAME);
	}

	// row_key, row_val
	$row_key = $filter->requestValue('row_key')->get();
	$row_val = $schedule->cleanRowValList( $filter->requestValue('row_val')->get() );

	// show_year
	$filter->requestValue('show_year')->get() ? $show_year = '1' : $show_year = '0';

	// col_*
	$col_list = array();
	$i = 0;
	while($i < $schedule->getColMax()) {
		if ($col = $filter->requestValue("col_$i")->get()) {
			$col_list[] = $col;
		}
		$i++;
	}
	if (!count($col_list)) {
		$filter->set(false)->getError(LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_ERROR);
	}

	if ($upd_submit_validation = $filter->validated())
	{
		$col_query = $schedule->prepareTmplColQueryUpdate($col_list);
		admin_informResult(
			$db->update(
				'schedule_tmpl; '.
				'name='		.$db->str_encode($name	).', '.
				'row_key='	.$db->str_encode($row_key).', '.
				'row_val='	.$db->str_encode($row_val).', '.
				"show_year=$show_year, $col_query; where: id=$upd_id"
			)
		);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($upd || ($upd_submit && !$upd_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_TMPL_TITLE_UPD.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	// upd_id
	$upd ? $upd_id = $upd : '';
	$tmpl = $db->selectOne("schedule_tmpl, *, where: id=$upd_id");
	$html .= $form->hidden('upd_id', $upd_id);

	// name, current_name, show_year
	$html .= $form->text('name', $tmpl['name'], LANG_ADMIN_COM_SCHEDULE_TMPL_NAME).'&nbsp; &nbsp;';
	$html .= $form->hidden('current_name', $tmpl['name']);
	$html .= $form->checkbox('show_year', $tmpl['show_year'], LANG_ADMIN_COM_SCHEDULE_TMPL_SHOW_YEAR).'<br /><br />';

	// row_key, row_val
	$fieldset = '<p>'.LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_TIPS."</p>\n";
	$fieldset .= $form->text	('row_key', $tmpl['row_key'], LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_KEY.'<br />').'<br /><br />';
	$fieldset .= $form->textarea('row_val', $tmpl['row_val'], LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_VAL.'<br />').'<br /><span class="grey">'.LANG_ADMIN_COM_SCHEDULE_SHEET_ROW_VAL_TIPS.'</span>';
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_FIELDSET);

	// col_*
	$fieldset = '<p>'.LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_TIPS."</p>\n";
	for ($i=0; $i<$schedule->getColMax(); $i++) {
		$fieldset .= '<span class="grey">'.sprintf('%02s', $i+1).'.</span> '.$form->text("col_$i", $tmpl["col_$i"], '', '', 'size=50')."<br />\n";
	}
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_FIELDSET);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$filter->reset();

	// name
	$name = $filter->requestValue('name')->getNotEmpty(1, '', LANG_ADMIN_COM_SCHEDULE_TMPL_NAME);
	if ($db->selectCount('schedule_tmpl, where: name='.$db->str_encode($name))) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_SCHEDULE_TMPL_NAME_DUPLICATE, LANG_ADMIN_COM_SCHEDULE_TMPL_NAME);
	}

	// row_key, row_val
	$row_key = $filter->requestValue('row_key')->get();
	$row_val = $schedule->cleanRowValList( $filter->requestValue('row_val')->get() );

	// show_year
	$filter->requestValue('show_year')->get() ? $show_year = '1' : $show_year = '0';

	// col_*
	$col_list = array();
	$i = 0;
	while($i < $schedule->getColMax()) {
		if ($col = $filter->requestValue("col_$i")->get()) {
			$col_list[] = $col;
		}
		$i++;
	}
	if (!count($col_list)) {
		$filter->set(false)->getError(LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_ERROR);
	}

	if ($new_submit_validation = $filter->validated())
	{
		$col_query = $schedule->prepareTmplColQueryInsert($col_list);
		admin_informResult(
			$db->insert(
				'schedule_tmpl; NULL, '.
				$db->str_encode($name	).', '.
				$db->str_encode($row_key).', '.             
				$db->str_encode($row_val).', '. 
				"$show_year, $col_query"
			)
		);
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new || ($new_submit && !$new_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_TMPL_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	// name, show_year
	$html .= $form->text('name', '', LANG_ADMIN_COM_SCHEDULE_TMPL_NAME).'&nbsp; &nbsp;';
	$html .= $form->checkbox('show_year', '', LANG_ADMIN_COM_SCHEDULE_TMPL_SHOW_YEAR).'<br /><br />';

	// row_key, row_val
	$fieldset = '<p>'.LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_TIPS."</p>\n";
	$fieldset .= $form->text	('row_key', '', LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_KEY.'<br />').'<br /><br />';
	$fieldset .= $form->textarea('row_val', '', LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_VAL.'<br />').'<br /><span class="grey">'.LANG_ADMIN_COM_SCHEDULE_SHEET_ROW_VAL_TIPS.'</span>';
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_FIELDSET);

	// col_*
	$fieldset = '<p>'.LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_TIPS."</p>\n";
	for ($i=0; $i<$schedule->getColMax(); $i++) {
		$fieldset .= '<span class="grey">'.sprintf('%02s', $i+1).'.</span> '.$form->text("col_$i", '', '', '', 'size=50')."<br />\n";
	}
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS_FIELDSET);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_SCHEDULE_TMPL_TITLE_START.'</h2>';

	$html = '';

	// Database
	$tmpl = $db->select('schedule_tmpl, id, name(asc), row_key');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($tmpl); $i++)
	{
		$tmpl[$i]['col_n'] = implode( '<br />', $schedule->getTmplColList($tmpl[$i]['id']) );

		$update[$i] = $form->submit('upd_'.$tmpl[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$tmpl[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
	}

	// Table
	$table = new tableManager($tmpl);
	if (count($tmpl)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(
		array(
			'',
			'ID',
			LANG_ADMIN_COM_SCHEDULE_TMPL_NAME,
			LANG_ADMIN_COM_SCHEDULE_TMPL_ROW_KEY,
			LANG_ADMIN_COM_SCHEDULE_TMPL_COLUMNS,
			''
		)
	);
	$table->delCol(1); # Delete the 'id' column
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)
	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>