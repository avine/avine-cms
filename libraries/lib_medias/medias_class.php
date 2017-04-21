<?php
/* Avine. Copyright (c) 2008 Stéphane Francel (http://avine.fr). Dual licensed under the MIT and GPL Version 2 licenses. */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Abstract Class
abstract class mediasManager_abstract
{
	protected static $extensions = array('pdf', 'mp3', 'mp4', 'flv', 'jpg', 'gif', 'png'); # Authorized extensions inside the class

	protected	$medias			= array();				# All medias datas : $this->medias[$n] = array( 'ext'=>'', 'src'=>'', 'title'=>'', 'width'=>'', 'height'=>'', 'preview'=>'' );
	protected	$string_error	= array();				# List of problems occured while analysis $string parameter in $this->stringSet() method

	protected	$add_download_link		= true;			# Add a download button to the media source (boolean)
	protected	$download_link_img		= '/libraries/lib_medias/images/download-link_32.png'; # Path to the download image
	const		DOWNLOAD_QUERY_STRING	= 'filepath';	# Example: http://avine.fr/download.php?filepath=/resource/image/sample.jpg

	protected	$autocomplete	= true;					# Use autocompletion in the method formInputs()



	/**
	 * Configuration
	 */

	public function __construct( $add_download_link = true )
	{
		$this->add_download_link = $add_download_link;
	}



	/**
	 * Manage self::$extensions property
	 */

	public function isAuthorizedExtension( $ext )
	{
		if (in_array($ext, self::$extensions))
		{
			return true;
		} else {
			return false;
		}
	}



	// Reduce the list of extensions into 4 categories : image, document, audio, video
	static public function getExtensionCategory( $ext )
	{
		switch($ext)
		{
			case 'jpg':
			case 'gif':
			case 'png':
				return 'image';

			case 'pdf':
				return 'document';

			case 'mp3':
				return 'audio';

			case 'mp4':
			case 'flv':
				return 'video';

			default:
				trigger_error("No category is defined for this extension : <b>'$ext'</b> in : ".__METHOD__);
				return false;
		}
	}



	// Get the extension of any file
	static public function getFileExtension( $file )
	{
		$pathinfo = pathinfo($file);
		$ext = strtolower($pathinfo['extension']);
		return $ext;
	}



	/**
	 *  Manage $this->medias property by using : strings (core methods)
	 */

	// Set $this->medias property (and $this->string_error if errors occured while analysis the string)
	public function stringSet( $string )
	{
		// Each media is separated by ';'
		$string = explode(';', $string);
		for ($i=0; $i<count($string); $i++)
		{
			// Empty part ?
			if (preg_match('~^(\s|\t|\n|\r)*$~', $string[$i])) {
				continue;
			}

			$media = array(); # Here's the new media we want to fill !

			$detected_src 	= false;
			$detected_title = false;

			// Each argument is separated by ','
			$argument = explode(',', $string[$i]);
			for ($j=0; $j<count($argument); $j++)
			{
				$n_v = explode('=', $argument[$j]); # name and value of the current argument
				$name  = trim(strtolower($n_v[0]));
				isset($n_v[1]) ? $value = trim($n_v[1]) : $value = '';

				// Arguments validation
				switch ($name)
				{
					case 'src':
						$detected_src = true;
						if (formManager_filter::isPathFile($value))
						{
							$media['src'] = $value;

							$ext = $this->getFileExtension($value);
							$this->isAuthorizedExtension($ext) ? $media['ext'] = $ext :
								$this->string_error[] =
									LANG_COM_MEDIAS_ERROR_INVALID_EXTENSION .htmlentities($ext, ENT_COMPAT, 'UTF-8').
									"<br /><i>(".LANG_COM_MEDIAS_ERROR_VALID_EXTENSIONS.implode(', ', self::$extensions).")</i>";
						} else {
							$this->string_error[] = LANG_COM_MEDIAS_ERROR_INVALID_SRC .htmlentities($value, ENT_COMPAT, 'UTF-8');
						}
						break;

					case 'title':
						$detected_title = true;
						$value ? $media['title'] = $value :
							$this->string_error[] = LANG_COM_MEDIAS_ERROR_EMPTY_TITLE; # Notice : the 'title' can not be empty !
						break;

					case 'width':
						formManager_filter::isInteger($value) ? $media['width'] = $value :
							$this->string_error[] = LANG_COM_MEDIAS_ERROR_INVALID_WIDTH .htmlentities($value, ENT_COMPAT, 'UTF-8');
						break;

					case 'height':
						formManager_filter::isInteger($value) ? $media['height'] = $value :
							$this->string_error[] = LANG_COM_MEDIAS_ERROR_INVALID_HEIGHT .htmlentities($value, ENT_COMPAT, 'UTF-8');
						break;

					case 'preview':
						formManager_filter::isPathFile($value) ? $media['preview'] = $value :
							$this->string_error[] = LANG_COM_MEDIAS_ERROR_INVALID_PREVIEW .htmlentities($value, ENT_COMPAT, 'UTF-8');
						break;
				}
			}

			// Media validation
			if (isset($media['src']) && isset($media['ext']))
			{
				if (isset($media['title']))
				{
					$this->medias[] = $media; # Cool ! All validation passed, let's add the media !
				}
				elseif (!$detected_title) {
					$this->string_error[] = LANG_COM_MEDIAS_ERROR_MISSING_TITLE;
				}
			}
			elseif (!$detected_src) {
				$this->string_error[] = LANG_COM_MEDIAS_ERROR_MISSING_SOURCE;
			}
		}
	}



