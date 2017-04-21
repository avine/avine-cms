<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Sub-Class

class comGeneric_frontend extends comGeneric_
{
	// The following code is right, but useless... Because if no _construct() method is defined into the child class, then the parent constructor is called automaticcaly.
	/*
	public function __construct( $init )
	{
		parent::__construct($init);
	}
	*/



	///////////////////////////////////////
	// Scope resolution operator functions

	// Instanciate class object with setup feature : comGeneric_frontend::scope();
	/*
	 *  Adapted code from : '/components/com_content/pages/index.php'
	 *
	 *  NOTICE :
	 *  Each component wich is using the comGeneric_frontend class engine, redefine some methods.
	 *  For example, the com_content component is using :
	 *	-> class comContent_frontend extends comGeneric_frontend
	 *  Then if you use comContent_frontend::scope(); you will access only to the comGeneric_frontend methods !
	 *  So, if it's a problem, you should redefine this scope function in the extended class...
	 */
	public static function scope( $com_setup_full_path, $redirection = false )
	{
		global $init;
		!isset($init) or trigger_error(LANG_COM_GENERIC_INIT_OVERWRITTEN, E_USER_WARNING);

		// Setup
		require($com_setup_full_path);				# This script define the $init variable

		// Instanciate class object
		$com_gen = new comGeneric_frontend($init);	# NOTICE : here you see we call explicitly 'comGeneric_frontend' even if you write the code: comContent_frontend::scope();
		$com_gen->configFrontend();

		// Redirection Url
		if ($redirection !== false) {
			$com_gen->setRedirection($redirection);
		}

		// Here a "strong" unset($init);
		$init = NULL;								# Component setting available in $com_gen properties

		return $com_gen;
	}



	////////////////////////
	// Properties & methods

	protected	$elements_per_step;			# Number of elements displayed per page
	protected	$elements_per_row;			# Number of columns used to display the current page elements
	protected	$elements_wrapper;			# Type of wrapper tag for elements columns : 0 = <table> ; 1 = <div>

	protected	$subnodes_per_row;			# Number of columns used to display the current page subnodes
	protected	$subnodes_wrapper;			# Type of wrapper tag for subnodes columns : 0 = <table> ; 1 = <div>
	protected	$subnodes_ontop;			# Subnodes navigation position into the page

	protected	$selector_node;				# Display nodes selector
	protected	$selector_node_relative;	# Nodes selector options are relative to the 'grand-parent' of the current node_id
	protected	$selector_archive;			# Display archive selector

	#protected	$home_per_row;				# Number of columns used to display the home-page # TODO - A SUPPRIMER
	#protected	$home_wrapper;				# Type of wrapper tag for home-page columns : 0 = <table> ; 1 = <div> # TODO - A SUPPRIMER

	/**
	 * Like $com_name property (see super-class), $page_name help us to set the 'action' argument of forms in frontend.
	 * Example : /index.php?com=$com_name&page=$page_name
	 * But unlike $com_name, it's not a part of the component setting infos (1) (see super-class).
	 * It is defined in each script, by using the setPageName() accessor.
	 */
	protected	$page_name;
	protected	$redirection = false;		# Replace the exact string url 'com=$com_name&page=$page_name'

	protected	$live_status;				# $live_status['steps_number']		Number of steps we should use to display all requested elements
											# $live_status['node_id']			Current node_id requested
											# $live_status['element_id']		Current element_id requested



	// Get the global frontend configuration from 'config' table
	public function configFrontend()
	{
		// Get config from database
		global $db;
		$config = $db->selectOne($this->table_prefix.'config, '.
						'elements_per_step, elements_per_row, elements_wrapper, '.
						'subnodes_per_row, subnodes_wrapper, subnodes_ontop, '.
						'selector_node, selector_node_rel, selector_archive');

		$this->elements_per_step		= $config['elements_per_step'	];
		$this->elements_per_row			= $config['elements_per_row'	];
		$this->elements_wrapper			= $config['elements_wrapper'	];

		$this->subnodes_per_row			= $config['subnodes_per_row'	];
		$this->subnodes_wrapper			= $config['subnodes_wrapper'	];
		$this->subnodes_ontop			= $config['subnodes_ontop'		];

		$this->selector_node			= $config['selector_node'		];
		$this->selector_node_relative	= $config['selector_node_rel'	];
		$this->selector_archive			= $config['selector_archive'	];

		#$this->home_per_row				= $config['home_per_row'		]; # TODO - A SUPPRIMER
		#$this->home_wrapper				= $config['home_wrapper'		]; # TODO - A SUPPRIMER

		// Set default values of $live_status properties
		$live_status['steps_number'	] = 1;
		$live_status['node_id'		] = false;
		$live_status['element_id'	] = false;
	}



	/* Accessors */
	public function getElementsPerStep()		{ return $this->elements_per_step; 		}
	public function getElementsPerRow()			{ return $this->elements_per_row; 		}
	public function getElementsWrapper()		{ return $this->elements_wrapper; 		}

	public function getSubnodesPerRow()			{ return $this->subnodes_per_row; 		}
	public function getSubnodesWrapper()		{ return $this->subnodes_wrapper; 		}
	public function getSubnodesOntop()			{ return $this->subnodes_ontop; 		}

	public function getSelectorNode()			{ return $this->selector_node; 			}
	public function getSelectorNodeRelative()	{ return $this->selector_node_relative; }
	public function getSelectorArchive()		{ return $this->selector_archive; 		}

	public function setPageName( $page_name ) { $this->page_name = $page_name; }
	public function setRedirection( $redirection ) { $this->redirection = $redirection; }



	// Return 'com=$com_name&page=$page_name' or $redirection
	public function pageUrlRequest()
	{
		if ($this->redirection === false)
		{
			return 'com='.$this->com_name.'&amp;page='.$this->page_name;
		} else {
			return $this->redirection;
		}
	}



	/**
	 * Here a very usefull method for the outsides components. It analyse the query_string and determine if the component is requested in the url
	 * But there's more: if a node_id (or element_id) is requested, it is transformed into his equivalent node (or element)
	 */
	public function findComponentInUrl()
	{
		// page url request
		$page_url_request = $this->pageUrlRequest();

		// Is the component page not requested ? Get out of here!
		if (!( ($page_url_request == "") || (preg_match('~^('.pregQuote(str_replace('&amp;', '&', $page_url_request)).')~', $_SERVER['QUERY_STRING'])) ))
		{
			return
				array(
					'component'			=> '',
					'node'				=> '',
					'element'			=> '',
					'all_in_one'		=> '',
					'node_alias_array'	=> array(),
					'element_alias'		=> ''
				);
		}

		$founded_node 		= '';
 	 	$founded_element 	= '';
		$node_alias_array 	= array();
		$element_alias		= '';

		// Some alias
		$request_nodes 		= $this->config_com_node_name;
		$request_element 	= $this->config_com_element_name;
		$request_node_id 	= $this->urlRequestNode_id();
		$request_element_id = $this->urlRequestElement_id();

		$virtual_root = false;

		// $request_element_id ?
		if ((isset($_GET[$request_element_id])) && (formManager_filter::isInteger($_GET[$request_element_id]))) # example : item_id=5
		{
			# Here the 'element_id' request as it appear in the url, but we are going now to replace it by his equivalent in regular 'element' request
			#$founded_element = $request_element_id.'='.$_GET[$request_element_id];

			$mix = $this->elementUrlEncoder($_GET[$request_element_id]); # contain the mix of $founded_node and $founded_element
			if ($mix['href'])
			{
				$mix = explode('&amp;'.$request_element.'=', $mix['href']);

				$founded_node 		= $mix[0];
				$founded_element 	= $request_element.'='.$mix[1];
			}
		}
		// $request_node_id ?
		elseif ((isset($_GET[$request_node_id])) && (formManager_filter::isInteger($_GET[$request_node_id]))) # example : section_category_id=3
		{
			# Here the 'node_id' request as it appear in the url, but we are going now to replace it by his equivalent in regular 'node' request
			#$founded_node = $request_node_id.'='.$_GET[$request_node_id];

			if ($_GET[$request_node_id] != '0')
			{
				$founded_node = $this->nodeUrlEncoder($_GET[$request_node_id]);
				$founded_node = $founded_node['href'];
			}
			// Fix now the limitation to find the url like: node_id=0 (virtual root for all contents of the component)
			else
			{
				$founded_node = "$request_node_id=0"; # Here the unique case that the url is encoded in 'node_id' format (from the selector)
				$virtual_root = true;
			}
		}
		else
		{
			// $request_nodes ?
			$request_nodes_validation = true;

			$node_valid_level = 0; # That mean : all levels (node1, node2, ...) are validated (with node=node1/node2)
			if (isset($_GET[$request_nodes]))
			{
				$request_nodes_array = explode('/', $_GET[$request_nodes]);
				for ($i=0; $i<count($request_nodes_array); $i++) if (!formManager_filter::isID($request_nodes_array[$i]))
				{
					$request_nodes_validation = false;
					$node_valid_level = -1; # That mean we can not validate all levels (even if one of them is validated). So, it's like : No level validated
				}
			}
			else {
				$request_nodes_validation = false;
			}

			if ($request_nodes_validation) {
				$founded_node = $request_nodes.'='.$_GET[$request_nodes];
			}

			// $request_element ?
			if ($request_nodes_validation) # example :  section=sec1&amp;category=cat1  or  section=sec1
			{
				if ((isset($_GET[$request_element])) && (formManager_filter::isID($_GET[$request_element]))) # example : item=itm1
				{
					$founded_element = $request_element.'='.$_GET[$request_element];
				}
			}
		}

		// $node_alias_array
		if ($founded_node && !$virtual_root)
		{
			$node_alias_array = explode('/', str_replace($request_nodes.'=', '', $founded_node));
		}

		// $element_alias
		if ($founded_element)
		{
			$element_alias = str_replace($request_element.'=', '', $founded_element);
		}

		// all_in_one
		$all_in_one = $page_url_request; # remember pageUrlRequest() can be == "" because of redirection
		if ($founded_node)
		{
			if ($all_in_one != "") {
				$all_in_one .= '&amp;';
			}
			$all_in_one .= $founded_node;
		}
		if ($founded_element)
		{
			if ($all_in_one != "") {
				$all_in_one .= '&amp;';
			}
			$all_in_one .= $founded_element;
		}

		// return
		return
			array(
				'component'			=> $page_url_request,
				'node'				=> $founded_node,
				'element'			=> $founded_element,
				'all_in_one'		=> $all_in_one,
				'node_alias_array'	=> $node_alias_array,
				'element_alias'		=> $element_alias
			);
	}



