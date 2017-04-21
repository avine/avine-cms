<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */

# TODO - Rajouter la possibilité d'importer un template (un peu comme le template par défaut)...

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Images Html
$png_new = '<img src="'.WEBSITE_PATH.'/admin/images/new.png" alt="new" />';


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



// (3) Case 'del'
if ($del)
{
	if (!$db->select("newsletter, id, where: tmpl_id=$del"))
	{
		admin_informResult( $db->delete("newsletter_tmpl; where: id=$del") );
	} else {
		admin_message(LANG_ADMIN_COM_NEWSLETTER_TMPL_DEL_WARNING, 'warning');
	}
}



// (1) Case 'new'
if ($new)
{
	$n = '';
	while ($db->selectOne("newsletter_tmpl, id, where: name=".$db->str_encode(LANG_ADMIN_PROCESS_NEW_DATA_DEFAULT_NAME.$n), 'id')) {
		$n++;
	}

	$x = 0;
	$id = array('NULL', '1');
	while ( ($x < count($id)) && (!isset($new_id)) )
	{
		if ($db->insert("newsletter_tmpl; col: id,name; {$id[$x]}, ".$db->str_encode(LANG_ADMIN_PROCESS_NEW_DATA_DEFAULT_NAME.$n)))
		{
			$new_id = $db->insertID();
	
			// Go now to the update form !
			$upd = $new_id; # Comment this line if you prefer to go back to the $start_view
		}
		$x++;
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	// Fields validation
	$upd_submit_validation = true;

	$filter->reset();

	// id
	$upd_id = $filter->requestValue('id')->getInteger();

	$name = $filter->requestValue('name')->getNotEmpty(1, '', LANG_ADMIN_COM_NEWSLETTER_TMPL_NAME);

	$header	= $filter->requestValue('header')->get();
	$footer	= $filter->requestValue('footer')->get();
	$item1	= $filter->requestValue('item1'	)->get();
	$item2	= $filter->requestValue('item2'	)->get();

	// No duplicate name allowed
	if ($name && $db->select("newsletter_tmpl, id, where: id!=$upd_id AND, where: name=".$db->str_encode($name))) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_NEWSLETTER_TMPL_ERROR_DUPLICATE_NAME);
	}

	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult(
			$db->update("newsletter_tmpl; "
				.'  name='		.$db->str_encode($name	)
				.', header='	.$db->str_encode($header)
				.', footer='	.$db->str_encode($footer)
				.', item1='		.$db->str_encode($item1	)
				.', item2='		.$db->str_encode($item2	)
				."; where: id=$upd_id"
			)
		);

		if ($filter->requestValue('record')->get()) {
			$upd_submit_validation = false; # Continue to update...
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_TMPL_TITLE_UPDATE.'</h2>';

	// Id
	if ($upd) {
		$upd_id = $upd;
	}

	if (isset($new_id) && $filter->requestValue('load_sample')->get())
	{
		$ftp = new ftpManager(sitePath().'/components/com_newsletter/tmpl/default/');
		$load_sample = explode ('<!-- SEPARATOR -->', $ftp->read('tmpl.html'));

		if (count($load_sample) == 4)
		{
			$db->update(
				'newsletter_tmpl; '
				.'  header='.$db->str_encode($load_sample[0])
				.', item1='	.$db->str_encode($load_sample[1])
				.', item2='	.$db->str_encode($load_sample[2])
				.', footer='.$db->str_encode($load_sample[3])
				."; where: id=$upd_id"
			);
		}
		else {
			trigger_error('Missing separator in "/components/com_newsletter/tmpl/default/tmpl.html" file');
		}
	}

	$current = $db->selectOne("newsletter_tmpl, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	// Name (remove default value when come from $new)
	$html .= $form->text('name', isset($new_id) ? '' : $current['name'], LANG_ADMIN_COM_NEWSLETTER_TMPL_NAME.'<br />', '', 'size=50').'<br /><br />';

	// Header, item1, item2, footer
	$html .= $form->textarea('header'	, $current['header'	], LANG_ADMIN_COM_NEWSLETTER_TMPL_HEADER.'<br />', '', 'cols=120;rows=10').admin_comNewsletter_tmpl::keywordsTips('header'	, $current['header'	]);
	$html .= $form->textarea('item1'	, $current['item1'	], LANG_ADMIN_COM_NEWSLETTER_TMPL_ITEM1	.'<br />', '', 'cols=120;rows=10').admin_comNewsletter_tmpl::keywordsTips('item'	, $current['item1'	]);
	$html .= $form->textarea('item2'	, $current['item2'	], LANG_ADMIN_COM_NEWSLETTER_TMPL_ITEM2	.'<br />', '', 'cols=120;rows=10').admin_comNewsletter_tmpl::keywordsTips('item'	, $current['item2'	]);
	$html .= $form->textarea('footer'	, $current['footer'	], LANG_ADMIN_COM_NEWSLETTER_TMPL_FOOTER.'<br />', '', 'cols=120;rows=10').admin_comNewsletter_tmpl::keywordsTips('footer'	, $current['footer'	]);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('record', LANG_ADMIN_BUTTON_RECORD);

	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_TMPL_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Database
	$tmpl = $db->select('newsletter_tmpl, id(desc),name');

	for ($i=0; $i<count($tmpl); $i++)
	{
		$update[$i] = $form->submit('upd_'.$tmpl[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$tmpl[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)

		if (isset($new_id) && $new_id == $tmpl[$i]['id']) {
			$delete[$i] = $png_new; # Overwrite the delete button
		}
	}

	// Table
	$table = new tableManager($tmpl);

	if (count($tmpl)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}

	$table->header(array('', 'ID', LANG_ADMIN_COM_NEWSLETTER_TMPL_NAME, ''));

	$table->delCol(1); # Delete the 'id' column
	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_COM_NEWSLETTER_TMPL_BUTTON_NEW);
	$html .= $form->checkbox('load_sample', '', LANG_ADMIN_COM_NEWSLETTER_TMPL_LOAD_SAMPLE);

	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>