	// Check $this->medias property (after called this->stringSet() method)
	public function stringValidated()
	{
		if (count($this->string_error))
		{
			return false;
		} else {
			return true;
		}
	}



	// Get medias string errors in Html format (after called this->stringSet() method)
	public function stringError()
	{
		if (count($this->string_error))
		{
			return LANG_COM_MEDIAS_STRING_ERROR_TITLE.'<br />'.implode('<br />', $this->string_error);
		}
	}



	// Format medias string to be ready for database insertion
	public function stringGet()
	{
		$string = '';
		for ($i=0; $i<count($this->medias); $i++)
		{
			$string .= self::arrayToString($this->medias[$i]);
		}
		return $string;
	}



	// Convert an array into string
	static public function arrayToString( $array )
	{
		$string = array();							# array

		reset($array);
		foreach($array as $key => $val) {
			$string[] = "$key=$val";
		}
		$string = implode(",\n", $string).";\n";	# string

		return $string;
	}



	/**
	 * Manage $this->medias property by using : inputs form
	 */

	public function formProcess()
	{
		$filter = new formManager_filter();
		$filter->requestVariable('post');

		// Suffix list
		$suffix = formManager_filter::arrayOnly( $filter->requestName('media_title_')->get(), false );

		// Keep list
		$keep = formManager_filter::arrayOnly( $filter->requestName('media_keep_')->get(), false );

		// Re-order the suffixes wich are in the keep list !
		$new_list = array();
		for ($i=0; $i<count($suffix); $i++) {
			if (in_array($suffix[$i], $keep)) {
				$new_list[$suffix[$i]] = $filter->requestValue('media_order_'.$suffix[$i])->get();
			}
		}
		asort($new_list);
		$suffix = array_keys($new_list);

		for ($i=0; $i<count($suffix); $i++)
		{
			$array = array();

			$array['title'	] = $this->cleanString($filter->requestValue('media_title_'	.$suffix[$i])->get()); # that's what we have at least !

			$src	 	= $filter->requestValue('media_src_'	.$suffix[$i])->get();
			$preview 	= $filter->requestValue('media_preview_'.$suffix[$i])->get();
			$width 		= $filter->requestValue('media_width_'	.$suffix[$i])->get();
			$height 	= $filter->requestValue('media_height_'	.$suffix[$i])->get();

			$src 		? $array['src'		] = $this->cleanString($src		) : '';
			$preview 	? $array['preview'	] = $this->cleanString($preview	) : '';
			$width 		? $array['width'	] = $this->cleanString($width	) : '';
			$height 	? $array['height'	] = $this->cleanString($height	) : '';

			// Try updating $this->medias property throw $this->stringSet() method
			$string = self::arrayToString($array);
			$this->stringSet($string);
		}
	}



