<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'thumbs');


// comResource class
$com_resource = new comResource_();


// FTP class
$ftp = new ftpManager(sitePath());

// Alias
$thumbs_dir = comResource_::THUMBS_DIR_NAME;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('thumbs_', 'post'); // (0)
if ($submit)
{
	$delete_thumbs = $filter->requestValue('delete_thumbs')->get(); // (3)
	$update_thumbs = $filter->requestValue('update_thumbs')->get(); // (1)
} else {
	$delete_thumbs = false;
	$update_thumbs = false;
}



// Update the thumbs config : value, key, quality (0)
if ($submit)
{
	$thumb_value 	= $filter->requestValue('thumb_value'	)->getInteger(1, '', LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_VALUE);

	$thumb_quality 	= $filter->requestValue('thumb_quality'	)->getInteger(1, '', LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_QUALITY);
	$thumb_quality < 1   ? $thumb_quality = '1'   : '';
	$thumb_quality > 100 ? $thumb_quality = '100' : '';

	$thumb_key 		= $filter->requestValue('thumb_key'		)->get();
	if (!array_key_exists($thumb_key, $com_resource->thumbKeyOptions())) {
		echo "<p style=\"color:red;\">Error occured : invalid posted variable : thumb_key=$thumb_key !</p>";
		exit; # This case should never append !
	}

	// DB process
	if ($filter->validated())
	{
		$result_submit = $db->update("resource_config; thumb_key=".$db->str_encode($thumb_key).", thumb_value=$thumb_value, thumb_quality=$thumb_quality");

		if ($filter->requestValue('submit')->get()) { # The submit button was clicked ! So, this is the only one process we have to do (no 'update_thumbs' or 'delete_thumbs' process requested). Inform about this simple result...
			admin_informResult($result_submit);
		}
	}
	else {
		echo $filter->errorMessage();
	}
}



// If the "update thumbs config" failed, then reset all process !
if (!$filter->validated())
{
	$delete_thumbs = false;
	$update_thumbs = false;
}



// List of images and thumbs on the web server (commun process for : (1) update_thumbs, and (3) delete_thumbs)
if ($update_thumbs || $delete_thumbs)
{
	// select_dir
	$select_dir = $filter->requestValue('select_dir')->getPath();
	if (RESOURCE_PATH != '' && $select_dir == '') {
		echo "<p style=\"color:red;\">Error occured : the base path for the new directory is invalid !</p>";
		exit; # This case should never append !
	}

	// List of images on the web server
	$ftp_list =
		$ftp->setTree($select_dir)
				->reduceTree('remove_invalid_dir_and_file')
				->reduceTree('keep_file_by_extension', array('jpg', 'gif')) # png not actually supported !
				->getTree();

	$list_images = array();
	$list_thumbs = array();
	foreach($ftp_list as $dir => $image)
	{
		// List of images
		if (!preg_match('~'.pregQuote($thumbs_dir).'$~', $dir))
		{
			for ($i=0; $i<count($image); $i++) {
				$list_images[$dir][] = $image[$i];
			}
		}
		// List of thumbs
		else
		{
			$dir_parent = preg_replace('~('.pregQuote($thumbs_dir).')$~', '', $dir);
			for ($i=0; $i<count($image); $i++) {
				$list_thumbs[$dir_parent][] = $image[$i];
			}
		}
	}
}



// Case 'delete_thumbs' (3)
if ($delete_thumbs)
{
	$images_deleted = array();
	reset($list_thumbs);
	foreach($list_thumbs as $dir => $image)
	{
		for ($i=0; $i<count($image); $i++)
		{
			if ($ftp->delete($dir.$thumbs_dir.'/'.$image[$i]))
			{
				$images_deleted[] = comResource_::beautifyPath(WEBSITE_PATH.$dir.$thumbs_dir.'/'.$image[$i]);
			} else {
				$images_deleted[] = '<span style="color:red;">'.comResource_::beautifyPath(WEBSITE_PATH.$dir.$thumbs_dir.'/'.$image[$i]).'</span>';
			}
		}
	}

	if (!count($images_deleted))
	{
		admin_message(str_replace('{directory}', comResource_::beautifyPath($select_dir), LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_NOTHING_TO_DO), 'ok');
	}
	else
	{
		$start_view = false;

		// Title
		echo '<h2>'.LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_TITLE.'</h2>';
		echo '<p>'.str_replace('{directory}', comResource_::beautifyPath($select_dir), LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_DIR).'</p><br />';

		$t = new tableManager($images_deleted, array(LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_DELETED_THUMB));
		echo $t->html();

		echo '<p><b><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_CONTINUE.' &gt;</a></b></p>'; # Alternative reset button...
	}
}



