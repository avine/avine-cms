<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Class
class ftpManager
{
	public	$base;

	private	$tree			= NULL;	# Contain the result of setTree() method

	private	$cache			= false;

	// chmod of new files or directories
	const	NEW_FILE_CHMOD	= NULL,	# NULL to disable or value (0644 (octal) for example)
			NEW_DIR_CHMOD	= 0755;	# Must be set (0755 (octal) for example)



	public function __construct( $base = '' )
	{
		clearstatcache();

		$this->setBase($base);
	}



	public function setBase( $base )
	{
		$this->base = $base; # Example: $_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH
	}



	public function getBase()
	{
		return $this->base;
	}



	public function useCache( $bool )
	{
		$this->cache = $bool ? true : false;
	}



	// Concatenate safely $this->base with any $path
	public function addBaseToPath( $path )
	{
		# For now, we just keep an eye on the detected errors...
		if ($this->base && $path)
		{
			if ( preg_match('~[^/]$~', $this->base) && preg_match('~^[^/]~', $path) )
			{
				trigger_error("Concatenated path is missing a '/' in ".__METHOD__." :<br />".$this->base.$path);
			}
			elseif ( preg_match('~[/]$~', $this->base) && preg_match('~^[/]~', $path) )
			{
				trigger_error("Concatenated path contains a double '/' in ".__METHOD__." :<br />".$this->base.$path);
			}
		}
		# end

		return $this->base.$path;
	}



	public function isDir( $path_dir = '' )
	{
		return is_dir($this->addBaseToPath($path_dir));
	}



	public function isFile( $path_file )
	{
		return is_file($this->addBaseToPath($path_file));
	}



	public function filePermsOctal( $path_file )
	{
		return substr(sprintf( '%o', fileperms($this->addBaseToPath($path_file)) ), -4);
	}



	public function read( $path_file )
	{
		$full_path_file = $this->addBaseToPath($path_file);

		if (is_readable($full_path_file))
		{
			$result = file_get_contents($full_path_file);
		} else {
			$result = false;
		}

		return $result; # String or FALSE
	}



	public function write( $path_file, $content = '', $append = false )
	{
		$full_path_file = $this->addBaseToPath($path_file);

		// New file ? Remember this !
		is_file($full_path_file) or $new_file = true;

		$result = false;
		if (isset($new_file) || is_writable($full_path_file))
		{
			$result = file_put_contents($full_path_file, $content, $append ? FILE_APPEND : 0);
		}
		$result === false or $result = true;

		// chmod (overwrite the default server config)
		if (isset($new_file) && self::NEW_FILE_CHMOD)
		{
			$this->chmod($path_file, self::NEW_FILE_CHMOD);
		}

		return $result; # TRUE or FALSE
	}



	public function chmod( $path_file, $mode )
	{
		if (!$this->isFile($path_file)) {
			return NULL; # NULL
		}

		return chmod($this->addBaseToPath($path_file), $mode); # TRUE or FALSE
	}



	public function rename( $old_path_file, $new_path_file, $use_base_for_new_path = true )
	{
		if ($use_base_for_new_path)
		{
			$new_path_file = $this->addBaseToPath($new_path_file);
		}

		return rename($this->addBaseToPath($old_path_file), $new_path_file);
	}



	public function mkdir( $dir_name, $mode = false )
	{
		($mode !== false) or $mode = self::NEW_DIR_CHMOD;

		return @mkdir($this->addBaseToPath($dir_name), $mode);
	}



	public function delete( $path )
	{
		if ($this->isDir($path)) {
			return @rmdir($this->addBaseToPath($path));
		}
		elseif ($this->isFile($path)) {
			return @unlink($this->addBaseToPath($path));
		}

		return NULL;
	}