	/**
	 * Here the suite of $com_in_url = findComponentInUrl() method.
	 *
	 * Typical use:
	 *			$com_in_url = $this->findComponentInUrl();
	 *			$this->pageContentDetails( $com_in_url['node_alias_array'], $com_in_url['element_alias'] );
	 *
	 * This method is designed to comMenu_ component and help him to find the right page title
	 */
	public function pageContentDetails( $nodes_alias_array, $element_alias )
	{
		$details =
			array(
				'title'			=> '',
				'access_level'	=> '999',
			  	'published'		=> '1',
			  	'meta_key'		=> '',
			  	'meta_desc'		=> ''
			);

		global $db;

		$parent_id = 0;
		if (count($nodes_alias_array))
		{
			$node_validation = true;
		} else {
			$node_validation = false;
		}
		for ($i=0; $i<count($nodes_alias_array); $i++)
		{
			if ($node = $db->selectOne($this->getTablePrefix()."node, id,id_alias, access_level,published, where: parent_id=$parent_id AND, where: id_alias=".$db->str_encode($nodes_alias_array[$i])))
			{
				$parent_id = $node['id'];
			} else {
				$node_validation = false;
				break;
			}
		}

		if ($node_validation)
		{
			if ($element_alias)
			{
				if ($this->element_item_field)
				{
					$element = $db->selectOne(
										$this->getTablePrefix()."element, id,id_alias, access_level,published, meta_key,meta_desc, where: node_id=$parent_id AND, where: id_alias=".$db->str_encode($element_alias).", join: id>; ".
										$this->getTablePrefix()."element_item, ".$this->getElementItemField().", join: <element_id" );
				} else {
					$element = $db->selectOne(
										$this->getTablePrefix()."element, id,id_alias, access_level,published, meta_key,meta_desc, where: node_id=$parent_id AND, where: id_alias=".$db->str_encode($element_alias));
				}

				if ($element)
				{
					if ($this->element_item_field)
					{
						$title = $element[$this->getElementItemField()];
					} else {
						$title = $element['id_alias'];
					}

					$details =
						array(
							'title'			=> $title.($element['meta_key'] ? ', '.$element['meta_key'] : ''), # Add keywords in the page title for SEO
							'access_level'	=> $element['access_level'],
				  			'published'		=> $element['published'],
				  			'meta_key'		=> $element['meta_key'],
				  			'meta_desc'		=> $element['meta_desc']
						);
				}
			}
			else
			{
				if ($this->node_item_field)
				{
					$title = $db->selectOne($this->getTablePrefix()."node_item, ".$this->getNodeItemField().", where: node_id=$parent_id", $this->getNodeItemField());
				} else {
					$title = $node['id_alias'];
				}

				$details =
					array(
						'title'			=> $title,
						'access_level'	=> $node['access_level'],
				  		'published'		=> $node['published'],
				  		'meta_key'		=> '',
				  		'meta_desc'		=> ''
					);
			}
		}

		return $details;
	}



	/**
	 * Url encoder & decoder for nodes & elements
	 */

	// Url encoder for Nodes
	public function nodeUrlEncoder( $node_id )
	{
		$link_href	= '';
		$link_value	= '';

		global $db;

		// Get link href : node path
		$node_path = array();
		$current_node = $node_id;
		while ($current_node != 0)
		{
			if ($node = $db->selectOne($this->table_prefix."node, id_alias,parent_id, where: id=$current_node"))
			{
				$node_path[count($node_path)] = $node['id_alias'];
				$current_node = $node['parent_id'];
			} else {
				break;
			}
		}
		$node_path = array_reverse($node_path);

		if (count($node_path))
		{
			for ($i=0; $i<count($node_path)-1; $i++) {
				$link_href .= $node_path[$i].'/';
			}
			$link_href .= $node_path[count($node_path)-1];

			$link_href = $this->config_com_node_name.'='.$link_href;

			// Get link value
			if ($this->node_item_field) 			# Use a Specific field from 'node_item' table
			{
				$link_value = $db->selectOne($this->table_prefix."node_item, $this->node_item_field, where: node_id=$node_id", $this->node_item_field);
			} else {								# Use a Generic-test field from 'node' table
				$link_value = $db->selectOne($this->table_prefix."node, id_alias, where: id=$node_id", 'id_alias');
			}
		}

		return array( 'href' => $link_href, 'value' => $link_value );
	}



	// Url encoder for Elements
	public function elementUrlEncoder( $element_id )
	{
		$link_href	= '';
		$link_value	= '';

		global $db;

		// Get link_href
		if ($element = $db->selectOne($this->table_prefix."element, node_id,id_alias, where: id=$element_id"))
		{
			$part_node 		= $this->nodeUrlEncoder($element['node_id']);				# Node part of the href
			$part_element 	= $this->config_com_element_name.'='.$element['id_alias'];	# Element part of the href
			$link_href = $part_node['href'].'&amp;'.$part_element;

			// Get link_value
			if ($this->element_item_field) 	# Use a Specific field from 'element_item' table
			{
				$link_value = $db->selectOne($this->table_prefix."element_item, $this->element_item_field, where: element_id=$element_id", $this->element_item_field);
			} else { 						# Use a Generic-test field from 'element' table
				$link_value = $db->selectOne($this->table_prefix."element, id_alias, where: id=$element_id", 'id_alias');
			}
		}

		return array( 'href' => $link_href, 'value' => $link_value );
	}



