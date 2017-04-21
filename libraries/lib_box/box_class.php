<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Class
class boxManager
{
	public	$class_prefix = '';

	const	CLASS_PREFIX_FRONTEND	= 'box';
	const	CLASS_PREFIX_BACKEND	= 'admin_box';



	public function __construct( $class_prefix = false )
	{
		if ($class_prefix === false)
		{
			// Load frontend or backend css class ?
			if (defined('WEBSITE_PATH') && preg_match('~^('.pregQuote(WEBSITE_PATH.'/admin/').')~', $_SERVER['PHP_SELF']))
			{
				$class_prefix = self::CLASS_PREFIX_BACKEND;
			} else {
				$class_prefix = self::CLASS_PREFIX_FRONTEND;
			}
		}

		$this->class_prefix = $class_prefix;
	}



	public function echoMultiMessage( $content_array, $title = '', $class = 'error', $icon = true, $width = '100%' )
	{
		echo $this->multiMessage($content_array, $title, $class, $icon, $width);
	}



	public function multiMessage( $content_array, $title = '', $class = 'error', $icon = true, $width = '100%' )
	{
		$sep = '&bull;&nbsp; ';
		$content_html = $sep.implode("<hr />\n$sep", $content_array);

		$title ? $title = "<p class=\"".$this->getClassPrefix()."title\">$title</p>\n" : '';

		return $this->message($title.$content_html, $class, $icon, $width);
	}



	// After processing something, display a message about the result
	public function echoResult( $result, $success = '', $failure = '', $width = 0 )
	{
		echo $this->result($result, $success, $failure, $width);
	}



	public function result( $result, $success = '', $failure = '', $width = 0 )
	{
		if ($success == '') {
			$success = LANG_BOX_MANAGER_PROCESS_SUCCESS;
		}
		if ($failure == '') {
			$failure = LANG_BOX_MANAGER_PROCESS_FAILURE;
		}

		if ($result)
		{
			return $this->message($success, 'ok', true, $width);
		} else {
			return $this->message($failure, 'error', true, $width);
		}
	}



	// Display a specific message
	public function echoMessage( $content, $class = 'error', $icon = true, $width = 0 )
	{
		echo $this->message($content, $class, $icon, $width);
	}



	public function message( $content, $class = 'error', $icon = true, $width = 0 )
	{
		return $this->boxHTML($content, $class, $icon, $width);
	}



	/**
	 * Private functions
	 */

	private function boxHTML( $content, $class = '', $icon = true, $width = 0 )
	{
		if ($width == 0)
		{
			$tag = 'span'; # Online message
		} else {
			$tag = 'div'; # Block message
		}

		if ($icon)
		{
			$html = "<$tag class=\"icon\">$content</$tag>";
		} else {
			$html = "$content";
		}

		$html = "<$tag class=\"" .$this->getClass($class). "\"" .$this->getWidthStyle($width). ">$html</$tag>";

		if ($tag == 'span') {
			$html = "<p class=\"$this->class_prefix\">$html</p>";
		}

		return "\n$html\n\n";
	}



	private function getClass( $class, $debug = false )
	{
		$class_list = array( 'error', 'ok', 'warning', 'info', 'help', 'tips' );

		$class = strtolower($class);

		if (in_array($class, $class_list))
		{
			return $this->getClassPrefix().$class;
		} else {
			return $this->getClassPrefix().'missing'; # inform that the type is invalid !
		}
	}



	private function getClassPrefix()
	{
		if ($this->class_prefix)
		{
			return "$this->class_prefix-"; # Add a character separator '-'
		} else {
			return '';
		}
	}



	private function getWidthStyle( $width )
	{
		$width = trim($width);

		if (!$width || $width == '100%') {
			return '';
		}

		if (!preg_match('~(px|%)$~', $width)) {
			$width .= 'px'; # default unit
		}

		return " style=\"width:$width;\"";
	}

}


?>