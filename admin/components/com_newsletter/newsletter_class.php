<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////
// class

class admin_comNewsletter_tmpl extends comNewsletter_tmpl
{

	static public function keywordsTips( $key, $html )
	{
		$keywords = parent::keywords();

		if (!array_key_exists($key, $keywords)) {
			trigger_error("Invalid parameter \$key=$key (expected : ".implode(',', array_keys($keywords)).")");
			return false;
		}

		// Keep only the current keywords
		$keywords = $keywords[$key];

		if (count($keywords))
		{
			for ($i=0; $i<count($keywords); $i++)
			{
				// Magnify the keyword
				$keywords[$i] = "{{$keywords[$i]}}";

				if (mb_strstr($html, $keywords[$i])) {
					$keywords[$i] = '<span class="green">'.$keywords[$i].'</span>';
				}
			}
			$list = '<span class="grey" style="font-family:Monospace;">'.implode(', ', $keywords).'</span>';
		} else {
			$list = '<span class="grey" style="font-style:italic;">'.LANG_ADMIN_COM_NEWSLETTER_TMPL_KEYWORDS_NONE.'</span>';
		}

		return '<p>'.LANG_ADMIN_COM_NEWSLETTER_TMPL_KEYWORDS.$list.'</p>';
	}



	static public function options()
	{
		global $db;
		$tmpl = $db->select('newsletter_tmpl, [id(desc)],name');

		foreach ($tmpl as $id => $name) {
			$tmpl[$id] = $name['name'];
		}

		return $tmpl;
	}



	static public function itemOptions()
	{
		return
			array(
				'1' => LANG_ADMIN_COM_NEWSLETTER_TMPL_ITEM1,
				'2' => LANG_ADMIN_COM_NEWSLETTER_TMPL_ITEM2
			);
	}



	/*
	 * This feature is designed for ckEditor :
	 * - Make the template css available on a temporary FTP
	 * - Get also the template header and footer inside the <body></body> tags (without the css part)
	 */
	static public function parts( $tmpl_id, &$tmpl_css, &$body_header, &$body_footer )
	{
		// Init
		$tmpl_css = false; # Path of the temporary css file
		$body_header = ''; # HTML from the <body> tag
		$body_footer = ''; # HTML until the </body> tag

		// Config of the css path
		$temp_path	= WEBSITE_PATH.'/admin/components/com_newsletter';
		$temp_dir	= '/temp';

		// Get the template
		global $db;
		$tmpl = $db->selectOne("newsletter_tmpl, header,footer, where: id=$tmpl_id");

		// Extract css
		$tmpl['header'] = preg_replace('~\<style((\s)type\="text/css")?\>|\</style\>~i', '[[[STYLE]]]', $tmpl['header']);
		$tmpl['header'] = explode('[[[STYLE]]]', $tmpl['header']);

		if (count($tmpl['header']) == 3)
		{
			// Here it is !
			$css = $tmpl['header'][1];

			// Keywords replacements (the css can also contains keywords like {site_url} for the images locations for example)
			$css = parent::generalsReplacements($css);

			// Make the '/temp' directory available
			$ftp = new ftpManager($_SERVER['DOCUMENT_ROOT'].$temp_path);
			$ftp->isDir($temp_dir) or $ftp->mkdir($temp_dir);

			// Write css content
			$ftp->write("$temp_dir/tmpl.css", $css);

			// Inform the path of the available css file
			$tmpl_css = $temp_path."$temp_dir/tmpl.css";

			// Build the header without the css part
			$body_header = $tmpl['header'][0].$tmpl['header'][2];
		}
		elseif (count($tmpl['header']) == 1) {
			$body_header = $tmpl['header'][0];
		}
		else {
			return false; # The template is not XHTML validated !
		}

		// Keep the header from the <body> tag
		$body_header = preg_replace('~\<body\>~i', '[[[BODY]]]', $body_header);
		$body_header = explode('[[[BODY]]]', $body_header);
		count($body_header) == 2 ? $body_header = $body_header[1] : $body_header = $body_header[0];

		// Keep the footer until the </body> tag
		$body_footer = preg_replace('~\</body\>~i', '[[[BODY]]]', $tmpl['footer']);
		$body_footer = explode('[[[BODY]]]', $body_footer);
		$body_footer = $body_footer[0];

		// Keywords replacements
		$body_header = parent::generalsReplacements($body_header);
		$body_footer = parent::generalsReplacements($body_footer);

		return true;
	}

}

?>