<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



/*
 * This class can be seen as an extension of the com_menu component.
 * So, to integrate this concept we have created a special function called : comMenu_rewrite().
 */
class comRewrite_
{
	const	S_MAX = 3;



	public function __construct()
	{
		if (self::isEnabled())
		{
			# Previous version wich not works on WAMP server : $url = $_SERVER['SCRIPT_URL'];
			$url = $_SERVER['REQUEST_URI'];

			// Remove from the $url the WEBSITE_PATH and the query string
			WEBSITE_PATH ? $url = preg_replace('~^'.pregQuote(WEBSITE_PATH).'~', '', $url) : '';
			$url = preg_replace('~\?(.)*$~', '', $url);

			if ($url != '/' && !preg_match('~^/index\.php~', $url))
			{
				// Get the dynamic url and set the expected $_GET
				if ($convert = self::convertUrl($url, false))
				{
					$addon = comMenu_emulateGET($convert);
				} else {
					// End slash missing ? Go inside the virtual directory !
					if (!preg_match('~/$~', $url) && $convert = self::convertUrl("$url/", false)) {
						header("Location: ".WEBSITE_PATH."$url/");
					}
					$addon = array('file' => 'error404.php');
				}
				$_GET		= array_merge($_GET		, $addon);
				$_REQUEST	= array_merge($_REQUEST	, $addon);

				// Clean $_GET from the rewrite engine rewriting
				unset($_GET['rewrite']);
				unset($_REQUEST['rewrite']);

				// Set the espected query_string
				if ($convert)
				{
					$query_string = comMenu_emulateGET($_SERVER['QUERY_STRING']);
					$_SERVER['QUERY_STRING'] =
						str_replace('&amp;', '&', $convert). # Here the query part wich come from the dynamic url
						str_replace('rewrite='.$query_string['rewrite'], '', $_SERVER['QUERY_STRING']); # The 'rewrite' key come from the rewrite engine action, and can be removed !
				} else {
					$_SERVER['QUERY_STRING'] = 'file=error404.php';
				}				
			}
		}
		else
		{
			// Prevent error 404
			header("Status: 200 OK", false, 200);
		}
	}



	public static function isEnabled()
	{
		static $enabled;

		if (!isset($enabled)) {
			#$db = new databaseManager(); # Old code - Should be a simple mistake...
			global $db;
			$enabled = $db->selectOne('rewrite_config, enabled', 'enabled');
		}
		return $enabled;
	}



	public static function getRules( )
	{
		static $rules;

		if (!isset($rules)) {
			#$db = new databaseManager(); # Old code - Should be a simple mistake...
			global $db;
			$rules = $db->select('rewrite_rules, *, pos(asc)');
		}

		// Be sure of the ampersand value
		for ($i=0; $i<count($rules); $i++) {
			$rules[$i]['dynamic'] = comRewrite_::encodeAmpersand($rules[$i]['dynamic']);
		}

		return $rules;
	}



	public static function encodeAmpersand( $string )
	{
		return str_replace('&', '&amp;', str_replace('&amp;', '&', $string));
	}



