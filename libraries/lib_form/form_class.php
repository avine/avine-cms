<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Availables dates formats
define('FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY', 'ddmmyyyy'); # French
define('FORM_MANAGER_FILTER_DATE_FORMAT_YYYYMMDD', 'yyyymmdd'); # English



/*
 * Class : filter
 */
class formManager_filter
{
	const		USER_PASS_LENGTH_MIN 	= 5;
	const		DATE_FORMAT 			= FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY; # Values: `ddmmyyyy` or `yyyymmdd`

	protected	$input 					= NULL,		# NULL or array
				$validated 				= true,		# Boolean
				$error_message 			= array();	# Array

	protected	$file					= NULL;		# NULL or array (contain uploaded files infos)

	protected	$request_variable		= 'REQUEST'; # 'POST', 'GET' or 'REQUEST'

	protected	$one_message_per_error,
				$error_title 			= LANG_FORM_MANAGER_FILTER_ERROR_MESSAGE_TITLE;



	public function __construct( $one_message_per_error = false )
	{
		$one_message_per_error ? $this->one_message_per_error = true : $this->one_message_per_error = false;
	}



	/*
	 * ---------------
	 * Statics filters
	 * ---------------
	 */

	public static function filterList( $lowercase = false, $view = false )
	{
		$filter_list = array();

		/*
		 * Notice: the methods names of statics filters are formated: is* (ex. isInteger, isEmail, ...)
		 * So, do not use this pattern for any other method of this class
		 */
		$class_methods = get_class_methods(__CLASS__);
		for ($i=0; $i<count($class_methods); $i++)
		{
			if (preg_match('~^(is)~', $class_methods[$i])) {
				$filter_list[] = $class_methods[$i];
			}
		}

		if ($lowercase) {
			$filter_list = array_map('strtolower', $filter_list);
		}

		if ($view)
		{
			if (class_exists('tableManager'))
			{
				$table = new tableManager($filter_list, 'Filter list');
				echo $table->html();
			} else {
				echo '<p><b>Filter list :</b><br />'.implode('<br />', $filter_list).'</p>';
			}
		}

		return $filter_list;
	}



	public static function filterExist( $filter_name )
	{
		if (!in_array(strtolower($filter_name), formManager_filter::filterList(true))) # Notice: the search is in lowercase
		{
			trigger_error(
				formManager_filter::trig_err(
					"Invalid name of filter: <b>$filter_name</b>. (<i>Filter list:</i> ".implode(', ', formManager_filter::filterList()).')'
				), E_USER_WARNING);

			return false;
		}

		return true;
	}



	// Call any static filter
	public static function filter( $input, $filter_name )
	{
		$key = array_search(strtolower($filter_name), formManager_filter::filterList(true));

		if ($key !== false) # Strict comparaison
		{
			$filter_list = formManager_filter::filterList();

			return formManager_filter::$filter_list[$key]($input); # Notice: $filter_list[$key] is case sensitive (ex. 'isEmail' and not 'isemail') (not necessary, but more academic...)
		}
		else {
			trigger_error(
				formManager_filter::trig_err(
					"Invalid name of filter: <b>$filter_name</b>. (<i>Filter list:</i> ".implode(', ', formManager_filter::filterList()).')'
				), E_USER_WARNING);

			return false;
		}
	}



	// Remember: the methods names of statics filters are formated: is* (ex. isInteger, isEmail, ...)

	public static function isInteger( $input )
	{
		if (!is_bool($input) && preg_match('~^(\d+)$~', $input)) { # Notice : a boolean is not an integer !
			return true;
		}
		return false;
	}



	public static function isSignedInteger( $input )
	{
		if (!is_bool($input) && preg_match('~^(\+|-)?(\d+)$~', $input)) {
			return true;
		}
		return false;
	}



	public static function isReal( $input, $strict = true ) # Notice: an integer is not a real (unless $strict = false)
	{
		if (!$strict && formManager_filter::isInteger($input)) {
			return true;
		}
		if (preg_match('~^(\d*\.\d+|\d+\.\d*)$~', $input)) { # Notice: the separator is '.' and not ','
			return true;
		}
		return false;
	}



	public static function isSignedReal( $input, $strict = true ) # Notice: an integer is not a real (unless $strict = false)
	{
		if (!$strict && formManager_filter::isSignedInteger($input)) {
			return true;
		}
		if (preg_match('~^(\+|-)?(\d*\.\d+|\d+\.\d*)$~', $input)) { # Notice: the separator is '.' and not ','
			return true;
		}
		return false;
	}



	public static function isVar( $input ) # Example: `id`, `id12`, `_id`, `my_id_12_`, but not: `12_id` (like a name of a variable)
	{
		if (preg_match('~^([a-zA-Z_]+)([a-zA-Z_\d]*)$~', $input)) {
			return true;
		}
		return false;
	}



	public static function isID( $input ) # The symbols `-` and `.` are authorized in the middle. Numerical value authorized even at the begining or the end
	{
		if (preg_match('~^([a-zA-Z_\d][\.a-zA-Z_\d\-]*[a-zA-Z_\d]|[a-zA-Z_\d])$~', $input)) {
			return true;
		}
		return false;
	}



	public static function isMD5( $input )
	{
		if (preg_match('~^([a-zA-Z_\d]+)$~', $input)) { # TODO - this pattern match more than only MD5 strings...
			return true;
		}
		return false;
	}



	public static function isFile( $input, $authorized_extensions = array() )
	{
		$extensions =
			array(
				'html', 'htm', 'xml', 'css', 'js', 'php', 
				'pdf', 'doc', 'docx', 'rtf', 'txt', 
				'xl', 'xls', 'xlsx', 'ppt', 'pptx', 'pps', 'ppsx', 
				'zip', 'tar', 'rar', 
				'flv', 'swf', 'wmv', 'rm', 'mov', 'avi', 'wma', 'mp3', 'mp4', 
				'jpg', 'jpeg', 'gif', 'png', 'bmp'
			);

		count($authorized_extensions) ? $extensions = $authorized_extensions : ''; # Take the extensions defined by the user

		$input_array = explode('.', $input);
		if (count($input_array) >= 2)
		{
			$ext	= $input_array[count($input_array)-1];
			$name	= preg_replace('~(\.'.$ext.')$~', '', $input);

			if (in_array($ext, $extensions) && formManager_filter::isID($name)) {
				return true;
			}
		}
		return false;
	}



	// Purpose: when isFile() method return false, use this method to generate a validated filename
	public static function cleanFileName( $filename, $middle_char_replacement = '-' )
	{
		// Check the replacement character as a middle character of isID() pattern
		if ($middle_char_replacement != '-' && !preg_match('~^[\.a-zA-Z_\d\-]$~', $middle_char_replacement)) {
			trigger_error(formManager_filter::trig_err('Invalid replacement character parameter in '.__METHOD__));
		}
		$filename = mb_strtolower(trim($filename));
		$filename = preg_replace('~(\s)+~', $middle_char_replacement, $filename);

		$filename = preg_replace('~[^\.a-zA-Z_\d\-]~', '', $filename); # Based on middle character of isID() pattern

		if (preg_match('~^[^a-zA-Z_\d]~', $filename)) { # Based on first character of isID() pattern
			$filename = '_'.$filename;
		}

		if (preg_match('~[^a-zA-Z_\d]$~', $filename)) { # Based on last character of isID() pattern
			$filename = $filename.'_';
		}

		return $filename;
	}



	// Validate a real server path (query string is not allowed ; back path (../) is not allowed)
	public static function isPath( $input )
	{
		$dns	= '((([a-zA-Z\d\-][\.a-zA-Z\d\-]*[a-zA-Z\d\-]|[a-zA-Z\d\-])(\.))?([a-zA-Z\d][a-zA-Z\d\-]*[a-zA-Z\d]|[a-zA-Z\d])(\.)([a-zA-Z][a-zA-Z\d\-]*[a-zA-Z\d]|[a-zA-Z]))';
		$dir	= '([a-zA-Z_\d][\.a-zA-Z_\d\-]*[a-zA-Z_\d]|[a-zA-Z_\d])'; # this is the isID() pattern

		$path_root	=  "((/$dir)*(/)?)"; # Example: '' or '/' or '/dir/subdir' or '/dir/subdir/' (but not a relative path like 'dir/subdir/')
		$path		=  "(($dir)?$path_root)";

		if (preg_match("~^($path|(http://|https://)$dns{$path_root})$~", $input)) {
			return true;
		}
		return false;
	}



	public static function isPathFile( $input )
	{
		$input_part = explode('/', $input);

		if (count($input_part) == 1) {
			$file = $input;
			$path = '';
		} else {
			$file = $input_part[count($input_part)-1];

			$path = mb_substr($input, 0, mb_strlen($input) - mb_strlen($file));
		}

		if (formManager_filter::isFile($file) && formManager_filter::isPath($path)) {
			return true;
		}
		return false;
	}