	// Url decoder and validator
	public function urlValidator()
	{
		/**
		 * Rules :
		 *  	If we find $valid_node_id, then $valid_element_id can be false.
		 *  	But if we find $valid_element_id, $valid_node_id can't be false!
		 * Validation :
		 *  	When something is found, we are checking it in the database.
		 *  	Then the returned information is a validated couple of node_id and element_id.
		 * So, this method is a decoder and a validator.
		 *
		 * About $valid_node_id :
		 *  	We use it as a 'node_id' field, for the 'element' table ; and a 'id' field for 'node' table.
		 *  	When subnodes navigation required, we use it also as a 'parent_id' field for 'node' table.
		 */
		$valid_node_id 		= false;
		$valid_element_id 	= false;

		global $db;
		$session = new sessionManager(sessionManager::FRONTEND, $this->com_name);
		$filter = new formManager_filter();

		// Search node_id
		if (isset($_GET[$this->urlRequestNode_id()])) {
			$get_node_id = $_GET[$this->urlRequestNode_id()];
		} else {
			$get_node_id = NULL;
		}
		if (($get_node_id) && (formManager_filter::isInteger($get_node_id))) 		# (id method)
		{
			if ($db->select($this->table_prefix."node, count(*), where: id=$get_node_id")) {
				$valid_node_id = $get_node_id; 										# Founded !
			}
		}
		elseif ($get_node_id == '0') 												# id method (suite) : detection of the root node selection
		{
			$valid_node_id = '0'; 													# Founded !
		}
		else 																		# (id_alias method)
		{
			$node_alias = array();
			$node_name = $this->config_com_node_name;
			if (isset($_GET[$node_name])) {
				$node_alias = explode('/', $_GET[$node_name]);
			}
			for ($i=0; $i<count($node_alias); $i++)
			{
				if (!formManager_filter::isID($node_alias[$i])) {
					$node_alias = array();
				}
			}

			$parent_id = 0;
			for ($i=0; $i<count($node_alias); $i++)
			{
				if ($node_id = $db->selectOne($this->table_prefix."node, id, where: parent_id=$parent_id AND, where: id_alias=".$db->str_encode($node_alias[$i])))
				{
					if ($i < count($node_alias)-1) {
						$parent_id = $node_id['id'];
					} else {
		  				$valid_node_id = $node_id['id']; 							# Founded !
		  			}
				}
				else {
					break;
				}
			}
		}

		// Search element_id
		if (isset($_GET[$this->urlRequestElement_id()])) {
			$get_element_id = $_GET[$this->urlRequestElement_id()];
		} else {
			$get_element_id = NULL;
		}
		$element_name = $this->config_com_element_name; 							# Prepare id_alias method

		if (($get_element_id) && (formManager_filter::isInteger($get_element_id))) # (id method)
		{
			if (($valid_node_id) && ($valid_node_id !== '0'))
			{
				if ($founded = $db->selectCount($this->table_prefix."element, where: node_id=$valid_node_id AND, where: id=$get_element_id"))
				{
					$valid_element_id = $get_element_id; # Founded !
				}
				else
				{
					/**
					 * Perhaps the element have been moved to another node.
					 * Then keep the $element_id and look for his $node_id
					 */
					if ($all = $db->selectOne($this->table_prefix."element, node_id, where: id=$get_element_id"))
					{
						$valid_element_id = $get_element_id;						# Founded !
						$valid_node_id = $all['node_id'];							# Interpreted !
					}
				}
			}
			elseif ($valid_node_id === false)
			{
				if ($founded = $db->selectOne($this->table_prefix."element, node_id, where: id=$get_element_id"))
				{
					$valid_element_id = $get_element_id; 							# Founded !
					$valid_node_id = $founded['node_id']; 							# $valid_node_id founded now !
				}
			}
		}
		elseif ((isset($_GET[$element_name])) && (formManager_filter::isID($_GET[$element_name]))) # (id_alias method)
		{
			if (($valid_node_id) && ($valid_node_id !== '0'))
			{
				if ($element_id = $db->selectOne($this->table_prefix."element, id, where: node_id=$valid_node_id AND, where: id_alias=".$db->str_encode($_GET[$element_name])))
				{
					$valid_element_id = $element_id['id']; 							# Founded !
				} else {
					$valid_node_id = false; 										# Reset $valid_node_id !
				}
			}
		}

		/**
		 * Check for $_SESSION[] : from  (a) nodeSelector()  &  (b) archiveSelector()  methods
		 */

		// (a) nodeSelector() :  Update session  or  reload $valid_node_id from session
		if ($valid_node_id === false)
		{
			// Default behaviour : always go to nodes root
			$valid_node_id = 0;

			// Alternative behaviour : remember the last visited page (might be not good for SEO)
			#$valid_node_id = $session->get('node_id', 0);
		} else {
			$session->set('node_id', $valid_node_id);
		}

		// (b) archiveSelector() :  Switch between online view and archive view : $_POST['archive_selector'] process
		if (formManager::isSubmitedForm('archive_selector_'))
		{
			$archive_selector = $filter->requestValue('select')->getInteger();
			$session->set('archive_selector', $archive_selector); 					# Update session
		} else {
			$archive_selector = $session->get('archive_selector', false); 			# Update from session
		}

		if ($archive_selector !== false) 											# Set $live_archive_status super-class property
		{
			if ($archive_selector == '0')
			{
				$this->live_archive_status['node'	] = 0;
				$this->live_archive_status['element'] = 0;
			}
			elseif ($archive_selector == '1')
			{
				$this->live_archive_status['node'	] = 2;
				$this->live_archive_status['element'] = 1;
			}
	  		else
	  		{
	  			$this->live_archive_status['node'	] = 2;
	  			$this->live_archive_status['element'] = 2;
	  		}
		}
		else # Default values
		{
			$this->live_archive_status['node'		] = 0;
			$this->live_archive_status['element'	] = 0;
			$session->set('archive_selector', 0);
		}

		// $live_archive_status property auto-adaptation (Overwrite what we just get before !)
		if ($valid_element_id !== false)
		{
			$archived = $db->selectOne($this->table_prefix."element, archived, where: id=$valid_element_id", 'archived');

			if (($archived) && ($this->live_archive_status['element'] == 0)) 		# Can't view anything !
			{
				// Switch to archives
				$this->live_archive_status['element'] = 1;
				$this->live_archive_status['node'   ] = 2;
				$session->set('archive_selector', '1');

			}
			elseif ((!$archived) && ($this->live_archive_status['element'] == 1)) 	# Can't view anything !
			{
				// Switch to online
				$this->live_archive_status['element'] = 0;
				$this->live_archive_status['node'   ] = 0;
				$session->set('archive_selector', '0');
			}
		}
		if (($valid_node_id !== false) && ($valid_node_id != '0'))
		{
			$archived = $db->selectOne($this->table_prefix."node, archived, where: id=$valid_node_id", 'archived');

			if (($archived) && ($this->live_archive_status['node'] == 0)) 			# Can't view anything !
			{
				// Switch to archives
				$this->live_archive_status['element'] = 2; 							# To prevent the wired case : the node is archived, but the element is non-archived !
				$this->live_archive_status['node'   ] = 2;
				$session->set('archive_selector', '2');
			}
		}

		/**
		 * Remember the results : (1) '$live_status' property ; (2) function return
		 */

		// (1) '$live_status' property
		$this->live_status['node_id'] 		= $valid_node_id;
		$this->live_status['element_id'] 	= $valid_element_id;

		// (2) Function return (facultative)
		return array( 'node_id' => $valid_node_id, 'element_id' => $valid_element_id );
	}



	/**
	 * MVC : MODEL
	 */

