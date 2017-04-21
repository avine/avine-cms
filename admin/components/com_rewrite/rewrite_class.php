<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////
// Class

class admin_comRewrite_config
{
	private	$ftp;

	const HTACCESS_COMMENT_SEPARATOR	= "############### SEPARATOR (DO NOT REMOVE) ###############";
	const HTACCESS_CHARSET				= "# Charset\nAddDefaultCharset utf-8\n\n";
	const HTACCESS_INDEX				= "# Index\nDirectoryIndex index.php index.html\n\n";
	const HTACCESS_ERROR404				= "# Error 404\nErrorDocument 404 WEBSITE_PATH/index.php?file=error404.php\n\n";
	const HTACCESS_REWRITE_ENGINE		= 
"# Url rewriting
<IfModule mod_rewrite.c>
Options +FollowSymLinks
Options +Indexes
RewriteEngine On
RewriteBase WEBSITE_PATH/
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule (.*) index.php?rewrite=$1 [QSA,L]\n</IfModule>\n\n";



	public function __construct()
	{
		// Set .htaccess path
		$this->ftp = new ftpManager(sitePath().'/');
	}



	public function isEnabled()
	{
		#$db = new databaseManager(); # Old code - Should be a simple mistake...
		global $db;
		return $db->selectOne('rewrite_config, enabled', 'enabled');
	}



	public function getHtaccess( $parts = false )
	{
		if (!$this->ftp->isFile('.htaccess')) {
			return NULL;					# .htaccess file is missing !
		}

		// Read .htaccess and try to find the separator between the system part and the user part
		$htaccess = $this->ftp->read('.htaccess');
		$htaccess_part = explode(self::HTACCESS_COMMENT_SEPARATOR, $htaccess);

		if (count($htaccess_part) != 2) {
			return false;					# .htaccess content is not valid !
		}

		if (!$parts) {
			return $htaccess;				# Return full content
		} else {
			return							# Return content parts
				array(
					'system'	=> $htaccess_part[0],
					'user'		=> trim($htaccess_part[1])
				);
		}
	}



	public function updateHtaccessUser( $htaccess_user )
	{
		if ($htaccess = $this->getHtaccess(true))
		{
			if ($this->ftp->write('.htaccess', $htaccess['system'].self::HTACCESS_COMMENT_SEPARATOR."\n\n".trim($htaccess_user)))
			{
				clearstatcache();
				return true;
			}
		}

		return false;
	}



	// Switch on/off the rewrite engine (modify '.htaccess' file and 'rewrite_config' table)
	public function switchEngine( $enabled, $include_user_part = true )
	{
		$htaccess = self::HTACCESS_CHARSET.self::HTACCESS_INDEX;

		if (!$enabled)
		{
			$enabled = '0';
			$htaccess .= self::HTACCESS_ERROR404;
		} else {
			$enabled = '1';
			$htaccess .= self::HTACCESS_REWRITE_ENGINE;
		}
		$htaccess = str_replace('WEBSITE_PATH', WEBSITE_PATH, $htaccess);

		$htaccess .= self::HTACCESS_COMMENT_SEPARATOR."\n\n";

		if ($include_user_part) {
			$htaccess_part = $this->getHtaccess(true);
			isset($htaccess_part['user']) ? $htaccess .= $htaccess_part['user'] : '';
		}

		if ($this->ftp->write('.htaccess', $htaccess))
		{
			clearstatcache();
			#$db = new databaseManager(); # Old code - Should be a simple mistake...
			global $db;
			$db->update("rewrite_config; enabled=$enabled");
			return true;
		} else {
			return false;
		}
	}

}



/////////////
// Functions

function admin_comRewrite_rulesHeader()
{
	return
		array(
			LANG_ADMIN_COM_REWRITE_RULES_ID,
			LANG_ADMIN_COM_REWRITE_RULES_POS,
			LANG_ADMIN_COM_REWRITE_RULES_STATIC,
			LANG_ADMIN_COM_REWRITE_RULES_TARGET,
			LANG_ADMIN_COM_REWRITE_RULES_S1,
			LANG_ADMIN_COM_REWRITE_RULES_S2,
			LANG_ADMIN_COM_REWRITE_RULES_S3
		);
}



?>