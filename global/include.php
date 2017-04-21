<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );

# TODO - Développer un système de cache pour loaderManager::CSS_FILENAME en mode PROD ; Pouvoir réinitialiser le fichier en mode DEV...
# TODO - $this->libraries et $this->components ne devraient contenir que le "core" à charger. Le reste devrait être chargé automatiquement...

// Liraries and components loader
class loaderManager
{
	private
		$libraries =
			array(
				'ftp',
				'database',
				'email',
				'form',
				'table',
				'box',
				'template',
				'medias',
				'ckeditor',
				'js',
				'slideshow'
			);

	private
		$components =
			array(
				'config',
				'rewrite',
				'user',
				'resource',
				'menu',
				'module',
				'template',
				'file',
				'generic',
				'content',
				'search',
				'newsletter',
				'contact',
				'payment',
				'sips',
				'donate',
				'wcal',
				'addrbook',
				'schedule',
				'contest'
			);

	private	$alt_base		= array('');				# Add (or replace) bases to the list of relatives bases to the required files

	/**
	 * This feature should be disabled in DEV mode and enabled only in PROD mode
	 */
	private	$merge_css		= false;					# Merge all required *.css into one single file
	const	CSS_FILENAME	= 'loadManager_merge.css';	# Filename of the merged css

	public	$debug;



	public function __construct( $debug = false )
	{
		$this->debug = $debug;
	}



	public function altBase( $alt_base, $replace = false )
	{
		is_array($alt_base) or $alt_base = array($alt_base);
		array_unique($alt_base);

		if (!$replace)
		{
			$this->alt_base = array_merge($alt_base, $this->alt_base); # Priority is given to the alternative bases list
		} else {
			$this->alt_base = $alt_base;
		}
	}



	private function altFile( $file )
	{
		for ($i=0; $i<count($this->alt_base); $i++)
		{
			/*
			 * Notice :
			 * Unlike the $file parameter wich is relative to the root of the website, the $return variable is relative to the document_root of the web server.
			 *
			 * So, the returned path of this method can be used like the following examples :
			 * For a require statement :	require($_SERVER['DOCUMENT_ROOT'].$this->altFile($file));
			 * For a link statement :		<link rel="stylesheet" type="text/css" href="<?php echo $this->altFile($file); ?>" />
			 */
			$return = WEBSITE_PATH.$this->alt_base[$i].$file;

			if (is_file($_SERVER['DOCUMENT_ROOT'].$return))
			{
				if (filesize($_SERVER['DOCUMENT_ROOT'].$return))
				{
					return $return;		# Return the first available file (to the relative base)
				}
				return false;			# Or stop searching even if it's empty !
			}
		}
		return false;
	}



	public function _class( $directory, $inc_list = false, $action = 'exclude' )
	{
		$this->pathConfig($directory, $prefix_, $inc);
		$this->overwriteInc($inc_list, $action, $inc);

		$_suffix = '_class';
		$this->requireFile($directory, $prefix_, $inc, $_suffix);
	}



	public function _lang( $directory, $inc_list = false, $action = 'exclude' )
	{
		$this->pathConfig($directory, $prefix_, $inc);
		$this->overwriteInc($inc_list, $action, $inc);

		$_suffix = '_lang';
		$this->requireFile($directory, $prefix_, $inc, $_suffix);
	}



	/**
	 * string unknown_type $prefix_ Sub-directory prefix
	 * string unknown_type $_suffix File suffix
	 * array unknown_type $inc List of sub-directory (and file) main key
	 */
	private function requireFile($directory, $prefix_, $inc, $_suffix)
	{
		$is_file = array();
		for ($i=0; $i<count($inc); $i++)
		{
			$include = $inc[$i];
			if ($file = $this->altFile("/$directory/$prefix_{$include}/{$include}$_suffix.php"))
			{
				require($_SERVER['DOCUMENT_ROOT'].$file);
				$is_file[] = $file;
			}
		}

		// Notice : debugging output only available for the '.php' files (to debug '.css' or '.js' files, simply open the page code)
		if ($this->debug)
		{
			$__c__ = __CLASS__;
			echo "<p><b>$__c__::$_suffix('$directory');</b><br />".implode('<br />', $is_file).'</p>';
			$this->checkAvailablesInc($directory, $prefix_, $inc);
		}
	}



	public function setMergeCss( $bool )
	{
		$this->merge_css = $bool;
	}



	public function _css( $directory, $inc_list = false, $action = 'exclude' )
	{
		$this->pathConfig($directory, $prefix_, $inc);
		$this->overwriteInc($inc_list, $action, $inc);

		$merge_css	= '';
		$return		= '';
		$_suffix	= '_style';

		for ($i=0; $i<count($inc); $i++)
		{
			$include = $inc[$i];
			if ($script = $this->altFile("/$directory/$prefix_{$include}/{$include}$_suffix.css"))
			{
				$return .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$script\" />\n";

				if ($this->merge_css)
				{
					$pathinfo = pathinfo($script);

					$merge_css .=
						"\n\n\n/*\n\tloadManager loads : $script\n*/\n\n\n".
						str_replace('url(images/', "url({$pathinfo['dirname']}/images/", file_get_contents($_SERVER['DOCUMENT_ROOT'].$script)); # FIXME : ça impose beaucoup la fome des css...
				}
			}
		}

		if ($return)
		{
			if ($this->merge_css)
			{
				if (!is_file($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH."/$directory/".self::CSS_FILENAME)) {
					file_put_contents($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH."/$directory/".self::CSS_FILENAME, $merge_css);
				}
				$return = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".WEBSITE_PATH."/$directory/".self::CSS_FILENAME."\" />\n"; # Overwrite the $return content
			}
			return "\n<!-- css : $directory -->\n$return";
		}
	}