	// Page content
	public function pageContentModel($node_id = -99, $element_id = -99, $start = true)
	{
		// Init. parameters
		$node_id 	== -99 ? $node_id 		= $this->live_status['node_id'		] : '';
		$element_id == -99 ? $element_id 	= $this->live_status['element_id'	] : '';

		// Init. output
		static $html;
		if ($start) {
			$html = '';
		}

		// Display one single element
		if ($element_id)
		{
			$elements_details = $this->elementsGlobalController($node_id, $element_id);
			$this->cleanElements($elements_details);

			$elements_html = $this->elementsSpecificController($elements_details, true);
			$html .= $elements_html;

			// Update element hits
			$session = new sessionManager(sessionManager::FRONTEND, $this->com_name);
			$session->init('element_id_hit', array());
			if (!in_array($element_id, $element_id_hit = $session->get('element_id_hit')))
			{
				// Hit the element once per session
				global $db;
				$hits = $db->selectOne($this->table_prefix."element, hits, where: id=$element_id", 'hits') +1;
				$db->update($this->table_prefix."element; hits=$hits; where: id=$element_id");

				// Remember the hited element id
				$element_id_hit[] = $element_id;
				$session->set('element_id_hit', $element_id_hit);
			}
		}
		// Display many elements
		else
		{
			// Subnodes navigation
			$subnodes_id  = $this->findSubNodesID($node_id);
			if (count($subnodes_id))
			{
				// Use wrapper to display subnodes with columns
				$wrapper = $this->getBoxesWrapper(count($subnodes_id), $this->subnodes_per_row, $this->subnodes_wrapper, $this->com_name.'-wrapper-subnodes');

				$current = 0; $subnodes_html = $wrapper['header']; # Use columns process
				for ($i=0; $i<count($subnodes_id); $i++)
				{
					$subnodes_details = $this->nodeGlobalController($subnodes_id[$i]);
					$this->cleanNode($subnodes_details);

					if ($this->subnodes_wrapper) # Use columns process
					{
						if ($i < count($subnodes_id) - (count($subnodes_id)%$this->subnodes_per_row))
						{
							$wrapper_open = $wrapper['open'];
						} else {
							$wrapper_open = $wrapper['open_last_row'];
						}
					}
					else {
						$wrapper_open = $wrapper['open'];
					}

					$subnodes_html .= $wrapper_open. $this->nodeSpecificController($subnodes_details, true) .$wrapper['close']; # Main content & Use columns process

					if ((++$current == $this->subnodes_per_row) && ($i<count($subnodes_id)-1)) # Use columns process
					{
						$subnodes_html .= $wrapper['row_sep'];
						$current = 0;
					}
				}
				$subnodes_html .= $wrapper['colspan'].$wrapper['footer']; # Use columns process
			}
			else {
				$subnodes_html = '';
			}

			// Display current node and his elements
			$node_details = $this->nodeGlobalController($node_id);
			$elements_details = $this->elementsGlobalController($node_id, $element_id);

			$this->cleanElements($elements_details, $node_details);
			$this->cleanNode($node_details);

			$node_html = $this->nodeSpecificController($node_details);
			$elements_html = $this->elementsSpecificController($elements_details);

			$elements_html .= $this->stepsNavigator(); # Add stepsNavigator() just after the elements

			if ($this->subnodes_ontop)
			{
				$html .= $node_html.$subnodes_html.$elements_html;
			} else {
				$html .= $node_html.$elements_html.$subnodes_html;
			}
		}
		global $com_generic_root_title;
		if ($node_id == 0 && isset($com_generic_root_title) && $com_generic_root_title) {
			$html = "<h1>$com_generic_root_title</h1>\n\n$html";
		}
		return $html;
	}



	public function cleanNode( &$node_details )
	{
		/* -----------------------------------------------------
		 * ! RULES FOR cleanNode() and cleanElements() methods !
		 * -----------------------------------------------------
		 *
		 * Meaning of fields :   view_*  ,  show_*  ,  disable_*
		 *
		 * If in node (or node_item) table, we have a field [node_field],
		 * then view_[node_field] wich is a condition on that field, will automaticaly clean [node_field] if necessary
		 *
		 * In the same way, show_[element_field], determine if [element_field] have to be cleaned.
		 *
		 * So, 'view_' is a keyword for node-fields ; and 'show_' is a keyword for element-fields.
		 * Now, if there's the same show_[element_field] condition into node table and element table,
		 * there's no problem ! Because 'show_' condition have effect only in his restricted area :
		 * 'show_' field into element table, have effect only when this element is requested ;
		 * 'show_' field into node table, have effect only when this node is requested. Simple!
		 *
		 * Now, a disable_[element_field], will disable this [element_field] in all requests (this element request, or his node request)
		 * So, if we have on this field, a show_[element_field] into node table, it will have effect only if disable_[element_field] is set to 0.
		 * Find fields with match the expression : 'show_*'
		 *
		 */

		// 'view_*' fields
		if (is_array($node_details))
		{
			reset($node_details);
			while (list($field, $boolean) = each($node_details))
			{
				if (preg_match('~^(view_)~', $field))
				{
					if (!$node_details[$field]) {
						$node_details[str_replace('view_', '', $field)] = '';
					}
					unset($node_details[$field]);
				}
				elseif (preg_match('~^(show_)~', $field))
				{
					unset($node_details[$field]);
				}
			}
		}
	}



	public function cleanElements( &$elements_details, $node_details = array() )
	{
		/* See cleanNode() method for explanations */

		global $db;

		// Node : 'show_*' list
		if ($node_details)
		{
			$node_show_list = array();
			$sample = $node_details; reset($sample);
			while (list($source, $boolean) = each($sample))
			{
				if (preg_match('~^(show_)~', $source))
				{
					$target = str_replace('show_', '', $source);
					$node_show_list[count($node_show_list)] = $target;
				}
			}
		}

		// Element :  'show_*' list  &  'disable_*' list
		$element_show_list = array();
		$disable_list = array();
		if ((isset($elements_details[0])) && (is_array($elements_details[0])))
		{
			$sample = $elements_details[0];
			reset($sample);
			while (list($source, $boolean) = each($sample))
			{
				if (preg_match('~^(show_)~', $source))
				{
					$target = str_replace('show_', '', $source);
					$element_show_list[count($element_show_list)] = $target;
				}
				elseif (preg_match('~^(disable_)~', $source))
				{
					$target = str_replace('disable_', '', $source);
					$disable_list[count($disable_list)] = $target;
				}
			}
		}

		// Delete what should not be displayed
		for ($i=0; $i<count($elements_details); $i++)
		{
			// First : 'show_*' list
			if (!$node_details)
			{
				for ($j=0; $j<count($element_show_list); $j++) {
					if (!$elements_details[$i]['show_'.$element_show_list[$j]]) $elements_details[$i][$element_show_list[$j]] = '';
				}
			}
			else
			{
				for ($j=0; $j<count($node_show_list); $j++) {
					if (!$node_details['show_'.$node_show_list[$j]]) $elements_details[$i][$node_show_list[$j]] = '';
				}
			}

			// Second : 'disable_*' list
			for ($j=0; $j<count($disable_list); $j++) {
				if ($elements_details[$i]['disable_'.$disable_list[$j]]) $elements_details[$i][$disable_list[$j]] = '';
			}

			/* Post process */

			// Unset 'show_*' & 'disable_*' fields of $elements_details
			for ($j=0; $j<count($element_show_list); $j++) {
				unset($elements_details[$i]['show_'	.$element_show_list[$j]]);
			}
			for ($j=0; $j<count($disable_list); $j++) {
				unset($elements_details[$i]['disable_'.$disable_list[$j]]);
			}

			// Replace 'author_id' field by 'author' field
			if ($elements_details[$i]['author_id'] !== '')
			{
				$elements_details[$i]['author'] = $db->selectOne('user, username, where: id='.$elements_details[$i]['author_id'], 'username');
			}
			else
			{
				$elements_details[$i]['author'] = '';
			}
			unset($elements_details[$i]['author_id']); # Now new 'author' field, is fully replacing 'author_id' field

			// Some others useless fields
			unset($elements_details[$i]['list_order']);
		}
	}



	/**
	 * MVC : CONTROLLER (Global)
	 */

	// Get 'node' and 'node_item' details
	public function nodeGlobalController( $node_id )
	{
		$node_details = array();

 	 	global $db;
		if (($this->authorizeNode($node_id)) && ($node_id != '0'))
		{
			// 'node' table infos : only some fields
			$node =
				$db->selectOne(
					$this->table_prefix."node, ".
					"id, id_alias, level, ".
					"show_date_creation, show_date_modified, show_author_id, show_hits, ".
					"where: id=$node_id"
				);

			if ($node)
			{
				foreach($node as $field => $value) {
					$node_details[$field] = $value;
				}

				// 'node_item' table infos : all fields except node_id (because we have the id from 'node' table)
				if ($node_item = $db->selectOne($this->table_prefix."node_item, *, where: node_id=".$node_details['id']))
				{
					foreach($node_item as $field => $value) {
						if ($field != 'node_id') {
							$node_details[$field] = $value;
						}
					}
				}
			}
		}
		return $node_details; # One node details : $node_details['id'], $node_details['id_alias'], ..., $node_details['title'], ...
	}



