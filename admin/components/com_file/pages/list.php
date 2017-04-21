<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'file_list');


// Images Html
$png_new = '<img src="'.WEBSITE_PATH.'/admin/images/new.png" alt="new" />';


// FTP of '/contents' directory
$ftp = new ftpManager(sitePath().'/contents');



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getPathFile(); // (4)

$submit = formManager::isSubmitedForm('file_', 'post'); // (0)
if ($submit)
{
	$del_dir = $filter->requestName ('del_dir_'	)->getInteger();

	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)

	$new_dir 	= $filter->requestValue('new_dir'	)->get(); // (1a)
	$new_file 	= $filter->requestValue('new_file'	)->get(); // (1b)
} else {
	$del_dir = false;

	$del = false;
	$upd = false;

	$new_dir 	= false;
	$new_file 	= false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)

$new_dir_submit  = formManager::isSubmitedForm('new_dir_' , 'post'); // (1a)
$new_file_submit = formManager::isSubmitedForm('new_file_', 'post'); // (1b)



// Permissions
$admin_perm = new adminPermissions();

if ($publish_status && !$admin_perm->publish($perm_denied))
{
	$publish_status = false;
	echo admin_message($perm_denied, 'warning');
}

if (($del_dir || $del) && !$admin_perm->delete($perm_denied))
{
	$del_dir = false;
	$del = false;
	echo admin_message($perm_denied, 'warning');
}



