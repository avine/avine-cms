<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


# TODO - Intégrer la propriété $com_content_archived dans un menu déroulant ....


/*
 * Note : this class works with exact match
 */
class comSearch_
{
	protected	$string;						# Requested search

	protected	$com_content_archived = false;	# Search only into archived elements of com_content component (see '/components/com_generic/generic_class.php' for more details)

	protected	$results_info;

	protected	$debug;

	// Configuration of the content preview
	const		PREVIEW_TAIL		= 60;
	const		PREVIEW_SEP			= ' .... ';
	const		PREVIEW_MAX_MATCH	= 5;

	const		RESULTS_PER_STEP	= 10;		# For pagination



	public function __construct( $debug = false )
	{
		$this->debug = $debug;

		global $db;
		global $g_user_login;

		/*
		 * Create search table
		 */

		$db->sendMysqlQuery(
			"CREATE TEMPORARY TABLE IF NOT EXISTS {table_prefix}search_temp
			(
				id				INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				title			TINYTEXT,
				content			MEDIUMTEXT,
				url				TEXT

			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' "
		);

		/*
		 * Index FTP contents
		 */

		if (!$this->com_content_archived)
		{
			$debug_excluded_content = '';

			$ftp = new ftpManager(sitePath().'/contents');

			$file = $db->select('file, path,title, where: published=1 AND, where: access_level >= '.$g_user_login->accessLevel());
			for ($i=0; $i<count($file); $i++)
			{
				if ($ftp->isFile($file[$i]['path']))
				{
					$content = NULL;

					$url = 'file='.preg_replace('~^(/)~', '', $file[$i]['path']);

					if (!($title = $file[$i]['title'])) {
						// Try to find a title from potential menu_link name
						$title = $db->selectOne('menu_link, name, where: href='.$db->str_encode($url), 'name');
					}

					$f = $ftp->read($file[$i]['path']);
	
					if (preg_match('~\.htm(l)?$~', $file[$i]['path']))
					{
						$content = self::fulltext($f);
					}
					elseif (preg_match('~\.php$~', $file[$i]['path']))
					{
						if (!preg_match('~(exit|die)(;|\()~', $f)) # Do not include files wich terminate the script !
						{
							if ($this->debug) {
								echo '<b>Require php file :</b> '.$file[$i]['path'].'<br />'; # If some php file terminates the script, find here the last good one !
							}
							$content = $this->loadPhpFile(sitePath().'/contents'.$file[$i]['path']);
						}
						else
						{
							$debug_excluded_content .= WEBSITE_PATH.'/contents'.$file[$i]['path'].'<br />';
						}
					}

					// Add to index
					if (isset($content)) {
						$db->insert('search_temp; NULL, '.$db->str_encode($title).', '.$db->str_encode($content).', '.$db->str_encode($url));
					}
				}
			}

			if ($debug_excluded_content)
			{
				$this->debugHtml('Files wich terminate the script are not indexed (contains <i>exit;</i> or <i>die;</i> instruction)', $debug_excluded_content);
			}
		}

		/*
		 * Index DB contents
		 */

		$com_content = comContent_frontendScope();

		if ($this->com_content_archived) {
			$com_content->setLiveArchiveStatus('node'	, 2);
			$com_content->setLiveArchiveStatus('element', 1); # Only archived elements
		} else {
			$com_content->setLiveArchiveStatus('node'	, 0);
			$com_content->setLiveArchiveStatus('element', 0);
		}

		$nodes = $com_content->getNodes( 0,false, true,$g_user_login->accessLevel() );
		for ($i=0; $i<count($nodes); $i++)
		{
			$elements = $com_content->getElements($nodes[$i]['id']);
			for ($j=0; $j<count($elements); $j++)
			{
				$item = false;
				if ($this->com_content_archived)
				{
					$item =	$db->selectOne(
								$com_content->getTablePrefix().'element_item, title,text_intro,text_main, where: element_id='.$elements[$j]['id'].' AND, join: element_id>;'.
								$com_content->getTablePrefix().'element, where: archived=1 AND, where: access_level >= '.$g_user_login->accessLevel().', join: <id'
							);
				}
				elseif ($com_content->isVisibleElement($elements[$j]['id']))
				{
					$item = $db->selectOne(
								$com_content->getTablePrefix().'element_item, title,text_intro,text_main, where: element_id='.$elements[$j]['id']
							);
				}

				if ($item)
				{
					$url		= $com_content->elementUrlEncoder($elements[$j]['id']);
					$url		= "com={$com_content->getComName()}&amp;page=index&amp;".$url['href'];

					$title		= $item['title'];
					$content	= self::fulltext($item['text_intro'].$item['text_main']);

					// Add to index
					$db->insert('search_temp; NULL, '.$db->str_encode($title).', '.$db->str_encode($content).', '.$db->str_encode($url));
				}
			}
		}

		/*
		 * Add fulltext index
		 */

		$db->sendMysqlQuery("ALTER TABLE {table_prefix}search_temp ADD FULLTEXT (title,content,url)");

		// Debug the search index
		if ($this->debug)
		{
			$search_temp = $db->select('search_temp, *');

			$s = array();
			$form = new formManager();
			for ($i=0; $i<count($search_temp); $i++)
			{
				$s[$i]['id'] = $search_temp[$i]['id'];

				$s[$i]['title_content_url'] = 
					$form->text('search_t', $search_temp[$i]['title'])." <span style=\"color:blue;\">{$search_temp[$i]['url']}</span><br />".
					$form->textarea('search_c', $search_temp[$i]['content'], '', '', 'cols=80');
			}
			$table = new tableManager($s, array('ID', 'TITLE - URL / CONTENT'));

			$this->debugHtml("FULLTEXT 'search_temp' table", $table->html());
		}
	}



	private function loadPhpFile( $pathfile )
	{
		ob_start();
		require($pathfile); # TODO - I'am not sure, but it seems that a fatal error can occur if the file contains further require()...
		return self::fulltext(ob_get_clean());
	}



	public function search( $string )
	{
		$search_time = microtime();

		$this->results_info = array('success'=>false, 'matches_count'=>0);

		// Set the $string property
		if (!$this->setString($string)) {
			return;
		}

		global $db;
		global $g_page;

		$html = '';

		// Remember the "last" search
		$session = new sessionManager(sessionManager::FRONTEND, 'com_search');
		$last_string = $session->get('string');

		// New search !
		if ($this->string != $last_string)
		{
			$this->debugHtml('Search info', 'This is a new search ! The matches are comming from the database fulltext query.');

			/*
			 * Note : if MySQL 5.1.7 is installed, you can use "IN NATURAL LANGUAGE MODE" (instead of "IN BOOLEAN MODE").
			 * In that mode the rows returned are automatically sorted with the highest relevance first.
			 */
			$result = $db->sendMysqlQuery('SELECT * FROM {table_prefix}search_temp WHERE MATCH (title,content,url) AGAINST ('.$db->str_encode($this->string).' IN BOOLEAN MODE)');
			$matches = $db->fetchMysqlResults($result);

			$session->set('matches', $matches); # Remember matches
		}
		// The good old search !
		else
		{
			$this->debugHtml('Search info', 'Same search as before ! The matches are comming from the session.');

			$matches = $session->get('matches'); # Remember matches
		}

		// For the next time, remember the "last" search
		$session->set('string', $this->string);

		if ($matches)
		{
			$this->results_info = array('success'=>true, 'matches_count'=>count($matches));

			// Sort matches
			$this->addRevelance($matches);
			usort($matches, 'comSearch_::sortbyRevelance'); # See the note before

			// Get results interval
			self::paginationInfos($step, count($matches));

			for ($i=$step['start']; $i<$step['stop']; $i++)
			{
				// Title
				$this->highlightMatchedWords($matches[$i]['title']);

				// Content
				$this->extractContentPreview($matches[$i]['content']);
				$this->highlightMatchedWords($matches[$i]['content']);

				// Url
				$href = comMenu_rewrite($matches[$i]['url']);
				$href_highlight = $href;
				$this->highlightMatchedWords($href_highlight);

				$html .=
					"\n<h3 class=\"comSearch_result_title\">".
					($this->debug ? "<span class=\"comSearch_result_revelance\">[{$matches[$i]['revelance']}%]</span> " : '').

					// Default behaviour : add site name only if no title available
					"<a href=\"$href\">".($matches[$i]['title'] ? $matches[$i]['title'] : $g_page['config_site_name'])."</a>".
					// Alternative behaviour : always add the site name
					#"<a href=\"$href\">".($matches[$i]['title'] ? $matches[$i]['title'].' - ' : '').$g_page['config_site_name']."</a>".

					"</h3>\n";

				$html .=
					"<p class=\"comSearch_result_content\">".
					($matches[$i]['content'] ? $matches[$i]['content']."<br />\n" : '').
					"<span class=\"comSearch_result_url\">$href_highlight</span>".
					"</p>\n";
			}
		}
		else {
			$html .= '<p class="comSearch_no_result">'.LANG_COM_SEARCH_INDEX_NO_RESULT.'</p>';
		}

		$this->results_info['search_time'] = sprintf('%5.3f', microtime() - $search_time);

		return $html;
	}



	public function success()
	{
		return $this->results_info['success'];
	}



	public function matchesCount()
	{
		return $this->results_info['matches_count'];
	}



	public function searchTime()
	{
		return $this->results_info['search_time'];
	}



	public function stats()
	{
		if ($this->success())
		{
			$stats =
				sprintf(
					LANG_COM_SEARCH_RESULTS_STATS,
					$this->results_info['matches_count'],
					$this->results_info['search_time']
				);

			return '<div class="comSearch_stats"><div>'.$stats.'</div></div>';
		}
		return '';
	}



	protected function setString( $string )
	{
		$string = self::fulltext($string); # Clean $string

		// Remove "IN BOOLEAN MODE" operators
		$bool_mod_op = array('+', '-', '>', '<', '(', ')', '~', '*', '"');
		for ($i=0; $i<count($bool_mod_op); $i++) {
			$bool_mod_op[$i] = preg_quote($bool_mod_op[$i]);
		}
		$string = preg_replace('/'.implode('|', $bool_mod_op).'/', '', $string);

		// Remove common words
		$string = $this->removeCommonWords($string);

		$string = self::fulltext($string); # Clean $string again

		$this->debugHtml('Formated string of search', $string);

		// Set $string property
		if ($this->string = $string)
		{
			return true;
		} else {
			return false;
		}
	}



	protected function removeCommonWords( $string )
	{
		// Get common words list
		$ftp = new ftpManager(sitePath().'/components/com_search/');

		// 'common_words.txt' is a simple text-file with one common word per line
		$common_word = explode('#', preg_replace('~(\n|\r)+~', '#', preg_replace('~#~', '', $ftp->read('common_words.txt'))));

		$this->debugHtml('List of common words', implode(', ', $common_word));

		$string = " $string "; # Required for the next preg_match()

		// Let's go !
		for ($i=0; $i<count($common_word); $i++) {
			$string = preg_replace('~\s'.pregQuote(trim($common_word[$i])).'\s~i', ' ', $string);
		}

		return trim($string);
	}



	protected function addRevelance( &$matches )
	{
		$word = explode(' ', $this->string);

		for ($i=0; $i<count($matches); $i++)
		{
			$title		= $matches[$i]['title'	];
			$content	= $matches[$i]['content'];
			$url		= $matches[$i]['url'	];

			$revelance = 0;
			$precision = 0;
			for ($j=0; $j<count($word); $j++)
			{
				$t = preg_match_all('~'.pregQuote($word[$j]).'~i', $title	, $m);
				$c = preg_match_all('~'.pregQuote($word[$j]).'~i', $content	, $m);
				$u = preg_match_all('~'.pregQuote($word[$j]).'~i', $url		, $m);

				if ($t || $c || $u) {
					$revelance++;
				}
				$precision += ($t ? $t-1 : 0) + ($c ? $c-1 : 0) + ($u ? $u-1 : 0);
			}
			$matches[$i]['revelance'] = sprintf('%05.2f', 100*$revelance/count($word) + $precision);
		}
	}



	static protected function sortbyRevelance( $match_1, $match_2 )
	{
	    if ($match_1['revelance'] == $match_2['revelance']) {
	        return 0;
	    }
	    if ($match_1['revelance'] < $match_2['revelance']) {
	    	return 1;
	    }
	    return -1;
	}



	protected function extractContentPreview( &$content )
	{
		// Alias
		$match_tail	= self::PREVIEW_TAIL;
		$match_sep	= self::PREVIEW_SEP;

		// Match all the words of the search
		$string = explode(' ', $this->string);
		for ($i=0; $i<count($string); $i++) {
			$string[$i] = pregQuote($string[$i]); # Prepare match pattern
		}
		preg_match_all('~'.implode('|', $string).'~i', $content, $matches, PREG_OFFSET_CAPTURE);
		$matches = $matches[0];

		// Limit preview size
		count($matches) <= self::PREVIEW_MAX_MATCH ? $max_match = count($matches) : $max_match = self::PREVIEW_MAX_MATCH;

		// Get intervals of the preview
		$interval = array();
		for ($i=0; $i<$max_match; $i++)
		{
			// Word lengh and offset
			$strlen = mb_strlen($matches[$i][0]);
			$offset = $matches[$i][1];

			// Preview part
			$start	= $offset -$match_tail;
			$stop	= $offset +$match_tail + $strlen;

			// Double tail size ?
			($start > 0) or $stop += $match_tail;
			($stop < mb_strlen($content)) or $start -= $match_tail;

			// Check boundaries
			($start >= 0) or $start = 0;
			($stop <= mb_strlen($content)) or $stop = mb_strlen($content);

			// Remember the previous stop to prevent interval overflow
			count($interval) ? $previous_stop = $interval[count($interval)-1]['stop'] : $previous_stop = -1;

			if ($start > $previous_stop)
			{
				$interval[] = array('start'=>$start, 'stop'=>$stop);	# New interval
			} else {
				$interval[count($interval)-1]['stop'] = $stop;			# Increase previous interval
			}
		}

		// Default interval
		if (!count($interval)) {
			$interval[] =
				array(
					'start'	=> 0,
					'stop'	=> (2*$match_tail <= mb_strlen($content) ? 2*$match_tail : mb_strlen($content))
				);
		}

		// So, let's go for the preview !
		$interval[0]['start'] == 0 ? $preview = '' : $preview = $match_sep;
		for ($i=0; $i<count($interval); $i++)
		{
			$sub_preview = mb_substr($content, $interval[$i]['start'], $interval[$i]['stop'] - $interval[$i]['start']);

			$preview .= $this->cropPreview($sub_preview);

			$interval[$i]['stop'] == mb_strlen($content) or $preview .= $match_sep;
		}
		$content = $preview;
	}



	// Remove the first and the last "word" (which may be truncated) of each preview part
	protected function cropPreview( $sub_preview )
	{
		$not_char = array('"', "'", '(', ')', '{', '}', '[', ']', '%', '<', '>', ',', ';', '.', ':', '?', '!');
		$not_char = preg_quote(implode('', $not_char));

		// At the begining...
		if (preg_match('~^[^\s'.$not_char.']+~', $sub_preview, $match) && !preg_match('~'.pregQuote($match[0]).'~i', $this->string))
		{
			$sub_preview = preg_replace('~^(\S)+\s~', '', $sub_preview);
		}

		// At the end...
		if (preg_match('~[^\s'.$not_char.']+$~', $sub_preview, $match) && !preg_match('~'.pregQuote($match[0]).'~i', $this->string))
		{
			$sub_preview = preg_replace('~\s(\S)+$~', '', $sub_preview);
		}

		return $sub_preview;
	}



	/*
	 * This function seems to be complicated !
	 * But it conserve the exact original match syntax !
	 */
	protected function highlightMatchedWords( &$content )
	{
		// Open and close <span> tag
		$span['code'] = array( '[[:SPAN:]]', '[[:/SPAN:]]' );
		$span['tag' ] = array( '<span class="comSearch_highlight">', '</span>' );

		$content_copy = $content;

		$string = explode(' ', $this->string);

		for ($i=0; $i<count($string); $i++)
		{
			preg_match_all('~'.pregQuote($string[$i]).'~i', $content_copy, $matches, PREG_OFFSET_CAPTURE);
			$matches = $matches[0];

			for ($j=0; $j<count($matches); $j++)
			{
				$match = mb_substr($content_copy, $matches[$j][1], mb_strlen($matches[$j][0]));

				$content = preg_replace('~'.pregQuote($match).'~', $span['code'][0].self::protectMatch($match).$span['code'][1], $content, 1);
			}
		}
		$content = self::restoreMatch($content);

		// Protect web content
		$content = htmlentities($content, ENT_COMPAT, 'UTF-8');

		// Get the real <span> tags
		$content = str_replace($span['code'][0], $span['tag'][0], $content);
		$content = str_replace($span['code'][1], $span['tag'][1], $content);
	}



	static protected function protectMatch( $match )
	{
		$protected = '';
		for ($i=0; $i<mb_strlen($match); $i++) {
			$protected .= mb_substr($match, $i, 1).'_~_';
		}
		return $protected;
	}



	static protected function restoreMatch( $content )
	{
		return str_replace('_~_', '', $content);
	}



	// Get a fulltext from a web content
	static public function fulltext( $content )
	{
		$content = self::removeStylesAndScripts($content);

		$content = strip_tags($content);

		$content = preg_replace('~(\s|\t|\n|\r)+~', ' ', $content);

		$content = trim($content);

		$content = self::htmlEntitiesToChar($content);

		return $content;
	}



	static protected function removeStylesAndScripts( $content )
	{
		/*
		 * Basic version
		 * This code is simple, but some times it's buggy and i don't know why...
		 */
		#$content = preg_replace('~<style.*>(.|\n|\r)*</style>~', '', $content);
		#$content = preg_replace('~<script.*>(.|\n|\r)*</script>~', '', $content);

		/*
		 * Alternative version, more tricky, but works fine...
		 */
		$content = preg_replace('~(\s|\t|\n|\r)+~', ' ', $content); # Do this first...
		$content = preg_replace('~<style.*>.*</style>~', '', $content);
		$content = preg_replace('~<script.*>.*</script>~', '', $content);

		return $content;
	}



	static function htmlEntitiesToChar( $content )
	{
		$entities = self::entitiesList();

		foreach($entities as $char => $ent) {
			$content = preg_replace('~'.pregQuote($ent).'~', $char, $content);
		}
		return $content;
	}



	static function charToHtmlEntities( $content )
	{
		$entities = self::entitiesList();

		foreach($entities as $char => $ent) {
			$content = preg_replace('~'.pregQuote($char).'~', $ent, $content);
		}
		return $content;
	}



	static public function entitiesList()
	{
		// Reserved characters in HTML
		$html = array(
			'"' => '&quot;',
			"'" => '&apos;',
			'&' => '&amp;',
			'<' => '&lt;',
			'>' => '&gt;'
		);

		// ISO 8859-1 symbols
		$symbols = array(
			' ' => '&nbsp;',
			'¡' => '&iexcl;',
			'¢' => '&cent;',
			'£' => '&pound;',
			'¤' => '&curren;',
			'¥' => '&yen;',
			'¦' => '&brvbar;',
			'§' => '&sect;',
			'¨' => '&uml;',
			'©' => '&copy;',
			'ª' => '&ordf;',
			'«' => '&laquo;',
			'¬' => '&not;',
			'­' => '&shy;',
			'®' => '&reg;',
			'¯' => '&macr;',
			'°' => '&deg;',
			'±' => '&plusmn;',
			'²' => '&sup2;',
			'³' => '&sup3;',
			'´' => '&acute;',
			'µ' => '&micro;',
			'¶' => '&para;',
			'·' => '&middot;',
			'¸' => '&cedil;',
			'¹' => '&sup1;',
			'º' => '&ordm;',
			'»' => '&raquo;',
			'¼' => '&frac14;',
			'½' => '&frac12;',
			'¾' => '&frac34;',
			'¿' => '&iquest;',
			'×' => '&times;',
			'÷' => '&divide;'
		);

		// ISO 8859-1 characters
		$characters = array(
			'À' => '&Agrave;',
			'Á' => '&Aacute;',
			'Â' => '&Acirc;',
			'Ã' => '&Atilde;',
			'Ä' => '&Auml;',
			'Å' => '&Aring;',
			'Æ' => '&AElig;',
			'Ç' => '&Ccedil;',
			'È' => '&Egrave;',
			'É' => '&Eacute;',
			'Ê' => '&Ecirc;',
			'Ë' => '&Euml;',
			'Ì' => '&Igrave;',
			'Í' => '&Iacute;',
			'Î' => '&Icirc;',
			'Ï' => '&Iuml;',
			'Ð' => '&ETH;',
			'Ñ' => '&Ntilde;',
			'Ò' => '&Ograve;',
			'Ó' => '&Oacute;',
			'Ô' => '&Ocirc;',
			'Õ' => '&Otilde;',
			'Ö' => '&Ouml;',
			'Ø' => '&Oslash;',
			'Ù' => '&Ugrave;',
			'Ú' => '&Uacute;',
			'Û' => '&Ucirc;',
			'Ü' => '&Uuml;',
			'Ý' => '&Yacute;',
			'Þ' => '&THORN;',
			'ß' => '&szlig;',
			'à' => '&agrave;',
			'á' => '&aacute;',
			'â' => '&acirc;',
			'ã' => '&atilde;',
			'ä' => '&auml;',
			'å' => '&aring;',
			'æ' => '&aelig;',
			'ç' => '&ccedil;',
			'è' => '&egrave;',
			'é' => '&eacute;',
			'ê' => '&ecirc;',
			'ë' => '&euml;',
			'ì' => '&igrave;',
			'í' => '&iacute;',
			'î' => '&icirc;',
			'ï' => '&iuml;',
			'ð' => '&eth;',
			'ñ' => '&ntilde;',
			'ò' => '&ograve;',
			'ó' => '&oacute;',
			'ô' => '&ocirc;',
			'õ' => '&otilde;',
			'ö' => '&ouml;',
			'ø' => '&oslash;',
			'ù' => '&ugrave;',
			'ú' => '&uacute;',
			'û' => '&ucirc;',
			'ü' => '&uuml;',
			'ý' => '&yacute;',
			'þ' => '&thorn;',
			'ÿ' => '&yuml;'
		);

		$others = array(
			"’" => "&rsquo;"
		);

		return array_merge($html, $symbols, $characters, $others);
	}



	static public function form( $form_id, $default_string = '' )
	{
		// Get the string value
		$filter = new formManager_filter();
		$filter->requestVariable('get');
		$string = $filter->requestValue('string')->get();

		// Default string
		if (!$string && $default_string) {
			$string = $default_string;
		}

		// Get the search form
		$html = '';
		if (!comRewrite_::isEnabled())
		{
			$form = new formManager(0,0);
			$html .= $form->form('get', WEBSITE_PATH.'/index.php', $form_id);

			$html .= $form->hidden('com', 'search')."\n";
			$html .= $form->hidden('page', 'index')."\n\n";

			$html .= $form->text('string', $string)."\n";
			$html .= $form->submit('submit', LANG_COM_SEARCH_FORM_SUBMIT);
		}
		else
		{
			$form = new formManager(0,0);
			$html .= $form->form('get', comMenu_rewrite('com=search&amp;page=index'), $form_id);

			$html .= $form->text('string', $string)."\n";
			$html .= $form->submit('submit', LANG_COM_SEARCH_FORM_SUBMIT);
		}
		$html .= $form->end();
		return $html;
	}



	// Warning : this method must be called after the search() method
	static public function pagination( $form_id )
	{
		$html = '';

		// Check session
		$session = new sessionManager(sessionManager::FRONTEND, 'com_search');
		$string = $session->get('string');
		$matches_number = count($session->get('matches'));

		// Url base
		$href = "com=search&amp;page=index&amp;form_id=$form_id&amp;string=".urlencode($string);

		// Steps number
		$step_num = intval($matches_number/self::RESULTS_PER_STEP);
		if ($matches_number%self::RESULTS_PER_STEP) {
			$step_num++;
		}

		// No pagination !
		if ($step_num == 1) {
			return '';
		}

		// Get current step
		self::paginationInfos($step, $matches_number);

		for ($i=1; $i<=$step_num; $i++)
		{
			if ($i != $step['current'])
			{
				$href_step = comMenu_rewrite("$href&amp;step=$i");
				$html .= " &nbsp;<a href=\"$href_step\">$i</a>&nbsp; ";
			} else {
				$html .= " &nbsp;<span>$i</span>&nbsp; ";
			}
		}
		return "\n<div class=\"comSearch_pagination\">$html</div>\n";
	}



	static protected function paginationInfos( &$step, $matches_number )
	{
		// Get current step
		$filter = new formManager_filter();
		$filter->requestVariable('get');
		$step_current = $filter->requestValue('step')->getInteger();
		$step_current or $step_current = 1;

		// Results current interval
		$start	= self::RESULTS_PER_STEP *($step_current-1);
		$stop	= $start + self::RESULTS_PER_STEP;

		$stop <= $matches_number or $stop = $matches_number;

		// Fill $step infos
		$step['current'	] = $step_current;
		$step['start'	] = $start;
		$step['stop'	] = $stop;
	}



	protected function debugHtml( $title, $content )
	{
		if ($this->debug) {
			echo
				"\n<!-- DEBUG : com_search : BEGIN -->\n".
				"<div style=\"margin-bottom:15px;\">\n".
					"<div style=\"color:red;font-weight:bold;\">$title</div>\n".
					"<div style=\"color:blue;\">\n$content\n</div>".
				"</div>\n".
				"<!-- DEBUG : com_search : END -->\n\n";
		}
	}


}


?>