	public function findSubNodesId( $parent_id )
	{
		$nodes_id = array();

		global $g_user_login;

		// Select online, or both (online & archive)
		if ($this->live_archive_status['node'] == 0)
		{
			$query_archived = ' AND, where: archived=0';
		} else {
			$query_archived = ''; # =1 or =2
		}

		global $db;
		if ($this->authorizeNode($parent_id))
		{
			$nodes =
				$db->select(
						$this->table_prefix."node, id, list_order(asc), where: parent_id=$parent_id AND, ".
						"where: published=1 AND, where: access_level>=".$g_user_login->accessLevel().$query_archived
				);

			for ($i=0; $i<count($nodes); $i++) {
				$nodes_id[$i] = $nodes[$i]['id'];
			}
		}
		return $nodes_id; # Simple list of ID : array( ID1, ID2, ID3, ... )
	}



	// Get 'element' and 'element_item' details
	public function elementsGlobalController( $node_id, $element_id = false )
	{
		$elements_details = array();

		/**
		 * Some querys parts
		 * Notice: this method is using the $db->sendMysqlQuery() method, so all the $query_* part are reals MySQL querys parts!
		 */
		$query_where = '';

		// archived
		if ($this->live_archive_status['element'] == 0)
		{
			$query_where .= 'AND archived=0 ';
		}
		elseif ($this->live_archive_status['element'] == 1)
		{
			$query_where .= 'AND archived=1 ';
		}

		// pubished
		$query_where .= 'AND published=1 ';

		// date_online & date_offline
		$mktime = time();
		$query_where .= "AND (date_online IS NULL OR date_online < $mktime) AND (date_offline IS NULL OR date_offline > $mktime) ";

		// access_level
		global $g_user_login;
		$query_where .= "AND access_level>=".$g_user_login->accessLevel();

		/**
		 * Some requested fields (of 'element' table)
		 */
		$query_fields_list = 'id,id_alias, date_creation,date_modified,author_id,hits, show_date_creation,show_date_modified,show_author_id,show_hits';

		/**
		 * Let's go!
		 */
		global $db;
		if ($this->authorizeNode($node_id))
		{
			// First get the node title
			if ($this->node_item_field)
			{
				$node_field = $this->node_item_field;
				$node_alias = $db->selectOne($this->table_prefix."node_item, $node_field, where: node_id=$node_id", $node_field);
			} else {
				$node_field = 'id_alias';
				$node_alias = $db->selectOne($this->table_prefix."node, $node_field, where: id=$node_id", $node_field);
			}

			if ($element_id)
			{
				$elements = $db->sendMysqlQuery("SELECT $query_fields_list FROM {table_prefix}{$this->table_prefix}element WHERE id=$element_id $query_where");
				$elements = $db->fetchMysqlResults($elements);
			}
			else
			{
				$num_elements = $db->sendMysqlQuery("SELECT count(*) FROM {table_prefix}{$this->table_prefix}element WHERE node_id=$node_id $query_where");
				$num_elements = $db->fetchMysqlResults($num_elements);
				$num_elements = $num_elements[0]['count(*)'];

				// Set '$this->live_status['steps_number']' property to prepare the 'stepsNavigator()' method
				$steps_number = intval($num_elements/$this->elements_per_step);
				($num_elements%$this->elements_per_step != 0) ? $steps_number++ : '';
				$this->live_status['steps_number'] = $steps_number;

				// Set $offset and $lines
				if ((isset($_GET['step'])) && (formManager_filter::isInteger($_GET['step'])) && ($_GET['step'] != 0))
				{
					if ($_GET['step'] > $this->live_status['steps_number'])
					{
						$current_step = $this->live_status['steps_number'];
					} else {
						$current_step = $_GET['step'];
					}
				}
				else {
					$current_step = 1;
				}

				$offset = ($current_step -1)*$this->elements_per_step;
				$lines  = $this->elements_per_step;
				$query_limit = "LIMIT $offset,$lines";

				$elements = $db->sendMysqlQuery("SELECT $query_fields_list FROM {table_prefix}{$this->table_prefix}element WHERE node_id=$node_id $query_where ORDER BY list_order ASC $query_limit");
				$elements = $db->fetchMysqlResults($elements);
			}

			for ($i=0; $i<count($elements); $i++)
			{
				// 'element' table infos
				$elements_details[$i] = $elements[$i];

				// 'element_item' table infos
				if ($elements_item = $db->selectOne($this->table_prefix."element_item, *, where: element_id=".$elements[$i]['id']))
				{
					foreach($elements_item as $field => $value)
					{
						if ($field != 'element_id') {
							$elements_details[$i][$field] = $value; #Skip element_id field (because we have the id from 'element' table)
						}
					}
				}

				// New! element icon : before you cleanElements() and eventually remove the 'date_modified' info, add this info as a new! icon for recent updated elements
				// Known limitation : this feature will be not available in the home page, wich currently don't call this globalController.
				######
				###### TODO - il faudra rajouter un champs dans la table 'config' : pour pouvoir indiquer la durée pendant laquelle un element est considéré comme nouveau...
				###### Pour le moment, c'est fixer de manière unique à 30 jours !
				######
				if (!isset($elements_details[$i]['new']))
				{
					($elements_details[$i]['date_modified'] > time() - 60*60*24*30) ? $elements_details[$i]['new'] = LANG_COM_GENERIC_NEW_ELEMENT : $elements_details[$i]['new'] = '';
				} else {
					// Security !
					trigger_error("Error occured in : ".__METHOD__." : Unable to create \$elements_details[\$i]['new'], because this key already exists ! You should modify the fields names of your 'element_item' table.");
				}

				// Add $node_id
				$elements_details[$i]['node_id'] = $node_id;

				// Add $node_id_alias
				$key = $this->config_com_node_name."_$node_field";
				if (!isset($elements_details[$i][$key]))
				{
					$elements_details[$i][$key] = $node_alias;
				} else {
					// Security !
					trigger_error("Error occured in : ".__METHOD__." : Unable to create \$elements_details[\$i]['$key'], because this key already exists ! You should modify the fields names of your 'element_item' table.");
				}
			}
		}
		return $elements_details; # Many elements details : $elements_details[$i]['id_alias'], $elements_details[$i]['title'], ...
	}



	// Steps navigator (must be called after elementsGlobalController() method wich is setting the '$this->live_status['steps_number']' property )
	public function stepsNavigator( $node_id = -99 )
	{
		// Steps number
		$steps_number = $this->live_status['steps_number'];
		if ($steps_number <= 1) {
			return '';
		}

		// Current step
		if ((isset($_GET['step'])) && (formManager_filter::isInteger($_GET['step'])) && ($_GET['step'] != 0))
		{
			if ($_GET['step'] > $steps_number)
			{
				$current_step = $steps_number;
			} else {
				$current_step = $_GET['step'];
			}
		}
		else {
			$current_step = 1;
		}

		// Href (using $node_id)
		if ($node_id == -99) {
			$node_id = $this->live_status['node_id']; # init.
		}
		$link = $this->nodeUrlEncoder($node_id);
		if ($this->pageUrlRequest())
		{
			$href = $this->pageUrlRequest().'&amp;'.$link['href'];
		} else {
			$href = $link['href'];
		}

		// Config
		$max_view_steps = 5; # Only odd number >= 3
		$i_min = $current_step - ($max_view_steps-1)/2;
		$i_max = $current_step + ($max_view_steps-1)/2;
		if ($i_min < 1)
		{
			$i_min = 1;
			($i_max<$steps_number) ? $i_max++ : '';
		}
		if ($i_max > $steps_number)
		{
			$i_max = $steps_number;
			($i_min>1) ? $i_min-- : '';
		}

		// Let's go !
		$steps_link = '';

		// Start & Back buttons
		if ($max_view_steps < $steps_number)
		{
			if (($steps_number > $max_view_steps) && ($current_step != 1))
			{
				$steps_link .= '<a href="'.comMenu_rewrite("$href&amp;step=1").'" id="start">&nbsp;</a>';
				$steps_link .= '<a href="'.comMenu_rewrite("$href&amp;step=".($current_step-1)).'" id="back">&nbsp;</a>'."\n";
			} else {
				$steps_link .= '<span id="start">&nbsp;</span><span id="back">&nbsp;</span>'."\n";
			}
		}

		// Steps buttons
		($i_min>1) ? $steps_link .= '&nbsp;...' : '';
		for ($i=$i_min; $i<=$i_max; $i++)
		{
			if ($i == $current_step)
			{
				$steps_link .= '<a href="'.comMenu_rewrite("$href&amp;step=$i").'" id="active"><span>[</span> '.$i.' <span>]</span></a>'."\n";
			} else {
				$steps_link .= '<a href="'.comMenu_rewrite("$href&amp;step=$i").'">&nbsp; '.$i.' &nbsp;</a>'."\n";
			}
		}
		($i_max<$steps_number) ? $steps_link .= '...&nbsp;' : '';

		// Forward & End buttons
		if ($max_view_steps < $steps_number)
		{
			if (($steps_number > $max_view_steps) && ($current_step != $steps_number))
			{
				$steps_link .= '<a href="'.comMenu_rewrite("$href&amp;step=".($current_step+1)).'" id="forward">&nbsp;</a>';
				$steps_link .= '<a href="'.comMenu_rewrite("$href&amp;step=".($steps_number)).'" id="end">&nbsp;</a>'."\n";
			} else {
				$steps_link .= '<span id="forward">&nbsp;</span><span id="end">&nbsp;</span>'."\n";
			}
		}

		// Return
		return "\n<div id=\"generic-navigator\">$steps_link</div>\n"; # generic class
	}



