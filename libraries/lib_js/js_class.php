<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



class jsManager
{
	const	SCRIPT_PATH	= '/libraries/lib_js/js_script.js',
			TMPL_PATH	= '/libraries/lib_js/tmpl/';



	static public function toggle( $content, $title, $title_tag = 'div' )
	{
		$replacements =
			array(
				'rpl_title_tag'	=>	$title_tag, # div, h2, h3, ...
				'rpl_title'		=>	$title,
				'rpl_content'	=>	$content
			);

		$template = new templateManager();
		return $template->setTmplPath(sitePath().self::TMPL_PATH.'toggle.html')->setReplacements($replacements)->process();
	}



	// FIXME - pour le moment, on ne peut en avoir qu'un seul par page...
	static public function formDialog( $_form_, $title, $width = '600', $height = '300' )
	{
		$replacements =
			array(
				'rpl_width'		=>	$width,
				'rpl_height'	=>	$height,
				'rpl_close'		=>	LANG_JS_MANAGER_FORM_DIALOG_CLOSE,
				'rpl_open'		=>	LANG_JS_MANAGER_FORM_DIALOG_OPEN,
				'rpl_title'		=>	$title,
				'rpl_form'		=>	$_form_
			);

		$template = new templateManager();
		return $template->setTmplPath(sitePath().self::TMPL_PATH.'formDialog.html')->setReplacements($replacements)->process();
	}

}
