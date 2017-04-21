<?php
/* Avine. Copyright (c) 2008 Stéphane Francel (http://avine.fr). Dual licensed under the MIT and GPL Version 2 licenses. */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'resource');


// comResource class
$com_resource = new comResource_();


// FTP class
$ftp = new ftpManager(sitePath());
#$ftp->useCache(true);

// Alias
$thumbs_dir = comResource_::THUMBS_DIR_NAME;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('resource_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)

	$new_dir 	= $filter->requestValue('new_dir'	)->get(); // (1a)
	$new_file 	= $filter->requestValue('new_file'	)->get(); // (1b)
} else {
	$del = false;
	$upd = false;

	$new_dir 	= false;
	$new_file 	= false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)

$new_dir_submit  = formManager::isSubmitedForm('new_dir_' , 'post'); // (1a)
$new_file_submit = formManager::isSubmitedForm('new_file_', 'post'); // (1b)



// (3) Case 'del'
if ($del)
{
	$debug = false; # Configuration : debug mode for testing or mode production

	$current_data = $filter->requestValue("hid_del_$del")->get();

	if ($current_data == RESOURCE_PATH)
	{
		echo "<p style=\"color:red;\">Error occured : '<b>$current_data</b>' should not be modified !</p>";
		exit; # This case should never append !
	}
	elseif ($ftp->isDir($current_data))
	{
		if ($debug) {
			echo "<p style=\"color:blue;\"><b>DEBUG MODE ACTIVATED</b><br />Ready to delete the directory : $current_data</p><br /><br />";
		}
		else {
			admin_informResult( $ftp->delete($current_data), LANG_ADMIN_COM_RESOURCE_LIST_DEL_RESULT_DIR.comResource_::beautifyPath($current_data) );
		}
	}
	elseif ($ftp->isFile($current_data))
	{
		if ($debug) {
			echo "<p style=\"color:blue;\"><b>DEBUG MODE ACTIVATED</b><br />Ready to delete the file : $current_data</p><br /><br />";
		}
		else {
			admin_informResult( $ftp->delete($current_data), LANG_ADMIN_COM_RESOURCE_LIST_DEL_RESULT_FILE.comResource_::beautifyPath($current_data) );
		}
	}
	else {
		admin_message(LANG_ADMIN_COM_RESOURCE_LIST_DATA_CORRUPTED.comResource_::beautifyPath($current_data), 'error');
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$debug = false; # Configuration : debug mode for testing or mode production

	// Fields validation
	$upd_submit_validation = true;

	$filter->reset();

	// current_data
	$current_data = $filter->requestValue('current_data')->get(); # (*)

	$path_info = pathinfo($current_data);
	$dirname 	= $path_info['dirname'	];
	$basename 	= $path_info['basename'	];
	$filename 	= $path_info['filename'	];
	$extension 	= $path_info['extension'];

	// dir
	$dir = $filter->requestValue('dir')->get();

	// name_base ?
	if ($name_base = $filter->requestValue('name_base')->get())
	{
		$new_name = $filter->set("$dir/$name_base", 'name_base')->getPath(1, LANG_ADMIN_COM_RESOURCE_LIST_UPD_INVALID_DIRNAME);
	}
	// name_file ?
	elseif ($name_file = $filter->requestValue('name_file')->get())
	{
		$new_name = $filter->set("$dir/$name_file.{$path_info['extension']}", 'name_file')->getPathFile(1, LANG_ADMIN_COM_RESOURCE_LIST_UPD_INVALID_FILENAME);
	} else {
		$filter->set(false, 'name_file')->getError(LANG_ADMIN_COM_RESOURCE_LIST_UPD_ERROR_NAME_MISSING);
	}

	// If this is an image, give the user the possibility to resize it !
	if (in_array($extension, array('jpg', 'gif', 'png')))
	{
		if ($filter->requestValue('image_value')->get())
		{
			$image_value = $filter->requestValue('image_value')->getInteger(1, '', LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_VALUE);

			$image_key = $filter->requestValue('image_key')->get();
			if (!array_key_exists($image_key, $com_resource->thumbKeyOptions())) {
				echo "<p style=\"color:red;\">Error occured : invalid posted variable : image_key=$image_key !</p>";
				exit; # This case should never append !
			}

			$image_quality 	= $filter->requestValue('image_quality')->getInteger(1, '', LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_QUALITY);
			$image_quality < 1   ? $image_quality = '1'   : '';
			$image_quality > 100 ? $image_quality = '100' : '';

			$filter->requestValue('image_copy')->get() ? $image_copy = true : $image_copy = false;

			$resize_image = true;
		}
	}

	// FTP Process
	if ($upd_submit_validation = $filter->validated())
	{
		// debug mode for testing
		if ($debug) {
			echo "<p style=\"color:blue;\"><b>DEBUG MODE ACTIVATED</b><br />Old path file : $current_data<br />New path file :$new_name</p><br /><br />";
		}
		// mode production
		else
		{
			admin_informResult($result = $ftp->rename($current_data, $new_name));

			// Resize image !
			if ($result && isset($resize_image))
			{
				$resized_image_path = $com_resource->resizeImage($new_name, $image_key, $image_value, $image_quality, $image_copy);

				if (!$resized_image_path) {
					admin_message(LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE_ERROR, 'warning');
				}
				elseif ($image_copy) {
					admin_message(LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE_COPY_PATH.$resized_image_path, 'info');
				}
			}
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
	echo '<h2>'.LANG_ADMIN_COM_RESOURCE_LIST_TITLE_UPDATE.'</h2>';

	// File
	if ($upd) {
		$current_data = $filter->requestValue("hid_upd_$upd")->get();
	} else {
		# $current_data already set (see before (*));
	}

	$path_info = pathinfo($current_data);
	$dirname 	= $path_info['dirname'	];
	$basename 	= $path_info['basename'	];
	$filename 	= $path_info['filename'	];
	$extension 	= $path_info['extension'];

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('current_data', $current_data);

	$html_part = '';

	// Dir options (begin)
	$ftp->setTree(RESOURCE_PATH)
			->reduceTree('remove_invalid_dir_and_file')
			->reduceTree('exclude_dir_by_name', $thumbs_dir);

	if ($current_data == RESOURCE_PATH)
	{
		echo "<p style=\"color:red;\">Error occured : '<b>$current_data</b>' should not be modified !</p>";
		exit; # This case should never append !
	}
	elseif ($ftp->isDir($current_data))
	{
		echo '<p><b>'.LANG_ADMIN_COM_RESOURCE_LIST_UPD_DIR.'</b>'.comResource_::beautifyPath($current_data).'</p><br />';

		// Dir options (end)
		$dir_list = $ftp->reduceTree('exclude_dir_by_path', $current_data, false)
						->getTree('dir_options');

		$html_part .= $form->select('dir', $form->selectOption($dir_list, $dirname)).'/';// end

		$html_part .= $form->text('name_base', $basename, '', '', 'size=40');
		$html_part .= '&nbsp; &nbsp;'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); # Submit button here !
	}
	elseif ($ftp->isFile($current_data))
	{
		echo '<p><b>'.LANG_ADMIN_COM_RESOURCE_LIST_UPD_FILE.'</b>'.comResource_::beautifyPath($current_data).'</p><br />';

		// Dir options (end)
		$dir_list = $ftp->getTree('dir_options');

		$html_part .= $form->select('dir', $form->selectOption($dir_list, $dirname)).'/';// end

		$html_part .= $form->text('name_file', $filename, '', '', 'size=40').'.'.$path_info['extension'].'</span>&nbsp; &nbsp;';

		// If this is an image, give the user the possibility to resize it !
		if (in_array($extension, array('jpg', 'gif', 'png')))
		{
			$image_size = $com_resource->getImageSize($current_data);
			$fieldset  = "<p><b>".LANG_ADMIN_COM_RESOURCE_LIST_UPD_CURRENT_IMAGE_SIZE."</b> {$image_size[0]} x {$image_size[1]} px</p>";

			$fieldset .= $form->text	('image_value'	, '', LANG_ADMIN_COM_RESOURCE_THUMB_VALUE_AND_KEY_LABEL.'<br />', '', 'size=4');
			$fieldset .= $form->select	('image_key'	, $com_resource->thumbKeyOptions()).'<br /><br />';
			$fieldset .= $form->text	('image_quality', '85', LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_QUALITY.'<br />', '', 'size=4').'%'.'<br /><br />';
			$fieldset .= $form->checkbox('image_copy'	, 1, LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE_CREATE_COPY);

			$html_part .= '<br /><br />'.admin_fieldset($fieldset, LANG_ADMIN_COM_RESOURCE_LIST_RESIZE_IMAGE);
		}

		$html_part .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); # Submit button here !
	}
	else {
		echo LANG_ADMIN_COM_RESOURCE_LIST_DATA_CORRUPTED.comResource_::beautifyPath($current_data);
	}

	$html .= "<span style=\"color:#999;\">$html_part</span>";

	$html .= $form->end();
	echo $html;
}



// (1b) Case 'new_file'
if ($new_file_submit)
{
	// Fields validation
	$new_file_submit_validation = true;

	$filter->reset();

	// dir
	$dir = $filter->requestValue('dir')->getPath();
	if (RESOURCE_PATH != '' && $dir == '') {
		echo "<p style=\"color:red;\">Error occured : the base path for the new directory is invalid !</p>";
		exit; # This case should never append !
	}

	// overwrite
	$filter->requestValue('overwrite')->get() ? $overwrite = true : $overwrite = false;

	// file
	$file = $filter->requestFile('file')->getUploadedfile(1, '', LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_UPLOAD);

	// FTP Process
	if ($new_file_submit_validation = $filter->validated())
	{
		$uploaded_file_name = $ftp->moveUploadedFile($file['tmp_name'], "$dir/{$file['name']}", $overwrite, false);

		// Global result
		admin_informResult($uploaded_file_name);

		// Inform the final uploaded file location and his exact name
		if ($uploaded_file_name) {
			admin_message(LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_UPLOAD_SUCCESS.'<b>'.comResource_::beautifyPath($uploaded_file_name).'</b>', 'info');
		}
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new_file) || (($new_file_submit) && (!$new_file_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_RESOURCE_LIST_TITLE_NEW_FILE.'</h2>';

	$html = '';
	$form = new formManager();
	$form->addMultipartFormData(); # Allow files uploading !
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_file_');

	// Dir options
	$dir_list =
		$ftp->setTree(RESOURCE_PATH)
				->reduceTree('remove_invalid_dir_and_file')
				->reduceTree('exclude_dir_by_name', $thumbs_dir)
				->getTree('dir_options');
	$html .= $form->select('dir', $form->selectOption($dir_list, $session->get('select_dir')), LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_PATH.'<br />');

	// Upload new resource
	$html .= '<br /><br />'.$form->file('file', LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_UPLOAD.'<br />', '', 'size=70');

	// Overwrite existing file
	$html .= '<br /><br />'.$form->checkbox('overwrite', 0, LANG_ADMIN_COM_RESOURCE_LIST_NEW_FILE_OVERWRITE);

	$html .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	$html .= $form->end();
	echo $html;
}



// (1a) Case 'new_dir'
if ($new_dir_submit)
{
	// Fields validation
	$new_dir_submit_validation = true;

	$filter->reset();

	// dir
	$dir = $filter->requestValue('dir')->getPath();
	if (RESOURCE_PATH != '' && $dir == '') {
		$filter->set(false)->getError('Error occured : the base path for the new directory is invalid !'); # Security !
	}

	// name
	if (!$filter->requestValue('name')->get())
	{
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_RESOURCE_LIST_NEW_DIR_NAME_ERROR);
	} else {
		$name = $filter->requestValue('name')->getPath();
	}

	// FTP Process
	if ($new_dir_submit_validation = $filter->validated())
	{
		admin_informResult( $ftp->mkdir("$dir/$name") );
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new_dir) || (($new_dir_submit) && (!$new_dir_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_RESOURCE_LIST_TITLE_NEW_DIR.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_dir_');

	// Dir options
	$dir_list =
		$ftp->setTree(RESOURCE_PATH)
				->reduceTree('remove_invalid_dir_and_file')
				->reduceTree('exclude_dir_by_name', $thumbs_dir)
				->getTree('dir_options');
	$html .= $form->select('dir', $form->selectOption($dir_list, $session->get('select_dir')), LANG_ADMIN_COM_RESOURCE_LIST_NEW_DIR_PATH.'<br />').'/';

	// New dir name
	$html .= $form->text('name', '');

	$html .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	$html .= $form->end();
	echo $html;
}



// If any action occured, clear the cache !!! # TODO - can be improved...
if ($del || $upd_submit || $new_dir_submit || $new_file_submit) {
	$ftp->clearCache();
}



//////////////
// Start view

if ($start_view)
{
	clearstatcache();

	// Title
	echo '<h2>'.LANG_ADMIN_COM_RESOURCE_LIST_TITLE_START.'</h2>';

	$html = '';

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'resource_');


	//////////////////////
	// Select extension ?

	$session->init('select_extension', '0');
	$select_extension =
		array(
			'all'	=>	LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_ALL,
			'image'	=>	LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_IMAGE,
			'video'	=>	LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_VIDEO,
			'text'	=>	LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_TEXT
		);

	// Here the form !
	$form_select_extension = $form->select('select_extension', $form->selectOption($select_extension, $session->setAndGet('select_extension', $submit ? $filter->requestValue('select_extension')->get() : false)), LANG_ADMIN_COM_RESOURCE_LIST_SELECT_EXT_LABEL.'<br />');


	///////////////////////////////////
	// Include '/thumbs' directories ?

	$session->init('include_thumbs_dir', '0');
	if ($submit) {
		$filter->requestValue('include_thumbs_dir')->get() ? $include_thumbs_dir = '1' : $include_thumbs_dir = '0';
	} else {
		$include_thumbs_dir = false;
	}

	// Here the form !
	$form_include_thumbs_dir = $form->checkbox('include_thumbs_dir', $session->setAndGet('include_thumbs_dir', $include_thumbs_dir), LANG_ADMIN_COM_RESOURCE_LIST_SELECT_THUMBS_LABEL.'<br />(left)').' <span style="color:grey;">( '.comResource_::THUMBS_DIR_NAME.' )</span>';


	////////////////
	// Select dir ?

	$ftp->setTree(RESOURCE_PATH)
			->reduceTree('remove_invalid_dir_and_file');

	if (!$session->get('include_thumbs_dir')) {
		$ftp->reduceTree('exclude_dir_by_name', $thumbs_dir);
	}
	$dir_list = $ftp->getTree('dir_options');

	$session->init('select_dir', RESOURCE_PATH);

	if ($select_dir_posted = $filter->requestValue('select_dir')->getPath()) {
		!array_key_exists($select_dir_posted, $dir_list) ? $select_dir_posted = false : '';
	}

	// Here the form !
	$form_select_dir = $form->select('select_dir', $form->selectOption($dir_list, $session->setAndGet('select_dir', $select_dir_posted)), LANG_ADMIN_COM_RESOURCE_LIST_SELECT_DIR_LABEL.'<br />');

	// Get now the current directory we want to see !
	$current_dir = $session->get('select_dir');
	!array_key_exists($current_dir, $dir_list) ? $current_dir = RESOURCE_PATH : '';


	/////////////////
	// Add preview ?

	$session->init('select_preview', '0');
	$select_preview =
		array(
			'0'	=>	LANG_ADMIN_COM_RESOURCE_LIST_SELECT_PREVIEW_NO,
			'1'	=>	LANG_ADMIN_COM_RESOURCE_LIST_SELECT_PREVIEW_YES
		);

	// Here the form !
	$form_select_preview = $form->select('select_preview', $form->selectOption($select_preview, $session->setAndGet('select_preview', $submit ? $filter->requestValue('select_preview')->getInteger() : false)), LANG_ADMIN_COM_RESOURCE_LIST_SELECT_PREVIEW_LABEL.'<br />');


	////////////////
	// Here we go ! (scan the web server depends of the requested selections)

	// Set all
	$ftp->setTree($current_dir);
	// Remove thumbs ?
	if (!$session->get('include_thumbs_dir')) {
		$ftp->reduceTree('exclude_dir_by_name', $thumbs_dir);
	}
	// Select extensions ?
	switch($session->get('select_extension'))
	{
		case 'image':
			$ftp->reduceTree('keep_file_by_extension', array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
			break;
		case 'video':
			$ftp->reduceTree('keep_file_by_extension', array('flv', 'swf', 'wmv', 'rm', 'mov', 'avi', 'wma', 'mp3', 'mp4'));
			break;
		case 'text':
			$ftp->reduceTree('keep_file_by_extension', array('pdf', 'doc', 'docx', 'rtf', 'txt', 'xl', 'xls', 'xlsx', 'ppt', 'pptx', 'pps', 'ppsx'));
			break;
	}
	// Get the result
	$ftp_list = $ftp->getTree();


	/////////////
	// Multipage

	$record_counter = 0;
	$record_number = 0;
	foreach ($ftp_list as $dir => $files) {
		$record_number += 1 + count($files); # dir + files
	}
	$multipage = new simpleMultiPage($record_number);
	$multipage->setFormID('resource_');
	$multipage->updateSession($session->returnVar('multipage'));
	$multipage_start = $multipage->linesOffset();
	$multipage_stop  = $multipage_start + $multipage->linesNumPerPage();


	// The Html output at last !
	$html .=
		admin_floatingContent(
			array(
				$form_include_thumbs_dir,
				$form_select_dir,
				$form_select_extension,
				$form_select_preview,

				'&nbsp; &nbsp;'.$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),

				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT) // (0)
			)
		);


	$resource_list = array();
	$delete = array();
	$update = array();

	$i = 0;
	foreach ($ftp_list as $dir => $files)
	{
		// DIR
		if ( $i >= $multipage_start && $i < $multipage_stop )
		{
			if ($i != 0) # Don't touch the RESOURCE_PATH directory itself !
			{
				if (!count($files)) # Delete only empty dir
				{
					$delete[] = $form->submit('del_'.($i+1), LANG_ADMIN_BUTTON_DELETE, '', 'image=delete').$form->hidden('hid_del_'.($i+1), $dir); // (3)
				} else {
					$delete[] = '';
				}

				if (!preg_match('~'.pregQuote($thumbs_dir).'$~', $dir)) # Update only none '/thumbs' dir
				{
					$update[] = $form->submit('upd_'.($i+1), LANG_ADMIN_BUTTON_UPDATE, '', 'image=update').$form->hidden('hid_upd_'.($i+1), $dir); // (2)
				} else {
					$update[] = '';
				}
			}
			else {
				$delete[] = '';
				$update[] = '';
			}

			if (formManager_filter::isPath($dir))
			{
				$span_l = '';
				$span_r = '';
			} else {
				// Invalid dir name
				$span_l = '<span style="color:red">';
				$span_r = '</span>';
			}

			$resource_list[$i]['path'		] = "<h4>$span_l".comResource_::beautifyPath($dir)."$span_r</h4>";
			$resource_list[$i]['infos'		] = '';
			$resource_list[$i]['link'		] = '';
			$resource_list[$i]['preview'	] = '';
		}
		$i++;

		for ($j=0; $j<count($files); $j++)
		{
			// FILE
			if ( $i >= $multipage_start && $i < $multipage_stop )
			{
				$delete[] = $form->submit('del_'.($i+1), LANG_ADMIN_BUTTON_DELETE, '', 'image=delete').$form->hidden('hid_del_'.($i+1), "$dir/{$files[$j]}"); // (3)

				if (!preg_match('~'.pregQuote($thumbs_dir).'$~', $dir)) # No update available for thumbs directories files !
				{
					$update[] = $form->submit('upd_'.($i+1), LANG_ADMIN_BUTTON_UPDATE, '', 'image=update').$form->hidden('hid_upd_'.($i+1), "$dir/{$files[$j]}"); // (2)
				} else {
					$update[] = '';
				}

				if (formManager_filter::isPathFile("$dir/{$files[$j]}"))
				{
					$span_l = '';
					$span_r = '';
				} else {
					// Invalid file name
					$span_l = '<span style="color:red">';
					$span_r = '</span>';
				}

				// For images get the size
				if ($session->get('select_preview') && preg_match('~\.(gif|jpg|png)$~', $files[$j]))
				{
					# Carrefull ! The following method can strongly reduce the performances of the script !
					# Because of that, this info is disabled when the preview is not required.
					$image_size = $com_resource->getImageSize("$dir/{$files[$j]}");
					$image_size ? $image_size = "<span style=\"color:grey;\"><br />({$image_size[0]} x {$image_size[1]} px)</span>" : '';
				} else {
					$image_size = '';
				}

				$resource_list[$i]['path'	] = "<span style=\"color:grey;\">.....</span> $span_l{$files[$j]}$span_r";
				$resource_list[$i]['infos'	] = '<div style="text-align:center;font:83% Verdana;">'.$ftp->filesizeHTML("$dir/{$files[$j]}").$image_size.'</div>';
				$resource_list[$i]['link'	] = "<a href=\"".WEBSITE_PATH."$dir/{$files[$j]}\" class=\"external no-arrow\">".comResource_::beautifyPath(WEBSITE_PATH."$dir/{$files[$j]}")."</a>";
				$resource_list[$i]['preview'] = $com_resource->preview("$dir/{$files[$j]}");
			}
			$i++;
		}
	}

	$table = new
		tableManager(
			$resource_list,
			array(
				LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_PATH,
				LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_INFOS,
				LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_LINK,
				LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_PREVIEW
			)
		);

	if (!$session->get('select_preview')) {
		$table->delCol(3); # Del preview col
	}

	if (count($update)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}

	$html .= $table->html();


	$html .= $form->submit('new_dir'	, LANG_ADMIN_COM_RESOURCE_LIST_BUTTON_NEW_DIR ).'&nbsp; &nbsp;'; // (1a)
	$html .= $form->submit('new_file'	, LANG_ADMIN_COM_RESOURCE_LIST_BUTTON_NEW_FILE); // (1b)

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>