	/**
	 *	How to use this method ? Learn the followed example...
	 *
	 *	$ftp = new ftpManager($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH);
	 *	$list = $ftp->setTree()->getTree();
	 *
	 *	$output = '';
	 *	foreach ($list as $dir => $files)
	 *	{
	 *		$output .= "<h3>$dir</h3>\n<p>";
	 *		for ($i=0; $i<count($files); $i++)
	 *		{
	 *			$output .= "$dir/{$files[$i]}<br>";
	 *		}
	 *		$output .= "</p>\n";
	 *	}
	 *
	 *	echo $output;
	 */
	public function setTree( $dir_path = '', $recursive = true )
	{
		static $tree  = array();

		// Init
		if (!( (func_num_args() == 3) && (func_get_arg(2) == 'in_process') )) {
			$tree = array();
		}

		// Read directory from cache or from FTP
		if (!$this->cache($dir_path, $here_dir, $here_file))
		{
			// Try to open dir
			if (!$current_dir = @opendir($this->addBaseToPath($dir_path))) {
				return $tree;
			}

			// Read dir from FTP
			$here_dir  = array();
			$here_file = array();
			while ($content = readdir($current_dir))
			{
				if (($content != '.') && ($content != '..'))
				{
					/*
					 * TODO - This code part is checking that there's no double '/' in the path.
					 * For now, the method $this->addBaseToPath() just notice this kind of problems.
					 * SO, if in the future, this method will fix the double '/' problem, then this code part will simplified...
					 */
					if ($dir_path) {
						$full_path = preg_replace('~/$~', '', $dir_path).'/'.$content; # Remove potential last '/'
					} else {
						preg_match('~/$~', $this->base) ? $full_path = $content : $full_path = '/'.$content;
					} # end of part

					if (is_dir($this->addBaseToPath($full_path)))
					{
						$here_dir[] = $full_path;
					} else {
						$here_file[] = $content;
					}
				}
			}
			sort($here_dir );
			sort($here_file);

			// Close dir
			closedir($current_dir);

			// Cache
			$this->cache($dir_path, $here_dir, $here_file);
		}

		// Get files of the current directory
		$tree[$dir_path] = $here_file;

		if ($recursive) {
			// Restart process for the childs directories
			for ($i=0; $i<count($here_dir); $i++) {
				$this->setTree($here_dir[$i], $recursive, 'in_process');
			}
		}

		$this->tree = $tree;
		return $this;
	}



	protected function cache( $dir_path, &$dir, &$file )
	{
		if (!$this->cache) {
			return false;
		}
		$session = new sessionManager(sessionManager::BACKEND, 'sessionManager');
		$cache = &$session->returnVar('cache');

		// Get cache
		if (isset($cache[$dir_path])) {
			$dir	= $cache[$dir_path]['dir'	];
			$file	= $cache[$dir_path]['file'	];
			return true;
		}

		// Set cache
		if ($dir || $file) {
			$cache[$dir_path] = array(
				'dir'	=> $dir,
				'file'	=> $file
			);
		}
		return false;
	}



	public function clearCache( $dir_path = '' )
	{
		$session = new sessionManager(sessionManager::BACKEND, 'sessionManager');

		if (!$dir_path) {
			$session->reset('cache');
		}
		else {
			$cache = &$session->returnVar('cache');
			if (isset($cache[$dir_path])) {
				$cache[$dir_path] = NULL;
			}
		}
	}



	// Final method for the setTree() method to get the result (unlike reduceTree() method, this one don't affect $this->tree property)
	public function getTree( $format = '' )
	{
		if (!isset($this->tree)) {
			trigger_error('Invalid call of the method : '.__METHOD__);
			return false;
		}

		if ($format == '') {
			return $this->tree; # Return the tree as it !
		}

		switch ($format)
		{
			// The result is formated to be compatible with formManager class to have full integration into a <select> form, adding the 'optgroup' where it's needed
			case 'dir_options':
				$case_dir_options = true;

			case 'file_options':
				if (!isset($case_dir_options))
				{
					$case_dir_options = false;
					$optgroup = '(optgroup)';
				} else {
					$optgroup = '';
				}

				$tree_options = array();
				reset($this->tree);
				foreach ($this->tree as $dir => $files)
				{
					// Dir
					$tree_options[$dir.$optgroup] = $dir;

					// Files
					if (!$case_dir_options) {
						for ($i=0; $i<count($files); $i++) {
							$tree_options[$dir.'/'.$files[$i]] = $files[$i];
						}
					}
				}
				return $tree_options;
				break;

			// Error !
			default:
				trigger_error("Invalid \$format=$format parameter in : ".__METHOD__);
				return $this->tree; # Even in that case return something...
				break;
		}
	}