	// Node selector (full form)
	public function nodeSelector( $node_id = -99 )			# TODO - le nodeSelector et le archiveSelector n'intègrent pas la function comMenu_rewrite()
	{
		// Init. parameter
		if ($node_id == -99) {
			$node_id = $this->live_status['node_id'];
		}

		global $g_user_login;

		$html = '';

		// Node selection by a select form { Careful : it use the 'node_id' field (and not the 'id_alias' as usual) }
		$form = new formManager(1,0);
		$html .= $form->form('get', $form->reloadPage(), 'node_selector_');

		// Hiddens fields to reload this page (com=$this->com_name&page=$this->page_name)
		if ($this->redirection === false)
		{
			$html .= $form->hidden('com', $this->com_name);
			$html .= $form->hidden('page', $this->page_name)."\n";
		}
		elseif ($this->redirection != '')
		{
			$redirection = preg_replace('~^\?~', '', $this->redirection);
			$redirection = str_replace('&amp;', '&', $redirection); # Be sure of the request separator
			$redirection = explode('&', $redirection);
			for ($i=0; $i<count($redirection); $i++)
			{
				$key_value = array();
				$key_value = explode('=', $redirection[$i]);
				$html .= $form->hidden($key_value[0], $key_value[1]);
			}
		}

		// Relative selector ?
		if ($this->selector_node_relative)
		{
			$grand_parent_id = $node_id;
		} else {
			$grand_parent_id = 0;
		}

		// Nodes options
		$nodes_options =
			$this->getNodesOptions(
				true, LANG_SELECT_OPTION_ROOT,
				$grand_parent_id,false, 1,$g_user_login->accessLevel()
			);

		// Html select
		$label = $this->translate(LANG_COM_GENERIC_NODE_SELECTION);
		$html .= $form->select($this->urlRequestNode_id(), formManager::selectOption($nodes_options, $node_id), $label);
		$html .= $form->submit('', LANG_BUTTON_SUBMIT, 'submit'); # Submit-button : do not give a name attribut (required for javaScript autoSubmit form to work)

		$html .= $form->end(); // End of Form

		if (count($nodes_options) >= 2)
		{
			return $html;
		} else {
			return false;
		}
	}



	// Url request to get page by node_id : $_GET[$this->urlRequestNode_id()]
	public function urlRequestNode_id( $config_com_node_name = false )
	{
		// Init.
		if ($config_com_node_name === false) {
			$config_com_node_name = $this->config_com_node_name;
		}

		$name_argument = $config_com_node_name.'_id';

		return $name_argument;
	}



	// Url request to get page by element_id : $_GET[$this->urlRequestNode_id()]
	function urlRequestElement_id( $config_com_element_name = false )
	{
		// Init.
		if ($config_com_element_name === false) {
			$config_com_element_name = $this->config_com_element_name;
		}

		$name_argument = $config_com_element_name.'_id';

		return $name_argument;
	}



	// Archive options for select form
	function archiveSelector()								# TODO - le nodeSelector et le archiveSelector n'intègrent pas la function comMenu_rewrite()
	{
		$options =
			array(
				'0' => LANG_COM_GENERIC_ARCHIVE_SELECTION_0,
				'1' => LANG_COM_GENERIC_ARCHIVE_SELECTION_1,
				'2' => LANG_COM_GENERIC_ARCHIVE_SELECTION_2
			);

		$session = new sessionManager(sessionManager::FRONTEND, $this->com_name);
		$options = formManager::selectOption($options, $session->get('archive_selector'), false);

		$html = '';

		// Archive selector
		$form = new formManager(0,0);
		$html .= $form->form('post', $form->reloadPage(), 'archive_selector_');

		$label = $this->translate(LANG_COM_GENERIC_ARCHIVE_SELECTION);
		$html .= $form->select('select', $options, $label);
		$html .= $form->submit('', LANG_BUTTON_SUBMIT, 'submit'); # Submit-button : do not give a name attribut (required for javaScript autoSubmit form to work)

		$html .= $form->end(); // End of Form

		return $html;
	}



	// Display node selector and archive selector
	function displaySelectors( $node_selector = -99, $archive_selector = -99)
	{
		$html = '';

		($node_selector 	== -99) ? $node_selector 	= $this->selector_node 		: '';
		($archive_selector 	== -99) ? $archive_selector = $this->selector_archive 	: '';

		// Is node selector empty ?
		if ($node_selector) {
			$node_selector_html = $this->nodeSelector();
			$node_selector_html or $node_selector = false;
		}

		if ($node_selector || $archive_selector)
		{
			$node_selector && $archive_selector ? $boxes_number = 2 : $boxes_number = 1;

			$wrapper = $this->getBoxesWrapper($boxes_number, $boxes_number, 1, 'generic-selectors'); # generic class

			$html .= $wrapper['header'];

			$node_selector		? $html .= $wrapper['open'].$node_selector_html		.$wrapper['close'] : '';
			$archive_selector	? $html .= $wrapper['open'].$this->archiveSelector().$wrapper['close'] : '';

			$html .= $wrapper['footer'];
		}

		return $html;
	}



	// Is a node and all his parents are authorized (check for 'published' and 'access_level') ?
	public function authorizeNode( $node_id )
	{
		$authorized = true;

		global $g_user_login;

		// Select online, or both (online & archive)
		if ($this->live_archive_status['node'] == 0)
		{
			$query_archived = ' AND, where: archived=0';
		} else {
			$query_archived = ''; # =1 or =2
		}

		global $db;
		$current_node = $node_id; # Notice that if $node_id=0, then $authorized=true
		while (($current_node != 0) && ($authorized))
		{
			$node =
				$db->selectOne(
					$this->table_prefix."node, parent_id, where: id=$current_node AND, ".
					"where: published=1 AND, where: access_level>=".$g_user_login->accessLevel().$query_archived
				);

			if ($node)
			{
				$current_node = $node['parent_id'];
			} else {
				$authorized = false;
			}
		}
		return $authorized;
	}



	/**
	 * MVC : CONTROLLER (Specific)
	 */

	// Specific 'node_item' controller
	public function nodeSpecificController( $node_details, $navig = false ) # $node_details contain all 'node_item' table fields (except node_id) and some 'node' table fields (id, id_alias, level)
	{
		$html = '';

		if (isset($node_details['id']))
		{
			// Debug mode
			$html .= $this->debugSpecificController($node_details, 'Node HTML keys');

			// Default name for the tmpl_*.html file
			$tmpl_name_default = 'tmpl_'.$this->config_com_node_name;

			// List of template names possibilities, ordered by priority
			$tmpl_name = array($tmpl_name_default);

			if ($navig) {
				array_unshift($tmpl_name, $tmpl_name_default.'_navig');
			}

			// Customize this node
			$this->nodeSpecificController_customize($node_details, $navig);

			// Get the final Html output !
			$html .= $this->contentView($tmpl_name, $node_details);

			// Debug
			if ($this->config_debug) {
				echo '<p style="color:green;background-color:#F0FFF0;padding:5px;margin:15px 0;"><b>Expected HTML templates for '.__METHOD__.' : </b><br />'.implode('<br />', $tmpl_name).'</p>';
			}
		}

		return $html;
	}