	static function cleanString( $string )
	{
		return preg_replace('~(,|;)~', '', $string); # simply remove reserved characters
	}



	// Display $this->medias property as inputs form
	public function formInputs( $form_id = '', $update = NULL, $add_new = true )
	{
		$form_part = array();

		// Form ID
		$form = new formManager();
		$form->setForm('post', $form_id);

		// Update fields ?
		if (!isset($update)) {
			$update = '';
		} else {
			$update ? $update = 'update=1;' : $update = 'update=0;';
		}

		$count = count($this->medias);
		if ($add_new) {
			$count = $count+2;
		}
		for ($i=0; $i<$count; $i++)
		{
			$title 		= '';
			$src 		= '';
			$preview 	= '';
			$width 		= '';
			$height 	= '';

			if ($i < count($this->medias))
			{
				// Default values
				isset($this->medias[$i]['title'		]) ? $title 	= $this->medias[$i]['title'		] : '';
				isset($this->medias[$i]['src'		]) ? $src 		= $this->medias[$i]['src'		] : '';
				isset($this->medias[$i]['preview'	]) ? $preview 	= $this->medias[$i]['preview'	] : '';
				isset($this->medias[$i]['width'		]) ? $width 	= $this->medias[$i]['width'		] : '';
				isset($this->medias[$i]['height'	]) ? $height 	= $this->medias[$i]['height'	] : '';

				$keep = '1';
			} else {
				$keep = '0';
			}

			// management fields (addon)
			$form_part[$i]['keep'	] = $form->checkbox("media_keep_$i", $keep		, '', '', $update			);
			$form_part[$i]['order'	] = $form->text("media_order_$i"	, (2*$i+1)	, '', '', $update.'size=2'	);

			// media fields
			$form_part[$i]['title'	] = $form->text("media_title_$i", $title, '', '', $update.'size=default');
			if (!$this->autocomplete)
			{
				$list_src = self::getResourcesList('file_options');
				$list_preview = self::getResourcesList('file_options', array('jpg', 'gif', 'png'));

				$form_part[$i]['src'	] = $form->select("media_src_$i"		, formManager::selectOption($list_src		, $src		), '', '', $update);
				$form_part[$i]['preview'] = $form->select("media_preview_$i"	, formManager::selectOption($list_preview	, $preview	), '', '', $update);
			} else {
				$form_part[$i]['src'	] = $form->text("media_src_$i"		, $src		, '', '', $update);
				$form_part[$i]['preview'] = $form->text("media_preview_$i"	, $preview	, '', '', $update);

				$form_part[$i]['src'	] .= self::getAutocompleteScript($form_id."media_src_$i");
				$form_part[$i]['preview'] .= self::getAutocompleteScript($form_id."media_preview_$i", array('jpg','gif','png'));
			}
			$form_part[$i]['width'	] = $form->text("media_width_$i"	, $width	, '', '', $update.'size=2');
			$form_part[$i]['height'	] = $form->text("media_height_$i"	, $height	, '', '', $update.'size=2');
		}

		return $form_part;
	}



	static public function formInputsHeader()
	{
		return
			array(
				LANG_COM_MEDIAS_LABEL_KEEP,
				LANG_COM_MEDIAS_LABEL_ORDER,
				LANG_COM_MEDIAS_LABEL_TITLE,
				LANG_COM_MEDIAS_LABEL_SRC,
				LANG_COM_MEDIAS_LABEL_PREVIEW,
				LANG_COM_MEDIAS_LABEL_WIDTH,
				LANG_COM_MEDIAS_LABEL_HEIGHT
			);
	}



	static function getResourcesList( $get_tree_options = '', $extensions = array(), $option_root = LANG_COM_MEDIAS_LABEL_SELECT_OPTION_ROOT )
	{
		static $ftp;
		if (!$ftp) {
			$ftp = new ftpManager(sitePath());
			$ftp->setTree(RESOURCE_PATH)->reduceTree('remove_invalid_dir_and_file');
		}

		if (!$extensions || array_diff($extensions, self::$extensions)) {
			$extensions = self::$extensions;
		}
		$ftp->reduceTree('keep_file_by_extension', $extensions);

		return
			array_merge(
				$option_root ? array('' => $option_root) : array(),
				$ftp->getTree($get_tree_options)
			);
	}



