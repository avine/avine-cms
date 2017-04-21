<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// class
class comAddrbook
{
	protected	$frontend			= true;			# Customized behaviours for frontend and backend

	protected	$form_id;
	const		FORM_FILTER_PREFIX = 'addrbook_filter_';

	protected	$results_per_page	= 20;
	protected	$steps				= '';			# Html code of the steps links
	const		POST_STEP			= 'step';		# $_POST['step']

	const		TMPL_PATH			= '/components/com_addrbook/tmpl/',
				TMPL_NAME_LIST		= 'list.html',
				TMPL_NAME_DETAILS	= 'details.html';

	protected	$tmpl_list,
				$tmpl_details;						# TODO : Actually not used (we are only using the global view)

	protected	$fixed_options		= array();		# List of options wich are automaticaly selected (the associated filter will not be displayed)
	protected	$options			= array();		# Contains the ID list of the selected options

	protected	$search				= '';			# Search by keywords ($this->search property is a simple string)



	/**
	 * -------------
	 * Configuration
	 * -------------
	 */

	public function __construct( $form_id = '', $frontend = true )
	{
		$this->form_id = $form_id;

		$this->setTmplList		(self::TMPL_NAME_LIST	);
		$this->setTmplDetails	(self::TMPL_NAME_DETAILS);

		$this->frontend = $frontend;
	}



	public function setTmplList( $tmpl_name ) {
		$this->tmpl_list = $tmpl_name;
	}

	public function setTmplDetails( $tmpl_name ) {
		$this->tmpl_details = $tmpl_name;
	}



	/**
	 * --------------
	 * Manage Filters
	 * --------------
	 */

	public function processFilters()
	{
		$options = array();

		$filter = new formManager_filter();
		$filter_id = formManager_filter::arrayOnly( $filter->requestName(self::FORM_FILTER_PREFIX)->getInteger() );
		for ($i=0; $i<count($filter_id); $i++)
		{
			if ($new_opt = formManager_filter::arrayOnly($filter->requestValue(self::FORM_FILTER_PREFIX.$filter_id[$i])->getInteger()))
			{
				$options = array_merge($options, $new_opt);
			}
		}

		// Make the process result available in $this->options property
		$this->setOptions($options);
	}



	public function getAllFilters( $addons = array() )
	{
		$html = '';

		// Add special css to customize the frontend
		$this->frontend ? $class = ' addrbook-filter-frontend' : $class = '';

		global $db;
		$filter_id = array_keys($db->select('addrbook_filter, [id], filter_order(asc)'));
		for ($i=0; $i<count($filter_id); $i++)
		{
			if (!$this->isDisabledFilter($filter_id[$i]) && ($filter = $this->getFilter($filter_id[$i])))
			{
				$html .= "<div class=\"addrbook-filter$class\">\n$filter\n</div>\n";
			}
		}

		for ($i=0; $i<count($addons); $i++) {
			$html .= "<div class=\"addrbook-filter$class\">\n{$addons[$i]}\n</div>\n";
		}

		if ($html) {
			return "\n$html<div class=\"addrbook-filter-clear\"></div>\n\n";
		}
	}



	public function getFilter( $filter_id, $root_key = 'root' )
	{
		if ($this->frontend)
		{
			$where		= ' AND, where: published=1';
			$br			= '';
			$multiple	= '';
		} else {
			$where		= '';			# In backend, always display the filter
			$br			= '<br />';		# In backend, the filters should be displayed in columns
			$multiple	= 'multiple;';	# In backend, make the select multi-choice
		}

		global $db;
		if ($filter = $db->selectOne("addrbook_filter, *, where: id=$filter_id".$where))
		{
			if ($option = $db->select("addrbook_filter_option, *, name(asc), where: filter_id=$filter_id"))
			{
				$opt[$root_key] = LANG_ADDRBOOK_FILTER_OPTION_ROOT;
	
				for ($i=0; $i<count($option); $i++) {
					$opt[ $option[$i]['id'] ] = $option[$i]['name'];
				}
				$opt = formManager::selectOption($opt, $this->options);

				if (!$this->frontend) {
					$multiple .= 'size='.count($opt); # In backend, set the size of the multi-select
				}

				$form = new formManager();
				$form->setForm('post', $this->form_id);
				return $form->select(self::FORM_FILTER_PREFIX.$filter_id, $opt, $filter['name'].$br, self::FORM_FILTER_PREFIX.$filter['id_alias'], $multiple);
			}
		}

		return false;
	}



