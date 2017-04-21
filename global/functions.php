<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



function setLocalTimeZone()
{
	date_default_timezone_set(TIME_ZONE);
	#setlocale(LC_TIME, 'fr_FR'); # FIXME : Convertir ici toutes les valeures possible de TIME_ZONE en $locale
	setlocale(LC_TIME, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8');
}



// Show time with some view options
function getTime( $timestamp = '', $options = '' )
{
	// Current time
	$timestamp or $timestamp = time();

	// Default view options
	$opt_format 	= 0; 		# 0=short  1=long
	$opt_time 		= 1; 		# 0=no     1=yes
	$opt_sep		= ' - ';

	// View options
	$options = explode(';', $options);
	for ($i=0; $i<count($options); $i++)
	{
		$options[$i] = explode('=', $options[$i]);
		$k = $options[$i][0];
		isset($options[$i][1]) ? $v = $options[$i][1] : $v = '';

		switch($k)
		{
			case 'format':
				($v === '0' || $v == 'short') or $opt_format = 1;
				break;

			case 'time':
				($v === '1' || $v == 'yes') or $opt_time = 0;
				break;

			case 'separator':
			case 'sep':
				$opt_sep = ' '.trim($v).' ';
				break;
		}
	}

	$format = '';

	$opt_format ? $format .= '%A %e %B %Y' : $format .= '%d/%m/%Y'; # FIXME : tenir compte du format anglais '%Y/%m/%d' ...
	!$opt_time or $format .= $opt_sep.' %H:%M';

	$strftime = strftime($format, $timestamp);
	#return mb_convert_encoding($strftime, 'UTF-8', 'ISO-8859-1');
	return $strftime;
}



/**
 * @param int $length duration in seconds
 */
function getLength( $length )
{
	$min	= intval($length	/60);
	$hours	= intval($min		/60);
	$days	= intval($hours		/24);

	$sec	= $length	%60;
	$min	= $min		%60;
	$hours	= $hours	%24;

	$days	<= 1 ? $d = LANG_TIME_DAY	: $d = LANG_TIME_DAYS;
	$hours	<= 1 ? $h = LANG_TIME_HOUR	: $h = LANG_TIME_HOURS;

	$m = LANG_TIME_MIN;
	$s = LANG_TIME_SEC;

	if ($days) {
		return sprintf("%s $d %s $h %02s $m %02s $s"	, $days,$hours,$min,$sec);
	}
	elseif ($hours) {
		return sprintf("%s $h %02s $m %02s $s"			, $hours,$min,$sec);
	}
	elseif ($min) {
		return sprintf("%02s $m %02s $s"				, $min,$sec);
	}
	elseif ($sec) {
		return sprintf("%02s $s"						, $sec);
	}
	else {
		return LANG_TIME_LESS_THAN_1_SEC;
	}
}



function microtimeScript()
{
	$now = microtime();

	static $start;
	isset($start) or $start = $now;

	if ($delay = ($now - $start))
	{
		echo '<p style="color:blue;">microtimeScript() delay = '.sprintf('%.2f', $delay).' micro sec</p>';
	}
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



/**
 * Desable magic_quotes
 *
 * Code source: http://talks.php.net/show/php-best-practices/26
 * See also: 	http://fr.php.net/magic_quotes
 */
if (get_magic_quotes_gpc())
{
	function strip_magic_quotes( &$var )
	{
		if (is_array($var))
		{
			array_walk($var, 'strip_magic_quotes');
		} else {
			$var = stripslashes($var);
		}
	}

	// Handle GPC
	foreach (array('REQUEST','GET','POST','COOKIE') as $v) # If necessary, add others variables like: $_FILE
	{
		if (!empty(${"_".$v})) {
			array_walk(${"_".$v}, 'strip_magic_quotes');
		}
	}

	// Allow others scripts to check if this function was applied or not
	define('STRIP_MAGIC_QUOTES', 1);
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



function sitePath()
{
	return $_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH;
}



function siteUrl()
{
	global $g_protocol;
	return $g_protocol.$_SERVER['HTTP_HOST'].WEBSITE_PATH;
}



function getRelativeFilePath( $file )
{
	return preg_replace('~^('.pregQuote($_SERVER['DOCUMENT_ROOT']).')~i', '', dirname($file));
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



// Insert the copyright date in the website !
function copyrightDate( $online_year = false )
{
	$online_year or $online_year = date('Y');

	$current_year = date('Y');

	if ($current_year != $online_year) {
		echo "$online_year - $current_year";
	} else {
		echo $online_year;
	}
}


/*
 * Customized ucwords() to fix the following bug :
 *
 * ucwords(strtolower(('jean-pierre')) Return  'Jean-pierre' instead of 'Jean-Pierre'
 */
function upperCaseWords( $string )
{
	$string = str_replace('-', ' ~-~ ', $string); # Encode

	$string = ucwords(mb_strtolower($string));

	$string = str_replace(' ~-~ ', '-', $string); # Decode

	return $string;
}



// Wordwrap a content
function wordwrapContent( $content, $size = '100', $strip_tag = false, $etc = '...' )
{
	$strip_tag ? $content = strip_tags($content) : '';

	if (mb_strlen($content) > $size)
	{
		$content = explode('{break}', wordwrap($content, $size, '{break}'));
		$content = $content[0].$etc;
	}

	return $content;
}



// Tool - TODO : en dev.....
function formatText( $text )
{
	//$text = preg_replace('/Toto/', '«&nbsp;', $text);

	return $text;


	/*$replacement =
		array(
			'’'	=>	'\'',
			'« '	=>	'«&nbsp;',
			' »'	=>	'&nbsp;»',
			' :'	=>	'&nbsp;:',
			'œ'		=>	'oe'
		);*/

	return searchAndReplace($text, $replacement);
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



// Escape meta-characters that are recognized outside (or inside) square brackets
function pregQuote( $string, $delimiter = '~', $outsite_square_brackets = true )
{
	/*
	 * Notice :
	 * Even if the php function : preg_quote() is doing this job, we prefer to use our function.
	 * But you prefer to use the php function use the following code.
	 * Also notice our default delimiter : ~
	 */
	# return preg_quote($string, $delimiter);

	if ($outsite_square_brackets) {
		$escape = array( '\\', '^', '$', '.', '[', ']', '|', '(', ')', '?',  '*', '+', '{', '}' );
	} else {
		$escape = array( '\\', '^', '-', ']');
	}

	if (in_array($delimiter, $escape)) {
		trigger_error("Invalid PCRE delimiter : $delimiter", E_USER_WARNING);
	}
	$escape[] = $delimiter; # Regular expression delimiter

	for ($i=0; $i<count($escape); $i++)
	{
		$string = str_replace($escape[$i], '\\'.$escape[$i], $string);
	}
	return $string;
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



// Result information, after processing something
function informResult( $result, $success = '', $failure = '', $width = 0 )
{
	$box = new boxManager();
	return $box->result($result, $success, $failure, $width);
}



// Simple information
function userMessage( $message, $class_type, $width = 0 )
{
	$box = new boxManager();
	return $box->message($message, $class_type, true, $width);
}



// Return an image : true => checked.png ; false => unchecked.png
function replaceTrueByChecked( $test, $clickable = true )
{
	if ($clickable)
	{
		$test ? $img = 'checked.png' 	: $img = 'unchecked.png';
	} else {
		$test ? $img = 'status_yes.gif' : $img = 'status_no.gif';
	}

	return '<img src="'.WEBSITE_PATH.'/admin/images/'.$img.'" alt="checked" border="0" />'; # TODO - les images sont actuellement prises de /admin !
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



/**
 * Transform the string `$param` into an array `$array` using 2 levels separators
 *
 *		$param = 'k1=v1; k2=v2; k2=v2;' ;
 *		$array = array('k1'=>'v1', 'k2'=>'v2', 'k3'=>'v3') ;
 */
function setArrayOfParam( $param, $sep1 = ';', $sep2 = '=', $clean = true )
{
	$array = array();

	$param = explode($sep1, $param);
	for ($i=0; $i<count($param); $i++)
	{
		$temp = explode($sep2, $param[$i]);

		// Key
		$clean ? $key = strtolower(trim($temp[0])) : $key = $temp[0];

		if (!$key) {
			continue;
		}

		// Value
		if (isset($temp[1])) {
			$clean ? $value = strtolower(trim($temp[1])) : $value = $temp[1];
		} else {
			$value = ''; # default value
		}

		$array[$key] = $value;
	}
	return $array;
}



function searchAndReplace( $string, $search_and_replace )
{
	foreach($search_and_replace as $search => $replace)
	{
		$string = str_replace($search, $replace, $string);
	}
	return $string;
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



/**
 * Simple session manager
 */
class sessionManager
{
	protected	$name1,
				$name2;

	private		$updated_keys = array(); # List of updated keys

	private		$error = false;

	/**
	 * You should store backend session variables into: $_SESSION[sessionManager::BACKEND],
	 * and for the frontend into: $_SESSION[sessionManager::FRONTEND].
	 */
	const		BACKEND 	= 's_admin_process',
				FRONTEND 	= 's_user_process';



	public function __construct( $name1, $name2 )
	{
		$this->setDomain($name1, $name2);
	}



	public function setDomain( $name1, $name2 )
	{
		if (!formManager_filter::isVar($name1) || !formManager_filter::isVar($name2))
		{
			$this->error = true;
			trigger_error($this->trig_err("Invalid \$name1 or/and \$name2 parameter: <b>$name1, $name2</b>. Expected pattern: \$my_session->setDomain('name1', 'name2'). The current instance of sessionManager has been freezed (\$_SESSION variable will not be affected by any method)."), E_USER_WARNING);
			return;
		}

		$this->name1 = $name1;
		$this->name2 = $name2;

		if (!isset($_SESSION[$this->name1][$this->name2]))
		{
			$_SESSION[$this->name1][$this->name2] = array();
		}
	}



	// For debugging
	public function getDomain()
	{
		if ($this->error) {
			return;
		}

		echo "<p style=\"color:#8B0000;\">Current sessionManager is connected to: <b>\$_SESSION['$this->name1']['$this->name2']</b></p>";
	}



	public function init( $key, $value )
	{
		if ($this->error) {
			return;
		}

		if (!isset($_SESSION[$this->name1][$this->name2][$key]))
		{
			// Here an updated-key
			$this->updated_keys[] = $key;

			$_SESSION[$this->name1][$this->name2][$key] = $value;
		}
	}



	public function set( $key, $value )
	{
		if ($this->error) {
			return;
		}

		if (!isset($_SESSION[$this->name1][$this->name2][$key]) || $_SESSION[$this->name1][$this->name2][$key] != $value)
		{
			// Here an updated-key
			$this->updated_keys[] = $key;
		}

		$_SESSION[$this->name1][$this->name2][$key] = $value;
	}



	/**
	 * WARNING: this method work only with $this->set() method.
	 * So, if you are using the "outside method" $this->&returnVar(), then the manually updated keys will not be recorded in $this->updated_keys property
	 */
	public function atLeastOneUpdatedKeys( /* Variable number of arguments */ )
	{
		$num_args = func_num_args();

		// Is there any updated-key ?
		if ($num_args == 0 && count($this->updated_keys)) {
			return true;
		}

		// Is there at least one updated-key in the list ?
		for ($i=0; $i<$num_args; $i++)
		{
			if (in_array(func_get_arg($i), $this->updated_keys))
			{
				return true;
			}
		}

		return false;
	}



	public function get( $key, $default_value = NULL )
	{
		if ($this->error) {
			return;
		}

		if (isset($_SESSION[$this->name1][$this->name2][$key]))
		{
			return $_SESSION[$this->name1][$this->name2][$key];
		} else {
			return $default_value;
		}
	}



	// Here the basic pattern when using sessions: if there's is a new value (usualy from $_REQUEST[]) then use it, else try to find an old value into the session
	public function setAndGet( $key, $new_value )
	{
		if ($new_value !== false) # Strict comparaison
		{
			$this->set($key, $new_value);
		}

		return $this->get($key);
	}



	public function notEmpty() # TODO - cette méthode n'a pas été testée...
	{
		if (isset($_SESSION[$this->name1][$this->name2]) && count($_SESSION[$this->name1][$this->name2]))
		{
			return true;
		} else {
			return false;
		}
	}



	public function reset( $key = NULL )
	{
		if ($this->error) {
			return;
		}

		if (isset($key))
		{
			// Reset one key
			if (isset($_SESSION[$this->name1][$this->name2][$key]))
			{
				$_SESSION[$this->name1][$this->name2][$key] = array();
				unset($_SESSION[$this->name1][$this->name2][$key]);
			}
		}
		else
		{
			// Reset all keys
			if (isset($_SESSION[$this->name1][$this->name2]))
			{
				$_SESSION[$this->name1][$this->name2] = array();
				unset($_SESSION[$this->name1][$this->name2]);
			}
		}
	}



	/**
	 * If you need to modify manually a session variable from the outside call this method (instead of using set() and get() methods)
	 * WARNING: in that case updated keys will not be recorded in $this->updated_keys property
	 */
	public function &returnVar( $key ) # Returned by reference
	{
		if ($this->error) {
			return;
		}

		return $_SESSION[$this->name1][$this->name2][$key]; # Notice: if the $key doesn't exist, it will be created!
	}



	protected static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000;background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



// This class is called "simple", because you can use it only for records wich they orders can not be changed
class simpleMultiPage
{
	private	$records_number;

	private	$form_id 					= '';
	private	$select_numperpage_name 	= 'multipage_numperpage';

	private	$get_currentpage_name 		= 'page';

	private	$numperpage					= '20';
	private	$numperpage_options 		=
				array(
					'10'	=>	'10',
					'20'	=>	'20',
					'50'	=>	'50',
					'100'	=>	'100'
				);

	private	$currentpage 				= '1';

	private	$reset_currentpage 			= false;



	public function __construct( $records_number )
	{
		$this->records_number = $records_number;
	}



	public function setFormID( $form_id )
	{
		$this->form_id = $form_id;
	}



	// If necessary, overwrite the default-name of the select-form
	public function setSelectName( $select_numperpage_name )
	{
		$this->select_numperpage_name = $select_numperpage_name;
	}



	public function setNumPerPageOptions( $numperpage_options, $default = false )
	{
		if (!is_string($numperpage_options)) {
			trigger_error($this->trig_err('invalid $numperpage_options parameter (Expected string example: $numperpage_options=\'5,15,30\';)'));
			return;
		}

		$options = array();

		$error = false;
		$numperpage_options = explode(',', $numperpage_options);
		for ($i=0; $i<count($numperpage_options); $i++)
		{
			if (formManager_filter::isInteger($numperpage_options[$i]))
			{
				$options[$numperpage_options[$i]] = $numperpage_options[$i];
			} else {
				$error = true;
			}
		}

		if ($error) {
			trigger_error($this->trig_err('invalid $numperpage_options parameter (Expected string example: $numperpage_options=\'5,15,30\';)'));
			return;
		}

		$this->numperpage_options = $options;
		if ($default) {
			$this->numperpage = $default;
		}
	}



	// Go back to the first page (and don't look after the posted or the session value)
	public function resetCurrentPage()
	{
		$this->reset_currentpage = true;
	}



	public function updateSession( &$session )
	{
		$filter = new formManager_filter();

		// numperpage
		$posted_numperpage = $filter->requestValue($this->select_numperpage_name, 'post')->get();

		$unchanged_numperpage = true;
		if ( $posted_numperpage && array_key_exists($posted_numperpage, $this->numperpage_options) )
		{
			if ($posted_numperpage != $session['numperpage'])
			{
				$session['numperpage'] = $posted_numperpage;
				$unchanged_numperpage = false;
			}
			$this->numperpage = $posted_numperpage;
		}
		elseif (isset($session['numperpage']))
		{
			$this->numperpage = $session['numperpage'];
		} else {
			$session['numperpage'] = $this->numperpage;
		}

		// currentpage
		if (($unchanged_numperpage) && (!$this->reset_currentpage))
		{
			$get_currentpage = $filter->requestValue($this->get_currentpage_name, 'get')->getInteger();

			if ($get_currentpage !== false)
			{
				$page_number = intval($this->records_number/$this->numperpage);
				if ($this->records_number%$this->numperpage) {
					$page_number++;
				}

				if ($get_currentpage < 1) {
					$get_currentpage = 1;
				}
				if ($get_currentpage > $page_number) {
					$get_currentpage = $page_number;
				}

				$session['currentpage'] = $get_currentpage;
				$this->currentpage = $session['currentpage'];
			}
			elseif (isset($session['currentpage']))
			{
				$this->currentpage = $session['currentpage'];
			} else {
				$session['currentpage'] = $this->currentpage;
			}
		}
		else
		{
			$session['currentpage'] = 1;
			$this->currentpage = 1;
		}

		// Is something available on the current page ?
		while (($this->currentpage > 1) && ($this->numperpage*($this->currentpage -1) +1 > $this->records_number))
		{
			$session['currentpage'] = --$this->currentpage;
		}
	}



	public function numPerPageForm( $label = false )
	{
		if ($label === false) {
			$label = LANG_ADMIN_SIMPLE_MULTI_PAGE_NUM_PER_PAGE; # Default label
		}

		$form = new formManager(0);
		$form->setForm('post', $this->form_id);

		$numperpage_options = formManager::selectOption($this->numperpage_options, $this->numperpage);
		return $form->select($this->select_numperpage_name, $numperpage_options, $label);
	}



	public function navigationTool( $title = false, $class_prefix = false )
	{
		$page_number = intval($this->records_number/$this->numperpage);
		if ($this->records_number%$this->numperpage) {
			$page_number++;
		}

		if ($page_number == 1) {
			return ''; # Not tool!
		}

		// Default title and title class
		if ($title === false) {
			$title = LANG_ADMIN_SIMPLE_MULTI_PAGE_CURRENT_PAGE;
		}
		if ($title) {
			$title = "<span class=\"{$class_prefix}multipage-nav-title\">$title</span>";
		}

		/**
		 * List of pages
		 */
		$pages = array();

		$offset = 2; # Displayed pages:  1, ..., $this->currentpage-$offset, ..., $this->currentpage-1, $this->currentpage, $this->currentpage+1, ..., $this->currentpage+$offset, ..., $page_number

		if ($page_number < 1+ (2*$offset +1) +1)
		{
			// No problem, Here the list!
			for ($i=1; $i<=$page_number; $i++) {
				$pages[] = $i;
			}
		}
		else
		{
			// Start and stop pages
			if ($this->currentpage - $offset > 1) {
				$start = $this->currentpage - $offset;
			} else {
				$start = 1 +1;
			}
			if ($this->currentpage + $offset < $page_number) {
				$stop = $this->currentpage + $offset;
			} else {
				$stop = $page_number -1;
			}

			// Near the first page ? add some pages at the end ! Near the last page ? add some pages at the begining !
			if ($this->currentpage < 1 + ($offset + 1))
			{
				$add_stop =  1 + ($offset + 1) - $this->currentpage;
			} else {
				 $add_stop = 0;
			}
			if ($this->currentpage > $page_number - ($offset + 1))
			{
				$add_start = $this->currentpage - ($page_number - ($offset + 1));
			} else {
				$add_start = 0;
			}

			// So, Here the list!
			$pages[] = 1; 						# First
			for ($i=$start-$add_start; $i<=$stop+$add_stop; $i++) {
				$pages[] = $i;					# Middle
			}
			$pages[] = $page_number; 			# Last
		}
		// end of: List of pages

		if (!count($pages)) {
			return ''; # There's no pages !
		}

		// Let's go for the Html output!
		$html = '';
		$html_missing = '<span>....</span>';
		for ($i=0; $i<count($pages); $i++)
		{
			($i < count($pages)-1) ? $sep = '&nbsp;' : $sep = '';

			($i==1 && $pages[1] != 2) ? $html .= $html_missing : '';
			($i==count($pages)-1 && $pages[count($pages)-2] != $page_number-1) ? $html .= $html_missing : '';

			if ($pages[$i] != $this->currentpage)
			{
				$html .= '&nbsp;<a href="'.formManager::reloadPage(true, $this->get_currentpage_name.'='.$pages[$i]).'">&nbsp;'.$pages[$i].'&nbsp;</a>'.$sep;
			} else {
				$html .= '&nbsp;<span>[</span>'.$pages[$i].'<span>]</span>&nbsp;'.$sep;
			}
		}
		$html = "<div class=\"{$class_prefix}multipage-nav\"><span class=\"{$class_prefix}multipage-nav\">$title{$html}</span></div>\n";
		return $html;
	}



	public function linesOffset()
	{
		return $this->numperpage*($this->currentpage -1);
	}



	public function linesNumPerPage()
	{
		return $this->numperpage;
	}



	public function dbLimit()
	{
		return $this->linesOffset().','.$this->linesNumPerPage();
	}



	protected static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000;background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////

/**
 * When instanciate this class, you should simply use the static methods :
 *
 *		- captcha::initSession();
 *		- captcha::showCode();
 *		- captcha::checkCode();
 */
class captcha
{
	/**
	 * Customize the secure-image appearance
	 */

	protected
		$setup =
			array(
				'image_width'		=> 210,				# in px
				'image_height'		=> 70,				# in px
				'perturbation'		=> 0.75,			# 1.0 = high distortion
				'text_transparency_percentage' => 65,	# 100 = completely transparent
				'num_lines'			=> 4,
			);

	protected
		$color =
			array(
				'image_bg_color'	=> '#888',
				'text_color'		=> '#FFF',
				'line_color'		=> '#111'
			);



	public function __construct()
	{

	}



	static public function loadLibrary()
	{
		require_once(sitePath().self::BASE.self::LIB);
	}



	public function config( $key, $value )
	{
		if (isset($this->setup[$key])) {
			$this->setup[$key] = $value;
		}
		elseif (isset($this->color[$key])) {
			$this->color[$key] = $value;
		}
		else {
			trigger_error("Invalid \$key=$key in ".__METHOD__);
			$this->configKeysList();
		}

		return $this;
	}



	public function configKeysList()
	{
		echo '<h3>Availables keys for the method : $this->config($key, $value)</h3>';
		echo '<p>'.implode(', ', array_keys(array_merge($this->setup, $this->color))).'</p>';
	}



	// Instanciate the customized captcha object to be ready-to-use from the outside
	public function getInstance()
	{
		captcha::loadLibrary();
		$securimage = new securimage();

		// Fill the object with the config (part 1)
		reset($this->setup);
		foreach($this->setup as $key => $value) {
			$securimage->$key = $value;
		}

		// Fill the object with the config (part 2)
		reset($this->color);
		foreach($this->color as $key => $value) {
			$securimage->$key = new Securimage_Color($value);
		}

		// Other config
		#$securimage->signature_color = new Securimage_Color(rand(0, 64), rand(64, 128), rand(128, 255));
		#$securimage->image_type = SI_IMAGE_PNG;

		return $securimage;
	}



	// Use this method to put in the session an instance of the customized captcha object
	static public function initSession()
	{
		captcha::loadLibrary();
		$captcha = new captcha();

		// Optional : overwrite the default config
		#$captcha->config($key1, $value1)->config($key2, $value2)->...;

		/*
		 * Make the captcha instance available in any script.
		 * And to get it, use the following code :
		 *
		 * session_start();
		 * $captcha = unserialize($_SESSION['avine_captcha_object']);
		 */
		$_SESSION['avine_captcha_object'] = serialize($captcha->getInstance());
	}



	/**
	 * Generate the secure-image in the form you need to securize
	 */

	// Base path
	const		BASE				= '/plugins/php/securimage/';

	// Scripts paths
	const		LIB					= 'securimage.php',
				SHOW				= 'securimage_avine_show.php',	# Customized version of 'securimage_show.php'
				PLAY				= 'securimage_avine_play.php';	# Customized version of 'securimage_play.php'

	// Buttons paths
	const		IMG_REFRESH			= 'images/refresh_avine.png';	# New version of 'images/refresh.gif'
	const		IMG_PLAY			= 'images/audio_icon.gif';		# Not used icon

	// Html ID
	const		IMG_ID				= 'captcha_img_id';



	// Display the Html code of the secure-image in the form you want to securize (notice : it's a static method with a simple Html output)
	static public function showCode()
	{
		$html = '';

		$base = siteUrl().self::BASE; # Alias

		// Secure-image
		$html .= '<img class="securimage-image" id="' .self::IMG_ID. '" src="' .$base.self::SHOW. '?sid=' .md5(rand()). '" alt="" />';

		// Play button
		$play_colors = '&amp;bgColor1=' .'#e3f5fe'. '&amp;bgColor2=' .'#ddd'. '&amp;iconColor=' .'#3470ac'. '&amp;roundedCorner=3';
		$html .=
			"\n\n".
			'<object' ."\n".
			'	class="securimage-play"' ."\n".
			'	classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"' ."\n".
			'	codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"' ."\n".
			'	width="19"' ."\n".
			'	height="19"' ."\n".
			'	id="SecurImage_as3">' ."\n\n".

			'	<param name="allowScriptAccess" value="sameDomain" />' ."\n".
			'	<param name="allowFullScreen" value="false" />' ."\n".
			'	<param name="movie" value="' .$base. 'securimage_play.swf?audio=' .$base.self::PLAY.$play_colors. '" />' ."\n".
			'	<param name="quality" value="high" />' ."\n".
			'	<param name="bgcolor" value="#ffffff" />' ."\n\n".

			'	<embed' ."\n".
			'		src="' .$base. 'securimage_play.swf?audio=' .$base.self::PLAY.$play_colors. '"' ."\n".
			'		quality="high"' ."\n".
			'		bgcolor="#ffffff"' ."\n".
			'		width="19"' ."\n".
			'		height="19"' ."\n".
			'		name="SecurImage_as3"' ."\n".
			'		allowScriptAccess="sameDomain"' ."\n".
			'		allowFullScreen="false"' ."\n".
			'		type="application/x-shockwave-flash"' ."\n".
			'		pluginspage="http://www.macromedia.com/go/getflashplayer" />' ."\n\n".

			'</object><br />'."\n\n";

		// Refresh button
		$html .=
			'<a '.
				'class="securimage-refresh" title="'.LANG_CAPTCHA_BUTTON_REFRESH.'" tabindex="-1" href="#" '.
				'onclick="document.getElementById(\'' .self::IMG_ID. '\').src=\''.$base.self::SHOW.'?sid=\'+Math.random();return false"'.
			'><img src="' .$base.self::IMG_REFRESH. '" alt="Refresh" onclick="this.blur()" /></a>' ."\n\n";

		// Show the refresh button (initially hidden by css) and display the captcha input value in uppercase
		$html .= '<script type="text/javascript">$(document).ready(function(){ $(".securimage-refresh").show(); $("#user_create_captcha").keyup(function(){$(this).val($(this).val().toUpperCase());}); });</script>' ."\n";

		return "\n\n<!-- Captcha::begin -->\n<div class=\"securimage-wrapper\">\n$html\n<br class=\"securimage-clear\" />\n</div>\n<!-- Captcha::end -->\n\n";
	}



	/**
	 * Check the code entered by the user
	 */

	static public function checkCode( $code )
	{
		captcha::loadLibrary();
		$securimage = new securimage();

		return $securimage->check($code);
	}

}


////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



/*
 * Display once a message for ie6 users : No more support for this browser !
 * The Html output is using some css classes defined in : '/global/global_css.css'
 */
function ie6_no_more()
{
	if (isset($_SESSION['ie6-no-more'])) {
		return;
	}
	$_SESSION['ie6-no-more'] = true;

	// Path to images
	$path = WEBSITE_PATH.'/global/ie6-no-more';

echo <<< END

<!--[if lte IE 6]>
<div id="ie6-no-more">
	<p><img src="$path/warning.jpg" id="ie6-no-more-warning" />
		<strong>La compatibilité de notre site web avec Internet Explorer 6 n'ai plus maintenue.</strong><br />
		Votre navigateur Internet Explorer 6 ne vous permet donc plus de profiter de manière optimale de l'ensemble des services de notre site web.</p>
	<p>Afin de vous assurer une navigation agréable, conviviale et plus rapide sur le site, nous vous invitons à procéder à une mise à jour de votre logiciel de navigation.
		Pour cela, nous avons sélectionné pour vous les logiciels suivants, que vous pouvez télécharger en toute sécurité à partir des liens ci-dessous :</p>
	<div id="ie6-no-more-downloads">
		<a href="http://www.google.com/chrome" title="Télécharger Chrome" target="_blank"><img src="$path/chrome.jpg" /></a>
		<a href="http://www.mozilla-europe.org" title="Télécharger Firefox" target="_blank"><img src="$path/firefox.jpg" /></a>
		<a href="http://windows.microsoft.com/fr-FR/internet-explorer/products/ie/home" title="Télécharger Internet Explorer" target="_blank"><img src="$path/ie.jpg" /></a>
		<a href="http://www.apple.com/fr/safari/download/" title="Télécharger Safari" target="_blank"><img src="$path/safari.jpg" /></a></div>
	<a onclick="javascript:this.parentNode.style.display='none'; return false;" href="#" title="Fermer la fenêtre" id="ie6-no-more-close"><img src="$path/close.jpg" /></a>
</div>
<![endif]-->

END;

}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



function alt_var_dump( $var )
{
	echo "\n<pre style=\"background-color:#FFF;\">\n";
	var_dump($var);
	echo "\n</pre>\n";
}



function alt_print_r( $var )
{
	echo "\n<pre style=\"background-color:#FFF;\">\n";
	print_r($var);
	echo "\n</pre>\n";
}



////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



/**
 * Debug Manager
 */
class debugManager
{
	public		$shift_html = '..........';

	const		ADDON_GLOBAL_VAR = 'debugManager.addon';



	public function __construct( $show_variable = false )
	{
		// Get messages from any component of the system
		$get_message = self::getMessage();

		// Display header
		if ($show_variable || $get_message)
		{
			echo
				"<!-- debugManager -->\n".
				'<div id="debug-manager-header"><img src="'.WEBSITE_PATH.'/global/debug-manager.png" alt="" /> &nbsp; Debug Manager</div>'."\n\n";

			?>
			<!-- Toggle behaviour -->
			<script type="text/javascript">//<![CDATA[
			$(document).ready(function(){
				// Init
				$(".debug-manager .debug-manager-content").hide();
				$(".debug-manager .debug-manager-title").css("cursor", "pointer");

				// Toggle behaviour
				$(".debug-manager").children(".debug-manager-title").click(function(){
					$(this).next(".debug-manager-content").slideToggle("slow", function(){
						if ($(this).css('display') != 'none'){
							// Go to anchor
							$(this).prev().prepend('<'+'a name="debug-manager-focus" id="debug-manager-focus"><'+'/a>');
							window.location.hash = "#debug-manager-focus";
							$("#debug-manager-focus").remove();
						}
					});
					return false;
				});

				// Show content on demand
				$(".debug-manager .debug-manager-title.show").trigger('click');
			});
			//]]></script>
			<?php
		}

		if ($show_variable)
		{
			global $g_page, $g_user_login, $g_protocol;

			// Variables details
			$this->variable( $_SERVER			, 'Server <span>($_SERVER)</span>'				);
			$this->variable( $_SESSION			, 'Session <span>($_SESSION)</span>'			);
			$this->variable( $_REQUEST			, 'Request <span>($_REQUEST)</span>'			);
			$this->variable( $_COOKIE			, 'Cookie <span>($_COOKIE)</span>'				);
			$this->variable( $g_page			, 'Menu component <span>($g_page)</span>'		);
			#$this->variable( $g_user_login		, 'User component <span>($g_user_login)</span>'	);	# Not usefull because $g_user_login is an Object
			#$this->variable( $g_protocol		, '$g_protocol'	);									# Actually not usefull...
		}

		echo $get_message;
	}



	public function variable( $variable, $var_name )
	{
		$this->throwVariable($variable, $result_html);
		echo self::html($var_name, $result_html);
	}



	private function throwVariable( $variable, &$result_html, $level = 0 )
	{
		$shift_html = '';
		for ($i=0; $i<$level; $i++)
		{
			$shift_html .= $this->shift_html;
		}
		$shift_html = '<span class="shift">'.$shift_html.' &nbsp;</span>';

		if (is_array($variable))
		{
			reset($variable);
			while(list($key, $value) = each($variable))
			{
				$result_html .= $shift_html.'<span class="key"><span>[ </span>'.htmlentities($key, ENT_COMPAT, 'UTF-8').'<span> ]</span></span>'."\n";

				if (is_array($value))
				{
					$result_html .= '<br />';
					$this->throwVariable($value, $result_html, $level+1);
				} else {
					is_object($value) ? $value = '[object]' : $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
					$result_html .=  '<span class="value"> <span>&nbsp;=&nbsp;</span> '.$value.'</span><br />'."\n";
				}
			}
		}
		else
		{
			$result_html = '<span class="value">'.htmlentities($variable, ENT_COMPAT, 'UTF-8').'</span><br />'."\n";
		}
	}



	public static function html( $title, $content )
	{
		if ($content)
		{
			$html  = "\n<!-- begin -->\n<div class=\"debug-manager\">\n";
			$html .= "<h3 class=\"debug-manager-title\">$title</h3>\n";
			$html .= "<div class=\"debug-manager-content\">$content</div>\n";
			$html .= "</div>\n<!-- end -->\n";

			return $html;
		}
	}



	/*
	 * Allow any component to add it's own messages into the debug manager
	 */

	public static function addMessage( $category, $message )
	{
		$GLOBALS[self::ADDON_GLOBAL_VAR][$category][] = $message;
	}



	public static function setMessageAttribute( $category, $title )
	{
		$GLOBALS[self::ADDON_GLOBAL_VAR][$category]['title'] = $title;
	}



	public static function getMessage()
	{
		if (!isset($GLOBALS[self::ADDON_GLOBAL_VAR])) {
			return;
		}

		$html = '';
		foreach ($GLOBALS[self::ADDON_GLOBAL_VAR] as $category => $messages)
		{
			isset($messages['title']) ? $title = $messages['title'] : $title = '';

			$content = '';
			for ($i=0; $i<count($messages); $i++) {
				!isset($messages[$i]) or $content .= $messages[$i];
			}

			$html .= self::html($title, $content);
		}
		return $html;
	}



	// Activate the error reporting
	public static function errorReporting( $on = false )
	{
		if ($on)
		{
			error_reporting(E_ALL | E_STRICT);	# Alternative: (E_ALL ^ E_NOTICE | E_STRICT)
			ini_set('display_errors', 'On');
		} else {
			ini_set('display_errors', 'Off');	# Comment this line to prevserve the php.ini setting
		}
	}

}


?>