	public function _js( $directory, $inc_list = false, $action = 'exclude' )
	{
		$this->pathConfig($directory, $prefix_, $inc);
		$this->overwriteInc($inc_list, $action, $inc);

		$return		= '';
		$_suffix	= '_script';

		for ($i=0; $i<count($inc); $i++)
		{
			$include = $inc[$i];
			if ($script = $this->altFile("/$directory/$prefix_{$include}/{$include}$_suffix.js"))
			{
				$return .= "<script type=\"text/javascript\" src=\"$script\"></script>\n";
			}
		}

		if ($return) {
			return "\n<!-- js : $directory -->\n$return";
		}
	}



	private function pathConfig( $directory, &$prefix_, &$inc )
	{
		switch($directory)
		{
			case 'libraries':
				$prefix_	= 'lib_';
				$inc		= $this->libraries;
				break;

			case 'components':
				$prefix_	= 'com_';
				$inc		= $this->components;
				break;

			default:
				trigger_error("Invalid parameter \$directory=$directory (expected : 'libraries', 'components')", E_USER_WARNING);
				exit;
		}
	}



	private function overwriteInc( $inc_list, $action, &$inc )
	{
		// No sub-array specified
		if (!is_array($inc_list)) {
			return;
		}

		// "$inc_list" should be sub-array of "$inc"
		if (count(array_diff($inc_list, $inc))) {
			trigger_error("Invalid value(s) of the parameter \$inc_list=".implode(', ', $inc_list), E_USER_WARNING);
			exit;
		}

		switch ($action)
		{
			// Include only the sub-array "$inc_list"
			case 'include':
				$inc = $inc_list;
				$debug_action = '<span style="color:green;">include</span>';
				break;

			// Exclude from "$inc" the sub-array "$inc_list"
			case 'exclude':
				$inc = array_values(array_diff($inc, $inc_list));
				$debug_action = '<span style="color:red;">exclude</span>';
				break;

			default:
				trigger_error("Invalid parameter \$action=$action (expected : 'include', 'exclude')", E_USER_WARNING);
				exit;
		}

		if ($this->debug) {
			$__c__ = __CLASS__;
			echo "<p><b>$__c__::overwriteInc(\$action='$debug_action');</b><br />".implode(', ', $inc_list).'</p>';
		}
	}



	// For now, this is only a debugging method
	private function checkAvailablesInc( $directory, $prefix_, $inc )
	{
		/*
		 * FIXME :
		 * J'ai mis cette variable pour rappeler que cette méthode ne tiens pas encore compte de la nouvelle méthode :
		 * $this->altBase()
		 */
		$alt_base_unique = '';

		if (!($dir = @opendir( $dir_path = $_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.$alt_base_unique."/$directory" ))) {
			return;
		}

		$inc_availables  = array();
		while ($current = readdir($dir))
		{
			if (is_dir("$dir_path/$current") && ($current != '.') && ($current != '..'))
			{
				$suffix = preg_replace('~^'.preg_quote($prefix_, '~').'~', '', $current, 1, $count);
				$count ? $inc_availables[] = $suffix : '';
			}
		}
		closedir($dir);

		// Notice : this method is called into a block : if($this->debug) {}
		if ($diff = array_values(array_diff($inc_availables, $inc)))
		{
			for ($i=0; $i<count($diff); $i++) {
				$diff[$i] = "$directory/$prefix_{$diff[$i]}/";
			}
			echo "<p><b>Availables $directory directories wich are not loaded :</b><br />".implode('<br />', $diff).'</p>';
		}
	}



	public static function directAccessBegin( $debug = NULL )
	{
		// Start session
		session_start();

		// mbstring internal encoding (read more: http://php.net/manual/fr/mbstring.overload.php)
		mb_internal_encoding("UTF-8");

		// Make this page W3C-Validated (read more: http://www.w3.org/QA/2005/04/php-session)
		ini_set('arg_separator.output', '&amp;');

		// Time zone
		setLocalTimeZone(); # Notice : This function is defined later when '/global/functions.php' is required

		// Output buffering
		OUTPUT_BUFFERING ? ob_start() : '';

		// Database connection
		global $db;
		$db = new databaseManager();
		$db->db_connect();

		// Activate debug mode as soon as possible (errors or queries above this line will not be reported)
		($debug !== NULL) or $debug = comConfig_getDebug();
		debugManager::errorReporting($debug);
		$db->debugMode($debug);
	}



	public static function directAccessEnd()
	{
		// Close database connection
		global $db;
		$db->db_close();

		// Output buffering : simple behaviour
		OUTPUT_BUFFERING ? ob_end_flush() : '';

		// Output buffering : alternative behaviour
		/*if (OUTPUT_BUFFERING)
		{
			$final_output = ob_get_clean();

			// Do something before sending output (like adding headers)
			# ...

			echo $final_output;
		}*/
	}

}



// Global
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/language.php');
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/functions.php');

// Libraries and components
$loader = new loaderManager();

$loader->_class('libraries');
$loader->_class('components');

# TODO - Add multi-languages support here (and make the choice possible in the 'config' table)
$loader->_lang('libraries');
$loader->_lang('components');


?>