<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Class
class templateManager
{
	// Html templates engine
	private	$tmpl_html 				= false,		# String wich contains the html code of the template
			$flags 					= array(), 		# Ex. : {flag_1} , {flag_2} , ...
			$multi_flags_blocks 	= array(); 		# Ex. : <!-- multiflag_1[OR] --> , <!-- multiflag_2[AND] --> , ...

	// Php templates engine
	private	$tmpl_path_php			= false,		# Path to a php template, using the $replacements keys as variables
			$variables				= array();		# List of php template variables

	// Used by all engines
	private	$replacements 			= array();		# Array (1 or 2 dimensions)

	private	$debug 					= false;



	/**
	 * Code example :
	 *
	 * $tmpl = new templateManager();
	 *
	 * // Html (or Php) template engine (from path)
	 * echo $tmpl->setTmplPath($path_to_html_or_php_file)->setReplacements($replacements)->process();
	 *
	 * // or Html template engine (from string)
	 * echo $tmpl->setTmplHtml($html)->setReplacements($replacements)->process();
	 */
	public function __construct( $debug = false )
	{
		if ($debug) {
			$this->debug = true;
		}
	}



	// Set $tmpl_html (or $tmpl_path_php) property from a FTP file
	public function setTmplPath( $tmpl_path ) # $tmpl_path parameter can be an array of paths possibilities
	{
		// Check template path
		$tmpl_path_founded = false;
		is_scalar($tmpl_path) ? $tmpl_path = array($tmpl_path) : '';
		$i=0;
		while ($i<count($tmpl_path) && !$tmpl_path_founded)
		{
			if (is_file($tmpl_path[$i]) && filesize($tmpl_path[$i]))
			{
				$tmpl_path_founded = $tmpl_path[$i]; # Take the first available path !
			}
			$i++;
		}

		if ($tmpl_path_founded)
		{
			// For debug
			$debug_tmpl = '';
			if ($this->debug)
			{
				if (count($tmpl_path) == 1)
				{
					$debug_tmpl .= $tmpl_path[0];
				} else {
					for ($i=0; $i<count($tmpl_path); $i++) {
						$debug_tmpl .= $tmpl_path[$i];
						($tmpl_path[$i] == $tmpl_path_founded) ? $debug_tmpl .= " [USED TEMPLATE]\n" : $debug_tmpl .= "\n";
					}
				}
			}

			if (preg_match('~\.html$~', $tmpl_path_founded))
			{
				$this->showDebug('$tmpl_path', $debug_tmpl, 'pre');

				// Set $tmpl_html property
				$this->setTmplHtml( file_get_contents($tmpl_path_founded) ); # That's it!
				/* OLD VERSION
				$handle = fopen($tmpl_path_founded, "r");
				$this->setTmplHtml( fread($handle, filesize($tmpl_path_founded)) ); # That's it!
				fclose($handle);*/
			}
			elseif (preg_match('~\.php$~', $tmpl_path_founded))
			{
				$this->showDebug('$tmpl_path_php', $debug_tmpl, 'pre');

				// Set $tmpl_path_php property
				$this->tmpl_path_php = $tmpl_path_founded; # That's it !
				$this->analyseTmplPHP();
			}
			elseif ($this->debug) {
				trigger_error($this->trig_err("Invalid template extension : $tmpl_path_founded (expected : '.html' or '.php')"));
			}
		}
		else
		{
			if ($this->debug) {
				trigger_error($this->trig_err('Template path error or empty template $tmpl_path=<br />'.implode(',<br />', $tmpl_path)), E_USER_WARNING);
			} else {
				echo '<p style="color:red;">Template path error</p>';
			}
		}

		return $this; # If you want, chain with setReplacements() method...
	}



	// Set directly $tmpl_html property from a string
	public function setTmplHtml( $tmpl_html )
	{
		$this->tmpl_html = $tmpl_html;

		$this->showDebug('$tmpl_html', $this->tmpl_html, 'pre');

		$this->analyseTmplHtml();

		return $this; # If you want, chain with setReplacements() method...
	}