	static public function getAutocompleteScript( $id, $extensions = array() )
	{
		$source = siteUrl().'/libraries/lib_medias/autocomplete.php';

		if ($extensions && !array_diff($extensions, self::$extensions)) {
			$source .= '?ext='.implode(',', $extensions);
		}
		/*
		 * If you need to limit the height of the results add :
		 * \$('.ui-autocomplete').css('max-height', '300px').css('overflow-y', 'auto').css('overflow-x', 'hidden');
		 */
		return <<<END
<script type="text/javascript">$(function(){
	\$('#$id').autocomplete({source:'$source', minLength:3});
});</script>
END;
	}



	public function setAutocomplete( $bool )
	{
		$this->autocomplete = $bool ? true : false;
	}



	/**
	 * Manage $this->medias property by using : parameters
	 */

	public function paramSet( $src, $title, $width = '', $height = '', $preview = '' )
	{
		// Convert the parameters into an array
		$array = array(
			'ext'		=> self::getFileExtension($src),
			'src'		=> $src,
			'title'		=> $title
		);
		$width		? $array['width'	] = $width		: '';
		$height		? $array['height'	] = $height		: '';
		$preview	? $array['preview'	] = $preview	: '';

		// Try updating $this->medias property throw $this->stringSet() method
		$string = self::arrayToString($array);
		$this->stringSet($string);
	}



	/**
	 * Accessor of $this->medias property
	 */

	public function arrayGet()
	{
		return $this->medias;
	}



	/**
	 * Get Html output from $this->medias property
	 *
	 * Notice :
	 * --------
	 * -> Each media of this->medias property have a 'title' and a 'src' at least. So, when using $this->showMedias() method, each media will have a title.
	 * -> If you want to display a media without the 'title' restriction, use : $this->display() method.
	 */

	public function showMedias( $path_prefix = '' )
	{
		if (!count($this->medias)) {
			return;
		}

		// Process the media preference
		self::mediaPreference();

		$html = '';

		// Display medias using jQuery Tabs
		$tabs = 'medias-tabs';

		$head = '';
		$body = '';

		for ($i=0; $i<count($this->medias); $i++)
		{
			$class		= self::getMediaClass($this->medias[$i]['ext']);

			$src		= self::addPathPrefix($this->medias[$i]['src'		], $path_prefix);
			$preview	= self::addPathPrefix(@$this->medias[$i]['preview'	], $path_prefix);

			$title		= $this->medias[$i]['title'];
			$content	= $this->display($src, $this->medias[$i]['title'], @$this->medias[$i]['width'], @$this->medias[$i]['height'], $preview);
			$download	= $this->addDownloadLink($src);

			$head .= "\t<li><a href=\"#$tabs-$i\"><span{$class}>$title</span></a></li>\n";
			$body .= "<div class=\"$tabs\" id=\"$tabs-$i\">\n$download\n$content\n</div>\n";
		}
		$head = "\n<ul class=\"medias-manager-full\">\n$head</ul>\n";

		// Block title
		LANG_COM_MEDIAS_SHOW_TITLE ? $t = '<h3>'.LANG_COM_MEDIAS_SHOW_TITLE."</h3>\n" : $t = '';

		// Optional : disable the Tabs fonctionnality if there's only one Tab (it's usefull if you let's the Tabs id)
		if (count($this->medias) === 1) {
			#$html .= "\n\n".'<script type="text/javascript">$(document).ready(function(){$("#medias-tabs").tabs("destroy")});</script>';
		}

		if (count($this->medias) >= 2)
		{
			// Include the tags to enable the Tabs fonctionnality
			$html .=
				"\n\n<!-- mediasManager::showMedias (begin) -->\n".
				$t."<div id=\"$tabs\">".$head.$body."</div>\n".
				"<!-- mediasManager::showMedias (end) -->\n\n";
		} else {
			// Do not include the tags of the Tabs fonctionnality
			$html .=
				"\n\n<!-- mediasManager::showMedias (begin) -->\n".
				$t."<div class=\"medias-manager-full medias-manager-full-single\"><span{$class}>$title</span></div>".$body."\n".
				"<!-- mediasManager::showMedias (end) -->\n\n";
		}

		return $html;
	}