// (4) Case 'publish_status'
if ($publish_status)
{
	if ($published = $db->selectOne('file, published, where: path='.$db->str_encode($publish_status)))
	{
		$published['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("file; published=$published; where: path=".$db->str_encode($publish_status));
	}
}



// Case 'del_dir_'
if ($del_dir && ($id_dir = $filter->requestValue("id_$del_dir")->getPath()))
{
	if ($ftp->isDir($id_dir) && !$ftp->delete($id_dir))
	{
		admin_message(LANG_ADMIN_COM_FILE_DEL_ERROR_FTP_DIR, 'error');
	} else {
		admin_informResult(true);
	}
}



// (3) Case 'delete'
if ($del && ($id_file = $filter->requestValue("id_$del")->getPathFile()))
{
	if ($db->delete('file; where: path='.$db->str_encode($id_file))) # Delete from DB
	{
		if ($ftp->isFile($id_file) && !$ftp->delete($id_file)) # Delete from FTP (if exists)
		{
			admin_message(LANG_ADMIN_COM_FILE_DEL_ERROR_FTP_FILE, 'error');
		} else {
			admin_informResult(true);
		}
	}
	else {
		admin_informResult(false);
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	// Fields validation
	$upd_path 		= $filter->requestValue('path')->getPathFile(); # Current path
	$title 			= $filter->requestValue('title')->get();
	$access_level 	= $filter->requestValue('access_level')->getInteger();

	$dir	= $filter->requestValue('dir')->getPath();
	$name	= $filter->requestValue('name')->getID();
	$ext	= $filter->requestValue('ext')->get();
	$new_path = "$dir/$name.$ext"; # New path

	if ($new_path !== $upd_path)
	{
		// Check $dir and $ext
		if (array_key_exists($dir, $dir_options = $ftp->setTree()->getTree('dir_options')) && in_array($ext, array('html', 'php')))
		{
			// File already exists ?
			if ($ftp->isFile($new_path)) {
				$filter->set($new_path, 'name')->getError(LANG_ADMIN_COM_FILE_UPD_FILE_PATH_NEW_EXISTS);
			}
		}
		else {
			$filter->set(false)->getError('Invalid directory or file extension');
		}
	}

	if ($upd_submit_validation = $filter->validated())
	{
		// FTP : update content
		$ftp->write($upd_path, formatText($filter->requestValue('file_content')->get()));

		// DB : update 'file' table
		$db->update('file; title='.$db->str_encode($title).', access_level='.$db->str_encode($access_level).'; where: path='.$db->str_encode($upd_path));

		if ($new_path !== $upd_path)
		{
			// FTP : move file
			if ($ftp->rename($upd_path, $new_path))
			{
				$menu_link_upd = $db->str_encode('file='.preg_replace('~^(/)~', '', $upd_path));
				$menu_link_new = $db->str_encode('file='.preg_replace('~^(/)~', '', $new_path));

				// DB : update 'file', 'menu_link' and 'module_xhref' tables
				if (
					$db->update('file; path='.$db->str_encode($new_path).'; where: path='.$db->str_encode($upd_path)) &&
					$db->update("menu_link;	href=$menu_link_new;			where: href=$menu_link_upd"		) &&
					$db->update("module_xhref;	link_href=$menu_link_new;	where: link_href=$menu_link_upd")
				) {
					$upd_path = $new_path; # Here is the new path fully updated !
				}
				else {
					$filter->set($new_path)->getError(LANG_ADMIN_COM_FILE_UPD_FILE_PATH_NEW_ERROR);
				}
			}
			else {
				$filter->set(false)->getError(LANG_ADMIN_COM_FILE_UPD_FILE_NOT_MOVED);
			}
		}
	}

	if ($upd_submit_validation)
	{
		admin_informResult(true);
		if ($filter->requestValue('record')->get()) {
			$upd_submit_validation = false; # Stay in the update form !
		}
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd && $id_file = $filter->requestValue("id_$upd")->getPathFile()) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_FILE_TITLE_UPDATE.'</h2>';

	// Path
	if ($upd) {
		$upd_path = $id_file;
	} else {
		# $upd_path already set (*)
	}

	$current = $db->selectOne('file, *, where: path='.$db->str_encode($upd_path));

	if (!$admin_perm->update($current['published'], false, $perm_denied)) {
		admin_message($perm_denied, 'warning');
	}

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('path', $current['path']);

	/////////////////
	// Fieldset path

	// Dir
	$dir_options = $ftp->setTree()->getTree('dir_options');
	$dir_options[''] = ''; # Root
	foreach($dir_options as $k =>$v) {
		$dir_options[$k] = "$v/"; # Add slash for presentation
	}

	// Name / Extention
	$pathinfo = pathinfo($current['path']);
	$fieldset  = LANG_ADMIN_COM_FILE_UPD_FILE_PATH_CURRENT.' <strong>'.$current['path'].'</strong><br /><br />';
	$fieldset .= $form->select('dir', formManager::selectOption($dir_options, $pathinfo['dirname']), LANG_ADMIN_COM_FILE_UPD_FILE_PATH_NEW.'<br />');
	$fieldset .= $form->text('name', $pathinfo['filename']);
	$fieldset .= $form->select('ext', formManager::selectOption(array('html'=>'.html', 'php'=>'.php'), $pathinfo['extension'])).'<br /><br />';

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_FILE_UPD_FIELDSET_PATH); // End of Fieldset

	///////////////////
	// Fieldset config

	// Title
	$fieldset = $form->text('title', $current['title'], LANG_ADMIN_COM_FILE_TITLE, '', 'maxlength=255').'&nbsp; &nbsp;';

	// Access_level
	$fieldset .= $form->select('access_level', comUser_getStatusOptions($current['access_level']), LANG_ADMIN_COM_FILE_ACCESS_LEVEL).'<br /><br />';

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_FILE_UPD_FIELDSET_CONFIG); // End of Fieldset

	// Update the file content on the web server
	$html .= $form->textarea('file_content', $file_content = $ftp->read($upd_path), LANG_ADMIN_COM_FILE_UPD_FILE_CONTENT.'<br />', '', 'cols=150;rows=30').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('record', LANG_ADMIN_BUTTON_RECORD);

	$path_info = pathinfo($upd_path);
	if (in_array($path_info['extension'], array('html', 'htm')) && $session->get('use_html_editor'))
	{
		// Use HTML editor to edit content
		$my_CKEditor = new loadMyCkeditor();
		$html .= $my_CKEditor->addName("file_content");
	}
	elseif (in_array($path_info['extension'], array('php')))
	{
		// Syntax highlighted for PHP file !
		$html .= '<div style="margin-top:15px; font-weight:bold; color:#999;">'.LANG_ADMIN_COM_FILE_HIGHLIGHT_SYNTAX_PHP.'</div>';
		$html .= '<div style="height:300px; overflow:auto; margin-bottom:15px; padding:10px; border:1px solid #CCC; border-left-width:5px; background-color:#FAFAFA;">';
		$html .= highlight_string($file_content, 1);
		$html .= '</div>';
	}

	$html .= $form->end();

	if ($perm_denied)
	{
		$start_view = true;
	} else {
		echo $html;
	}
}



