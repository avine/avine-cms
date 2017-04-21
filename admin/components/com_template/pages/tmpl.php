<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Images Html
$png_new   = ' <img src="'.WEBSITE_PATH.'/admin/images/new.png" alt="new" />';



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
if ($submit = formManager::isSubmitedForm('tmpl_', 'post')) // (0)
{
	$upd 			= $filter->requestName ('update_'		)->getInteger();	// (3)
	$del 			= $filter->requestName ('delete_'		)->getInteger();	// (2)
	$submit_default = $filter->requestValue('submit_default')->get();			// (1)
} else {
	$upd 			= false;
	$del 			= false;
	$submit_default = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (3)



// (1) Case 'submit_default'
if ($submit_default)
{
	$filter->reset();

	$default_id = $filter->requestValue('default')->getInteger();

	if ($filter->validated())
	{
		$db->update('template; current=0');
		$result = $db->update("template; current=1; where: id=$default_id");
	
		admin_informResult($result);
	}
	else {
		admin_message(LANG_ADMIN_COM_TEMPLATE_DEFAULT_TMPL_NOT_SELECTED ,'error');
	}
}



// (2) Case 'delete'
if ($del)
{
	$db->delete("template; where: id=$del");
}



// (3) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	$upd_id		= $filter->requestValue('id')->getInteger(); # (*)
	$upd_name	= $filter->requestValue('name')->getID();
	$upd_desc	= $filter->requestValue('desc')->get();

	$upd_php	= $filter->requestValue('php')->get();
	$upd_css	= $filter->requestValue('css')->get();

	// Check the updated template name
	$ftp = new ftpManager(sitePath()."/templates/");
	$current_name = $db->selectOne("template, name, where: id=$upd_id", 'name');
	if ($upd_name && ($upd_name != $current_name) && $ftp->isDir($upd_name))
	{
		$filter->set($upd_name, 'name')->getError(LANG_ADMIN_COM_TEMPLATE_UPD_NAME_ERROR);
	}

	if ($upd_submit_validation = $filter->validated())
	{
		// Database process
		$result = $db->update('template; name='.$db->str_encode($upd_name).', comment='.$db->str_encode($upd_desc)."; where: id=$upd_id");
		admin_informResult($result);

		// FTP process
		if ($upd_name != $current_name)
		{
			$ftp->rename($current_name, $upd_name);
		}
		if ($ftp->isFile("$upd_name/index.php") && $ftp->isFile("$upd_name/index.css"))
		{
			$message = array();
			if (md5($upd_php) != md5($ftp->read("$upd_name/index.php"))) {
				if ($ftp->write("$upd_name/index.php", $upd_php)) {
					$message[] = 'index.php';
				}
			}
			if (md5($upd_css) != md5($ftp->read("$upd_name/index.css"))) {
				if ($ftp->write("$upd_name/index.css", $upd_css)) {
					$message[] = 'index.css';
				}
			}
			!count($message) or admin_message('<b>'.LANG_ADMIN_COM_TEMPLATE_MODIFIED_FILES.'</b>'.implode(', ', $message), 'info');
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if ( ($upd) || (($upd_submit) && (!$upd_submit_validation)) )
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_TEMPLATE_TITLE_UPDATE.'</h2>';

	// Id
	if ($upd)
	{
		$upd_id = $upd;
	} else {
		# $upd_id is already set (see before (*))
	}

	$html = '';

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	$name = $db->selectOne("template, name, comment, where: id=$upd_id");

	$html .= $form->hidden('id', $upd_id);
	$html .= $form->text('name', $name['name'	], LANG_ADMIN_COM_TEMPLATE_NAME).' &nbsp; &nbsp; &nbsp; ';
	$html .= $form->text('desc', $name['comment'], LANG_ADMIN_COM_TEMPLATE_DESC, '', 'maxlength=255').'<br /><br />';

	$ftp = new ftpManager(sitePath()."/templates/{$name['name']}/");
	$html .= $form->textarea('php', $ftp->read('index.php'), LANG_ADMIN_COM_TEMPLATE_PHP.' (index.php)<br />', '', 'cols=115;rows=20').'<br /><br />';
	$html .= $form->textarea('css', $ftp->read('index.css'), LANG_ADMIN_COM_TEMPLATE_CSS.' (index.css)<br />', '', 'cols=115;rows=20').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();

	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_TEMPLATE_TITLE_START.'</h2>';

	$html = '';

	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'tmpl_'); # Form begin

	$template_ftp = admin_comTemplate_readDir();

	$tmpl_list			= array();	# Here the list !
	$template_default	= false;	# Is default template defined ?
	$tmpl_list_founded	= '';		# See below explanation
	for ($i=0; $i<count($template_ftp); $i++)
	{
		if (formManager_filter::isID($template_ftp[$i]['dir'])) # Validate the name of the template directory
		{
			if (!($template_db = $db->selectOne('template, *, where: name='.$db->str_encode($template_ftp[$i]['dir']))))
			{
				// New template detected! Insert it into database
				$db->insert('template; NULL, '.$db->str_encode($template_ftp[$i]['dir']).", 0, ''");

				$template_db = $db->selectOne('template, *, where: name='.$db->str_encode($template_ftp[$i]['dir']));

				$span_l = '<span class="green">';
				$span_r = '</span>';
				$new = true;
			}
			else {
				$span_l = '';
				$span_r = '';
				$new = false;
			}

			// DB infos
			if ($template_ftp[$i]['php'] && $template_ftp[$i]['css']) # Template ok !
			{
				$tmpl_list[$i]['current'] = $form->radio('default', formManager::checkValue($template_db['id'], $template_db['current']), '', 'default_'.$template_db['id']);
			} else {
				$tmpl_list[$i]['current'] = $form->radio('default', 0, '', 'default_'.$template_db['id'], 'disabled'); # Disabled button !
			}
			$tmpl_list[$i]['id'     ] =				$template_db['id'		]; # Do not add $span_l and $span_r
			$tmpl_list[$i]['name'   ] = $span_l.	$template_db['name'		]	.$span_r;
			$tmpl_list[$i]['comment'] = $span_l.	$template_db['comment'	]	.$span_r;

			$new ? $tmpl_list[$i]['comment'] .= $png_new : ''; # New icon !

			// FTP infos
			$tmpl_list[$i]['php'	] = admin_replaceTrueByChecked($template_ftp[$i]['php'], false);
			$tmpl_list[$i]['css'	] = admin_replaceTrueByChecked($template_ftp[$i]['css'], false);

			// Update button
			$tmpl_list[$i]['update']  = $form->submit('update_'.$template_db['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (3)

			if ($template_db['current']) {
				$template_default = true;
			}

			$tmpl_list_founded .= " where:name!='".$template_ftp[$i]['dir']."' AND,"; # Prepare next query : List of founded ftp-templates
		}
		else # Invalid name of the template directory
		{
			$span_l = '<span class="highlight-red">';
			$span_r = '</span>';

			$tmpl_list[$i]['current']	=			'';
			$tmpl_list[$i]['id'		]	= 			0;
			$tmpl_list[$i]['name'	]	= $span_l.	'&nbsp;'.$template_ftp[$i]['dir'].'&nbsp;'					.$span_r;
			$tmpl_list[$i]['comment']	= $span_l.	'&nbsp;'.LANG_ADMIN_COM_TEMPLATE_INVALID_DIR_NAME.'&nbsp;'	.$span_r;
			$tmpl_list[$i]['php'	]	=			'';
			$tmpl_list[$i]['css'	]	=			'';
			$tmpl_list[$i]['update'	]	=			'';
		}
	}
	$tmpl_list_founded = preg_replace('~\sAND,$~', '', $tmpl_list_founded); # Format the query : delete the end of string

	$span_l = '<span class="red">';
	$span_r = '</span>';

	// Templates wich are recorded into DB (but not exist into FTP)
	if ($tmpl_list_founded != '')
	{
		$template_error = $db->select("template, *, $tmpl_list_founded");
	} else {
		$template_error = $db->select("template, *");
	}

	for ($i=0; $i<count($template_error); $i++)
	{
		// DB infos
		$tmpl_list[count($template_ftp)+$i]['current'] 	=			$form->submit('delete_'.$template_error[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');
		$tmpl_list[count($template_ftp)+$i]['id'     ] 	=			$template_error[$i]['id'		];
		$tmpl_list[count($template_ftp)+$i]['name'   ] 	= $span_l.	$template_error[$i]['name'		]	.$span_r;
		$tmpl_list[count($template_ftp)+$i]['comment'] 	= $span_l.	$template_error[$i]['comment'	]	.LANG_ADMIN_COM_TEMPLATE_MISSING_DIR.$span_r;

		// FTP infos
		$tmpl_list[count($template_ftp)+$i]['php'] = admin_replaceTrueByChecked(false, false);
		$tmpl_list[count($template_ftp)+$i]['css'] = admin_replaceTrueByChecked(false, false);

		// Action button
		$tmpl_list[count($template_ftp)+$i]['update']  = '';

		if ($template_error[$i]['current'] == true) {
			$template_default = false; # If the default_template defined here, it's still not good !!!
		}
	}

	// Re-order the templates list by 'id'
	if (count($tmpl_list)) {
		usort($tmpl_list, 'admin_comTemplate_orderByID');
	}

	// Table
	$table = new tableManager($tmpl_list);
	$table->header(
				array(
					LANG_ADMIN_COM_TEMPLATE_DEFAULT,
					LANG_ADMIN_COM_TEMPLATE_ID,
					LANG_ADMIN_COM_TEMPLATE_NAME,
					LANG_ADMIN_COM_TEMPLATE_DESC, 
					LANG_ADMIN_COM_TEMPLATE_PHP,
					LANG_ADMIN_COM_TEMPLATE_CSS,
					''
				)
	);

	$table->delCol(1); # Delete the 'id' column wich is useless for the view
	$html .=  $table->html(0);

	if (!$template_default) {
		admin_message(LANG_ADMIN_COM_TEMPLATE_DEFAULT_TMPL_NOT_DEFINED, 'warning');
	}

	$html .= $form->submit('submit_default', LANG_ADMIN_BUTTON_SUBMIT); // (1)

	$html .= $form->end(); # End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>