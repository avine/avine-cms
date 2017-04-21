<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


//////////////////////////////////////////////////////////////////////////////
// Class and associated functions : Manage to navigation into the admin pages

// Functions : Pathway //

/**
 * To surf into the amdin of the site, we are using $_GET variable.
 * Those functions allows to manage the url details
 *
 * Ex.: /admin/index.php?go=menu&tab=link  means:  go the 'menu' section and open the 'link' tab
 */

// Update the pathway when go deeper in the admin (when include files into files)
function admin_updatePathway( $path, $name )
{
	global $g_admin_pathway;

	$new_pos = count($g_admin_pathway);

	$g_admin_pathway[$new_pos]['url' ] = $path;
	$g_admin_pathway[$new_pos]['name'] = $name;
}



// Get current or back_x pathway
function admin_getPathway( $back = 0 )
{
	$pathway = '';

	global $g_admin_pathway;

	/*
	 * Default behaviour
	 * -----------------
	 * This function is called by a script wich is required from the "backend"
	 * The variable $g_admin_pathway have been initialized in the main admin script : '/admin/index.php'
	 */
	if (isset($g_admin_pathway))
	{
		if ($back < 0) {
			$back *= -1; # $back is strictly >= 0
		}

		$steps = count($g_admin_pathway);
		if ($back < $steps)
		{
			$pathway = '?'.$g_admin_pathway[0]['url'];

			for ($i=1; $i<$steps-$back; $i++) {
				$pathway .= '&amp;'.$g_admin_pathway[$i]['url'];
			}
		}
	}
	/*
	 * Special behaviour (to allow the loading of backend scripts in frontend)
	 * -----------------
	 * This function is called by a script wich is required from the "frontend"
	 * In frontend the variable $g_admin_pathway should never be initialized !
	 */
	else
	{
		if ($_SERVER['QUERY_STRING']) {
			$pathway = '?'.$_SERVER['QUERY_STRING'];
		}
	}

	return $pathway;
}



// Show the pathway
function admin_showPathway()
{
	global $g_admin_pathway;

	$pathway = '<span>&gt;</span> ';
	$steps = count($g_admin_pathway);
	for ($i=0; $i<$steps-1; $i++)
	{
		$pathway .= $g_admin_pathway[$i]['name'].' <span>&gt;</span> ';
	}

	if ($steps != 0) {
		$pathway .= $g_admin_pathway[$steps-1]['name'];
	}

	return $pathway;
}



// Find the url_key ( ex.: admin/index.php?$menu_url_key[$i]={url_value} )
function admin_getUrl_key( $offset = 0 )
{
	$menu_url_key = array('go', 'tab', 'menu');

	global $g_admin_pathway;

	return $menu_url_key[count($g_admin_pathway) + $offset];
}



// Allow all users to view the administration in demo mode
function admin_demoMode()
{
	static $once = false;
	if ($once) {
		return;
	}

	global $g_user_login;
	if ($g_user_login->userID() === '1') # If the logged user is the super administrator, then disable the demo mode !!!
	{
		admin_demoMode_message(LANG_ADMIN_DEMO_MODE_TIPS_SUPER_ADMIN);
	} else {
		admin_demoMode_message(LANG_ADMIN_DEMO_MODE_TIPS);

		$_REQUEST	= NULL;
		$_POST		= NULL;
		foreach($_GET as $k => $v) {
			if (!in_array($k, array('go', 'tab', 'menu'))) { # Preserve the admin navigation !
				unset($_GET[$k]);
			}
		}
	}

	$once = true;
}



function admin_demoMode_message( $message )
{
	echo
		'<div style="position:absolute; left:29px; top:31px; width:350px; line-height:14px; font-size:11px; font-family:Verdana; color:#c41111;">'.
			'<img src="'.siteUrl().'/admin/images/demo_mode.png" style="float:left; margin-right:7px;" /><em>'.$message.'</em>'.
		"</div>\n";
}



// Class : Admin-menu //

/**
 * All available pages of the admin section are stored into a database table.
 * The 'admin_menu' table contain the links details informations.
 */

class admin_menuManager
{
	public	$path,		# The pathway of where we come from
			$url_key,	# The current key of the url ( index.php?go=...&tab=... )
			$menu;		# The menu infos from database