	// Specific 'element_item' controller
	public function elementsSpecificController( $elements_details, $single_element = false, $home_page = false ) # $elements_details contain all 'element_item' table fields (except element_id) and some 'element' table fields (id, id_alias, date_creation,date_modified,author,hits)
	{
		$html = '';

		if (count($elements_details))
		{
			// Debug mode
			$html .= $this->debugSpecificController($elements_details[0], 'Element HTML keys <span style="font-weight:normal;">(first one if many)</span>');

			// Use wrapper to display elements with columns
			if (!$home_page || TRUE) # TODO - TEMPORAIRE !!!!!!!!!!!!!!!!
			{
				$boxes_per_row = $this->elements_per_row;
				$boxes_wrapper = $this->elements_wrapper;
			} else {
				#$boxes_per_row = $this->home_per_row; # TODO - A SUPPRIMER
				#$boxes_wrapper = $this->home_wrapper; # TODO - A SUPPRIMER
			}
			$wrapper = $this->getBoxesWrapper(count($elements_details), $boxes_per_row, $boxes_wrapper, $this->com_name.'-wrapper-elements');

			// Default name for the tmpl_*.html file
			$tmpl_name_default = 'tmpl_'.$this->config_com_element_name;

			// List of template names possibilities, ordered by priority
			$tmpl_name = array($tmpl_name_default);

			if ($home_page) {
				array_unshift($tmpl_name, $tmpl_name_default.'_home');
			}
			elseif ($single_element) {
				array_unshift($tmpl_name, $tmpl_name_default.'_single');
			}

			$current = 0;
			$html .= $wrapper['header']; # Use columns process
			for ($i=0; $i<count($elements_details); $i++)
			{
				if ($elements_details[$i]['id'])
				{
					if ($home_page)
					{
						if ($i == 0) {
							array_unshift($tmpl_name, $tmpl_name_default.'_home_first');
						}
						elseif ($i == 1) {
							array_shift($tmpl_name); # Remove '_home_first' template
						}
					}

					// Customize this element
					$this->elementsSpecificController_customize($elements_details[$i], $single_element, $home_page);

					if ($boxes_wrapper) # Use columns process
					{
						if ($i < count($elements_details) - (count($elements_details)%$boxes_per_row))
						{
							$wrapper_open = $wrapper['open'];
						} else {
							$wrapper_open = $wrapper['open_last_row'];
						}
					}
					else {
						$wrapper_open = $wrapper['open'];
					}

					// Complete the final Html output !
					$html .= $wrapper_open. $this->contentView($tmpl_name, $elements_details[$i]) .$wrapper['close']; # Main content & Use columns process

					if ((++$current == $boxes_per_row) && ($i<count($elements_details)-1)) # Use columns process
					{
						$html .= $wrapper['row_sep'];
						$current = 0;
					}

					// Debug
					if ($this->config_debug) {
						echo '<p style="color:green;background-color:#F0FFF0;padding:5px;margin:15px 0;"><b>Expected HTML templates for '.__METHOD__.' : </b><br />'.implode('<br />', $tmpl_name).'</p>';
					}
				}
				else {
					echo '<p style="color:red;">Error occured : <br />You try to display an element using elementsSpecificController($elements_details) method of comGeneric_ class. <br />But the $elements variable don\'t seems to be valid !</p>';
				}
			}
			$html .= $wrapper['colspan'].$wrapper['footer']; # Use columns process
		}

		return $html;
	}



	protected function debugSpecificController( $data, $title )
	{
		if ($this->config_debug)
		{
			$keys = array();
			reset($data);
			foreach($data as $field =>$content) {
				$keys[] = '{'.$field.'}';
			}

			$table = new tableManager($keys);
			if ($table->dim()) {
				$table->header(array($title));
				return $table->html();
			}
		}
	}



	/**
	 * MVC : all-in-one for HOME PAGES !
	 */

	public function homeContentModel( $home_nde_id, $tmpl_name_specific = '' )
	{
		global $db;

		// Get the requested 'home_nde' details
		if ($this->node_item_field)
		{
			$db_query =	$this->table_prefix."home_nde, *, where: id=$home_nde_id, join: id>;" .
						$this->table_prefix."home_nde_item, join: <home_nde_id;";
		} else {
			$db_query =	$this->table_prefix."home_nde, *, where: id=$home_nde_id";
		}
		$home_nde = $db->selectOne($db_query);

		// Get the list of 'home_elm' elements (and the node_id of each one of them)
		$home_elm =
			$db->select(
					$this->table_prefix."home_elm, elm_id,elm_order(asc), where: elm_published=1 AND, where: home_nde_id=$home_nde_id, join: elm_id>; " .
					$this->table_prefix."element, node_id, join: <id"
				);

		// Let's go !
		$elements_details = array();
		for ($i=0; $i<count($home_elm); $i++) {
			$elements_details = array_merge($elements_details, $this->elementsGlobalController($home_elm[$i]['node_id'], $home_elm[$i]['elm_id']));
		}
		$this->cleanElements($elements_details, $home_nde);
		$home_elm_html = $this->elementsSpecificController($elements_details, false, true);

		// Check that 'home_elm_html' is a free keyword !
		if (isset($home_nde['home_elm_html'])) {
			trigger_error("The 'home_nde_item' table contains a field using the following reserved keyword <b>'home_elm_html'</b> !");
		}
		$home_nde['home_elm_html'] = $home_elm_html;

		// Templates list
		$tmpl_name = array('tmpl_home_'.$home_nde['id_alias'], 'tmpl_home');
		if ($tmpl_name_specific) {
			array_unshift($tmpl_name, $tmpl_name_specific);
		}

		return $this->contentView($tmpl_name, $home_nde);
	}



	/*
	 * Use this method to call any home page by it's id_alias
	 * Code example : $this->homeContentModel( $this->getHomeNdeID('my_alias') );
	 */
	public function getHomeNdeID( $id_alias )
	{
		global $db;
		return $db->selectOne($this->table_prefix.'home_nde, id, where: id_alias='.$db->str_encode($id_alias), 'id');
	}



	// This is reverse method of : $this->getHomeNdeID( $id_alias );
	public function getHomeNdeIdAlias( $id )
	{
		global $db;
		return $db->selectOne($this->table_prefix."home_nde, id_alias, where: id=$id", 'id_alias');
	}



	public function getDefaultHomeNdeID()
	{
		global $db;
		return $db->selectOne($this->table_prefix."home_nde, id, where: default_nde=1", 'id');
	}



	public function homeNdeSelector()
	{
		// Availables home pages
		$home_nde_options = $this->getHomeNdeOptions(true); # selected by 'id_alias'

		// Is there's more than one home page ?
		if (count($home_nde_options) == 1) {
			return;
		}

		// Session
		$session = new sessionManager(sessionManager::FRONTEND, $this->com_name);
		$session->init('home_nde_selector', $this->getDefaultHomeNdeID()); # Carefull : this is the 'id' (not the 'id_alias)

		// Update session ( form process simplified, it's missing the condition : formManager::isSubmitedForm('home_nde_selector_', 'get') )
		$filter = new formManager_filter();
		if ($home_nde_id_alias = $filter->requestValue('alias', 'get')->getID()) {
			if (array_key_exists($home_nde_id_alias, $home_nde_options)) {
				$session->set('home_nde_selector', $this->getHomeNdeID($home_nde_id_alias));
			}
		}

		// Form
		$form = new formManager(0,0);
		$html = $form->form('get', $form->reloadPage(), 'home_nde_selector_');

		// Hiddens fields to reload the home page
		$html .= $form->hidden('com', $this->com_name);
		$html .= $form->hidden('page', 'home')."\n";

		$html .= $form->select('alias', formManager::selectOption($home_nde_options, $this->getHomeNdeIdAlias($session->get('home_nde_selector'))));
		$html .= $form->submit('', LANG_BUTTON_SUBMIT, 'submit'); # Submit-button : do not give a name attribut (required for javaScript autoSubmit form to work)
		$html .= $form->end();

		return "\n<div class=\"generic-selectors\">$html</div>\n";
	}