	/*
	 * Special return :
	 *		- for wrong characters, return NULL
	 *		- for min-length not reached return false
	 */
	public static function isUserPass( $input, $length_min = NULL ) # usefull for username or password
	{
		if (preg_match('~^([\.a-zA-Z_@\d\-]+)$~', $input)) # Note : the `@` is authorized to allow the username to be identical to the email field (usefull for silent registration).
		{
			!isset($length_min) ? $length_min = self::USER_PASS_LENGTH_MIN : '';

			if (mb_strlen($input) >= $length_min) {
				return true;
			} else {
				return false;
			}
		}
		return NULL;
	}



	public static function removeInvalidUserPassChar( $input )
	{
		return preg_replace('~[^\.a-zA-Z_@\d\-]+~', '', $input);
	}



	public static function isEmail( $input )
	{
		$address	= '([\.a-zA-Z_\d\-]+)';
		$host		= '((([a-zA-Z\d][a-zA-Z\d\-]*[a-zA-Z\d]|[a-zA-Z\d])(\.))+([a-zA-Z][a-zA-Z\d\-]*[a-zA-Z\d]|[a-zA-Z]))'; # Host includes sub-domains too !

		if (preg_match("~^$address(@)$host$~", $input)) {
			return true;
		}
		return false;
	}



	/*
	 * Special return :
	 *		- for wrong structure, return NULL
	 *		- for out of range (unix system limitation) return false
	 *		- if the $input is valid, return his mktime()
	 */
	public static function isFormatedDate( $input, $format = NULL ) # Example: `23/11/2008` (french) or `2008/11/23` (english)
	{
		// Get date format
		(!isset($format) || ($format != FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY && $format != FORM_MANAGER_FILTER_DATE_FORMAT_YYYYMMDD)) ? $format = self::DATE_FORMAT : '';

		// Format separator
		$input = preg_replace('~(\.|-)~', '/', $input); # Separator values: `/`, `.`, `-`

		// Init
		$dd 	= false;
		$mm 	= false;
		$yyyy 	= false;

		// Process input
		$date = explode('/', $input);
		if (count($date) == 3)
		{
			if (($format == FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY) && (preg_match('~^(\d){1,2}/(\d){1,2}/((\d){4}|(\d){2})$~', $input))) # French
			{
				$dd   = sprintf('%02u', $date[0]);
				$mm   = sprintf('%02u', $date[1]);

				$yyyy = $date[2];
				strlen($yyyy) == 2 ? $yyyy += 2000 : ''; # Example: if $yyyy=08, then $yyyy=2008

				$input = "$dd/$mm/$yyyy";
			}
			elseif (($format == FORM_MANAGER_FILTER_DATE_FORMAT_YYYYMMDD) && (preg_match('~^((\d){4}|(\d){2})/(\d){1,2}/(\d){1,2}$~', $input))) # English
			{
				$yyyy = $date[0];
				strlen($yyyy) == 2 ? $yyyy += 2000 : '';

				$mm   = sprintf('%02u', $date[1]);
				$dd   = sprintf('%02u', $date[2]);

				$input = "$yyyy/$mm/$dd";
			}
		}

		// Validate intervals
		if (($dd>=1 && $dd<=31) && ($mm>=1 && $mm<=12) && (formManager_filter::isInteger($yyyy))) # DD, MM, YYYY
		{
			if ($yyyy>=1902 && $yyyy<=2038) # YYYY (unix system limitation)
			{
				return mktime(6, 0, 0, $mm, $dd, $yyyy);
			} else {
				return false;
			}
		}
		return NULL;
	}



	public static function isNotEmpty( $input )
	{
		if (preg_match('~(\S)~', $input)) {
			return true;
		}
		return false;
	}



	/*
	 * -----------------------------------------------
	 * Set and get the input wich need to be validated
	 * -----------------------------------------------
	 */

	public function set( $input, $name = NULL, $action = 'push' )
	{
		if (!is_array($input))
		{
			$this->input = array($input); # Internal input property is always an array
		} else {
			$this->input = $input;
		}

		if (!in_array($action, array('push', 'bypass'))) {
			trigger_error(formManager_filter::trig_err("Unexpected value of \$action=$action parameter in ".__METHOD__));
		}
		$this->addErrorStack($action, $name);

		return $this;
	}



	// Set the default request variable where to search posted forms
	public function requestVariable( $method = false )
	{
		if ($method)
		{
			in_array($method=strtolower(trim($method)), array('post', 'get'))
			or  trigger_error($this->trig_err("Unexpected value of \$method=<b>$method</b> parameter ('get' or 'post' was expected)."), E_USER_WARNING);

			if ($method == 'post') {
				$this->request_variable = 'POST';
			}
			elseif ($method == 'get') {
				$this->request_variable = 'GET';
			}
			else {
				$this->request_variable = 'REQUEST';
			}
		}
		else {
			$this->request_variable = 'REQUEST'; # Default search
		}

		return $this;
	}



	// Search value into $_REQUEST
	public function requestValue( $fullname, $method = false )
	{
		$fullname = trim($fullname);

		if ($method)
		{
			in_array($method=strtolower(trim($method)), array('post', 'get'))
			or trigger_error($this->trig_err("Unexpected value of \$method=<b>$method</b> parameter ('get' or 'post' was expected)."), E_USER_WARNING);

			$method == 'post' ? $submited = $_POST : $submited = $_GET;
		} else {
			global 		${"_".$this->request_variable}; # This is required inside a function
			$submited = ${"_".$this->request_variable}; # Default
		}

		if (isset($submited[$fullname]))
		{
			$input = $submited[$fullname]; # Can be string or array
		} else {
			$input = false;
		}

		$this->set($input, $fullname, 'push'); # Set input property wich is always an array

		return $this;
	}



	// Search name into $_REQUEST
	public function requestName( $prefix, $method = false )
	{
		$prefix = trim($prefix);

		if ($method)
		{
			in_array($method=strtolower(trim($method)), array('post', 'get'))
			or trigger_error($this->trig_err("Unexpected value of \$method=<b>$method</b> parameter ('get' or 'post' was expected)."), E_USER_WARNING);

			$method == 'post' ? $submited = $_POST : $submited = $_GET;
		} else {
			global 		${"_".$this->request_variable}; # This is required inside a function
			$submited = ${"_".$this->request_variable}; # Default
		}

		$input = array();
		if (is_array($submited))
		{
			reset($submited);
			foreach($submited as $name => $value)
			{
				if (preg_match('~^('.pregQuote($prefix).')~i', $name))
				{
					$suffix = preg_replace('~(^('.pregQuote($prefix).')|(_x|_y)$)~i'  , '', $name); # Remove also coordinates of type=image (see also the nameAttributeRestriction() method )
	
					if ($suffix == "") {
						$suffix = true; 					# Maximum match
					}
					if (!in_array($suffix, $input)) {
						$input[] = $suffix; 				# Prevent duplicate entries (like `*_x`, `*_y`)
					}
				}
			}
		}
		if (!count($input)) {
			$input = false;
		}

		/*
		 * You can see in the commented line, the code which was escpected, like in the requestValue() method.
		 * But, we need to bypass this feature in this requestName() method, and here's the explanation :
		 *
		 * In the javascript function addErrorHighlight_callback(), we need to process a match with the inputs names attributes.
		 * And the requestName() method is working with prefixes (instead of full-match like in the requestValue() method).
		 * So, in the addErrorHighlight_callback() function, the espected code to process the match should be : $('input[name^="my_name_attribute"]').
		 * But if we do that, names like 'title' and 'title_alias' will be matched even if the user entry error in only on the 'title' field...
		 *
		 * To prevent this, we have choose to bypass the "highlight" feature in this method.
		 * Finally, in the javascript function, we process a full match like that : $('input[name="my_name_attribute"]').
		 */
		#$this->set($input, $prefix, 'push');
		$this->set($input, $prefix, 'bypass'); # Set input property wich is always an array

		return $this;
	}



