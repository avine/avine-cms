<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Get path to index.php and template_css.css of the template
function comTemplate_()
{
	global $g_page;
	$g_page['template_default_dir'	] = false;	# Default template
	$g_page['template_dir'			] = false;	# Current template for this page

	global $db;
	$template = $db->select('template, *');

	// Search...
	for ($i=0; $i<count($template); $i++)
	{
		// Default template
		if ((!$g_page['template_default_dir']) && ($template[$i]['current'] == 1))
		{
			$g_page['template_default_dir'] = '/templates/'.$template[$i]['name'];
		}
		// Special current template
		if (($g_page['link_template_id']) && (!$g_page['template_dir']) && ($template[$i]['id'] == $g_page['link_template_id']))
		{
			$g_page['template_dir'] = '/templates/'.$template[$i]['name'];
		}
	}

	// No default template! Search anyone!
	if ((!$g_page['template_default_dir']) && (count($template)>0))
	{
		$g_page['template_default_dir'] = '/templates/'.$template[0]['name'];
		echo LANG_TMPL_DEFAULT_NOT_DEFINED;
	}

	// What we get ?
	if (!$g_page['template_default_dir']) {
		die(LANG_TMPL_NO_TEMPLATE_FOUND); 							# No template was found!
	}
	elseif (!$g_page['template_dir']) {
		$g_page['template_dir'] = $g_page['template_default_dir']; 	# Current template 
	}

	// Fatal error !
	if (!is_dir(sitePath().$g_page['template_dir'])) {
		die(str_replace('{template_dir}', $g_page['template_dir'], LANG_TMPL_TEMPLATE_HAS_GONE));
	}
}



// Get the name of the directory of the template (example: '/templates/default' will return 'default')
function comTemplate_getCurrent() { global $g_page; return str_replace('/templates/', '', $g_page['template_dir']); 		}
function comTemplate_getDefault() { global $g_page; return str_replace('/templates/', '', $g_page['template_default_dir']); }



function comTemplate_headerAddon( $rss = true )
{
	$html = '';

	global $g_page;

	// Title
	if ($g_page['config_site_name'] && $g_page['title'])
	{
		$title_separator = ' - ';
	} else {
		$title_separator = '';
	}
	$html .= '<title>'.$g_page['title'].$title_separator.$g_page['config_site_name'].'</title>'."\n\n";

	// Metas
	isset($g_page['config_meta_desc'	]) ?  $html .= $g_page['config_meta_desc'		] : '';
	isset($g_page['config_meta_keywords']) ?  $html .= $g_page['config_meta_keywords'	] : '';
	isset($g_page['config_meta_author'	]) ?  $html .= $g_page['config_meta_author'		] : '';

	// Generator
	$html .= '<meta name="Generator" content="Avine.fr (c) Tous droits réservés" />'."\n";

	// Before the first 'href' or 'src' reference, set the base path
	$html .= '<base href="'.siteUrl().'/" />'."\n";

	// Rss feed
	if ($rss) {
		$html .= "\n".'<link rel="alternate" type="application/rss+xml" href="'.WEBSITE_PATH.'/rss.php" />'."\n";
	}

	echo $html;
}


/**
 * @param array (or string) $alt_base
 */
function comTemplate_loadDefaultResource( $alt_base = array() )
{
	$html  = "<!-- css : default -->\n";
	$html .= '<link rel="stylesheet" type="text/css" href="'.WEBSITE_PATH.'/global/global_css.css" />'."\n";

	$loader = new loaderManager();
	!$alt_base or $loader->altBase($alt_base);

	$html .= $loader->_css('libraries');
	$html .= $loader->_css('components');

	$html .= $loader->_js('libraries');
	$html .= $loader->_js('components');

	echo $html;
}



function comTemplate_loadTemplateResource( $css_addons = array() )
{
	$css = array_merge( array('index'), $css_addons );

	global $g_page;
	$html = "<!-- css : template -->\n";
	for ($i=0; $i<count($css); $i++)
	{
		$href = WEBSITE_PATH.$g_page['template_dir']."/{$css[$i]}.css";

		$html .= '<link rel="stylesheet" type="text/css" href="'.$href.'" />'."\n";
	}
	echo $html."\n";
}



function comTemplate_siteOfflineFlag()
{
	global $g_page;

	$admin_is_logged = new comUser_login(1,1,0); # The setting: 'prefix=s_admin_' come from '/admin/index.php'

	// Site offline but still visible for logged administrator
	if ((!$g_page['config_online']) && ($admin_is_logged->userID()))
	{
		echo '<div id="comTemplate-site-offline-flag">SITE OFFLINE</div>';
	}
}



// Get the default 'index.css' file path
function comTemplate_defaultIndexCss( $relative_path = true )
{
	global $db;

	if ($name = $db->selectOne('template, name, where: current=1', 'name'))
	{
		$index_css = WEBSITE_PATH."/templates/$name/index.css";

		if (is_file($_SERVER['DOCUMENT_ROOT'].$index_css))
		{
			if ($relative_path)
			{
				return $index_css;
			} else {
				return $_SERVER['DOCUMENT_ROOT'].$index_css;
			}
		}
	}

	return false;
}



class comTemplate_loader
{
	protected	$relative_base; # Relative base to the website root

	public		$media	= 'screen';



	public function __construct()
	{
		
	}



	public function setBase( $relative_base )
	{
		$this->relative_base = preg_replace('~/$~', '', $relative_base);	# Remove the potential slash at the end

		return $this;
	}



	protected function getFile( $file )
	{
		$file = preg_replace('~^/~', '', $file);							# Remove the potential slash at the begining

		$pathfile = WEBSITE_PATH.$this->relative_base .'/'. $file;			# Get full path from the website root

		if (!is_file($_SERVER['DOCUMENT_ROOT'].$pathfile)) {
			trigger_error("Invalid pathfile : $pathfile in ".__METHOD__);
			return false;
		}

		return $pathfile;
	}



	protected function css( $file )
	{
		return '<link rel="stylesheet" type="text/css" href="'.$this->getFile($file).'" media="'.$this->media.'" />'."\n";
	}



	protected function js( $file )
	{
		return '<script type="text/javascript" src="'.$this->getFile($file).'"></script>'."\n";
	}



	public function resources( $file_list )
	{
		$resources = '';

		for ($i=0; $i<count($file_list); $i++)
		{
			$pathinfo = pathinfo($file_list[$i]);

			switch($ext = $pathinfo['extension'])
			{
				case 'css':
				case 'js':
					$resources .= $this->$ext($file_list[$i]);
					break;

				default:
					trigger_error("Invalid file extension : {$file_list[$i]} in ".__METHOD__);
					break;
			}
		}

		echo "\n<!-- comTemplate_loader::resources() -->\n$resources\n";
	}

}


?>