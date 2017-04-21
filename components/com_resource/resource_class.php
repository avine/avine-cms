<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Class
class comResource_
{
	private	$base = array();

	private	$width_max; # Preview config

	const	THUMBS_DIR_NAME = '/thumbs'; # each directory 'myDir' have his thumbs directory 'myDir/thumbs'



	static function thumbKeyOptions()
	{
		return
			array(
				'width'		=>	LANG_COM_RESOURCE_THUMB_KEY_WIDTH,
				'height'	=>	LANG_COM_RESOURCE_THUMB_KEY_HEIGHT,
				'percent'	=>	LANG_COM_RESOURCE_THUMB_KEY_PERCENT
			);
	}



	static function beautifyPath( $path )
	{
		return str_replace('/', '<span class="comResource-beautifyPath">/</span>', $path);
	}



	public function __construct( $basedir_relative = WEBSITE_PATH, $width_max = '240' )
	{
		$this->base['path'] = 			$_SERVER['DOCUMENT_ROOT'].$basedir_relative;
		$this->base['url' ] = 'http://'.$_SERVER['HTTP_HOST'	].$basedir_relative;

		$this->width_max = $width_max;
	}



	public function getBase()
	{
		return
			array(
				'path'	=>	$this->base['path'],
				'url'	=>	$this->base['url' ]
			);
	}



	public function preview( $resource )
	{
		$resource_path = $this->base['path'].$resource;
		$resource_url  = $this->base['url' ].$resource;

		if (formManager_filter::isPathFile($resource_path))
		{
			$medias = new mediasManager();

			$ext = mediasManager::getFileExtension($resource_path);
			if ($medias->isAuthorizedExtension($ext))
			{
				$cat = mediasManager::getExtensionCategory($ext);
				switch($cat)
				{
					case 'image':
						list($width) = getimagesize($resource_path);
						$width <= $this->width_max or $width = $this->width_max;
						return $medias->display($resource_url, LANG_COM_RESOURCE_GO_TO_PREVIEW, $width);

					case 'audio';
						$height = mediasManager::AUDIO_HEIGHT;
						return $medias->display($resource_url, LANG_COM_RESOURCE_GO_TO_PREVIEW, $this->width_max, $height);

					case 'video':
						$height = round($this->width_max / mediasManager::VIDEO_WIDTH * mediasManager::VIDEO_HEIGHT + mediasManager::TOOL_BAR_HEIGHT);
						return $medias->display($resource_url, LANG_COM_RESOURCE_GO_TO_PREVIEW, $this->width_max, $height);

					case 'document':
					default:
						return $medias->display($resource_url, LANG_COM_RESOURCE_GO_TO_PREVIEW);
				}
			}
			else
			{
				return "<a href=\"$resource_url\" target=\"_blank\">".LANG_COM_RESOURCE_GO_TO_PREVIEW."</a>";
			}
		}
	}



	public function getimagesize( $resource )
	{
		if (is_file($resource_path = $this->base['path'].$resource))
		{
			return getimagesize($resource_path);
		}

		return false;
	}



	/**
	 * $thumb_value 				: define the size of the thumb (int)
	 * Availables $thumb_key are 	: 'width', 'height', or 'percent'
	 * $thumb_quality 				: from '1' to '100'
	 */
	public function createThumbnail( $source, $thumb_key = 'width', $thumb_value = '120', $thumb_quality = '85' )
	{
		$thumbs_dir = self::THUMBS_DIR_NAME;

		// Is the source available ?
		if (!is_file($source_path = $this->base['path'].$source)) {
			return false;
		}

		$path_info = pathinfo($source_path);
		$dirname 	= $path_info['dirname'	];
		$basename 	= $path_info['basename'	];
		$extension 	= $path_info['extension'];
		$filename 	= $path_info['filename'	];

		// Is this an image ?
		if (!in_array($extension, array('jpg', 'gif', 'png'))) {
			return false;
		}

		// Prevent recursive thumbs directories !
		if (preg_match('~('.pregQuote($thumbs_dir).')$~', $dirname)) {
			return false;
		}

		// Check the '/thumbs' directory availability
		if (!is_dir("$dirname{$thumbs_dir}") && !mkdir("$dirname{$thumbs_dir}")) {
			return false;
		}

		// Remove previous thumb
		if (is_file("$dirname{$thumbs_dir}/$basename") && !unlink("$dirname{$thumbs_dir}/$basename")) {
			return false;
		}

		// Get new size
		list($width, $height) = getimagesize($source_path);
		if (!$width || !$height) {
			return false;
		}
		switch($thumb_key)
		{
			// Fixed percentage
			case 'percent':
				$new_width  = $width  *$thumb_value/100;
				$new_height = $height *$thumb_value/100;
				break;

			// Fixed width
			case 'width':
				$new_width  = $thumb_value;
				$new_height = $thumb_value * $height/$width;
				break;

			// Fixed height
			case 'height':
				$new_width  = $thumb_value * $width/$height;
				$new_height = $thumb_value;
				break;

			default:
				trigger_error("Invalid \$thumb_key=$thumb_key parameter in".__METHOD__." (expected : 'width', 'height', 'percent')");
				return false;
		}

		// Get source
		switch ($extension)
		{
			case 'jpg';
				$image_source = imagecreatefromjpeg($source_path);
				break;

			case 'gif';
				$image_source = imagecreatefromgif($source_path);
				break;

			case 'png';
				$image_source = imagecreatefrompng($source_path);
				break;
		}

		// Create thumb
		$image_thumb = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled( $image_thumb,$image_source, 0,0,0,0, $new_width,$new_height, $width,$height );

		// Save thumb
		switch ($extension)
		{
			case 'jpg';
				$result = imagejpeg($image_thumb, "$dirname{$thumbs_dir}/$basename", $thumb_quality);
				break;

			case 'gif';
				$result = imagegif($image_thumb, "$dirname{$thumbs_dir}/$basename", $thumb_quality);
				break;

			case 'png';
				$result = imagepng($image_thumb, "$dirname{$thumbs_dir}/$basename", $thumb_quality);
				break;
		}

		// Free processor resources
		imagedestroy($image_thumb);

		if (!$result) {
			return false;
		}

		chmod("$dirname{$thumbs_dir}/$basename", 0604);															# Be sure the created file is readable !
		return preg_replace('~^('.pregQuote($this->base['path']).')~', '', "$dirname{$thumbs_dir}/$basename");	# Return the relative file path of the thumb image
	}