	public	$submenus;	# The submenu of the current menu



	// Get menu infos from database and get the current pathway infos
	public function __construct ( $menu_name )
	{
		$this->path		= admin_getPathway();
		$this->url_key	= admin_getUrl_key();

		$this->menu		= $this->getMenu($menu_name);
	}



	private function getMenu ( $menu_name )
	{
		global $db, $g_user_login;

		return
			$db->select(
				'admin_menu, url_value, inc_file, link_name, link_order(asc), '.
				'where: name='.$db->str_encode($menu_name).' AND, where: access_level>='.$g_user_login->accessLevel().' AND, where: published=1'
			);
	}



	public function addSubmenus ( )
	{
		for ($i=0; $i<count($this->menu); $i++) {
			$this->submenus[] = $this->getMenu($this->menu[$i]['url_value']);
		}
	}



	// Display menu using '$this->menu'
	public function displayMenu ( $class = '' )
	{
		$html = '';

		if ($class != '') {
			$class = " class=\"$class\"";
		}

		if ($this->path == '') {
			$sep = '?';
		} else {
			$sep = '&amp;';
		}

		if (!isset($_GET[$this->url_key])) {
			$take_first = true;
			$get_url_key = NULL;
		} else {
			$take_first = false;
			$get_url_key = $_GET[$this->url_key];
		}

		$html .= "\n<ul$class>";
		for ($i=0; $i<count($this->menu); $i++)
		{
			$title	= $this->menu[$i]['link_name'];
			$query	= $this->url_key.'='.$this->menu[$i]['url_value'];
			$link	= $_SERVER['PHP_SELF'].$this->path.$sep.$query;

			// Menu
			if (($take_first == true) || ($get_url_key == $this->menu[$i]['url_value']))
			{
				// Alternative 1 : current link not clickable !
				#$html .= "<li class=\"focus\"><span><span>&nbsp;</span>$title</span>";

				// Alternative 2 : current link also clickable !
				$html .= "<li class=\"current\"><a href=\"$link\"><span>&nbsp;</span>$title</a>";
			} else {
				$html .= "<li><a href=\"$link\"><span>&nbsp;</span>$title</a>";
			}

			// Submenu
			if ( ($submenu = $this->submenus[$i]) && (count($submenu) > 1) )
			{
				$html .= "<ul>";
				for ($j=0; $j<count($submenu); $j++)
				{
					$query = admin_getUrl_key(1).'='.$submenu[$j]['url_value'];
					$html .= "<li><a href=\"$link&amp;$query\">{$submenu[$j]['link_name']}</a></li>"; # Known limitation : this $submenu can not have the focus !
				}
				$html .= "</ul>";
			}

			$html .= '</li>';

			$take_first = false;
		}
		$html .= "</ul>\n";

		echo $html;
	}



	// Include the required target file
	public function includeTarget ()
	{
		if (!isset($_GET[$this->url_key]))
		{
			$take_first = true;
			$get_url_key = NULL;
		} else {
			$take_first = false;
			$get_url_key = $_GET[$this->url_key];
		}

		$i = 0;
		$inc = '';
		while (($inc == '') && ($i < count($this->menu)))
		{
			if (($take_first == true) || ($get_url_key == $this->menu[$i]['url_value']))
			{
				$key_value 	= $this->url_key.'='.$this->menu[$i]['url_value'];
				$name 		= $this->menu[$i]['link_name'];
				$inc 		= sitePath().$this->menu[$i]['inc_file'];
			}
			$i++;
		}

		if ($inc != '')
		{
			admin_updatePathway($key_value, $name); /* Update the pathway and go safely deeper in the admin */

			if (file_exists($inc))
			{
				// Activate the demo mode !
				!ADMIN_DEMO_MODE or admin_demoMode();

				// Give the required file some usefull variable (so, please do not redefine them...)
				global $db;

				require($inc);
			} else {
				echo LANG_ADMIN_ADMIN_MENU_CONFIG_ERROR_WITH_ADMIN_MENU_TABLE;
			}
		}
		else {
			echo LANG_ADMIN_ADMIN_MENU_TARGET_NOT_FOUND;
		}
	}

}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