	static public function getMediaClass( $ext )
	{
		if ($cat = self::getExtensionCategory($ext))
		{
			return " class=\"medias-manager-$cat\"";
		}
	}



	static protected function addPathPrefix( $path, $path_prefix )
	{
		if ($path && $path_prefix && !preg_match('~^(http://|https://)~', $path))
		{
			return $path_prefix.$path;
		} else {
			return $path;
		}
	}



	public function showMediasPreview( $href = '' )
	{
		if (!count($this->medias)) {
			return;
		}

		// Process the media preference
		self::mediaPreference();

		if ($href)
		{
			$href = " href=\"$href\"";
			$tag = 'a';
		} else {
			$tag = 'span';
		}

		$html = '';

		for ($i=0; $i<count($this->medias); $i++) {
			$class = self::getMediaClass($this->medias[$i]['ext']);
			$title = $this->medias[$i]['title'];

			$html .= "<$tag{$href}$class>$title</$tag> ";
		}

		// Block title
		LANG_COM_MEDIAS_SHOW_PREVIEW_TITLE ? $t = '<span class="medias-manager-preview-title">'.LANG_COM_MEDIAS_SHOW_PREVIEW_TITLE.'</span> ' : $t = '';

		return "\n<p class=\"medias-manager-preview\">$t{$html}</p>\n";
	}



	/**
	 * Display Html output by using function parameters
	 *
	 * Notice :
	 * --------
	 * -> In this method, the title can be empty !
	 */

	public final function display( $src, $title = '', $width = '', $height = '', $preview = '' )
	{
		$cat = self::getExtensionCategory(self::getFileExtension($src));

		switch($cat)
		{
			case 'image';
				return $this->displayImage		($src, $title, $width, $height, $preview);

			case 'document';
				return $this->displayDocument	($src, $title, $width, $height, $preview);

			case 'audio';
				return $this->displayAudio		($src, $title, $width, $height, $preview);

			case 'video';
				return $this->displayVideo		($src, $title, $width, $height, $preview);
		}
	}



	/**
	 * Here is the way you want to display the medias
	 * Thoses methods should return an Html output
	 */
	abstract protected function displayImage	( $src, $title = '', $width = '', $height = '', $preview = '' );
	abstract protected function displayDocument	( $src, $title = '', $width = '', $height = '', $preview = '' );
	abstract protected function displayAudio	( $src, $title = '', $width = '', $height = '', $preview = '' );
	abstract protected function displayVideo	( $src, $title = '', $width = '', $height = '', $preview = '' );



	/**
	 * Add download link to medias including the media filesize when available
	 */

	public function addDownloadLink( $src )
	{
		if (!$this->add_download_link) {
			return;
		}

		$download_link_img = '<img src="'.WEBSITE_PATH.$this->download_link_img.'" alt="'.LANG_COM_MEDIAS_DOWNLOAD_LINK.'" />';

		$parse_url = parse_url($src);

		if (isset($parse_url['host']) && $parse_url['host'] && $parse_url['host'] != $_SERVER['HTTP_HOST'])
		{
			// The resource is NOT located on the local server. So, use a simple link to it !
			return	"<a href=\"$src\" title=\"".LANG_COM_MEDIAS_DOWNLOAD_LINK."\" class=\"medias-manager-download\" target=\"_blank\" ".
					"onclick=\"window.open('$src','".LANG_COM_MEDIAS_DOWNLOAD_LINK."','toolbar=0,menubar=0,location=0,width=800,height=600');return false;\" >$download_link_img</a>";
		}
		else
		{
			// The resource is located on the local server. So, use the '/libraries/lib_medias/download.php' script
			if (is_file(sitePath().'/download.php'))
			{
				$download_script_path = '/download.php'; # Simple alias of the real script, but located at the website root
			} else {
				$download_script_path = '/libraries/lib_medias/download.php';
			}
			$href = siteUrl()."$download_script_path?".self::DOWNLOAD_QUERY_STRING.'='.$parse_url['path'];

			is_file(sitePath().$src) ? $filesize = ftpManager::convertBytes(filesize(sitePath().$src), 'optimize') : $filesize = '';
			if ($filesize)
			{
				$filesize_title = ' ('.LANG_COM_MEDIAS_DOWNLOAD_LINK_FILE_SIZE." : $filesize)";
				$filesize .= '<br />'; # Some Html added here to put the text and the image on 2 lines !
			} else {
				$filesize_title = '';
			}
			return	"<a href=\"$href\" title=\"".LANG_COM_MEDIAS_DOWNLOAD_LINK.$filesize_title."\" class=\"medias-manager-download\">{$filesize}$download_link_img</a>";
		}
	}