	/*
	 * Search value into $_FILE
	 *
	 * > Advanced example (1)
	 *
	 *		// Process
	 *		$filter->requestFile('myfile')->getUploadedfile(0);
	 *
	 *		// Form
	 *		for ($i=0; $i<3; $i++) {
	 *			echo $form->file('myfile[]', "My upload $i").'<br />';
	 *		}
	 *
	 * > Advanced example (2)
	 *
	 *		// Process
	 *		$filter->requestFile('myfile1')->getUploadedfile(1); // Requested file
	 *		$filter->requestFile('myfile2')->getUploadedfile(0); // Optional file
	 *
	 *		// Form
	 *		echo $form->file('myfile1', "My upload 1").'<br />';
	 *		echo $form->file('myfile2', "My upload 2").'<br />';
	 *
	 * > Advanced example (3)
	 *
	 *		// Process
	 *		$filter = new formManager_filter();
	 *		$filter	->requestFile('file1')
	 *				->requestFile('file2')
	 *				->getUploadedFile(); // unique process for all uploaded files
	 */
	public function requestFile( $fullname )
	{
		$fullname = trim($fullname);

		if (isset($_FILES[$fullname]))
		{
			// Many file
			if (is_array($_FILES[$fullname]['name']))
			{
				for ($i=0; $i<count($_FILES[$fullname]['name']); $i++)
				{
					$this->file[] =
						array(
							'name'		=> $_FILES[$fullname]['name'	][$i],
							'type'		=> $_FILES[$fullname]['type'	][$i],
							'size'		=> $_FILES[$fullname]['size'	][$i],
							'tmp_name'	=> $_FILES[$fullname]['tmp_name'][$i],
							'error'		=> $_FILES[$fullname]['error'	][$i],
						);
				}
			}
			// One file
			else {
				$this->file[] = $_FILES[$fullname];
			}
		}
		// No such input in $_FILES[] !
		else {
			$this->file[] = false;
		}

		$this->set($this->file, $fullname, 'push');

		return $this; # Chain this method with getUploadedfile() method
	}



	// Name attribute restriction for the forms fields (this restriction is required to use safely the requestName() method)
	public static function nameAttributeRestriction( $name )
	{
		$name = trim($name);

		if (preg_match('~(_x|_y)$~i', $name))
		{
			trigger_error(
				$this->trig_err(
					"Wrong name attribute : <b>$name</b>. ".
					"In the form inputs, the patterns '*_x' or '*_y' are strongly not recommended for the <b>name</b> attribute. ".
					"Results are unpredictable !"
				), E_USER_WARNING);
		}
		return $name;
	}



	/*
	 * Here is the basic pattern for all filters : $my_input_value = $filter->set('my_input_value')->get();
	 * It becomes usefull when you are using the get* methods like getInterger() for example.
	 */
	public function get( $add_error = false )
	{
		// Check
		if (!$this->check()) {
			return false;
		}

		if (!$add_error) {
			$this->addErrorStack('pop'); # This user entry is validated. Pop it from the error stack !
		}

		// Keep only the non-false results
		$input = array();
		for ($i=0; $i<count($this->input); $i++)
		{
			if ($this->input[$i] !== false) # Strict comparaison
			{
				$input[] = $this->input[$i];
			}
		}

		// Reset
		$this->input = NULL;

		// Return
		if (count($input) == 0) {
			return false;		# No result, return false
		}
		elseif (count($input) == 1) {
			return $input[0];	# Only one result, return a string
		}
		else {
			return $input;		# Many results, return an array
		}
	}



	protected function check()
	{
		if (isset($this->input)) {
			return true;
		} else {
			trigger_error($this->trig_err("\$input property is NULL. You can't get it or apply a filter to it."), E_USER_WARNING);
			return false;
		}
	}



	protected function clean( $tool = '' )
	{
		for ($i=0; $i<count($this->input); $i++)
		{
			if (is_bool($this->input[$i])) {
				continue; # Protect the boolean format !
			}

			switch ($tool)
			{
				case 'space':
					$this->input[$i] = preg_replace('~(\s)~', '', $this->input[$i]);
					break;

				case 'trim':
					$this->input[$i] = trim($this->input[$i]);
					break;

				case 'strtolower':
					$this->input[$i] = mb_strtolower($this->input[$i]);
					break;
			}
		}
	}



	/*
	 * Control the return of the filters
	 * Example of use : $my_field = formManager_filter::arrayOnly( $filter->requestValue('my_field')->get() );
	 */
	public static function arrayOnly( $result, $false_return_false = true )
	{
		if (is_array($result)) {
			return $result; # Nothing to do !
		}

		if ($result === false)
		{
			if ($false_return_false) {
				return false; # false is a complete exception !
			} else {
				return array(); # Notice : we did not return array(false), but only an empty array. So, false is still a little exception...
			}
		}

		return array($result); # Put the result into an array !
	}



	/*
	 * -----------------------------
	 * Filters (non-statics) methods
	 * -----------------------------
	 */