	/*
	 * If you know the 'id_alias' of the filter, you can use the following code to display a filter :
	 *
	 * $addrbook = new comAddrbook();
	 * $html = $addrbook->getFilter( comAddrbook::getFilterID('my_alias') );
	 */
	public static function getFilterID( $id_alias )
	{
		global $db;
		return $db->selectOne('addrbook_filter, id, where: id_alias='.$db->str_encode($id_alias), 'id');
	}



	/**
	 * --------------------
	 * Manage Fixed options (this feature is only available in Frontend)
	 * --------------------
	 */

	public function fixedOption( $option_id )
	{
		if ($this->frontend)
		{
			global $db;
			if ($filter_id = $db->selectOne("addrbook_filter_option, filter_id, where: id=$option_id", 'filter_id'))
			{
				$this->fixed_options[$option_id] = $filter_id; # Notice the particular struture of this array !
			}
		}
		else
		{
			trigger_error(__METHOD__.' is not available in Backend.');
		}

		return $this;
	}



	public function isDisabledFilter( $filter_id )
	{
		if (in_array($filter_id, $this->fixed_options))
		{
			return true;
		} else {
			return false;
		}
	}



	public function fixedOptionsDebug( $sep = ' <span>&nbsp;|&nbsp;</span> ' )
	{
		$html = array();
		foreach($this->fixed_options as $option_id => $filter_id)
		{
			if (self::optionDetails($option_id, $option_name, $filter_name))
			{
				$html[] = "$option_name <span>($filter_name)</span>";
			}
		}

		return '<h3>DEBUG FIXED OPTIONS</h3><p>'.implode($sep, $html).'</p>';
	}



	/**
	 * --------------
	 * Manage Options
	 * --------------
	 */

	/*
	 * ID List of the selected options
	 * $this->options property is used to fill the default selected options when displaying filters by calling $this->getAllFilters() method.
	 * This property is also used to filter the addresses that matches the selected options when calling $this->getBook() method.
	 */
	public function setOptions( $options = array() )
	{
		$this->options = $options;
	}



	public function getOptions()
	{
		return $this->options;
	}



	public function optionsBreadcrumb( $sep = ' <strong>&nbsp;&middot;&nbsp;</strong> ' )
	{
		$html = array();
		for ($i=0; $i<count($this->options); $i++)
		{
			if (self::optionDetails($this->options[$i], $option_name, $filter_name))
			{
				$html[] = "$filter_name&nbsp;&gt;&nbsp;$option_name";
			}
		}
		return implode($sep, $html);
	}



	// Get the name of an option and it's filter name
	static public function optionDetails( $option_id, &$option_name, &$filter_name )
	{
		global $db;
		if ($option = $db->selectOne("addrbook_filter_option, name,filter_id, where: id=$option_id"))
		{
			$option_name = $option['name'];
			$filter_name = $db->selectOne("addrbook_filter, name, where: id=".$option['filter_id'], 'name');
			return true;
		}
		return false;
	}



	/**
	 * Manage Search
	 */

	public function search( $string )
	{
		$this->search = $string;
	}



	/**
	 * Manage Book
	 */