	// Middle method for the setTree() method to reduce $this->tree (before calling the final method getTree())
	public function reduceTree( $action )
	{
		if (!isset($this->tree)) {
			trigger_error('Invalid call of the method : '.__METHOD__);
			return false;
		}

		$tree = array();
		reset($this->tree);

		switch ($action)
		{
			// Remove all invalid dir name and file name (notice: if you need this, do it first !)
			case 'remove_invalid_dir_and_file':
				foreach ($this->tree as $dir => $files)
				{
					if (formManager_filter::isPath($dir)) {
						if (count($files))
						{
							for ($i=0; $i<count($files); $i++) {
								if (formManager_filter::isFile($files[$i])) {
									$tree[$dir][] = $files[$i];
								}
							}
						}
						else {
							$tree[$dir] = $files;
						}
					}
				}
				break;

			// Keep only dir
			case 'keep_dir':
				foreach ($this->tree as $dir => $files) {
					$tree[$dir] = array();
				}
				break;

			// Exclude dir by path
			case 'exclude_dir_by_path':
				$excluded_path 			= func_get_arg(1); # Required argument !
				$only_childs_excluded 	= func_get_arg(2); # Required argument !
				foreach ($this->tree as $dir => $value)
				{
					if	(	($excluded_path != $dir || $only_childs_excluded) 										# Are not equal, unless we are excluding only the childs !
							&&
							($excluded_path == $dir || !preg_match('~^('.pregQuote($excluded_path).')~', $dir)) 	# Don't look like each other, unless there are equal !
						) {
						$tree[$dir] = $value;
					}
				}
				break;

			// Exclude dir by name
			case 'exclude_dir_by_name':
				$excluded_name 			= func_get_arg(1); # Required argument !
				foreach ($this->tree as $dir => $value)
				{
					if (!preg_match('~('.pregQuote($excluded_name).')$~', $dir)) {
						$tree[$dir] = $value;
					}
				}
				break;

			// Keep file by extension
			case 'keep_file_by_extension':
				$included_ext 			= func_get_arg(1); # Required argument !
				foreach ($this->tree as $dir => $files)
				{
					for ($i=0; $i<count($files); $i++) {
						$path_info = pathinfo($files[$i]);
						if (in_array($path_info['extension'], $included_ext)) {
							$tree[$dir][] = $files[$i];
						}
					}
				}
				break;

			// Do nothing !
			default:
				trigger_error("Invalid \$action=$action parameter in : ".__METHOD__);
				$tree = $this->tree;
				break;
		}

		$this->tree = $tree;

		return $this;
	}



	/**
	 * @return the location of the uploaded file (full path file) of false
	 */
	public function moveUploadedFile( $full_path_file, $destination, $overwrite = false, $mkdir = false, $middle_char_replacement = '-' )
	{
		$destination = $this->addBaseToPath($destination);

		// Create dir
		$dirname = dirname($destination);
		if (!is_dir($dirname))
		{
			if (!$mkdir) {
				return false;
			}
			elseif (!mkdir($dirname)) {
				return false;
			}
		}

		// Alias
		$mcr = $middle_char_replacement;

		// Clean basename
		$path_info = pathinfo($destination);
		$destination = $path_info['dirname'].'/'.formManager_filter::cleanFileName($path_info['basename'], $mcr);

		// Upload file
		if ($overwrite || !is_file($destination)) {
			if (move_uploaded_file($full_path_file, $destination)) {
				return preg_replace('~^('.pregQuote($this->base).')~', '', $destination); # Return the final name of the uploaded file (relative to $this->base)
			}
		}

		// Rename destination and upload file
		for ($i=1; $i<100; $i++)
		{
			$new_destination = $path_info['dirname'].'/'.formManager_filter::cleanFileName($path_info['filename']." $i.".$path_info['extension'], $mcr); // from PHP 5.2.0
			if (!is_file($new_destination))
			{
				if (move_uploaded_file($full_path_file, $new_destination))
				{
					chmod($new_destination, 0604); # Be sure the uploaded file is readable !

					return preg_replace('~^('.pregQuote($this->base).')~', '', $new_destination); # Return the final name of the uploaded file (relative to $this->base)
				}
			}
		}

		return false;
	}