	// Set $flags and $multi_flags_blocks properties
	private function analyseTmplHtml()
	{
		$flag_pattern = '~\{[\.a-zA-Z_0-9\-]+\}~';

		// Get $flags property
		$flags = array();
		preg_match_all($flag_pattern, $this->tmpl_html, $flags);
		$flags = $flags[0];
		$flags = array_unique($flags);
		sort($flags); # sort() is used only to assigns new ordered keys (0,1,2, ...) ; and it's also possible to use array_values() instead
		$this->flags = $flags; # That's it!

		// Find multi-flags delimiter
		$multi_flags_delimiter = array();
		preg_match_all('~(!--)(\s)+([\.a-zA-Z_0-9\-]+)(\[)(OR|AND)(\])(\s)+(--)~', $this->tmpl_html, $multi_flags_delimiter);
		$multi_flags_delimiter = $multi_flags_delimiter[0];
		$multi_flags_delimiter = array_unique($multi_flags_delimiter);
		sort($multi_flags_delimiter); # That's it!

		// Get $multi_flags_blocks property
		$multi_flags_delimiter_errors = '';
		for ($i=0; $i<count($multi_flags_delimiter); $i++)
		{
			$inside_flags_html = explode('<'.$multi_flags_delimiter[$i].'>', $this->tmpl_html);
			if (count($inside_flags_html) == 3)
			{
				$flags = array();
				preg_match_all($flag_pattern, $inside_flags_html[1], $flags);
				$flags = $flags[0];
				$flags = array_unique($flags);
				sort($flags);
			  	$this->multi_flags_blocks['<'.$multi_flags_delimiter[$i].'>'] = $flags; # That's it!
			}
			elseif ($this->debug) {
				$multi_flags_delimiter_errors .= "&lt;{$multi_flags_delimiter[$i]}&gt;<br />";
			}
		}

		if ($this->debug)
		{
			// $flags
			if (count($this->flags))
			{
				$this->showDebug('$flags', $this->flags, 'implode');
			} else {
				trigger_error($this->trig_err("The \$html_tmpl doesn't contain any flag replacement!"), E_USER_NOTICE);
			}

			// $multi_flags_delimiter_errors
			if ($multi_flags_delimiter_errors)
			{
				trigger_error($this->trig_err("Invalid number of \$multi_flags_delimiter:<br /><b>$multi_flags_delimiter_errors</b>"), E_USER_NOTICE);
			}

			// $multi_flags_blocks
			if (count($this->multi_flags_blocks))
			{
				$temp = array();
				reset($this->multi_flags_blocks);
				foreach($this->multi_flags_blocks as $multi_flag_delimiter => $flags)
				{
					$temp[htmlentities($multi_flag_delimiter, ENT_COMPAT, 'UTF-8')] = implode('<br />', $flags);
				}
				$this->showDebug('$multi_flags_blocks', $temp);
			}
		}
	}



	// Set $variables property
	private function analyseTmplPHP()
	{
		$tmpl_php = file_get_contents($this->tmpl_path_php);
		/* OLD VERSION
		$handle = fopen($this->tmpl_path_php, "r");
		$tmpl_php = fread($handle, filesize($this->tmpl_path_php));
		fclose($handle);*/

		preg_match_all('~(\$)([a-zA-Z_]+[a-zA-Z_\d]*)~', $tmpl_php, $variables); # variable pattern
		#alt_print_r($variables); # Check $variables content if you need to

		// Set $variable property
		$this->variables = array_fill_keys(array_unique($variables[2]), NULL);	# The variables without the $

		$this->showDebug('$tmpl_path_php (file content)', $tmpl_php, 'pre');
		$this->showDebug('$variables', array_unique($variables[0]), 'implode');	# The variables with the $
	}



	public function setReplacements( $replacements )
	{
		$this->replacements = $replacements;

		if ($this->debug)
		{
			// $replacements
			$temp = array();
			reset($this->replacements);
			foreach($this->replacements as $search => $replace)
			{
				if (is_array($replace)) {
					$temp[$search] = implode('<hr />', $replace);
				}
				else {
					$temp[$search] = $replace;
				}
			}
			$this->showDebug('$replacements', $temp);
		}

		return $this; # If you want, chain with process() method...
	}



	public function process()
	{
		if ($this->tmpl_html)
		{
			return $this->processHtml();
		}
		elseif ($this->tmpl_path_php)
		{
			return $this->processPhp();
		}

		return '';
	}