	public function getBook( $tmpl_name = '' )
	{
		$html = '';

		// Merge all required options
		$options = array_merge(array_keys($this->fixed_options), $this->options);

		global $db;
		$session = new sessionManager(sessionManager::FRONTEND, 'addrbook');

		if (isset($_POST[self::POST_STEP]) && $session->get('last_search'))
		{
			// Load book from session (cache)
			$addrbook = $session->get('last_search');
		}
		elseif (!count($options) && !$this->search)
		{
			// Get the entire book
			$addrbook = $db->select('addrbook, *, name(asc)');
		}
		else
		{
			// The Search
			if ($this->search)
			{
				$query = 'SELECT ft.`id` FROM `{table_prefix}addrbook` AS ft WHERE MATCH (ft.`search`) AGAINST ('.$db->str_encode($this->search).' IN BOOLEAN MODE)'; # 'ft' means 'full text'
			} else {
				$query = '';
			}

			// The Filters
			for ($i=0; $i<count($options); $i++)
			{
				$select = "SELECT s$i.`addrbook_id` FROM `{table_prefix}addrbook_filter_search` AS s$i WHERE s$i.`option_id`={$options[$i]}";

				if (!$query)
				{
					$query = $select;
				} else {
					$query = "$select AND s$i.`addrbook_id` IN ($query)";
				}
			}

			// And the book itself !
			$query = "SELECT * FROM `{table_prefix}addrbook` AS book WHERE book.`id` IN ($query) ORDER BY book.`name`"; # That's a funny query, isn't it ?

			if ($result = $db->sendMysqlQuery($query))
			{
				$addrbook = $db->fetchMysqlResults($result);
			} else {
				$addrbook = array();
			}
		}

		// Remember the last search !
		$session->set('last_search', $addrbook);

		// Multipage
		$this->chunkBook($addrbook);

		// Default template
		$tmpl_name or $tmpl_name = $this->tmpl_list;

		$template = new templateManager();
		$template->setTmplPath(sitePath().self::TMPL_PATH.$tmpl_name);

		// Html output
		for ($i=0; $i<count($addrbook); $i++)
		{
			if ($addrbook[$i]['web']) {
				$addrbook[$i]['web'] = '<a href="'.$addrbook[$i]['web'].'"'.(comRewrite_::isLocalHostUrl($addrbook[$i]['web'], $url_dyn_part) ? '' : ' class="external"').'>'.$addrbook[$i]['web'].'</a>';
			}

			if ($addrbook[$i]['email']) {
				$addrbook[$i]['email'] = '<a href="mailto:'.$addrbook[$i]['email'].'">'.$addrbook[$i]['email'].'</a>';
			}

			$addrbook[$i]['full_address'] = self::formatAddress($addrbook[$i]);

			// For debugging, it might be interesting to show for each entry the list of associated options
			#$addrbook[$i]['breadcrumb'	] = self::bookBreadcrumb($addrbook[$i]['id']);

			$html .= $template->setReplacements($addrbook[$i])->process()."\n\n";
		}

		if (!count($addrbook))
		{
			if (!count($this->options) && !$this->search) # FIXME - Faut-il mettre !count($this->options) ou bien !count($options) ?!?
			{
				$html .= '<p><br />'.LANG_ADDRBOOK_BOOK_EMPTY.'</p>';
			} else {
				$html .= '<p><br />'.LANG_ADDRBOOK_SEARCH_NO_RESULT.'</p>';
			}
		}

		return "\n\n".$html;
	}



	static public function formatAddress( $addrbook )
	{
		// Alias
		$address	= $addrbook['address'	];
		$city		= $addrbook['city'		];
		$state		= $addrbook['state'		];
		$country	= $addrbook['country'	];
		$zip		= $addrbook['zip'		];

		// Let's go !
		$html = '';

		!$address or $html .= "$address<br />";

		(!$zip && !$city) or $html .= "$zip $city<br />";

		if ($state && $country) {
			$html .= "$state - $country";
		}
		elseif ($state || $country) {
			$html .= $state.$country;
		}

		return $html;
	}



	public function bookOptions( $addrbook_id )
	{
		global $db;
		$options = array_keys($db->select("addrbook_filter_search, [option_id], where: addrbook_id=$addrbook_id"));

		// Make the options availables in $this->options property
		$this->setOptions($options);
	}



	protected function chunkBook( &$addrbook )
	{
		if (count($addrbook) <= $this->results_per_page) {
			return;
		}

		$chunk_num = count( $chunk = array_chunk($addrbook, $this->results_per_page) );

		if ( isset($_POST[self::POST_STEP]) && formManager_filter::isInteger($_POST[self::POST_STEP]-1) )
		{
			$index = $_POST[self::POST_STEP]-1;
		} else {
			$index = 0;
		}
		isset($chunk[$index]) or $index = 0;

		$addrbook = $chunk[$index];

		// Generate the Html steps
		$form = new formManager();
		$form->setForm('post', $this->form_id);
		$steps = '';
		for ($i=1; $i<=count($chunk); $i++)
		{
			$steps .= $form->submit('step', $i, ($i-1 == $index ? "step_current" : "step_$i"));
		}
		$this->steps = "<div class=\"addrbook-filters-steps\"> $steps</div>";
	}



	public function getSteps()
	{
		return $this->steps;
	}



	public static function bookBreadcrumb( $addrbook_id, $sep = ' <strong>&nbsp;&middot;&nbsp;</strong> ' )
	{
		$html = array();

		global $db;
		$options = array_keys($db->select("addrbook_filter_search, [option_id], where: addrbook_id=$addrbook_id"));

		for ($i=0; $i<count($options); $i++)
		{
			if (self::optionDetails($options[$i], $option_name, $filter_name))
			{
				$html[] = "$filter_name&nbsp;&gt;&nbsp;$option_name";
			}
		}

		return implode($sep, $html);
	}

}