	/*
	 * Important notice
	 *
	 * - Dynamic to static conversion : in that case, this method returns a complete and ready to use url
	 *
	 *		Example : http://www.mysite.com/index.php?com=user&page=login -> http://www.mysite.com/user/login
	 *
	 * - Static to dynamic conversion : in that case, this method returns only the dynamic part, wihtout any prefix
	 *
	 *		Example : http://www.mysite.com/user/login -> com=user&page=login
	 *
	 */
	protected static function convertUrl( $url, $dyn_to_stat = true )
	{
		// Be sure of the ampersand value
		$url = comRewrite_::encodeAmpersand($url);

		if ($dyn_to_stat) {
			$source	= 'dynamic';
			$target	= 'static';
		} else {
			$source	= 'static';
			$target	= 'dynamic';
		}

		$rules = self::getRules();

		for ($i=0; $i<count($rules); $i++)
		{
			// Prepare pattern
			$pattern = str_replace('\$', '$', pregQuote( $rules[$i][$source] )); # Escape the pattern (except for the $ symbol)

			// Fill sub-pattern
			for ($s=1; $s<=self::S_MAX; $s++) {
				if ($rules[$i]["s$s"]) {
					$pattern = preg_replace('~\$'."$s~", $rules[$i]["s$s"], $pattern);
				}
			}

			// Match pattern
			if (preg_match("~$pattern~", $url, $matches))
			{
				$replacement = $rules[$i][$target];

				for ($s=1; $s<=self::S_MAX; $s++) {
					if ($rules[$i]["s$s"]) {
						$replacement = preg_replace('~\$'."$s~", $matches[$s], $replacement);
					}
				}

				$url = preg_replace("~$pattern~", $replacement, $url); # Match !!!

				// Post process
				if ($dyn_to_stat) {
					$needle = '&amp;';
					if ( ($needle_pos = strpos($url, $needle)) !== false  &&  !strstr($url, '?') ) {
						$url = substr_replace($url, '?', $needle_pos, strlen($needle)); # Replace the first `&amp;` by `?` !
					}
				}
				else {
					$needle = '/index.php?';
					if ( ($needle_pos = strpos($url, $needle)) !== false ) {
						$url = substr($url, $needle_pos + strlen($needle)); # Keep only the main query_string (keys and values) !
					}
				}

				return $url;
			}
		}

		return false; # No match !!!
	}



	/*
	 * This method assume that the $url parameter is a dynamic url
	 * Notice : the return depends of the given $url : full or relative path
	 */
	public static function dynToStat( $url )
	{
		if (!self::isEnabled()) {
			return $url;
		}

		if ($convert = self::convertUrl($url, true)) {
			return $convert;
		}

		return $url;
	}



	/*
	 * This method assume that the $url parameter is a static url
	 * Notice : the return is always a relative path !
	 */
	public static function StatToDyn( $url )
	{
		if (!self::isEnabled()) {
			return $url;
		}

		if ($convert = self::convertUrl($url, false)) {
			return WEBSITE_PATH."/index.php?$convert";
		}

		return $url;
	}


	/**
	 * @return false, 'dynamic', 'ftp', 'static', true
	 *
	 * When the method returns 'dynamic' or 'static', the $url_dyn_part parameter contains the associated dynamic url
	 * Examples :
	 *
	 * If :
	 * $url =			'http://www.mysite.com/index.php?com=user&amp;page=login' or
	 *					'http://www.mysite.com/user/login/'
	 * Then :
	 * $url_dyn_part =	'com=user&amp;page=login'
	 */
	public static function isLocalHostUrl( $url, &$url_dyn_part )
	{
		$url_dyn_part = false;

		// Protocol
		$url = preg_replace('~^http(s)?://~i', '', $url, 1, $protocol);

		if ($protocol)
		{
			// Localhost
			$url = preg_replace('~^'.pregQuote($_SERVER['HTTP_HOST']).'~i', '',  $url, 1, $localhost);

			if (!$localhost) {
				return false;
			}
		}

		// Dynamic url
		$url = preg_replace('~^'.pregQuote(WEBSITE_PATH.'/index.php').'(\?)?~i', '',  $url, 1, $dynamic);

		if ($dynamic) {
			$url_dyn_part = $url;
			return 'dynamic';
		}

		// Path from root (and without query_string)
		$url_root = $_SERVER['DOCUMENT_ROOT'].(!preg_match('~^/~i', $url) ? WEBSITE_PATH.'/' : '').preg_replace('~\?(.)*$~', '', $url);		

		// Ftp resource
		if (is_file($url_root) || is_dir($url_root))
		{
			return 'ftp';
		}

		// Static url
		if ($url_dyn_part = self::convertUrl($url, false)) {
			return 'static';
		}

		return true;
	}


}

?>