// Case 'update_thumbs' (1)
if ($update_thumbs)
{
	$config = $db->selectOne('resource_config, *');

	$resource_base = $com_resource->getBase();

	// List of images that's missing they thumbs
	$thumbs_missing = array();
	reset($list_images);
	foreach($list_images as $dir => $image)
	{
		!isset($list_thumbs[$dir]) ? $list_thumbs[$dir] = array() : '';

		for ($i=0; $i<count($image); $i++)
		{
			if (!in_array($image[$i], $list_thumbs[$dir]))
			{
				$current_counter = count($thumbs_missing);
	
				// New thumb full purl
				$url_thumb = $resource_base['url'].$dir.$thumbs_dir.'/'.$image[$i];

				$thumbs_missing[$current_counter]['path'] = "<a href=\"$url_thumb\" class=\"external no-arrow\">".comResource_::beautifyPath(WEBSITE_PATH.$dir.$thumbs_dir.'/'.$image[$i])."</a>";

				// Create the missing thumbnail !
				if ($com_resource->createThumbnail( $dir.'/'.$image[$i], $config['thumb_key'], $config['thumb_value'], $config['thumb_quality'] ))
				{
					$thumbs_missing[$current_counter]['preview'] = "<a href=\"$url_thumb\" class=\"external no-arrow\"><img src=\"$url_thumb\" alt=\"\" border=\"0\" /></a>";
				} else {
					$thumbs_missing[$current_counter]['preview'] = '<span style="color:red;">?</span>';
				}
			}
		}
	}

	// List of thumbs that's missing they images
	$images_missing = array();
	reset($list_thumbs);
	foreach($list_thumbs as $dir => $image)
	{
		for ($i=0; $i<count($image); $i++)
		{
			if (!in_array($image[$i], $list_images[$dir]))
			{
				// Delete the useless thumbnail !
				if ($ftp->delete($dir.$thumbs_dir.'/'.$image[$i]))
				{
					$images_missing[] = comResource_::beautifyPath(WEBSITE_PATH.$dir.$thumbs_dir.'/'.$image[$i]);
				} else {
					$images_missing[] = '<span style="color:red;">'.comResource_::beautifyPath(WEBSITE_PATH.$dir.$thumbs_dir.'/'.$image[$i]).'</span>';
				}
			}
		}
	}

	// Final messages
	if (!count($thumbs_missing) && !count($images_missing))
	{
		admin_message(str_replace('{directory}', comResource_::beautifyPath($select_dir), LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_NOTHING_TO_DO), 'ok');
	}
	else
	{
		$start_view = false;

		// Title
		echo '<h2>'.LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_TITLE.'</h2>';
		echo '<p>'.str_replace('{directory}', comResource_::beautifyPath($select_dir), LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_DIR).'</p><br />';

		if (count($thumbs_missing)) {
			$t = new tableManager($thumbs_missing, array(LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_NEW_THUMB_AVAILABLE, LANG_ADMIN_COM_RESOURCE_LIST_START_VIEW_PREVIEW));
			echo $t->html().'<br />';
		}

		if (count($images_missing)) {
			$t = new tableManager($images_missing, array(LANG_ADMIN_COM_RESOURCE_THUMBS_RESULT_UPD_DELETED_THUMB));
			echo $t->html().'<br />';
		}

		echo '<p><b><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_CONTINUE.' &gt;</a></b></p>'; # Alternative reset button...
	}
}



//////////////
// Start view

if ($start_view)
{
	clearstatcache();

	// Title
	echo '<h2>'.LANG_ADMIN_COM_RESOURCE_THUMBS_TITLE_START.'</h2>';

	$html = '';

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'thumbs_');

	$config = $db->selectOne('resource_config, *');

	//////////////////////////////
	// Thumb value, key & quality
	$fieldset = '';

	$fieldset .= $form->text('thumb_value', $config['thumb_value'], LANG_ADMIN_COM_RESOURCE_THUMB_VALUE_AND_KEY_LABEL.'<br />', '', 'size=4');
	$fieldset .= $form->select('thumb_key', $form->selectOption($com_resource->thumbKeyOptions(), $config['thumb_key'])).'<br /><br />';
	$fieldset .= $form->text('thumb_quality', $config['thumb_quality'], LANG_ADMIN_COM_RESOURCE_CONFIG_THUMB_QUALITY.'<br />', '', 'size=4').'%';
	$fieldset .= '<br /><br />'.$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (0)

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_RESOURCE_THUMBS_FIELDSET1);

	///////////
	// Actions
	$fieldset = '';

	// Choose a specific directory
	$dir_list = 
		$ftp->setTree(RESOURCE_PATH)
				->reduceTree('remove_invalid_dir_and_file')
				->reduceTree('exclude_dir_by_name', $thumbs_dir)
				->getTree('dir_options');
	$fieldset .= $form->select('select_dir', $dir_list, LANG_ADMIN_COM_RESOURCE_THUMBS_ACTION_SELECT_DIR_LABEL.'<br />').'<br /><br />';

	$fieldset .= $form->submit('update_thumbs', LANG_ADMIN_COM_RESOURCE_THUMBS_BUTTON_UPDATE).'&nbsp; &nbsp;'; // (1)
	$fieldset .= $form->submit('delete_thumbs', LANG_ADMIN_COM_RESOURCE_THUMBS_BUTTON_DELETE); // (3)

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_RESOURCE_THUMBS_FIELDSET2);

	$html .= $form->end();

	echo $html;
	echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>'; # This time, the reset button is inside the start view !
}


?>