	/**
	 * Media preference
	 */

	protected function mediaPreference()
	{
		$status = self::mediaPreferenceStatus();
		if ($status == 'all') {
			return;
		}

		// Separated list of audios and videos resources
		$audio = array();
		$video = array();

		for ($i=0; $i<count($this->medias); $i++)
		{
			$pathinfo = pathinfo($this->medias[$i]['src']);

			if (self::getExtensionCategory($pathinfo['extension']) == 'audio') {
				$audio[$i] = $pathinfo['filename'];
			}
			elseif (self::getExtensionCategory($pathinfo['extension']) == 'video') {
				$video[$i] = $pathinfo['filename'];
			}
		}

		if ($status == 'video') {
			// If duplicate, keep video !
			$remove	= $audio;
			$keep	= $video;
		}
		elseif ($status == 'audio') {
			// If duplicate, keep audio !
			$remove	= $video;
			$keep	= $audio;
		}

		// List of $remove resources wich can be removed (because there's the same one in the $keep list)
		$intersect = array_intersect($remove, $keep);

		$medias = array();
		for ($i=0; $i<count($this->medias); $i++)
		{
			// Keep the "unique" resources
			if (!array_key_exists($i, $remove) || !array_key_exists($i, $intersect))
			{
				$medias[] = $this->medias[$i];
			}
		}
		$this->medias = $medias;
	}



	static public function mediaPreferenceButton( $button_audio = LANG_COM_MEDIAS_PREF_BUTTON_AUDIO, $button_video = LANG_COM_MEDIAS_PREF_BUTTON_VIDEO )
	{
		$html = '';

		// Activate the media preference feature !
		$switch = self::mediaPreferenceStatus(true);

		$form = new formManager(0,0);
		$html .= $form->form('post', formManager::reloadPage(), 'media_manager_pref_');

		$switch == 'audio' ? $text = $button_audio : $text = $button_video;
		$html .= $form->submit('switch', $text);

		$html .= $form->end();

		return $html;
	}



	static protected function mediaPreferenceStatus( $activate = false )
	{
		// The first time, the feature will be enabled only if $activate==true !
		$session = new sessionManager(sessionManager::FRONTEND, 'media_manager_pref_');
		if (!$activate && !$session->get('switch')) {
			return 'all';
		}

		$switch = 'audio';
		if (!$session->get('switch')) {
			if (isset($_COOKIE['media_manager_pref']) && in_array($_COOKIE['media_manager_pref'], array('audio', 'video'))) {
				$switch = $_COOKIE['media_manager_pref'];
			}
		}
		$session->init('switch', $switch);

		// Check once for new status !
		static $process_once = false;
		if (!$process_once)
		{
			if (formManager::isSubmitedForm('media_manager_pref_'))
			{
				($session->get('switch') == 'audio') ? $switch = 'video' : $switch = 'audio';
				$session->set('switch', $switch);
				setcookie('media_manager_pref', $switch, time()+(60*60*24*365*3)); # Expires : 3 years
			}
		}
		$process_once = true;

		// Return the current status
		return $session->get('switch');
	}



	// Known limitation : this method must be called after a call of the protected method $this->mediaPreferenceStatus()
	static public function mediaPreferenceGetStatus()
	{
		$session = new sessionManager(sessionManager::FRONTEND, 'media_manager_pref_');
		return $session->get('switch');
	}



	static public function mediaPreferenceDisable()
	{
		$session = new sessionManager(sessionManager::FRONTEND, 'media_manager_pref_');
		$session->reset();
	}

}