	/**
	 * Alternative stat function
	 *
	 * Code source: http://fr3.php.net/manual/fr/function.stat.php
	 */
	public function stat( $path_file )
	{
		$file = $this->addBaseToPath($path_file); # Here the full path file (unmodified name of the variable)

		clearstatcache();
		$ss=@stat($file);
		if(!$ss) {
			return false; // Could not stat file
		}

		$ts=array(
			0140000	=>	'ssocket',
			0120000	=>	'llink',
			0100000	=>	'-file',
			0060000	=>	'bblock',
			0040000	=>	'ddir',
			0020000	=>	'cchar',
			0010000	=>	'pfifo'
		);

		$p=$ss['mode'];
		$t=decoct($ss['mode'] & 0170000); // File Encoding Bit

		$str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
		$str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
		$str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
		$str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
		$str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
		$str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
		$str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));

		$s=array(
			'perms'	=>	array(
							'umask'			=>	sprintf("%04o",@umask()),
							'human'			=>	$str,
							'octal1'		=>	sprintf("%o",($ss['mode']&000777)),
							'octal2'		=>	sprintf("0%o",0777&$p),
							'decimal'		=>	sprintf("%04o",$p),
							'fileperms'		=>	@fileperms($file),
							'mode1'			=>	$p,
							'mode2'			=>	$ss['mode']
						),
			'owner'	=>	array(
							'fileowner'		=>	$ss['uid'],
							'filegroup'		=>	$ss['gid'],
							'owner'			=>	(function_exists('posix_getpwuid'))?@posix_getpwuid($ss['uid']):'',
							'group'			=>	(function_exists('posix_getgrgid'))?@posix_getgrgid($ss['gid']):''
						),
			'file'	=>	array(
							'filename'		=>	$file,
							'realpath'		=>	(@realpath($file)!=$file)?@realpath($file):'',
							'dirname'		=>	@dirname($file),
							'basename'		=>	@basename($file)
						),
			'filetype'=>array(
							'type'			=>	substr($ts[octdec($t)],1),
							'type_octal'	=>	sprintf("%07o", octdec($t)),
							'is_file'		=>	@is_file($file),
							'is_dir'		=>	@is_dir($file),
							'is_link'		=>	@is_link($file),
							'is_readable'	=>	@is_readable($file),
							'is_writable'	=>	@is_writable($file)
						),
			'device'=>	array(
							'device'		=>	$ss['dev'], //Device
							'device_number'	=>	$ss['rdev'], //Device number, if device.
							'inode'			=>	$ss['ino'], //File serial number
							'link_count'	=>	$ss['nlink'], //link count
							'link_to'		=>	(@$s['type']=='link')?@readlink($file):''
						),
			'size'	=>	array(
							'size'			=>	$ss['size'], //Size of file, in bytes.
							'blocks'		=>	$ss['blocks'], //Number 512-byte blocks allocated
							'block_size'	=>	$ss['blksize'] //Optimal block size for I/O.
						),
			'time'	=>	array(
							'mtime'			=>	$ss['mtime'], //Time of last modification
							'atime'			=>	$ss['atime'], //Time of last access.
							'ctime'			=>	$ss['ctime'], //Time of last status change
							'accessed'		=>	@date('Y M D H:i:s',$ss['atime']),
							'modified'		=>	@date('Y M D H:i:s',$ss['mtime']),
							'created'		=>	@date('Y M D H:i:s',$ss['ctime'])
						),
		);
		clearstatcache();
		return $s;
	}



	public function stat_view($s)
	{
		$html  = "\n".'<table cellspacing="0" class="alt_stat_view">';
		$html .= '<tr><th colspan="2" class="title">FILE INFOS : analizing the return of ftpManager::stat() method</th></tr>';

		reset($s);
		while(list($k1,$v1)=each($s))
		{
			$html .= "<tr><th colspan=\"2\">$k1</th></tr>";

			while(list($k2,$v2)=each($s[$k1]))
			{
				$html .= "<tr><td>&nbsp; &nbsp;$k2</td><td class=\"value\">$v2</td></tr>";
			}
		}

		$html .= '</table>'."\n";

		echo $html;
	}



	public function filesize( $path_file ) # FIXME - Not work well with utf8 encoded text files
	{
		if (!file_exists($this->addBaseToPath($path_file))) {
			return false;
		}

		return filesize($this->addBaseToPath($path_file));
	}



	public function filesizeHTML( $path_file )
	{
		if ($filesize = $this->filesize($path_file)) {
			return $this->convertBytes($filesize, 'optimize');
		}

		return '';
	}



	static function convertBytes( $value, $output_unit = 'b' )
	{
		// Convert
		if (is_numeric($value))
		{
			$qty = $value; // bit
		}
		else
		{
			$value_length = strlen($value);
			$qty = substr($value, 0, $value_length -1);
			$unit = strtolower(substr($value, $value_length -1));

			switch($unit)
			{
				case 'k': // kilo
					$qty *= 1024;
					break;
				case 'm': // mega
					$qty *= 1024*1024;
					break;
				case 'g': // giga
					$qty *= 1024*1024*1024;
					break;
			}
		}

		// Optimize the output unit and add the unit in the output
		$unit_str = false;
		if ($output_unit == 'optimize')
		{
			if ($qty < 1024)
			{
				$output_unit 	= 'b';
				$unit_str 		= ' Octets';
			}
			elseif ($qty < 1024*1024)
			{
				$output_unit 	= 'k';
				$unit_str 		= ' Ko';
			}
			elseif ($qty < 1024*1024*1024)
			{
				$output_unit 	= 'm';
				$unit_str 		= ' Mo';
			}
			else
			{
				$output_unit 	= 'g';
				$unit_str 		= ' Go';
			}
		}

		// Output unit
		switch($output_unit)
		{
			case 'b':
				#$qty = $qty;
				break;
			case 'k':
				$qty /= 1024;
				break;
			case 'm':
				$qty /= 1024*1024;
				break;
			case 'g':
				$qty /= 1024*1024*1024;
				break;
			default:
				trigger_error("Error occured in convertBytes() function : invalid value for \$output_unit=$output_unit parameter. Expected values : 'b', 'k', 'm', 'g', 'optimize'.");
				return false;
		}

		// Precision
		if (formManager_filter::isReal($qty)) {
			$qty = sprintf('%.2f', $qty);
		}

		if ($unit_str === false)
		{
			return $qty;
		} else {
			return $qty.$unit_str;
		}
	}

}


?>