	// Process for Html template engine
	private function processHtml()
	{
		if ($this->debug)
		{
			$unknown_search = array();
			reset($this->replacements);
			foreach($this->replacements as $search => $replace)
			{
				if (!in_array('{'.$search.'}', $this->flags)) {
					$unknown_search[] = '{'.$search.'} ';
				}
			}
			if (count($unknown_search)) {
				$this->showDebug('$replacements keys wich doesn\'t match any $tmpl_html flag', $unknown_search, 'implode');
			}
		}

	    if (!count($this->flags )) {
			return $this->tmpl_html; # Static template!
		}

		$flags_replaced 		= array();
		$flags_delimiter_errors = '';

		// $flags replacements
		$output = $this->tmpl_html;
		for ($i=0; $i<count($this->flags); $i++)
		{
			$flag 			= $this->flags[$i];
			$flag_delimiter = "<!-- $flag -->";
			$search 		= preg_replace('~(\{|\})~', '', $flag);

			// Prepare current replacement
			$current_replacement = array();
			if (array_key_exists($search, $this->replacements))
			{
				if (is_scalar($this->replacements[$search]))
				{
					if ($this->replacements[$search] != '') {
						$current_replacement[] = $this->replacements[$search]; # Simple replacement
					}
				}
				else
				{
					for ($j=0; $j<count($this->replacements[$search]); $j++)
					{
						if ($this->replacements[$search][$j] != '') {
							$current_replacement[] = $this->replacements[$search][$j]; # Multi replacement
						}
					}
				}
			}
			if (count($current_replacement)) {
				$flags_replaced[] = $flag;
			}

			$output_array = explode($flag_delimiter, $output);
			if (count($output_array) == 3)
			{
				// Remove from the output the $flag_delimiter itself
				$top 		= trim($output_array[0]);
				$middle 	= '';
				$bottom 	= trim($output_array[2]);

				if (count($current_replacement))
				{
					$middle_pattern = trim($output_array[1]);

					for ($j=0; $j<count($current_replacement); $j++)
					{
						$middle .= str_replace($flag, $current_replacement[$j], $middle_pattern);
					}
				}
				$output = $top.$middle.$bottom;
			}
			elseif (count($output_array) == 1)
			{
				$middle = '';
				for ($j=0; $j<count($current_replacement); $j++)
				{
					$middle .= $current_replacement[$j];
				}
				$output = str_replace($flag, $middle, $output);
			}
			else {
				/**
				 * Known limitation:
				 * The html template can contain multiples occurences of any flag: <h2>{flag}</h2><h3>{flag}</h3>
				 * But the flag delimiter can appear only once: <!-- {flag} --><p>{flag}</p><!-- {flag} -->
				 */
				$flags_delimiter_errors .= "&lt;!-- $flag --&gt; ";
			}
		}

		// $multi_flags_blocks process
		reset($this->multi_flags_blocks);
		foreach($this->multi_flags_blocks as $multi_flag_delimiter => $flags)
		{
			if (strstr($multi_flag_delimiter, '[OR]')) 		# 'OR' condition
			{
				$empty = true;
				$i = 0;
				while (($empty) && ($i<count($flags))) {
					in_array($flags[$i++], $flags_replaced) ? $empty = false : '';
				}
			}
			else 											# 'AND' condition
			{
				$empty = false;
				$i = 0;
				while ((!$empty) && ($i<count($flags))) {
					!in_array($flags[$i++], $flags_replaced) ? $empty = true : '';
				}
			}

			if ($empty)
			{
				$output_array = explode($multi_flag_delimiter, $output);
				if (count($output_array) == 3) {
					$output = trim($output_array[0]).trim($output_array[2]);
				}
			}
			else {
				$output = str_replace($multi_flag_delimiter, '', $output); # Simply remove from the output the $multi_flag_delimiter itself
			}
		}

		if ($this->debug)
		{
			// flags errors
			if ($flags_delimiter_errors) {
				trigger_error($this->trig_err("Invalid number of \$flags_delimiter:<br /><b>$flags_delimiter_errors</b>"), E_USER_NOTICE);
			}

			// $output
			$this->showDebug('$output', $output, 'pre');
			echo "<hr />\n"; # Last debug output !
		}

		return $output;
	}



	// Process for Php template engine
	private function processPhp()
	{
		if ($this->debug)
		{
			if ($diff = array_keys(array_diff_key($this->replacements, $this->variables))) {
				$this->showDebug('$replacements keys wich doesn\'t match any php variable', '$'.implode(', $', $diff));		
			}
			unset($diff);
			echo "<hr />\n"; # Last debug output !
		}

		// Let's go !
		ob_start();

		extract($this->variables);		# Init all template variables (prevent php errors)
		extract($this->replacements);	# Set availables template variables

		require($this->tmpl_path_php);
		$ob_get_contents = ob_get_contents();

		ob_end_clean();

		return $ob_get_contents;
	}



	private function showDebug( $title, $content, $type = '' )
	{
		if (!$this->debug) {
			return;
		}

		echo '<div style="margin-bottom:15px; border:1px solid #5F9EA0;">'; # Wrapper begin
		echo "<div style=\"background-color:#5F9EA0; color:#FFF; font-weight:bold;\">&nbsp;$title</div>"; # Title
		echo '<div style="padding:5px;">';

		if (is_scalar($content))
		{
			if ($type == 'pre')
			{
				echo '<pre style="margin:0; padding:0; font-size:11px; overflow:auto;">'.htmlentities($content, ENT_COMPAT, 'UTF-8').'</pre>';
			} else {
				echo htmlentities($content, ENT_COMPAT, 'UTF-8');
			}
		}
		else
		{
			if ($type == 'implode')
			{
				echo implode(', ', $content);
			} else {
				$table = new tableManager($content);
				echo $table->html(1);
			}
		}

		echo '</div>';
		echo '</div>'; # Wrapper end
	}



	private static function trig_err( $message )
	{
		return " <span style=\"color:#8B0000; background-color:#FFEAEA;\">&nbsp;in class ".__CLASS__." : $message&nbsp;</span>";
	}

}


?>