// Class
class mediasManager extends mediasManager_abstract
{
	public	$player_path		= '/libraries/lib_medias/mediaplayer/'; # Relative path to audio and/or video player

	const	VIDEO_WIDTH			= '320',
			VIDEO_HEIGHT		= '240';

	const	AUDIO_WIDTH			= '320',
			AUDIO_HEIGHT		= '240';	# 240 or 0			# Notice : the tool bar height will be automatically added

	const	TOOL_BAR_HEIGHT		= '24';		# 0 or 24			# The mediaplayer tool bar height



	protected function displayAudio( $src, $title = '', $width = '', $height = '', $preview = '' )
	{
		return $this->mediaplayer($src, $title, $width, $height, $preview);
	}



	protected function displayVideo( $src, $title = '', $width = '', $height = '', $preview = '' )
	{
		return $this->mediaplayer($src, $title, $width, $height, $preview);
	}



	/**
	 * Generic method to display audio and/or video
	 * Learn more about the player : http://www.longtailvideo.com/
	 */
	protected function mediaplayer( $src, $title = '', $width = '', $height = '', $preview = '' )
	{
		// Default width and height
		$cat = parent::getExtensionCategory( parent::getFileExtension($src) );
		switch($cat)
		{
			case 'audio';
				$width  or $width  = self::AUDIO_WIDTH;
				$height or $height = self::AUDIO_HEIGHT + self::TOOL_BAR_HEIGHT; # add the tool bar !
				break;

			case 'video';
				$width  or $width  = self::VIDEO_WIDTH;
				$height or $height = self::VIDEO_HEIGHT + self::TOOL_BAR_HEIGHT; # add the tool bar !
				break;

			default:
				trigger_error("Unknown file category for <b>$src</b> in : ".__METHOD__);
				return;
		}

		$player_path_full = WEBSITE_PATH.$this->player_path;

		// Mediaplayer preview image
		$preview or $preview = $player_path_full.'preview.jpg';

		// Get Html template
		$ftp = new ftpManager($_SERVER['DOCUMENT_ROOT']);
		$mediaplayer_html = $ftp->read($player_path_full.'mediaplayer.html');
		if (!$mediaplayer_html) {
			trigger_error("Html template for Mediaplayer not founded : $player_path_full in :".__METHOD__);
			return;
		}

		$mediaplayer_html = searchAndReplace($mediaplayer_html,
			array(
				'{mediaplayer_title}'			=> $title,					# Currently unused Tag
				'{mediaplayer_src}'				=> $src,
				'{mediaplayer_width}'			=> $width,
				'{mediaplayer_height}'			=> $height,
				'{mediaplayer_preview}'			=> $preview,
				'{player_id}'					=> self::getUniqueID(),	# Unique player_id
				'{mediaplayer_scripts_path}'	=> $player_path_full,
				'{mediaplayer_loading}'			=> LANG_COM_MEDIAS_LOADING
			)
		);

		return $mediaplayer_html;
	}



	// Get a unique ID each time you need
	static protected function getUniqueID()
	{
		static $id = 0;
		return ++$id;
	}



	protected function displayDocument( $src, $title = '', $width = '', $height = '', $preview = '' )
	{
		if ($preview)
		{
			$alt = str_replace('"', '', $title);

			$width  ? $width  = " width=\"$width\""   : $width  = '';
			$height ? $height = " height=\"$height\"" : $height = '';

			return "<a href=\"$src\" target=\"_blank\"><img src=\"$preview\" alt=\"$alt\"$width{$height} /></a>";
		}
		else
		{
			$title or $title = $src; # Remember : $title might be empty !
			return "<a href=\"$src\" target=\"_blank\">$title</a>";
		}
	}



	protected function displayImage( $src, $title = '', $width = '', $height = '', $preview = '' )
	{
		$alt = str_replace('"', '', $title);

		$width  ? $width  = " width=\"$width\""   : $width  = '';
		$height ? $height = " height=\"$height\"" : $height = '';

		$preview or $preview = $src;

		return
			"<a href=\"$src\" title=\"$alt\" target=\"_blank\" class=\"medias-manager-lightbox\"><img src=\"$preview\" alt=\"$alt\"$width{$height} /></a>";
	}

}

?>