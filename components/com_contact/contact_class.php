<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



class comContact_
{
	protected	$title		= array();
	protected	$contact	= array();

	const		IMAGE_DIR	= '/components/com_contact/e/'; # 'e' for 'emails' but don't tell the spiders that this is the emails directory !
	const		FONT_PATH	= '/components/com_contact/font/arial.ttf';



	public function __construct()
	{
		global $db;
		$contact = $db->select('contact, [user_id], contact_order(asc), title');
		foreach($contact as $user_id => $details)
		{
			$user_details = new comUser_details($user_id);
			if (!$user_details->isInvalidUserID() && $user_details->get('activated'))
			{
				$this->title	[$user_id] = $details['title'];
				$this->contact	[$user_id] = $user_details;
			}
		}
	}



	public function title( $user_id )
	{
		if (isset($this->title[$user_id]))
		{
			return $this->title[$user_id];
		} else {
			return NULL;
		}
	}



	public function userDetails( $user_id )
	{
		if (isset($this->contact[$user_id]))
		{
			return $this->contact[$user_id];
		} else {
			return NULL;
		}
	}



	public function options()
	{
		$options = array();

		foreach($this->contact as $user_id => $user_details)
		{
			if ($this->title[$user_id])
			{
				$info = $this->title[$user_id];			# Contact title (if specified)
			} else {
				$info = $user_details->get('username');	# Username
			}
			$options[$user_id] = $info;
		}

		return $options;
	}



	/**
	 * @param boolean $link_to Link the image to the contact page
	 */
	public function preview( $user_id, $link_to = false )
	{
		$html = '';

		if (isset($this->contact[$user_id]))
		{
			$user_details = $this->contact[$user_id];
			
			if ($full_name = $user_details->getFullName()) {
				$html .= "<strong>$full_name</strong><br />";
			}

			if ($full_title = $user_details->getFullTitle()) {
				$html .= "<em>$full_title</em><br />";
			}

			$html .= $user_details->getFullAdress('<br />');

			if ($phone_1 = $user_details->get('phone_1')) {
				$phone = $phone_1;
			}
			if ($phone_2 = $user_details->get('phone_2')) {
				isset($phone) ? $phone .= " - $phone_2" : $phone = $phone_2;
			}
			!isset($phone) or $html .= "$phone<br />";

			// Add the email image
			if ($img = $this->emailImage($user_id))
			{
				!$link_to or $img = '<a href="'.comMenu_rewrite("com=contact&page=index&id=$user_id").'" class="contact_link-to" title="'.LANG_CONTACT_SEND_MAIL_TO."\">$img</a>";
				$html .= $img;
			}
		}

		if ($html) {
			$html = "<div class=\"contact_preview\" id=\"contact_preview_$user_id\">$html</div>\n";
		}

		return $html;
	}



	public function previewAll()
	{
		$html = '';
		foreach($this->contact as $user_id => $user_details)
		{
			$html .= $this->preview($user_id);
		}
		return $html;
	}



	public function emailImageTask()
	{
		// Check the images directory
		is_dir($image_dir = sitePath().self::IMAGE_DIR) or mkdir($image_dir);

		// Check TTF font path
		if (!is_file($font_path = sitePath().self::FONT_PATH)) {
			 trigger_error('TTF font is missing !', E_USER_ERROR);
			 return;
		}

		// Let's go
		foreach($this->contact as $user_id => $user_details)
		{
			$image_path = $image_dir."$user_id.png";

			// Check the image availability and modification time
			if (!is_file($image_path) || (filemtime($image_path) < time()-60*60*24)) # Create the images once per day
			{
				$user_email = $user_details->get('email');

				// Image size
				$size = array(
					'width'		=> 12*strlen($user_email),
					'height'	=> 18
				);

				// Create image
				$image = imagecreatetruecolor($size['width'], $size['height']); # Create image

				// Image colors
				$color = array(
					'background'=> imagecolorallocate($image, 255, 255, 255),	// #FFF
					'text'		=> imagecolorallocate($image,  51,  51,  51)	// #333
				);

				// Fill background
				imagefilledrectangle($image, 0, 0, $size['width']-1, $size['height']-1, $color['background']);

				// Add text
				imagettftext($image, 10, 0, 0, 14, $color['text'], $font_path, $user_email);

				// Output image
				imagepng($image, $image_path);
				imagedestroy($image);
			}
		}
	}



	public function emailImage( $user_id )
	{
		$image_path = self::IMAGE_DIR."$user_id.png";

		if (is_file(sitePath().$image_path)) {
			return '<img src="'.siteUrl().$image_path.'" alt="" />';
		}
	}

}