// (1b) Case 'new_file'
if ($new_file_submit)
{
	$new_file_submit_validation = true;

	$filter->reset();

	// Fields validation
	$dir	= $filter->requestValue('dir')->getPath();
	$name	= $filter->requestValue('name')->getID();
	$ext	= $filter->requestValue('ext')->get();
	!in_array($ext, array('html', 'php')) ? $ext = 'html' : '';

	// File already exists !
	if ($ftp->isFile("$dir/$name.$ext")) {
		$filter->set("$dir/$name.$ext", 'name')->getError(LANG_ADMIN_COM_FILE_NEW_FILE_ERROR_FILE_EXISTS);
	}

	// Database Process
	$result_db  = false;
	if ($new_file_submit_validation = $filter->validated())
	{
		admin_informResult( $ftp->write("$dir/$name.$ext") );
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new_file || ($new_file_submit && !$new_file_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_FILE_TITLE_NEW_FILE.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_file_');

	$dir_options = $ftp->setTree()->getTree('dir_options');
	$dir_options[''] = LANG_ADMIN_COM_FILE_SELECT_DIR_ROOT; # Root
	$html .= $form->select('dir', formManager::selectOption($dir_options, $session->get('select_dir')), LANG_ADMIN_COM_FILE_NEW_FILE_DIR.'<br />').'<br /><br />';

	$html .= $form->text('name', '', LANG_ADMIN_COM_FILE_NEW_FILE_NAME_EXTENSION.'<br />');
	$html .= $form->select('ext', array('html'=>'.html', 'php'=>'.php')).'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// (1a) Case 'new_dir'
if ($new_dir_submit)
{
	$new_dir_submit_validation = true;

	$filter->reset();

	// Fields validation
	$dir	= $filter->requestValue('dir')->getPath();
	$name	= $filter->requestValue('name')->getID();

	// Dir already exists !
	if ($name && $ftp->isDir("$dir/$name")) {
		$filter->set("$dir/$name", 'name')->getError(LANG_ADMIN_COM_FILE_NEW_DIR_ERROR_DIR_EXISTS);
	} 

	// Database Process
	$result_db  = false;
	if ($new_dir_submit_validation = $filter->validated())
	{
		admin_informResult( $ftp->mkdir("$dir/$name") );
	}
	else {
		echo $filter->errorMessage();
	}
}
if ($new_dir || ($new_dir_submit && !$new_dir_submit_validation))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_FILE_TITLE_NEW_DIR.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_dir_');

	$dir_options = $ftp->setTree()->getTree('dir_options');
	$dir_options[''] = LANG_ADMIN_COM_FILE_SELECT_DIR_ROOT; # Root
	$html .= $form->select('dir', formManager::selectOption($dir_options, $session->get('select_dir')), LANG_ADMIN_COM_FILE_NEW_DIR_DIR.'<br />').'<br /><br />';

	$html .= $form->text('name', '', LANG_ADMIN_COM_FILE_NEW_DIR_NAME.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_FILE_TITLE_START.'</h2>';

	$html = '';

	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'file_');

	$file_ftp = $ftp->setTree()->getTree();
	$dir_ftp = array_keys($file_ftp);

	// Sub-directory selection
	$dir_options[''] = LANG_ADMIN_COM_FILE_SELECT_DIR_ROOT;
	for ($i=1; $i<count($dir_ftp); $i++) {
		$dir_options[$dir_ftp[$i]] = $dir_ftp[$i];
	}
	$session->init('select_dir', '');
	if ($submit) {
		$select_dir = $filter->requestValue('select_dir')->get();
		if (array_key_exists($select_dir, $dir_options)) {
			$session->set('select_dir', $select_dir);
		}
	}
	$html .= $form->select('select_dir', formManager::selectOption($dir_options, $session->get('select_dir')), LANG_ADMIN_COM_FILE_SELECT_DIR_LABEL);
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit-top').'<br /><br />';

	// Update session of config part
	$session->init('use_html_editor', 0);
	$session->init('skip_problems', 0);
	if ($submit) {
		$session->set('use_html_editor', $filter->requestValue('use_html_editor')->get());
		$session->set('skip_problems', $filter->requestValue('skip_problems')->get());
	}

	// Here the final list we are looking for...
	$list = array();

	$status = comUser_getStatusOptions();

	$file_founded = '';
	foreach ($file_ftp as $dir => $files)
	{
		if (preg_match('~^'.pregQuote($session->get('select_dir')).'($|/)~', $dir))
		{
			$id = count($list);

			// Delete this empty directory
			$del_dir = '';
			if (!count($files)) {
				$del_dir = $form->submit("del_dir_$id", LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

				// $dir and $id association
				$del_dir .= $form->hidden("id_$id", $dir);
			}

			// Add $dir info
			$list[$id] = array(
				'path_for_order'	=>	$dir ? "$dir/" : '/', # Add slash (important!)
				'delete'			=>	$del_dir,
				'path'				=>	'<h4>'.($dir ? $dir : '/').'</h4>',
				'title'				=>	'',
				'access_level'		=>	'',
				'published'			=>	'',
				'link_counter'		=>	'',
				'update'			=>	''
			);

			for ($i=0; $i<count($files); $i++)
			{
				$id = count($list);
				$dir_file = "$dir/{$files[$i]}";

				// Init
				$list[$id] = array(
					'path_for_order'	=>	$dir_file,
					'delete'			=>	'',
					'path'				=>	'',
					'title'				=>	'',
					'access_level'		=>	'',
					'published'			=>	'',
					'link_counter'		=>	'',
					'update'			=>	''
				);

				if (formManager_filter::isPathFile($dir_file))
				{
					// Get file infos (db)
					if ($file_db = $db->selectOne('file, *, where: path='.$db->str_encode($dir_file)))
					{
						// Count of links to this page
						if ($link_counter = $db->selectCount('menu_link, where: href='.$db->str_encode('file='.preg_replace('~^(/)~', '', $dir_file))))
						{
							$list[$id]['link_counter'] = $link_counter;
						} else {
							$list[$id]['delete'] = $form->submit("del_$id", LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); # This one can be deleted !
						}
						$css_class = '';
					}
					// Add file (db)
					else
					{
						$admin_perm->publish($perm_denied) ? $publish = 1 : $publish = 0;

						$db->insert('file; '.$db->str_encode($dir_file).", '', ".comUser_getLowerStatus().", $publish");
						$file_db = $db->selectOne('file, *, where: path='.$db->str_encode($dir_file));

						$list[$id]['delete'] = $png_new; # Add 'new' icon

						$css_class = 'green';
					}

					$list[$id]['update'] = $form->submit("upd_$id", LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');

					// $dir_file and $id association
					$list[$id]['update'] .= $form->hidden("id_$id", $dir_file);

					// Link to the online page
					$link_to_page = '<a class="external" href="'.comMenu_rewrite('file='.preg_replace('~^/~', '', $dir_file))."\">{$files[$i]}</a>";

					// File infos
					$list[$id]['path'			] = admin_comFile_cssClass("<span style=\"color:grey;\">.....</span> ".$link_to_page, $css_class);
					$list[$id]['title'			] = admin_comFile_cssClass($file_db['title'], $css_class);
					$list[$id]['access_level'	] = admin_comFile_cssClass($status[$file_db['access_level']], $css_class);
					$list[$id]['published'		] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$file_db['path'].'">'.admin_replaceTrueByChecked($file_db['published']).'</a>';

					$file_founded .= ' where: path!='.$db->str_encode($file_db['path']).' AND,'; # Prepare next query
				}
				elseif (!$session->get('skip_problems'))
				{
					$list[$id]['path'			] = admin_comFile_cssClass("<span style=\"color:grey;\">.....</span> ".'<i>'.$files[$i].'</i>', 'grey');
					$message_invalid = true;
				}
				else {
					array_pop($list); # Remove the Init
				}
			}
		}
	}
	$file_founded = substr($file_founded, 0, strlen($file_founded)-5);

	// Files wich are still recorded into DB, but has disappear from FTP !
	if (!$session->get('skip_problems'))
	{
		$dir_db = array();
		$file_error = $db->select('file, *'. ($file_founded ? ", $file_founded" : '') );
		for ($i=0; $i<count($file_error); $i++)
		{
			$pathinfo = pathinfo($file_error[$i]['path']);

			if (preg_match('~^'.pregQuote($session->get('select_dir')).'($|/)~', $pathinfo['dirname']))
			{
				// Is the directory of the $file_error exists ?
				if ( ($pathinfo['dirname'] != '/') && (!in_array($pathinfo['dirname'], $dir_ftp)) && (!in_array($pathinfo['dirname'], $dir_db)) )
				{
					$dir_db[] = $pathinfo['dirname']; # Remember it !

					$list[] = array(
						'path_for_order'	=>	$pathinfo['dirname'].'/', # Add slash (important!)
						'delete'			=>	'',
						'path'				=>	'<h4 class="red">'.$pathinfo['dirname'].'</h4>',
						'title'				=>	'',
						'access_level'		=>	'',
						'published'			=>	'',
						'link_counter'		=>	'',
						'update'			=>	''
					);
				}

				// Count of links to this page
				$link_counter = $db->selectCount('menu_link, where: href='.$db->str_encode('file='.preg_replace('~^(/)~', '', $file_error[$i]['path'])));
				$link_counter or $link_counter = '';

				$del_error = '';
				if (!$link_counter) {
					$id = count($list);
					$del_error = $form->submit("del_$id", LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

					// $file_error and $id association
					$del_error .= $form->hidden("id_$id", $file_error[$i]['path']);
				}

				$list[] = array(
					'path_for_order'	=>	$file_error[$i]['path'],
					'delete'			=>	$del_error,
					'path'				=>	admin_comFile_cssClass("<span style=\"color:grey;\">.....</span> ".$pathinfo['basename'], 'red'),
					'title'				=>	admin_comFile_cssClass($file_error[$i]['title'], 'red'),
					'access_level'		=>	admin_comFile_cssClass($status[$file_error[$i]['access_level']], 'red'),
					'published'			=>	'',
					'link_counter'		=>	admin_comFile_cssClass($link_counter, 'red'),
					'update'			=>	''
				);
				$message_error = true;
			}
		}
	}

	// Re-order the files list by 'path'
	if (count($list)) {
		usort($list, 'admin_comFile_orderByPath');
	}

	$table = new tableManager($list);
	$table->delCol(0); # Remove 'path_for_order' column
	$table->header(
		array(
			'',
			LANG_ADMIN_COM_FILE_PATH,
			LANG_ADMIN_COM_FILE_TITLE,
			LANG_ADMIN_COM_FILE_ACCESS_LEVEL,
			LANG_ADMIN_COM_FILE_PUBLISHED,
			LANG_ADMIN_COM_FILE_LINK_COUNTER,
			''
		));
	$html .= $table->html();

	if ($admin_perm->canAccessStatus('webmaster')) { # Only webmaster can modify the ftp structure of directories
		$html .= $form->submit('new_dir', LANG_ADMIN_COM_FILE_NEW_DIR ).'&nbsp; &nbsp;'; // (1a)
	}
	$html .= $form->submit('new_file', LANG_ADMIN_COM_FILE_NEW_FILE); // (1b)

	// Errors messages
	if (!$session->get('skip_problems') && (isset($message_invalid) || isset($message_error))) {
		$html .= '<br />';
		!isset($message_invalid	) or $html .= '<br />'.admin_comFile_cssClass('* '.LANG_ADMIN_COM_FILE_INVALID_FILE_NAME, 'grey');
		!isset($message_error	) or $html .= '<br />'.admin_comFile_cssClass('* '.LANG_ADMIN_COM_FILE_MISSING_FILE		, 'red');
	}

	// Configuration
	$fieldset  = $form->checkbox('use_html_editor', $session->get('use_html_editor'), LANG_ADMIN_COM_FILE_USE_HTML_EDITOR).'<br />';
	$fieldset .= $form->checkbox('skip_problems', $session->get('skip_problems'), LANG_ADMIN_COM_FILE_SKIP_PROBLEMS);
	$fieldset .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT, 'submit-bottom');
	$html .= '<br /><br />'.admin_fieldset($fieldset, LANG_ADMIN_COM_FILE_FIELDSET_CONFIG);

	$html .= $form->end();

	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>