class adminPermissions
{
	private	$access_level,
			$user_id,
			$user_comment;



	public function __construct()
	{
		global $g_user_login;
		$this->access_level	= $g_user_login->accessLevel();
		$this->user_id		= $g_user_login->userID();

		// Additional info
		$status = comUser_getStatusOptions();
		$this->user_comment	= $status[$this->access_level];
	}



	public function canAccessLevel( $access_level )
	{
		if ($this->access_level > $access_level) {
			return false;
		}
		return true;
	}



	// Same method as canAccessLevel() but using the status (administrator, webmaster, ...) instead of the access_level (1, 2, ...)
	public function canAccessStatus( $status )
	{
		global $db;
		$user = $db->select('user_status, [status], id');

		return $this->canAccessLevel($user[$status]['id']);
	}



	public function create( &$perm_denied )
	{
		if ($this->access_level > 4)					# Registered (=5) can not create
		{
			$perm_denied = str_replace('{user_status}', $this->user_comment.'s', LANG_ADMIN_PERMISSIONS_CAN_NOT_CREATE);
			return false;
		}
		return true;
	}



	public function publish( &$perm_denied )
	{
		if ($this->access_level > 3)					# Author (=4) can not publish/unpublish
		{
			$perm_denied = str_replace('{user_status}', $this->user_comment.'s', LANG_ADMIN_PERMISSIONS_CAN_NOT_PUBLISH);
			return false;
		}
		return true;
	}



	public function delete( &$perm_denied )
	{
		if ($this->access_level > 2)					# Editor (=3) can not delete
		{
			$perm_denied = str_replace('{user_status}', $this->user_comment.'s', LANG_ADMIN_PERMISSIONS_CAN_NOT_DELETE);
			return false;
		}
		return true;
	}



	public function archive( &$perm_denied )
	{
		if ($this->access_level > 2)					# Editor (=3) can not archive
		{
			$perm_denied = str_replace('{user_status}', $this->user_comment.'s', LANG_ADMIN_PERMISSIONS_CAN_NOT_ARCHIVE);
			return false;
		}
		return true;
	}



	public function update( $published, $author_id, &$perm_denied )
	{
		if ($this->access_level <= 3) {					# Editor (=3) can modify the data of any author
			return true;
		}
		if ($published) {								# Published data can not be modified by any author
			$perm_denied = LANG_ADMIN_PERMISSIONS_CAN_NOT_UPDATE;
			return false;
		}
		if (!$author_id) {								# The data is not associated to an author ($author_id == false)
			return true;
		}
		if ($this->user_id != $author_id) {				# Unpublished data can be modified only by their author
			$perm_denied = LANG_ADMIN_PERMISSIONS_IS_NOT_THE_AUTHOR;
			return false;
		}
		return true;
	}

}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



