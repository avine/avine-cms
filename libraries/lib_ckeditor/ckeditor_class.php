<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Require
require_once(sitePath().'/plugins/php/ckeditor/ckeditor.php');


// Class
class loadMyCkeditor
{
	public	$CKEditor;

	public	$output = '';



	public function __construct()
	{
		$this->CKEditor = new CKEditor();

		$this->CKEditor->basePath = WEBSITE_PATH.'/plugins/php/ckeditor/';

		$this->CKEditor->returnOutput = true;

		$this->contentCss();

		/*
		config.toolbar_Full =
		[
		    ['Source','-','Save','NewPage','Preview','-','Templates'],
		    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
		    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
		    '/',
		    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		    ['Link','Unlink','Anchor'],
		    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		    '/',
		    ['Styles','Format','Font','FontSize'],
		    ['TextColor','BGColor'],
		    ['Maximize', 'ShowBlocks','-','About']
		];
		 */
		$this->CKEditor->config['toolbar'] =
			array(
				array( 'PasteText' ),
				array( 'Bold','Italic', '-', 'Link','Unlink' ),
				array( 'Image','Table','HorizontalRule','SpecialChar' ),
				//array( 'Blockquote' ),
				array( 'Format' ),
				array( 'NumberedList','BulletedList' ),
				array( 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ),
				array( 'Undo','Redo' ),
				array( 'SelectAll','RemoveFormat' ),
				array( 'Maximize', 'ShowBlocks', '-', 'Source' ),
			);

		return $this;
	}



	// Set the customized css file applied to the editor
	public function contentCss( $relative_path = '' ) # Path is relative to the website root
	{
		// Get the default 'index.css' file of the website template, wich contains only the contents styles (not the wrappers styles)
		$relative_path or $relative_path = comTemplate_defaultIndexCss();

		if ($relative_path)
		{
			if (is_file($_SERVER['DOCUMENT_ROOT'].$relative_path))
			{
				$this->CKEditor->config['contentsCss'] = $relative_path;
			} else {
				trigger_error("File not found : $relative_path");
			}
		}
	}



	public function addName( $name )
	{
		$this->output .= $this->CKEditor->replace($name);

		return $this;
	}



	public function __toString()
	{
		return $this->output;
	}

}


?>