	public function getInteger( $required = true, $error_message = '', $input_label = '' )
	{
		// Check
		if (!$this->check()) {
			return false;
		}

		// Clean input
		$this->clean('space');

		// Validate input
		for ($i=0; $i<count($this->input); $i++)
		{
			// Test
			if (!formManager_filter::isInteger($this->input[$i]))
			{
				// Add error
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_INTEGER, $input_label) or $add_error = true;

				// Unvalidate
				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getSignedInteger( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('space');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isSignedInteger($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_SIGNED_INTEGER, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getReal( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('space');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isReal($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_REAL, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getSignedReal( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('space');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isSignedReal($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_SIGNED_REAL, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getVar( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isVar($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_VAR, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getID( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isID($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_ID, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getMD5( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isMD5($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_MD5, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getFile( $required = true, $error_message = '', $input_label = '', $authorized_extensions = array() )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isFile($this->input[$i], $authorized_extensions))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_FILE, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getPath( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isPath($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_PATH, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getPathFile( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isPathFile($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_FILE_PATH, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getUserPass( $required = true, $error_message = '', $input_label = '', $length_min = NULL )
	{
		if (!$this->check()) {
			return false;
		}

		# This time, do not clean !

		for ($i=0; $i<count($this->input); $i++)
		{
			$isUserPass = formManager_filter::isUserPass($this->input[$i], $length_min);

			// Notice: if a password is requested, you should overwrite the $default_error_message to prevent the password to be written in clear in the `{input}` replacement
			if ($isUserPass === NULL)
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_USERPASS, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
			elseif ($isUserPass === false)
			{
				!isset($length_min) ? $length_min = self::USER_PASS_LENGTH_MIN : '';
				!$this->addError(
					$required, $this->input[$i], $error_message,
					str_replace('{length_min}', $length_min, LANG_FORM_MANAGER_FILTER_IS_USERPASS_LENGTH_MIN_NOT_REACHED),
					$input_label
				)
				or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getEmail( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('trim');
		$this->clean('strtolower'); # Always return the emails in lower case

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isEmail($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_NOT_EMAIL, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	// Notice: if the $input is valid, this method is returning his mktime()
	public function getFormatedDate( $required = true, $error_message = '', $input_label = '', $format = NULL )
	{
		if (!$this->check()) {
			return false;
		}

		$this->clean('space');

		for ($i=0; $i<count($this->input); $i++)
		{
			$isFormatedDate = formManager_filter::isFormatedDate($this->input[$i], $format);

			if ($isFormatedDate === NULL)
			{
				(!isset($format) || ($format != FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY && $format != FORM_MANAGER_FILTER_DATE_FORMAT_YYYYMMDD)) ? $format = self::DATE_FORMAT : '';
				if ($format == FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY)
				{
					$lang_is_not_formated_date = LANG_FORM_MANAGER_FILTER_IS_NOT_FORMATED_DATE_DDMMYYYY;
				} else {
					$lang_is_not_formated_date = LANG_FORM_MANAGER_FILTER_IS_NOT_FORMATED_DATE_YYYYMMDD;
				}
				!$this->addError($required, $this->input[$i], $error_message, $lang_is_not_formated_date, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
			elseif ($isFormatedDate === false)
			{
				!$this->addError($required, $this->input[$i], '', LANG_FORM_MANAGER_FILTER_IS_FORMATED_DATE_OUT, $input_label) or $add_error = true;

				$this->input[$i] = false;
			}
			else
			{
				$this->input[$i] = $isFormatedDate;
			}
		}

		return $this->get(isset($add_error) ? true : false);
	}



	public function getNotEmpty( $required = true, $error_message = '', $input_label = '' )
	{
		if (!$this->check()) {
			return false;
		}

		# This time, do not clean !

		for ($i=0; $i<count($this->input); $i++)
		{
			if (!formManager_filter::isNotEmpty($this->input[$i]))
			{
				!$this->addError($required, $this->input[$i], $error_message, LANG_FORM_MANAGER_FILTER_IS_EMPTY, $input_label, false) or $add_error = true;

				$this->input[$i] = false;
			}			
		}

		return $this->get(isset($add_error) ? true : false);
	}



	// Chain this method after the requestFile() method
	public function getUploadedfile( $required = true, $error_message = '', $input_label = '' )
	{
		// Check
		if (!$this->check()) {
			return false;
		}

		// Validate input
		for ($i=0; $i<count($this->input); $i++)
		{
			// Test
			if ($this->input[$i] === false)
			{
				// Add error
				!$this->addError($required, '', $error_message, LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_NO_FILE, $input_label) or $add_error = true;
			}
			elseif ($err = $this->input[$i]['error'])
			{
				switch($err)
				{
					case UPLOAD_ERR_INI_SIZE:
						$default_error_message =
							str_replace(
								'{upload_max_filesize}',
								ftpManager::convertBytes(ini_get('upload_max_filesize'), 'm').'MB',
								LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_INI_SIZE
							);
						break;

					case UPLOAD_ERR_FORM_SIZE:
						$default_error_message = LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_FORM_SIZE;
						break;

					case UPLOAD_ERR_PARTIAL:
						$default_error_message = LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_PARTIAL;
						break;

					case UPLOAD_ERR_NO_FILE:
						$default_error_message = LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_NO_FILE;
						break;

					default:
						$default_error_message = LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_OTHER;
						break;
				}

				// Add error
				!$this->addError($required, $this->input[$i]['name'], $error_message, $default_error_message, $input_label) or $add_error = true;

				// Unvalidate
				$this->input[$i] = false;
			}
		}

		// Reset
		$this->file = NULL;

		return $this->get(isset($add_error) ? true : false);
	}



	/**
	 * Add manually an error (public version of the addError() method)
	 * Example : $filter->set('My input value')->getError('My error message');
	 *
	 * @param [string|array] $error_message
	 * @param string $input_label
	 * @param boolean $once Set if the error add to be added only once
	 */
	public function getError( $error_message, $input_label = '', $once = false ) # Carefull : there's no $required parameter !
	{
		// Check
		if (!$this->check()) {
			return false;
		}

		// Format
		is_array($error_message) or $error_message = array($error_message);

		// Unvalidate all inputs
		for ($i=0; $i<count($this->input); $i++)
		{
			// Add error
			for ($j=0; $j<count($error_message); $j++)
			{
				if (!$once || !in_array($this->cleanErrorMessage($error_message[$j]), $this->error_message))
				{
					$this->addError(1, $this->input[$i], $error_message[$j], '', $input_label, false); # Will always return : $add_error = true;
				}
			}
			$this->input[$i] = false;
		}

		return $this->get(true);
	}



	/*
	 * --------------
	 * Filters errors
	 * --------------
	 */

	protected function addError( $required, $input, $special_error_message, $default_error_message, $input_label, $not_filled_message = true )
	{
		if ( ($required) || (!$required && formManager_filter::isNotEmpty($input)) )
		{
			$special_error_message ? $error_message = $special_error_message : $error_message = $default_error_message;

			$input_label ? $message = "<b>$input_label : </b>" : $message = '';

			if ($input || !$not_filled_message) {
				$message .= str_replace('{input}', "<i>'$input'</i>", $error_message);
			} else {
				$message .= LANG_FORM_MANAGER_FILTER_IS_NOT_FILLED; # Overwrite message
			}

			$this->error_message[] = $this->cleanErrorMessage($message);

			$this->validated = false;

			return true;
		}
		return false;
	}



	/*
	 * Remember the names of posted inputs
	 *
	 * What is the purpose of this method and some others ?
	 * In the following code, you can see 2 chained methods : 1) GET a posted value. 2) VALIDATE this value as a required integer.
	 *
	 * $filter = new formManager_filter();
	 * $my_field = $filter->requestValue('my_field')->getInteger();
	 *
	 * Now, when we GET the value, we push the input name 'my_field' into the stack, which means the user not entered an integer as espected.
	 * Just after that, when we VALIDATE the value, if it's actually an integer, we pop the last input name from the stack...
	 *
	 * Finally, we keep in the stack, the names of entries that the user has not filled as espected !
	 */
	protected static function addErrorStack( $action, $name = NULL )
	{
		static $stack = array();

		switch($action)
		{
			case 'bypass':
				$bypass = ' {_$_~BYPASS~_$_}'; # Will be automatically popped in this->addErrorHighlight() method
			case 'push':
				$name !== NULL or $name = '{_$_~NONAME~_$_}';
				array_push($stack, $name.(isset($bypass) ? $bypass : ''));
				break;

			case 'pop':
				if (count($stack)) {
					array_pop($stack);
				} else {
					trigger_error(formManager_filter::trig_err('Can not apply array_pop($stack); because $stack is an empty array in '.__METHOD__));
				}
				break;

			case 'flush':
				$stack_copy = $stack;
				$stack = array(); # Flush the array !
				return $stack_copy;
				break;

			default:
				trigger_error(formManager_filter::trig_err("Invalid \$action=$action in ".__METHOD__));
				break;
		}
	}



	/*
	 * Handle the Html Form which contains bad user entries with javascript behaviours. 
	 * And flush the error stack.
	 */
	public static function addErrorHighlight()
	{
		// Get and flush the stack
		$add_error_list = formManager_filter::addErrorStack('flush');

		// Remove 'bypass' and 'noname' names
		$tmp = array();
		for ($i=0; $i<count($add_error_list); $i++) {
			if ((strpos($add_error_list[$i], ' {_$_~BYPASS~_$_}') === false) && (strpos($add_error_list[$i], '{_$_~NONAME~_$_}') === false)) {
				$tmp[] = $add_error_list[$i];
			}
		}
		$add_error_list = $tmp;

		// Remove duplicates
		$add_error_list = array_unique($add_error_list);

		if (!$add_error_list) {
			return '';
		}

		return
			"\n<!-- Highlight the bad user entries -->\n".
			"<script type=\"text/javascript\">$(document).ready(function(){ $.each(['".implode("','", $add_error_list)."'], addErrorHighlight_callback); });</script>\n\n";
	}



	public function reset()
	{
		$this->input			= NULL;
		$this->validated		= true;
		$this->error_message	= array();
	}



	public function setTitle( $title )
	{
		$this->error_title = $title;

		return $this;
	}



	public function errorMessage( $width = '420px', $skin = 'error' ) # The width is in 'px' or '%' (set '' for width:100%;)
	{
		$html = '';

		if (!count($this->error_message)) {
			return $html;
		}

		$skin == 'error' ? $skin = 'error' : $skin = 'warning'; # Display error message in 'error' color or 'warning' color

		if ($this->one_message_per_error)
		{
			for ($i=0; $i<count($this->error_message); $i++)
			{
				$box = new boxManager();
				$html .= $box->message($this->error_message[$i], $skin, true, 0); # Inline message
			}
		}
		else
		{
			$box = new boxManager();
			$html = $box->multiMessage($this->error_message, $this->error_title, $skin, true, $width ? $width : '100%'); # Block message
		}
		return $html;
	}



	// Instead of the default errorMessage() method, if you prefere to customize the Html error message, get here all the messages in array format
	public function errorMessageArray()
	{
		return $this->error_message;
	}



	public function validated()
	{
		return $this->validated;
	}



	public static function cleanErrorMessage( $message )
	{
		return strip_tags($message, '<div><span><p><a><strong><b><i><br><hr><img>');
	}



	// Tips: each extended class should redefine this method (to get the right `__CLASS__` value)
	protected static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000;background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}



/*
 * Class : manage forms
 */
class formManager
{
	/*
	 * Encode & decode the name attribut
	 *
	 * Example:
	 *		- wrong: <input name="myfile.php" 		value="update" type="submit" /> 	->	will be available into: $_REQUEST['myfile_php']
	 *		- right: <input name="myfile{point}php" value="update" type="submit" /> 	->	will be available into: $_REQUEST['myfile{point}php']
	 */
	public static function encodePoint( $name )
	{
		return str_replace('.'		, '{point}'	, $name);
	}



	public static function decodePoint( $name )
	{
		return str_replace('{point}', '.'		, $name);
	}



	// Quick detection of submited form (and checkRandomID() when possible)
	public static function isSubmitedForm( $form_id, $method = false, $view = true )
	{
		// Remember the forms wich have been already detected
		static $submited_form_list = array();
		if (in_array($form_id, $submited_form_list)) {
			return true;
		}

		if ($method) {
			in_array($method=strtolower(trim($method)), array('post', 'get'))
			or trigger_error(formManager::trig_err("Unexpected value of \$method=<b>$method</b> parameter ('get' or 'post' was expected)."), E_USER_WARNING);

			$method == 'post' ? $submited = $_POST : $submited = $_GET;
		} else {
			$submited = $_REQUEST;
		}

		// Check form_id
		if ((isset($submited['form_id'])) && ($submited['form_id'] == $form_id))
		{
			/*
			 * TODO - on peut améliorer la sécurité en enregistrant en même temps que le random-id, l'adresse exacte de page qui a émis le formulaire.
			 * Ensuite, il suffit de vérifier cette page de référence au moment du traitment...
			 * Mais ça reste complexe car plusieurs formulaires peuvent avoir le même 'form_id' comme par exemple dans l'Admin avec 'new_', 'upd_', ... 
			 */
			// Match the host of the previous page
			if (!isset($_SERVER['HTTP_REFERER']) || !preg_match('~^(http://|https://)'.pregQuote($_SERVER['HTTP_HOST']).'/~', $_SERVER['HTTP_REFERER']))
			{
				if ($view) {
					formManager::alert(LANG_FORM_MANAGER_HTTP_REFERER_NOT_MATCH, '420px');
				}
				return false;
			}

			// Check random_id
			if (session_id())
			{
				if
				(
					!isset($_SESSION[self::SESSION_FORM_RANDOM_ID][$form_id]) # No session, alert!
					||
					(
						$_SESSION[self::SESSION_FORM_RANDOM_ID][$form_id] != 'no-random-id' # random_id is required, continue...
						&&
						( !isset($submited['random_id']) || $submited['random_id'] != $_SESSION[self::SESSION_FORM_RANDOM_ID][$form_id] ) # No random_id or random_id not match, alert!
					)
				)
				{
					if ($view) {
						formManager::alert(LANG_FORM_MANAGER_RANDOM_ID_NOT_MATCH, '420px');
					}
					return false;
				}
				else {
					unset($_SESSION[self::SESSION_FORM_RANDOM_ID][$form_id]);
					$submited_form_list[] = $form_id;
				}
			}
			return true;
		}
		else {
			return false;
		}
	}



	// Quick setting for the `action` attribut of the <form> tag
	public static function reloadPage( $include_query_string = true, $add_or_replace = '', $remove = '', $protocol = false )
	{
		$href = '';

		// Protocol
		if ($protocol === false) {
			global $g_protocol;
			$g_protocol ? $href .= $g_protocol : $href .= 'http://'; # $g_protocol is available into our Avine cms project
		} else {
			$href .= $protocol;
		}

		// Host
		$href .= $_SERVER['HTTP_HOST'];

		// Query
		if ($include_query_string) {
			$href .= $_SERVER['REQUEST_URI']; # (*) Here we know that the query separator is `&` and not `&amp;`
		} else {
			$href .= $_SERVER['PHP_SELF']; # Notice : if the url-rewriting is activated, it means "Leave this page and go to the website root"
		}

		// Decode href before analysis
		$href = urldecode($href);

		// Do we have a query string ?
		$href_part = explode('?', $href);

		if (isset($href_part[1]))
		{
			// Remove (do this first, just in case a $remove part contain a $add_or_replace part)
			if ($remove) {
				$remove = explode('&', str_replace('&amp;', '&', $remove)); # (*) Here we need to be sure of the query separator
			} else {
				$remove = array();
			}

			$remove[] = 'form_id';
			$remove[] = 'random_id'; # Always remove the hidden fields generated by the form() method

			for ($i=0; $i<count($remove); $i++)
			{
				$r_quote = pregQuote($remove[$i]);

				if (strstr($href, '?'.$remove[$i]))		# Keep `?`
				{
					if (strstr($remove[$i], '=')) {
						$href = preg_replace("~($r_quote)(&)?~", '', $href);				# Exact match
					} else {
						$href = preg_replace("~($r_quote)(=)?([^&]*)(&)?~", '', $href); 	# Match expression
					}
				}
				else {									# Remove `&`
					if (strstr($remove[$i], '=')) {
						$href = preg_replace("~(&)($r_quote)~", '', $href);
					} else {
						$href = preg_replace("~(&)($r_quote)(=)?([^&]*)~", '', $href);
					}
				}
			}

			// All the query string has been removed ?
			if ($href == $href_part[0].'?')
			{
				$href = $href_part[0]; # Remove the last `?`
			}
		}

		// Add or replace
		if ($add_or_replace)
		{
			strstr($href, '?') ? $sep = '&' : $sep = '?'; # init

			$add_or_replace = str_replace('&amp;', '&', $add_or_replace); # (*) Here we need to be sure of the query separator
			$add_or_replace = explode('&', $add_or_replace);

			for ($i=0; $i<count($add_or_replace); $i++)
			{
				$part = explode('=', $add_or_replace[$i]);
				$param = $part[0];
				(count($part) == 2) ? $value = $part[1] : $value = '';

				$p_quote = pregQuote($param);

				if (strstr($href, $param))
				{
					$href = preg_replace("~($p_quote)((=)([^&]*))?~", "$param=$value", $href); 	# Replace
				} else {
					$href .= "{$sep}$param=$value";												# Add
					$sep = '&';
				}
			}
		}

		# TODO - Here, each part of final query should be encoded with urlencode()...

		// Format query separator
		$href = str_replace('&', '&amp;', $href);

		# TODO - For security reasons, htmlspecialchars() has been added here. But it can have side effects...
		return htmlspecialchars($href, ENT_QUOTES, 'UTF-8', false);
	}



	// General form infos
	protected		$method,
					$form_id;

	// Add enctype="multipart/form-data" in the <form> declaration
	protected		$multipart_form_data = false;

	// Choose here if we must update the form elements when reloading the page, with $_GET (or $_POST) and overwrite the default values
	protected		$update;

	// Hidden field using a random_id to check the form
	protected		$random_id;
	const			SESSION_FORM_RANDOM_ID = 'form_random_id'; # Store all 'random_id' in $_SESSION[self::SESSION_FORM_RANDOM_ID]
	const			RANDOM_ID_DISABLED_IN_BACKEND = true; # Allow more flexibility in backend when editing many scripts at the same time !

	// css details
	protected		$class_prefix = 'form-';

	// html details
	protected		$text_size,
					$textarea_cols,
					$textarea_rows;

	const			TEXT_SIZE 		= 24,
					TEXTAREA_COLS 	= 36,
					TEXTAREA_ROWS 	= 6,
					SELECT_SIZE 	= 6; # For multiple select

	const			IMG_PATH = '/libraries/lib_form/images/'; # Images location for submit buttons using type="image"



	public function __construct( $update = true, $random_id = true )
	{
		/*
		 * Always disable the random_id feature in backend
		 *
		 * Notice :
		 *		- If sometimes you needs this feature in backend call $this->setRandomId() method just after the constructor
		 *		- If you prefer to always enable this feature in backend set the constant self::RANDOM_ID_DISABLED_IN_BACKEND = false;
		 */
 		if (self::RANDOM_ID_DISABLED_IN_BACKEND && defined('WEBSITE_PATH') && ($_SERVER['PHP_SELF'] == WEBSITE_PATH.'/admin/index.php'))
		{
			$random_id = false;
		}

		$this->setUpdate($update);
		$this->setRandomId($random_id);

		$this->text_size	 = self::TEXT_SIZE;
		$this->textarea_cols = self::TEXTAREA_COLS;
		$this->textarea_rows = self::TEXTAREA_ROWS;
	}



	public function setUpdate( $update )
	{
		$update ? $this->update = true : $this->update = false;
	}



	public function setRandomId( $random_id )
	{
		$random_id ? $this->random_id = true : $this->random_id = false;
	}



	public function addMultipartFormData()
	{
		$this->multipart_form_data = true;
	}



	public function form( $method, $action, $form_id = '', $class_prefix = '' )
	{
		$html = '';

		// Overwrite default value of $class_prefix property
		if ($class_prefix != '') {
			$this->class_prefix = $class_prefix;
		}

		// Check $method
		if (!in_array($method=strtolower(trim($method)), array('post', 'get')))
		{
			trigger_error($this->trig_err("Unexpected value of \$method=<b>$method</b> parameter ('get' or 'post' was expected)."), E_USER_WARNING);
			$method = 'get'; # Default
		}

		// Set properties
		$this->method = $method;
		$this->form_id = $form_id;

		if ($this->form_id)
		{
			// id attribute
			$id = " id=\"$this->form_id\""; # Usefull for JavaScript manipulation...
		} else {
			$id = '';
		}

		if ($this->multipart_form_data)
		{
			$enctype = ' enctype="multipart/form-data"';

			if ($this->method != 'post') {
				trigger_error($this->trig_err('Configuration error : <b>enctype="multipart/form-data"</b> is compatible only with method="post".'), E_USER_WARNING);
			}
		} else {
			$enctype = '';
		}

		$html .= "\n\n<!-- form: $this->form_id :begin -->\n";
		$html .= "<form method=\"$method\" action=\"$action\"$enctype{$id}><div>\n"; # <div> tag for xhtml validation

		// Quick detection of submited form
		$html .= '<input type="hidden" name="form_id" value="'.$this->form_id.'" />'."\n";

		/*
		 * Security against : Cross-Site Request Forgeries (CSRF)
		 * When possible each form is send with a random_id.
		 * This feature is available only if session_start(); was called.
		 * To know that we verify that session_id() is not null.
		 */
		if (session_id())
		{
			if ($this->random_id)
			{
				$_SESSION[self::SESSION_FORM_RANDOM_ID][$this->form_id] = md5(rand());

				$html .= '<input type="hidden" name="random_id" value="'.$_SESSION[self::SESSION_FORM_RANDOM_ID][$this->form_id].'" />'."\n";
			} else {
				$_SESSION[self::SESSION_FORM_RANDOM_ID][$this->form_id] = 'no-random-id';
			}
		}

		return $html."\n";
	}



	/*
	 * An Html-form is like this : 
	 * <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"> <input type="text" name="name" value="" /> ... </form>
	 *
	 * To do that with this class we use this code : 
	 * $form = new formManager(); $form->form('post', $_SERVER['PHP_SELF']);  $form->text('name', ''); ...;  $form->end();
	 *
	 * If in a script, a particular function need to add inputs-fields to an existing form (ie: which was already defined into the main script).
	 * We want to write a code like this :
	 * $form = new formManager();  $form->text('email', '');
	 *
	 * But it's wrong, because this will not define the form-method ! Then the automatic update form system will not work !
	 * To solve that, all you need is to define the form-method by using the following function.
	 *
	 * Here an example:
	 * $form = new formManager();  $form->setForm('post');  $form->text('email', '');
	 */
	public function setForm( $method, $form_id = '' )
	{
		$this->method = $method;
		$this->form_id = $form_id;
	}



	public function end()
	{
		$html = '';

		$html .= "\n</div></form>\n"; # <div> tag for xhtml validation
		$html .= "<!-- form: $this->form_id :end -->\n";

		$html .= formManager_filter::addErrorHighlight(); # Here is a good place to handle the bad user entries with javascript behaviours

		return $html."\n";
	}



	private function checkNameAttr( $name )
	{
		return formManager_filter::nameAttributeRestriction($name);
	}



	/*
	 * Transform the string `$param` into an array `$array`
	 *
	 *		$param = 'k1=v1; k2=v2; k2=v2;' ;
	 *		$array = array('k1'=>'v1', 'k2'=>'v2', 'k3'=>'v3') ;
	 */
	private function setArrayOfParam( $param, $sep = ';' )
	{
		$array = array();

		$param = explode($sep, $param);
		for ($i=0; $i<count($param); $i++)
		{
			$temp = explode('=', $param[$i]);

			// Key
			$key = strtolower(trim($temp[0]));

			if (!$key) continue;

			// Value
			if (isset($temp[1])) {
				$value = strtolower(trim($temp[1]));
			} else {
				$value = ''; # Default value
			}

			$array[$key] = $value;
		}

		return $array;
	}



	public function submit( $name, $value, $id = '', $param = '' )
	{
		$name = $this->checkNameAttr($name);
		$value = trim(htmlspecialchars($value));

		if ($id == '') {
			$id = $name;
		}

		$class 		 = 'class="'.$this->class_prefix.'submit"';
		$class_image = 'class="'.$this->class_prefix.'submit-image"';

		$image = '';
		$disabled = '';
		$wrapper = '';

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'image':
					$image = $v;
					break;

				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class 			= 'class="'.$this->class_prefix.'submit-disabled"';
					$class_image 	= 'class="'.$this->class_prefix.'submit-image-disabled"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;
			}
		}

		if ($image == '')
		{
			$input = "<input type=\"submit\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"$value\"$disabled $class />";
		} else {
			$input = "<input type=\"image\" src=\"".$this->getImageSource($image)."\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"$value\"$disabled $class_image />";
		}

		// Confirm delete with javascript
		if ($image == 'delete' || $image == 'del') {
			echo '<script type="text/javascript">$(function(){ $("#'.$this->form_id.$id.'").click(function(){ return confirm("'.LANG_FORM_MANAGER_DELETE_CONFIRM.'"); }); });</script>'."\n";
		}

		return $this->combineInputAndLabel($input, '', $id, $wrapper);
	}



	private function getImageSource( $alias )
	{
		// Path info ($g_protocol and WEBSITE_PATH are available into our cms project)
		global $g_protocol;
		$g_protocol ? $protocol = $g_protocol : $protocol = 'http://';
		defined('WEBSITE_PATH') ? $web_site_path = WEBSITE_PATH : $web_site_path = '';
		$path = $protocol.$_SERVER['HTTP_HOST'].$web_site_path.self::IMG_PATH;

		switch(trim($alias))
		{
			case 'add':
				$image = 'add.png';
				break;

			case 'delete':
			case 'del':
				$image = 'delete.png';
				break;

			case 'update':
			case 'upd':
				$image = 'page_edit.png';
				break;

			case 'go':
				$image = 'go.png';
				break;

			case 'go_down':
				$image = 'go_down.png';
				break;

			case 'lock':
				$image = 'lock.png';
				break;

			case 'lock_open':
				$image = 'lock_open.png';
				break;

			case 'submit':
				$image = 'button_submit.gif';
				break;

			default:
				$image = 'bug_error.png'; # Default image if no valid source specified
				trigger_error($this->trig_err("The alias of the image is unknown: <b>$alias</b>", E_USER_WARNING));
				break;
		}

		$src = $path.$image; # Here the `src` attribut for <input type="image" src="$src" ... />
		return $src;
	}



	// Notice : Beware to update the $alias array according to the switch{} of the getImageSource() method
	public static function submitImageSourceAlias()
	{
		$alias = array( 'add', 'delete', 'del', 'update', 'upd', 'go', 'go_down', 'submit');

		echo '<p><b>List of alias for formManager::submit() method using images :</b><br />'.implode('<br />', $alias).'</p>';
	}



	public function reset( $name, $value, $id = '', $param = '' )
	{
		$name = $this->checkNameAttr($name);
		$value = trim(htmlspecialchars($value));

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'submit"';

		$disabled = '';
		$wrapper = '';

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'submit-disabled"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;
			}
		}

		$input = "<input type=\"reset\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"$value\"$disabled $class />";

		return $this->combineInputAndLabel($input, '', $id, $wrapper);
	}



	public function text( $name, $value, $label = '', $id = '', $param = '' )  # Example: $param = ' size=20; maxlength=30; wrapper=td '
	{
		$name = $this->checkNameAttr($name);
		$value = trim($value);

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'text"';

		$maxlength = '';
		$disabled = '';
		$readonly = '';
		$wrapper = '';
		$local_update = NULL;

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'maxlength':
					$maxlength = " maxlength=\"$v\"";
					break;

				case 'size':
					$v != 'default' ? $this->text_size = $v : $this->text_size = self::TEXT_SIZE;
					break;

				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'text-disabled"';
					break;

				case 'readonly':
					$readonly = ' readonly="readonly"';
					$class = 'class="'.$this->class_prefix.'text-readonly"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;

				case 'update':
					$local_update = $v;
					break;
			}
		}

		$value = $this->update('text', $name, $value, '', $local_update);

		$input = "<input type=\"text\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"$value\" size=\"".$this->text_size."\"$maxlength{$disabled}$readonly $class />";

		return $this->combineInputAndLabel($input, $label, $id, $wrapper);
	}



	public function password( $name, $value, $label = '', $id = '', $param = '' )  # 'text' and 'password' are identical methods
	{
		$name = $this->checkNameAttr($name);
		$value = trim($value);

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'text"';
	
		$maxlength = '';
		$disabled = '';
		$readonly = '';
		$wrapper = '';
		$local_update = NULL;

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'maxlength':
					$maxlength = " maxlength=\"$v\"";
					break;

				case 'size':
					$v != 'default' ? $this->text_size = $v : $this->text_size = self::TEXT_SIZE;
					break;

				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'text-disabled"';
					break;

				case 'readonly':
					$readonly = ' readonly="readonly"';
					$class = 'class="'.$this->class_prefix.'text-readonly"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;

				case 'update':
					$local_update = $v;
					break;
			}
		}

		$value = $this->update('password', $name, $value, '', $local_update);

		$input = "<input type=\"password\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"$value\" size=\"".$this->text_size."\"$maxlength{$disabled}$readonly $class />";

		return $this->combineInputAndLabel($input, $label, $id, $wrapper);
	}



	public function textarea( $name, $value, $label = '', $id = '', $param = '' )  # Example: $param = ' cols=90; rows=30; wrapper=tr '
	{
		$name = $this->checkNameAttr($name);
		$value = trim($value);

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'text"';

		$disabled = '';
		$readonly = '';
		$wrapper = '';
		$local_update = NULL;

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'cols':
					$v != 'default' ? $this->textarea_cols = $v : $this->textarea_cols = self::TEXTAREA_COLS;
					break;

				case 'rows':
					$v != 'default' ? $this->textarea_rows = $v : $this->textarea_rows = self::TEXTAREA_ROWS;
					break;

				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'text-disabled"';
					break;

				case 'readonly':
					$readonly = ' readonly="readonly"';
					$class = 'class="'.$this->class_prefix.'text-readonly"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;

				case 'update':
					$local_update = $v;
					break;
			}
		}

		$value = $this->update('textarea', $name, $value, '', $local_update);

		$input = "<textarea name=\"$name\" id=\"".$this->form_id.$id."\" cols=\"".$this->textarea_cols."\" rows=\"".$this->textarea_rows."\"$disabled{$readonly} $class >$value</textarea>";

		return $this->combineInputAndLabel($input, $label, $id, $wrapper);
	}



	public function checkbox( $name, $checked = 0, $label = '', $id = '', $param = '' )
	{
		$name = $this->checkNameAttr($name);

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'check"';

		$disabled = '';
		$readonly = '';
		$wrapper = '';
		$local_update = NULL;

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'check-disabled"';
					break;

				case 'readonly':
					$readonly = ' readonly="readonly"';
					$class = 'class="'.$this->class_prefix.'check-readonly"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;

				case 'update':
					$local_update = $v;
					break;
			}
		}

		$checked = $this->update('checkbox', $name, $checked, '', $local_update);
		if ($checked == 1) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}

		// Notice: set a unique value for all navigators
		$input = "<input type=\"checkbox\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"1\"$checked{$disabled}$readonly $class />";

		return $this->combineInputAndLabel($input, $label, $id, $wrapper, 'right');
	}



	public function radio( $name, $value, $label = '', $id = '', $param = '' )
	{
		$name = $this->checkNameAttr($name);
		$value = trim(htmlspecialchars($value));

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'check"';

		$disabled = '';
		$readonly = '';
		$wrapper = '';
		$local_update = NULL;

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'check-disabled"';
					break;

				case 'readonly':
					$readonly = ' readonly="readonly"';
					$class = 'class="'.$this->class_prefix.'check-readonly"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;

				case 'update':
					$local_update = $v;
					break;
			}
		}

		if (preg_match('~^\[(.*)\]$~', $value))
		{
			$checked = 1;
			$value = preg_replace('~^\[~', '', $value);
			$value = trim(preg_replace('~\]$~', '', $value));
		}
		else {
			$checked = 0;
		}

		$checked = $this->update('radio', $name, $checked, $value, $local_update);
		if ($checked == 1) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}

		$input = "<input type=\"radio\" name=\"$name\" id=\"".$this->form_id.$id."\" value=\"$value\"$checked{$disabled}$readonly $class />";

		return $this->combineInputAndLabel($input, $label, $id, $wrapper, 'right');
	}



	/*
	 * "Tool" method designed for quick setting of: the $value parameter of the radio() method.
	 * Notice: The radio button is checked if it is between [] symbols.
	 */
	public static function checkValue( $value, $checked = true )
	{
		if ($checked) {
			$value = "[$value]";
		}
		return $value;
	}



	// Add a list of radios buttons in one shot!
	public function radiosList( $name, $values_labels, $param = '' )
	{
		$html = '';

		$id_counter = 1;
		foreach($values_labels as $value => $label)
		{
			$automatic_id = $name.'_'.($id_counter++); # The unique `id` is automaticaly generated: $name.'_1', $name.'_2', ...

			$html .= $this->radio($name, $value, $label, $automatic_id, $param); # The param is applied to each radio button
		}

		return $html;
	}



	public function select( $name, $options, $label = '', $id = '', $param = '' )  # Example: $param = ' size=3; multiple; wrapper=td '
	{
		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'select"';

		$size = '';
		$multiple = 0;
		$disabled = '';
		$readonly = '';
		$wrapper = '';
		$local_update = NULL;

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'size':
					$size = " size=\"$v\"";
					break;

				case 'multiple':
					$multiple = 1;
					break;

				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'select-disabled"';
					break;

				case 'readonly':
					$readonly = ' readonly="readonly"';
					$class = 'class="'.$this->class_prefix.'select-readonly"'; # Warning: according to W3C validation, 'readonly' attribut doesn't exists fot the select tag
					break;

				case 'wrapper':
					$wrapper = $v;
					break;

				case 'update':
					$local_update = $v;
					break;
			}
		}

		if (!is_scalar($options))
		{
			if ($multiple)
			{
				$input = "<select name=\"{$name}[]\" id=\"".$this->form_id.$id."\" multiple=\"multiple\"$size{$disabled}$readonly $class>\n";
			} else {
				$input = "<select name=\"$name\" id=\"".$this->form_id.$id."\"$size{$disabled}$readonly $class>\n";
			}

			$optgroup = false;
			reset($options);

			foreach($options as $opt_value => $opt_txt)
			{
				if ( (mb_strstr($opt_value, '(optgroup)')) || (mb_strstr($opt_txt, '(optgroup)')) )
				{
					if (mb_strstr($opt_value, '(optgroup)')) {
						$opt_value 		= trim(str_replace('(optgroup)', '', $opt_value));
						$optgroup_label = htmlspecialchars($opt_value);
					}
					if (mb_strstr($opt_txt  , '(optgroup)')) {
						$opt_txt   		= trim(str_replace('(optgroup)', '', $opt_txt  ));
						$optgroup_label = htmlspecialchars($opt_txt); # If '(optgroup)' is founded in both variables $opt_value and $opt_txt, then here the final $optgroup_label ! 
					}

					if ($optgroup) {
						$input .= "</optgroup>\n";
					}
					$optgroup = true;

					$input .= "<optgroup label=\"$optgroup_label\">\n";
				}
				else
				{
					$opt_value = trim(htmlspecialchars($opt_value));
					$opt_txt   = trim(htmlspecialchars($opt_txt));

					if ( preg_match('~^\[(.*)\]$~', $opt_value) || preg_match('~^\[(.*)\]$~', $opt_txt) )					
					{
						$selected = 1;

						$opt_value = preg_replace		('~^\[~', '', $opt_value);
						$opt_value = trim(preg_replace	('~\]$~', '', $opt_value));

						$opt_txt   = preg_replace		('~^\[~', '', $opt_txt  );
						$opt_txt   = trim(preg_replace	('~\]$~', '', $opt_txt  ));		  
					}
					else {
						$selected = 0;
					}

					$selected = $this->update('select', $name, $selected, $opt_value, $local_update);
					if ($selected == 1) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}

					$input .= "\t<option value=\"$opt_value\"$selected>$opt_txt</option>\n";
				}
	    
			}
			if ($optgroup) {
				$input .= "</optgroup>\n";
			}
			$input .= "</select>";

			return $this->combineInputAndLabel($input, $label, $id, $wrapper);
		}
		else {
			trigger_error($this->trig_err('Invalid \'$options\' parameter in select() method.'), E_USER_WARNING);
			return '';
		}
	}



	/*
	 * "Tool" method designed for quick setting of: the $options parameter of the select() method.
	 * The selected option is the one wich is between [] symbols.
	 */
	public static function selectOption( $options = array(), $selected_keys = false, $add_to_key = true)
	{
		$return = array();

		/*
		 * $selected_keys formats: (string) or even array()
		 *
		 * For a simple select, $selected_keys is simply the selected key: 'key';
		 * For a multiple select, $selected_keys can also be an array of the selected keys: array( 'key_1', key2', ... );
		 *
		 * To have a unique process, we transform $selected_keys to be always in array() format
		 */
		if (is_scalar($selected_keys)) {
			$selected_keys = array($selected_keys);
		}

		reset($options);
		foreach($options as $key => $value)
		{
			// Search
			$is_selected = false;
			$i = 0;
			while (($is_selected == false) && ($i<count($selected_keys)))
			{
				if (strval($key) === strval($selected_keys[$i++])) {
					$is_selected = true;
				}
			}

			// Format
			if ($is_selected)
			{
				if ($add_to_key) {
					$return['['.$key.']'] =     $value;
				} else {
					$return[    $key    ] = '['.$value.']';
				}
			}
			else {
				$return[$key] = $value;
			}
		}

		return $return;
	}



	public function hidden( $name, $value )
	{
		$name = $this->checkNameAttr($name);
		$value = trim($value);

		$value = $this->update('hidden', $name, $value);

		$html = "<input type=\"hidden\" name=\"$name\" value=\"$value\" />";
		return $html;
	}



	public function file( $name, $label = '', $id = '', $param = '', $MAX_FILE_SIZE_Mo = 0 )
	{
		if (!$this->multipart_form_data) {
			trigger_error($this->trig_err("To upload files, you must call the addMultipartFormData() method before you call the form() method."));
		}

		$name = $this->checkNameAttr($name);

		if ($id == '') {
			$id = $name;
		}

		$class = 'class="'.$this->class_prefix.'file"';

		$upload_max_filesize = false;
		$disabled = '';
		$wrapper = '';

		$param = $this->setArrayOfParam($param);
		reset($param);
		foreach ($param as $k => $v)
		{
			switch ($k)
			{
				case 'upload_max_filesize':
					$upload_max_filesize = true; # Server side limition
					break;

				case 'size':
					$v != 'default' ? $this->text_size = $v : $this->text_size = self::TEXT_SIZE;
					break;

				case 'disabled':
					$disabled = ' disabled="disabled"';
					$class = 'class="'.$this->class_prefix.'file-disabled"';
					break;

				case 'wrapper':
					$wrapper = $v;
					break;
			}
		}

		$input = "<input type=\"file\" name=\"$name\" id=\"".$this->form_id.$id."\" size=\"".$this->text_size."\"$disabled $class />";

		if ($upload_max_filesize || $MAX_FILE_SIZE_Mo)
		{
			$size_infos = formManager::uploadMaxFilesizeInfos($MAX_FILE_SIZE_Mo);

			if (!$size_infos['server'])
			{
				$input = '<input type="hidden" name="MAX_FILE_SIZE" value="'.$size_infos['size'].'">'.$input; # Client side limitation
			}
			$input .= $size_infos['message']; # Server or client limitation
		}

		return $this->combineInputAndLabel($input, $label, $id, $wrapper);
	}



	static public function uploadMaxFilesizeInfos( $MAX_FILE_SIZE_Mo = 0 )
	{
		$upload_max_filesize = ftpManager::convertBytes(ini_get('upload_max_filesize'));

		// Default return
		$return = array('size' => $upload_max_filesize, 'server' => true);		# Server side limitation

		if ($MAX_FILE_SIZE_Mo)
		{
			// Get MAX_FILE_SIZE in bytes
			$MAX_FILE_SIZE = round(1024*1024*$MAX_FILE_SIZE_Mo);

			// Resolve conflict between client and server limitations
			if ($MAX_FILE_SIZE < $upload_max_filesize)
			{
				$return = array('size' => $MAX_FILE_SIZE, 'server' => false);	# Client side limitation
			}
		}

		$return['message'] = str_replace('{upload_max_filesize}', ftpManager::convertBytes($return['size'], 'optimize'), LANG_FORM_MANAGER_FILTER_UPLOAD_MAX_FILESIZE);

		return $return;
	}



	private function combineInputAndLabel( $input, $label, $id, $wrapper, $default_label_pos = 'left' )
	{
		$label_info = $this->getLabelInfo($label);

		if ($label_info['label'] != '')
		{
			$label = $this->label($id, $label_info['label']);

			if ($label_info['pos'] == '') {
				$pos = $default_label_pos;
			}
			else {
				$pos = $label_info['pos'];
			}

			if ($pos=='left') {
				$html = $this->wrapper($wrapper, $label, $input);
			} else {
				$html = $this->wrapper($wrapper, $input, $label);
			}
		}
		else {
			$html = $this->wrapper($wrapper, $input);
		}

		return $html;		
	}



	private function getLabelInfo( $label )
	{
		$exp_left 	= '~\((\s)*left(\s)*\)~i';
		$exp_right 	= '~\((\s)*right(\s)*\)~i';

		$pos = ''; # Default position will be used

		// Label position (used by checkbox() and radio() methods)
		if (preg_match($exp_left, $label))
		{
			$label = preg_replace($exp_left, '', $label);
			$pos = 'left';
		}
		elseif (preg_match($exp_right, $label))
		{
			$label = preg_replace($exp_right, '', $label);
			$pos = 'right';
		}

		// Return structure
		$info['label'] =  trim($label);
		$info['pos']   =  $pos;

		return $info;
	}



	public function label( $for, $label)
	{
		return "<label for=\"$this->form_id{$for}\" class=\"{$this->class_prefix}label\" >$label</label>";
	}



	public function wrapper( $tag, $cell_left, $cell_right = '' )
	{
		$tag = strtolower(trim($tag));

		switch($tag)
		{
			// Put the `inputs` and the `labels` into <p> or <div> tags
			case 'p':
			case 'div':
				$return = "<$tag>{$cell_left}{$cell_right}</$tag>\n";
				break;

			// Allow fixed width of the label tag
			case 'div.label-fixed':
			case 'div.label-100px':
			case 'div.label-150px':
			case 'div.label-200px':
			case 'div.label-250px':
			case 'div.label-300px':
				$tag = str_replace('div.', '', $tag);
				$return = "<div class=\"{$this->class_prefix}$tag\">{$cell_left}{$cell_right}</div>\n";
				break;

			// Put the `inputs` and the `labels` into cells table
			case 'td':
			case 'tr':
				if ($cell_right == '') {
					$return = '<td>'.$cell_left."</td>\n";
				} else  {
					// Notice the followed css class
					$return = "\t<td class=\"form-table-cell-left\">$cell_left</td>\n\t<td class=\"form-table-cell-right\">$cell_right</td>\n";
				}
				// Notice: if ($tag == 'tr') this wrapper() method is adding `tr` and also `td` tag inside
				if ($tag == 'tr') {
					$return = "<tr>\n".$return."</tr>\n";
				}
				break;

			case '':
			default:
				$return = $cell_left.$cell_right;
				break;
		}
		return $return;
	}



	// Update the values of the form elements when reloading the page
	private function update( $type, $name, $return, $param = '', $local_update = NULL )
	{
		// This time, no need to search into $_REQUEST, we know exactly where to look!
		$this->method == 'post' ? $submited = $_POST : $submited = $_GET;

		// Is this form submited ?
		if ((isset($submited['form_id'])) && ($submited['form_id'] == $this->form_id))
		{
			$global_update = $this->update;

			// Local update
			if (isset($local_update))
			{
				if ($local_update == '1' || $local_update == 'yes') {
					$local_update = true;
				} else {
					$local_update = false;
				}
			}

			// Update
			if ((!isset($local_update) && ($global_update)) || (isset($local_update) && ($local_update)))
			{
				switch($type)
				{
					// $return=$value (notice: there's no reason to update in case 'hidden')
					case 'text':
					case 'password':
					case 'textarea':
						if (isset($submited[$name]))
						{
							/*
							 * Security verification:
							 * We are checking here that when necessary, our strip_magic_quotes() function is applied (this is a user function)
							 * This function reverse the magic_quotes_gpc() effect (ie: emulate magic_quotes_gpc=off).
							 * For details watch : '/global/functions.php' (we have the same problem into '/libraries/lib_database/database_class.php')
							 */
							if ( (!get_magic_quotes_gpc()) || ((get_magic_quotes_gpc()) && (defined('STRIP_MAGIC_QUOTES'))) )
							{
								$return = $submited[$name];
							} else {
								trigger_error($this->trig_err('Failed to emulate <b>magic_quotes_gpc=false</b>. The system continue to work, but errors could appears into text fields'), E_USER_WARNING);
								$return = stripslashes($submited[$name]);
							}
						}
						break;

					// $return=$checked
					case 'checkbox':
						if (isset($submited[$name])) {
							$return = 1;
						} else {
							$return = 0;
						}
						break;

					// $return=$checked / $param=$value
					case 'radio':
						if ((isset($submited[$name])) && ($submited[$name] == $param)) {
							$return = 1;
						} else {
							$return = 0;
						}
						break;

					// $return=$selected / $param=$opt_value
					case 'select':
						if (isset($submited[$name]))
						{
							# For explanation, see how work the selectOption() method

							// To have a unique process, we transform $submited[$name] to be always in array() format: $submited[$name][]
							if (!is_array($submited[$name])) {
								$submited_name = array($submited[$name]);
							} else {
								$submited_name = $submited[$name];
							}

							// Search
							$return = 0;
							$i = 0;
							do {
								if (strval($param) === strval($submited_name[$i++])) {
									$return = 1;
								}
							}
							while (($return == 0) && ($i<count($submited_name)));
						}
						else {
							$return = 0;
						}
						break;

					case 'hidden': # Do nothing !
						break;
				}
			}
		}

		return htmlspecialchars(trim($return)); # Prevent html injections
	}



	public static function alert( $message, $width = '' ) # width in 'px' or '%'
	{
		$box = new boxManager();
		$box->echoMessage($message, 'error', true, $width);
		return;
	}



	// Tips: each extended class should redefine this method (to get the right `__CLASS__` value)
	protected static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000;background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}


?>