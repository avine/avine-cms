<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/*
 * Slideshow environment
 *
 * Example of use :
 *
 * final class mySlideshow extends slideshow
 * {
 * 		public function display() {
 * 			// Your code wich create javaScript + Html slideshow
 * 		}
 * }
 *
 * $slideshow = new mySlideshow();
 *
 * $slideshow
 * 	->add('1.jpg')
 * 	->add('2.jpg')
 * 	->add('3.jpg');
 *
 * echo $slideshow->display();
 *
 */

abstract class slideshow
{
	protected	$config = array();

	protected	$slide_base = '';

	protected	$slide = array();



	public function __construct( $width = 468, $height = 60, $slide_base = '' )
	{
		// Slideshow width and height
		$this->config['width'		] = $width;
		$this->config['height'		] = $height;

		// Basepath for all slides
		$this->slide_base = $slide_base;

		// Slideshow unique ID
		$this->uniqueIdSuffix(true);

		$this->duration();
		$this->navigation();

		return $this->wrapperID(); # Optional return to know where's located the slideshow in the DOM elements
	}



	// Slideshow unique ID suffix (Internal method. To get the final ID : call the wrapperID() method)
	private function uniqueIdSuffix( $increment = false )
	{
		static $id = array();

		isset($id[__CLASS__]) or $id[__CLASS__] = 0;

		!$increment or $id[__CLASS__]++;

		return $id[__CLASS__];
	}



	// ID attribute to find the wrapper <div id="ID"></div> in the DOM elements
	public function wrapperID()
	{
		return __CLASS__.$this->uniqueIdSuffix();
	}



	// Pause between 2 slides and slide fade duration
	public function duration( $pause = 4000, $fade = 1000 )
	{
		$this->config['pause'		] = $pause;
		$this->config['fade'		] = $fade;
	}



	// Enable navigation tool
	public function navigation( $bool = true )
	{
		$this->config['navigation'	] = $bool;
	}



	// Add new slide
	public function add( $image, $desc = '', $link = '' )
	{
		$this->slide[] = array($this->slide_base.$image, $desc, $link);

		return $this;
	}



	// Determine the link target attribute : "_self" or "_blank"
	public function linkTarget( $link )
	{
		$http = '(http://|https://)';
		$host = pregQuote($_SERVER['HTTP_HOST']);

		if (preg_match("~^($http{$host})~", $link) || !preg_match("~^$http~", $link))
		{
			return '_self';
		} else {
			return '_blank';
		}
	}



	public function debug()
	{
		echo '<b>CONFIG</b><br />';
		echo 'wrapper_id = '.$this->uniqueIdSuffix().'<br />';
		$table = new tableManager($this->config, array('Value'), 'Param');
		echo $table->html(1);

		echo '<b>SLIDE</b><br />';
		$table = new tableManager($this->slide, array('Image', 'Desc', 'Link'));
		echo $table->html(0);
	}



	// Create javaScript + Html output
	abstract public function display();
}


?>