	/**
	 * This method is adapted from the createThumbnail() method.
	 * Destructive method : the original image is replaced by the resized one.
	 */
	public function resizeImage( $source, $thumb_key = 'width', $thumb_value = '120', $thumb_quality = '85', $create_copy = false )
	{
		// Is the source available ?
		if (!is_file($source_path = $this->base['path'].$source)) {
			return false;
		}

		$path_info = pathinfo($source_path);
		$dirname 	= $path_info['dirname'	];
		$basename 	= $path_info['basename'	];
		$extension 	= $path_info['extension'];
		$filename 	= $path_info['filename'	];

		// Is this an image ?
		if (!in_array($extension, array('jpg', 'gif', 'png'))) {
			return false;
		}

		// temporary file name
		$random_name = substr(md5(rand()), 0, 10);

		// Get new size
		list($width, $height) = getimagesize($source_path);
		if (!$width || !$height) {
			return false;
		}
		switch($thumb_key)
		{
			// Fixed percentage
			case 'percent':
				$new_width  = $width  *$thumb_value/100;
				$new_height = $height *$thumb_value/100;
				break;

			// Fixed width
			case 'width':
				$new_width  = $thumb_value;
				$new_height = $thumb_value * $height/$width;
				break;

			// Fixed height
			case 'height':
				$new_width  = $thumb_value * $width/$height;
				$new_height = $thumb_value;
				break;

			default:
				trigger_error("Invalid \$thumb_key=$thumb_key parameter in".__METHOD__." (expected : 'width', 'height', 'percent')");
				return false;
		}

		// Get source
		switch ($extension)
		{
			case 'jpg';
				$image_source = imagecreatefromjpeg($source_path);
				break;

			case 'gif';
				$image_source = imagecreatefromgif($source_path);
				break;

			case 'png';
				$image_source = imagecreatefrompng($source_path);
				break;
		}

		// Create thumb
		$image_thumb = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled( $image_thumb,$image_source, 0,0,0,0, $new_width,$new_height, $width,$height );

		// Save thumb
		switch ($extension)
		{
			case 'jpg';
				$result = imagejpeg($image_thumb, "$dirname/$random_name", $thumb_quality);
				break;

			case 'gif';
				$result = imagegif($image_thumb, "$dirname/$random_name", $thumb_quality);
				break;

			case 'png';
				$result = imagepng($image_thumb, "$dirname/$random_name", $thumb_quality);
				break;
		}

		// Free processor resources
		imagedestroy($image_thumb);

		if (!$result) {
			return false;
		}

		if (!$create_copy)
		{
			unlink("$dirname/$basename");													# Delete original
			$resized_name = "$dirname/$basename";
		} else {
			@unlink("$dirname/{$filename}_resized.$extension");								# Delete previous copy
			$resized_name = "$dirname/{$filename}_resized.$extension";
		}

		rename("$dirname/$random_name", $resized_name);
		chmod($resized_name, 0604);															# Be sure the created file is readable !
		return preg_replace('~^('.pregQuote($this->base['path']).')~', '', $resized_name);	# Return the relative file path of the resized image
	}



	static function fileExtensionIcon( $extension )
	{
		if (is_file(sitePath()."/components/com_resource/images/extensions/icon_$extension.png"))
		{
			return "<img src=\"".siteUrl()."/components/com_resource/images/extensions/icon_$extension.png\" alt=\"icon_$extension.png\" border=\"0\" />";
		}

		return '';
	}


}


?>