	/*
	 * Use this method to call the home page wich is recorded into the session
	 * Code example : $this->homeContentModel( $this->homeNdeSession() );
	 */
	public function homeNdeSession()
	{
		$session = new sessionManager(sessionManager::FRONTEND, $this->com_name);
		$session->init('home_nde_selector', $this->getDefaultHomeNdeID());

		return $session->get('home_nde_selector'); # Known limitation : php error if this home page is deleted from the administration !
	}



	////////////////
	// Cutomization

	/**
	 * For each component you create, using the comGeneric_frontend class,
	 * you should redefine those 2 methods, depends of your 'node_item' and 'element_item' tables structures :
	 *
	 * -> nodeSpecificController_customize()
	 *			$node_details[{fields}] : contain all availables {fields} for 'node' and 'node_item' table
	 *
	 * -> elementsSpecificController_customize()
	 *			$element_detail[{fields}] : contain all availables {fields} for 'element' and 'element_item' table
	 */

	// Customize the specific 'node_item' controller
	public function nodeSpecificController_customize( &$node_details, $navig )
	{
		/**
		 * Costumize 'node' fields
		 */

		$link = $this->nodeUrlEncoder($node_details['id']);
		if ($this->pageUrlRequest())
		{
			$href = $this->pageUrlRequest().'&amp;'.$link['href'];
		} else {
			$href = $link['href'];
		}
		$href = comMenu_rewrite($href);

		$node_details['id_alias'] = "<a href=\"$href\">".$node_details['id_alias']."</a>";

		/**
		 * Costumize 'node_item' fields
		 */

		# ...
	}



	// Customize the specific 'element_item' controller
	public function elementsSpecificController_customize( &$element_detail, $single_element, $home_page )
	{
		/**
		 * Costumize 'element' fields
		 */

		// id_alias
		$link = $this->elementUrlEncoder($element_detail['id']);
		if ($this->pageUrlRequest())
		{
			$href = $this->pageUrlRequest().'&amp;'.$link['href'];
		} else {
			$href = $link['href'];
		}
		$href = comMenu_rewrite($href);

		$element_detail['id_alias'] = "<a href=\"$href\">".$element_detail['id_alias']."</a>";

		// date_creation, date_modified (dates format)
		if ($element_detail['date_creation'] !== '') {
			$element_detail['date_creation'] = getTime($element_detail['date_creation'], 'time=no');
		}
		if ($element_detail['date_modified'] !== '') {
			$element_detail['date_modified'] = getTime($element_detail['date_modified'], 'time=no');
		}

		/**
		 * Costumize 'element_item' fields
		 */

		# ...
	}

	// End of Cutomization
	//////////////////////



	/**
	 * MVC : VIEW
	 */

	// Use wrapper to display boxes with columns
	public function getBoxesWrapper( $boxes_number, $boxes_per_row, $boxes_wrapper, $class )
	{
		$last_row_boxes_number = $boxes_number%$boxes_per_row;

		// Simple wrapper
		if (($boxes_per_row == 1) || ($boxes_number == 1))
		{
			$wrapper_open 			= '';
			$wrapper_open_last_row 	= '';
			$wrapper_close 			= '';
			$row_sep 				= '';
			$wrapper_colspan 		= '';

			if ($boxes_wrapper)
			{
				$wrapper_header 	= "\n".'<div class="'.$class.' clearfix">'."\n"; # clearfix for ie6
				$wrapper_footer 	= "\n\n".'</div>'."\n";
			} else {
				$wrapper_header 	= "\n".'<table width="100%" cellspacing="0" cellpadding="0" border="0" class="'.$class.' clearfix"><tr valign="top"><td>'."\n";
				$wrapper_footer 	= "\n".'</td></tr></table>'."\n\n";
			}
		}
		// Tag type : <div>  ($boxes_wrapper = 1)
		elseif ($boxes_wrapper)
		{
			if ($boxes_number < $boxes_per_row) {
				$fact = $last_row_boxes_number;
			}
			elseif ($last_row_boxes_number != 0) {
				$fact = $boxes_per_row*$last_row_boxes_number;
			}
			else {
				$fact = $boxes_per_row;
			}

			/* OLD VERSION - TODO : je comprends pas pourquoi j'ai mis la condition ?!?
			$delta = 0;
			if ($boxes_per_row == 1) {
				$delta = 0;  # Delta : a little adaptation of the width (if necessary with some user agents)
			}*/
			$delta = 0; /* Nouvelle version : nécessaire dans certains cas pour le pata-caisse d'ie6... sans commentaire... */

			$width_max = intval((100-$delta)/$fact) * $fact;
			$width     = intval($width_max/$boxes_per_row);
			if ($last_row_boxes_number != 0)
			{
				$width_last_row = intval($width_max/$last_row_boxes_number);
			} else {
				$width_last_row = $width;
			}

			$wrapper_header 		= "\n".'<div class="'.$class.' clearfix">'."\n"; # clearfix for ie6
			$wrapper_open 			= '<div style="float:left;width:'.$width         .'%;">';
			$wrapper_open_last_row 	= '<div style="float:left;width:'.$width_last_row.'%;">';
			$wrapper_close 			= '</div>'."\n";
			$row_sep 				= ''; # '<div style="clear:left;"></div>'; # Add this if necessary
			$wrapper_colspan 		= ''; # only for compatibility
			$wrapper_footer 		= "\n\n".'<div style="clear:left;"></div>'.'</div>'."\n";
		}
		// Tag type : <table>  ($boxes_wrapper = 0)
		else
		{
			if ($last_row_boxes_number != 0)
			{
				$colspan = $boxes_per_row - $last_row_boxes_number;
			} else {
				$colspan = 0;
			}

			$wrapper_header 		= "\n".'<table width="100%" cellspacing="0" cellpadding="0" border="0" class="'.$class.'"><tr valign="top">'."\n";
			$wrapper_open 			= '<td width="'.(100/$boxes_per_row).'%">';
			$wrapper_open_last_row 	= ''; # only for compatibility
			$wrapper_close 			= '</td>'."\n";
			$row_sep 				= '</tr><tr valign="top">'."\n";
			if ($colspan)
			{
				$wrapper_colspan = '<td colspan="'.$colspan.'">&nbsp;</td>';
			} else {
				$wrapper_colspan = '';
			}
			$wrapper_footer 		= "\n".'</tr></table>'."\n\n";
		}

		// Return
		$wrapper = array(
			'header'		=>	$wrapper_header,
			'open'			=>	$wrapper_open,
			'open_last_row'	=>	$wrapper_open_last_row,
			'close'			=>	$wrapper_close,
			'row_sep'		=>	$row_sep,
			'colspan'		=>	$wrapper_colspan,
			'footer'		=>	$wrapper_footer
		);
		return $wrapper;
	}



	// Html template to display contents ($elements_details, $nodes_details)
	public function contentView( $tmpl_name, $replacements, $debug = false )
	{
		// 'tmpl_*' file extensions
		$ext = array('php', 'html'); # Php template has priority !

		// Where can be located all 'tmpl_*' files ?
		$dir = array_values(array_unique(array(comTemplate_getCurrent(), comTemplate_getDefault(), 'default'))); # First priority is to match the $tmpl_name into any $dir ; second, try with alternative $tmpl_name

		// 'tmpl_*' full path pattern
		$tmpl_path = sitePath()."/components/com_$this->com_name/tmpl/{dir}/{tmpl_name}.{ext}";

		// Test locations
		$n = 0;
		do {
			$this_name = str_replace('{tmpl_name}', $tmpl_name[$n++], $tmpl_path);

			$e = 0;
			do {
				$this_name_ext = str_replace('{ext}', $ext[$e++], $this_name);

				$d = 0;
				do {
					$this_dir_name_ext = str_replace('{dir}', $dir[$d++], $this_name_ext);
				}
				while (!file_exists($this_dir_name_ext) && ($d<count($dir)));
			}
			while(!file_exists($this_dir_name_ext) && ($e<count($ext)));
		}
		while (!file_exists($this_dir_name_ext) && ($n<count($tmpl_name)));

		// Here it is !
		$tmpl_path = $this_dir_name_ext;

		// Debug mode ?
		($debug || $this->config_debug) ? $debug = true : '';

		// Process replacements
		$template = new templateManager($debug);
		$tmpl_html = $template->setTmplPath($tmpl_path)->setReplacements($replacements)->process();
		return $tmpl_html;
	}


}


?>