function indexTitleIcon( $alias )
{
	$base = WEBSITE_PATH.'/admin/images/index-title';

	if (is_file($_SERVER['DOCUMENT_ROOT']."$base/$alias.png")) {
		return "<img src=\"$base/$alias.png\" alt=\"\" />";
	}
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



function admin_siteName()
{
	global $db;
	return $db->selectOne('config, site_name', 'site_name');
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////


# TODO - Simplifier ! C'est devenu le même code que en frontend !!!!! puisque boxManager détecte par défault ou il est !!!...

// Result information, after processing something
function admin_informResult( $result, $success = '', $failure = '', $width = 0 )
{
	$box = new boxManager();
	$box->echoResult($result, $success, $failure, $width);
}



// Simple information
function admin_message( $message, $class_type, $width = 0 )
{
	$box = new boxManager();
	$box->echoMessage($message, $class_type, true, $width);
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



// Put content into a fieldset
function admin_fieldset( $fieldset, $legend = '' )
{
	$eol = "\n";
	$html = '<fieldset class="admin_fieldset">'.$eol;

	if ($legend != '')
	{
		$html .= "<legend>$legend</legend>$eol";
	}
	$html .= $fieldset;

	$html .= '</fieldset>'.$eol;

	return $html;
}



// Put fieldset into a wrapper to the left or to the right
function admin_fieldsetsWrapper( $wrapper, $legend = '', $param = 'none,99' )
{
	if ($param === 'clear') {
		return '<br style="clear:both;" />';
	}

	$param = explode(',', $param);

	// Float
	!in_array($param[0], array('left', 'none', 'right')) ? $param[0] = '' : '';
	$param[0] ? $float = "float:{$param[0]};" : $float = '';

	// Width
	!preg_match('~^[0-9]+$~', $param[1]) ? $param[1] = '' : '';
	$param[1] ? $width = "width:{$param[1]}%;" : $width = '';

	($float || $width) ? $style = ' style="'.$float.$width.'"' : $style = "";

	$eol = "\n";
	$html = "<fieldset class=\"admin_fieldsets-wrapper\"$style>$eol";

	if ($legend != '')
	{
		$html .= "<legend>$legend</legend>$eol";
	}
	$html .= "<div class=\"admin_fieldsets-wrapper-content\">$wrapper</div>";

	$html .= "</fieldset>$eol";

	return $html;
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Simply display array of contents with float:left; or float:right; css style
function admin_floatingContent( $contents = array(), $float = 'left' )
{
	$html = '';

	if ($float == 'left') {
		$float = 'left';
	}
	else {
		$float = 'right';
		$contents = array_reverse($contents);
	}

	$html .= "<div class=\"admin_floating-content\">\n";
	for ($i=0; $i<count($contents); $i++)
	{
		if ($contents[$i]) {
			$html .= "<div class=\"admin_floating-content-$float\">\n{$contents[$i]}</div>\n";
		}
	}
	$html .= "<br class=\"admin_floating-content-$float\" />&nbsp;</div>\n\n";

	return $html;
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



// Return an image : true => checked.png ; false => unchecked.png
function admin_replaceTrueByChecked( $test, $clickable = true )
{
	if ($clickable)
	{
		$test ? $img = 'checked.png' 	: $img = 'unchecked.png';
	} else {
		$test ? $img = 'status_yes.gif' : $img = 'status_no.gif';
	}

	return '<img src="'.WEBSITE_PATH.'/admin/images/'.$img.'" alt="checked" border="0" />';
}



function admin_textPreview( $text, $max_length = 50, $tag = '', $class = '' )
{
	$preview = mb_substr($text, 0, $max_length);
	($preview == $text) or $preview .= '...';

	$attr = '';
	$tag != 'a'			or $attr .= " href=\"$text\"";
	$preview == $text	or $attr .= " title=\"$text\" style=\"cursor:help;\"";
	!$class				or $attr .= " class=\"$class\"";

	if ($tag) {
		return "<$tag{$attr}>$preview</$tag>";
	} else {
		return $preview;
	}
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////



class simpleStats
{
	private	$title			= '';

	private	$statistics		= array();
	private	$unit			= '';

	private	$value_total;
	private	$percent_max;				# Usefull for zoom option

	private	$order = array();			# Order of the stats

	private	$bar_width		= '100',	# match the width of : /admin/templates/default/images/stats-bar-V-bg.jpg
			$bar_height		= '100';	# match the width of : /admin/templates/default/images/stats-bar-H-bg.jpg



	public function __construct( $stats, $unit = '', $title = '' )
	{
		// Check $stats structure and get $value_total property
		$stats_validation = true;
		$value_total = 0;
		reset($stats);
		foreach($stats as $name => $value)
		{
			if (!preg_match('~^([0-9]+)(\.([0-9]+))?$~', $value))
			{
				$stats_validation = false;
				echo '<p style="color:red;">Error occured in <b>'.__CLASS__.'</b> class : invalid <i>$stats</i> parameter in '.__METHOD__.'.</p>';
			}
			$value_total += $value;
		}

		// Get $statistics property
		if ($stats_validation)
		{
			$this->value_total = $value_total;

			$percent_max = 0;
			$statistics = array();
			$i = 0;
			reset($stats);
			foreach($stats as $name => $value)
			{
				$statistics[$i]['name' ] = $name;
				$statistics[$i]['value'] = $value;

				if ($this->value_total != 0)
				{
					$statistics[$i]['percent'] = sprintf('%.1f', $value/$this->value_total*100);
				} else {
					$statistics[$i]['percent'] = 0;
				}

				if ($statistics[$i]['percent'] > $percent_max)
				{
					$percent_max = $statistics[$i]['percent'];
				}

				$i++;
			}
			$this->statistics = $statistics;
			$this->percent_max = $percent_max;
		}

		// Set others properties
		$this->unit = $unit;
		$this->title = $title;
	}



	public function orderBy( $param )
	{
		if (($param != 'name') && ($param != 'value') && ($param != 'percent'))
		{
			echo '<p style="color:red;">Error occured in <b>'.__CLASS__.'</b> class : invalid <i>$$param</i> parameter in orderBy() method</p>';
			return false;
		}

		$asort = array();
		for ($i=0; $i<count($this->statistics); $i++) $asort[$i] = $this->statistics[$i][$param];
		asort($asort);

		$order = array();
		reset($asort);
		foreach($asort as $key => $value) {
			$order[] = $key;
		}

		$this->order = $order;
	}



	public function htmlDiagramHorizontal( $zoom = false )
	{
		if (($zoom) && ($this->percent_max != 0)) $base = $this->percent_max; else $base = 100;

		$html  = "\n".'<table class="simpleStats" cellspacing="0">'."\n";
		$html .= '<thead><tr><th colspan="2">'.$this->title.'</th><td>'.$this->value_total.' '.$this->unit."</td></tr></thead>\n<tbody>";
		for ($i=0; $i<count($this->statistics); $i++)
		{
			if (count($this->order) == count($this->statistics))
			{
				$current = $this->order[$i];
			} else {
				$current = $i;
			}

			$html .= "<tr>\n";
			$html .= "\t<th>".$this->htmlBarHorizontal($this->statistics[$current]['percent'], $base)."</th>\n";
			$html .= "\t<td>".$this->statistics[$current]['name']."</td>\n";
			$html .= "\t<td class=\"value\">".$this->statistics[$current]['value'].' '.$this->unit."</td>\n";
			$html .= "</tr>\n";
		}
		$html .= "</tbody></table>\n\n";

		return $html;
	}



	####### TODO - EN DEV -
	public function htmlDiagramVertical( $zoom = false )
	{
		if (($zoom) && ($this->percent_max != 0)) $base = $this->percent_max; else $base = 100;

		$html = "<h3>Value total= $this->value_total / Unit= $this->unit</h3>";

		for ($i=0; $i<count($this->statistics); $i++)
		{
			if (count($this->order) == count($this->statistics))
			{
				$current = $this->order[$i];
			} else {
				$current = $i;
			}

			echo $this->htmlBarVertical($this->statistics[$current]['percent'], $base);

#			$html .= "<p>Percent= {$this->statistics[$current]['percent']} / Name= {$this->statistics[$current]['name']} / Value={$this->statistics[$current]['value']} $this->unit</p>";
		}

		return $html;
	}
	####### TODO - EN DEV -



	private function htmlBarHorizontal( $percent, $base )
	{
		$html  = '<div class="simpleStats-horizontal" style="width:'.$this->bar_width.'px;">';
		$html .= '<div class="simpleStats-horizontal-percent">'.$percent."%</div>\n";
		$html .= '<div class="simpleStats-horizontal-bar" style="width:'.round($percent/$base*$this->bar_width).'px;">'."&nbsp;</div>\n";
		$html .= '</div>';

		return $html;
	}



	private function htmlBarVertical( $percent, $base )
	{
		$html  = '<div class="simpleStats-vertical" style="height:'.$this->bar_height.'px;">';
		$html .= '<div class="simpleStats-vertical-percent">'.$percent."%</div>\n";
		$html .= '<div class="simpleStats-vertical-bar" style="height:'.round($percent/$base*$this->bar_height).'px;">'."&nbsp;</div>\n";
		$html .= '</div>';

		return $html;
	}



	public function htmlSimpleForDebug()
	{
		$html = "<h3>Value total= $this->value_total / Unit= $this->unit</h3>";

		for ($i=0; $i<count($this->statistics); $i++)
		{
			if (count($this->order) == count($this->statistics))
			{
				$current = $this->order[$i];
			} else {
				$current = $i;
			}

			$html .= "<p>Percent= {$this->statistics[$current]['percent']} / Name= {$this->statistics[$current]['name']} / Value={$this->statistics[$current]['value']} $this->unit</p>";
		}

		return $html;
	}


}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

class simpleCSV
{
	private	$separator_field 			= "\t",
			$separator_line 			= "\n";

	const	SEP_REP						= ' '; 		# Replacment of the separators

	const	BREAK_LINES_NUM 			= 2;

	private	$add_space_to_header_fields; 			# (boolean) Add space to each field of the header

	private	$header 					= '',
			$content					= '';

	private	$summary 					= array( 'before' => '', 'after' => ''); # Put summary before or after the csv lines

	private	$error 						= false;



	public function __construct( $separator_field = "", $separator_line = "", $add_space_to_header_fields = false )
	{
		$separator_field 	? $this->separator_field 	= $separator_field 	: '';
		$separator_line 	? $this->separator_line 	= $separator_line 	: '';

		$add_space_to_header_fields ? $this->add_space_to_header_fields = true : $this->add_space_to_header_fields = false;
	}



	public function set( $content, $header = array() )
	{
		// Content
		reset($content);
		foreach($content as $num => $array)
		{
			if (isset($content_cols) && $content_cols != count($array)) {
				trigger_error('Invalid structure of $content parameter in '.__METHOD__);
				$this->error = true;
			}
			$content_cols = count($array);

			$count = 0;
			foreach($array as $k => $v)
			{
				$this->content .= $this->clean($v);
				(++$count == $content_cols) or $this->content .= $this->separator_field;
			}
			$this->content .= $this->separator_line;
		}

		// Header
		if ($header_cols = count($header))
		{
			$this->add_space_to_header_fields ? $space = ' ' : $space = '';

			$count = 0;
			reset($header);
			foreach($header as $k => $v)
			{
				$this->header .= $space.$this->clean($v).$space;
				(++$count == $header_cols) or $this->header .= $this->separator_field;
			}
			$this->header .= $this->separator_line;

			if ($header_cols != $content_cols) {
				trigger_error('Error occured in '.__METHOD__.' : the structures of $content and $header parameters are not compatible.');
				$this->error = true;
			}
		}
	}



	public function addSummaryBefore( $summary, $break_lines_num = NULL )
	{
		$this->summary['before'] .= $this->clean($summary).$this->breakLines($break_lines_num);
	}



	public function addSummaryAfter( $summary, $break_lines_num = NULL )
	{
		$this->summary['after'] .= $this->clean($summary).$this->breakLines($break_lines_num);
	}



	private function breakLines( $num = NULL )
	{
		$break_lines = '';

		!isset($num) ? $num = self::BREAK_LINES_NUM : ''; # Get default ?

		if ($num != 0) {
			// Add line(s)
			for ($i=0; $i<$num; $i++) {
				$break_lines .= $this->separator_line;
			}
		}
		else {
			// Add space
			$break_lines = ' ';
		}

		return $break_lines;
	}



	private function clean( $value )
	{
		return preg_replace("~($this->separator_field|$this->separator_line)~", self::SEP_REP, $value);
	}



	public function get()
	{
		if ($this->error) {
			trigger_error('Configuration error occured ! We are unable to display the CSV file.');
			return false;
		}

		$output = '';

		$break_less = $this->separator_line.$this->separator_line;	# 2 breaks
		$break_more = $break_less .$this->separator_line;			# 3 breaks

		if (isset($this->summary['before'])) {
			$output .= preg_replace("~($this->separator_line)+$~", '', $this->summary['before']) .$break_more;
		}

		$this->header 	? $output .= $this->header.$this->separator_line : '';
		$this->content 	? $output .= $this->content : '';

		if (isset($this->summary['after'])) {
			$output .= $break_less. preg_replace("~($this->separator_line)+$~", '', $this->summary['after']) .$this->separator_line;
		}

		return $output;
	}



	public function textareaBox( $label = 'CSV', $cols = '70', $rows = '10', $form_id = 'csv_', $textarea_name = 'content' )
	{
		$form = new formManager(0,0);

		$output =
			$form->form('post', '#', $form_id).
			$form->textarea($textarea_name, $this->get(), $label ? $label.'<br />' : '', '', "cols=$cols;rows=$rows;readonly").
			$form->end();

		return $output;
	}

}


?>