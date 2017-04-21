<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


///////////////
// Super-Class

class comGeneric_
{
	// Component setting infos (1)
	protected	$table_prefix; 				# $this->table_prefix.'config', $this->table_prefix.'node', $this->table_prefix.'item', ...

	protected	$node_item_field;			# Use this field from 'node_item' table (specific), instead of 'id_alias' from 'node' table (generic-test). Also used in 'home_nde_item' and 'home_nde' tables in the same way.
	protected	$element_item_field;		# Use this field from 'element_item' table, instead of 'id_alias' from 'element' table

	protected	$lang_node_item_field;		# $node_item_field translation (will appear in the header table of start view)
	protected	$lang_element_item_field;	# $element_item_field translation

	/*
	 * $com_name is a frontend property. Then, it was expected to be defined into the sub-class (comGeneric_frontend).
	 * But, because it must be set by the 'com_setup.php' script. Then $com_name property have been included here, in the super-class.
	 */
	protected	$com_name;					# Used to set the 'action' argument of forms in frontend. Ex. : '/index.php?com='.$this->com_name.'&page=index')
											# Used as space name for the session variables
											# Used to get the directory of 'tml_*.html' files (wich are necessary to display nodes and elements). Ex. : '/components/com_$this->com_name/tmpl/default/tmpl_node.html'

	protected	$lang_node;					# 'node' translation for the current component
	protected	$lang_element;				# 'element' translation for the current component

	// 'config' table infos (2)
	protected	$config_com_node_name;		# ex. : node
	protected	$config_com_element_name;	# ex. : element

	protected	$config_debug;

	// Archive (3)
	protected	$live_archive_status;		# $live_archive_status['node'];    Values: 0= view online ; 2= view both
											# $live_archive_status['element']; Values: 0= view online ; 1= view archive ; 2 = view both



	// Load the 'com_setup.php' configuration file, which is always located in the parent directory of current script
	static public function comSetupPath( $__FILE__ )
	{
		$pathinfo	= pathinfo($__FILE__);
		$parent_dir	= preg_replace('~(/|\\\)[\.a-zA-Z_\d\-]*$~', '', $pathinfo['dirname']); # Notice : '/' for Linux and '\\\' for Windows

		return "$parent_dir/com_setup.php"; # Return the path of the config file
	}



	public function __construct( $init )
	{
		$get_class_vars = get_class_vars('comGeneric_');

		// Component setting infos (1)
		foreach($init as $property => $default)
		{
			if (array_key_exists($property, $get_class_vars))
			{
				$this->$property = $default;
			} else {
				trigger_error('The <strong>"com_setup.php"</strong> script, try to initialize a class property wich does not exist !');
			}
		}

		// 'config' table infos (2)
		global $db;
		if ($config = $db->selectOne($this->table_prefix.'config, com_node_name,com_element_name,debug'))
		{
			$this->config_com_node_name				= $config['com_node_name'	];
			$this->config_com_element_name			= $config['com_element_name'];

			$this->config_debug						= $config['debug'];

			// Set default values archive status (3)
			$this->live_archive_status['node'	]	= 0;
			$this->live_archive_status['element']	= 0;
		}
		else {
			trigger_error("Missing configuration for the following table : <strong>{$this->table_prefix}config</strong>");
		}

		// Debug mode : Check super-class properties
		if ($this->config_debug)
		{
			$properties = array();
			reset($get_class_vars);
			foreach($get_class_vars as $property => $default)
			{
				$properties[$property] = $this->$property;
				if (array_key_exists($property, $init)) {
					$properties[$property] .= ' <span style="color:red;">(*)</span>';
				}
			}
			echo '<br /><strong>Super-Class properties :</strong><br /><span style="color:red;">(*) Initialized by "com_setup.com" script</span>';
			$table = new tableManager($properties, array('Value'), 'Property');
			echo $table->html(1).'<br />';
		}
	}



	/**
	 * Accessors
	 */

	// Setting (1)
	public function getTablePrefix()			{ return $this->table_prefix;				}
	public function getNodeItemField()			{ return $this->node_item_field;			}
	public function getElementItemField()		{ return $this->element_item_field;			}
	public function getLangNodeItemField()		{ return $this->lang_node_item_field;		}
	public function getLangElementItemField()	{ return $this->lang_element_item_field;	}
	public function getComName()				{ return $this->com_name;					}
	public function getLangNode()				{ return $this->lang_node;					}
	public function getLangElement()			{ return $this->lang_element;				}

	// Config (2)
	public function getComNodeName()			{ return $this->config_com_node_name;		}
	public function getComElementName()			{ return $this->config_com_element_name;	}
	public function getDebugMode() 				{ return $this->config_debug;				}

	// Others (3)
	public function getLiveArchiveStatus( $key ) { return $this->live_archive_status[$key]; }

	public function setLiveArchiveStatus( $key, $value )
	{
		if (($key == 'node') || ($key == 'element'))
		{
			$this->live_archive_status[$key] = $value;
		} else {
			trigger_error("Invalid parameter \$key=$key in ".__METHOD__." (expected values : 'node' or 'element').");
		}

		return $value;
	}



	/**
	 * Translate the strings '{node}' and '{element}'
	 */

	public function translate( $lang )
	{
		$return = str_replace( '{node}'		, $this->lang_node		, $lang		);
		$return = str_replace( '{element}'	, $this->lang_element	, $return	);
		return ucfirst($return);
	}



	/**
	 * Get nodes-tree
	 */

	// Return nodes-tree
	public function getNodes( $parent_id = 0,$excluded_id = false, $published = false,$access_level = false, $level_relative = 0 )
	{
		static $nodes_tree = array();
		if ($level_relative == 0) {
			$nodes_tree = array(); # Reset when begining
		}

		// Set prefix
		$path_prefix = '';
		for ($i=0; $i<$level_relative; $i++) {
			$path_prefix .= '.....';
		}
		$path_prefix .= ' ';

		// Select only published and required access level
		$published		? $query_published		= ' AND, where: published=1'					: $query_published		= '';
		$access_level	? $query_access_level	= " AND, where: access_level>=$access_level"	: $query_access_level	= '';

		// Select online, or both (online & archive)
		if ($this->live_archive_status['node'] == 0)
		{
			$query_archived = ' AND, where: archived=0';
		} else {
			$query_archived = ''; # =1 or =2
		}

		// Get node childs
		if ($this->node_item_field)
		{
			// Use a Specific field from 'node_item' table
			$db_query = $this->table_prefix."node, id,id_alias,level,access_level,published,archived,list_order(asc), where: parent_id=$parent_id".$query_published.$query_access_level.$query_archived.", join: id>;" .
						$this->table_prefix."node_item, $this->node_item_field, join: <node_id;";
		} else {
			// Use a Generic-test field from 'node' table
			$db_query = $this->table_prefix."node, id,id_alias,level,access_level,published,archived,list_order(asc), where: parent_id=$parent_id".$query_published.$query_access_level.$query_archived;
		}

		global $db;
		if ($node = $db->select($db_query))
		{
			for ($i=0; $i<count($node); $i++)
			{
				if ($node[$i]['id'] != $excluded_id) # Exclude this node, including his sub-nodes
				{
					$temp = array();
					$temp['id'] 	= $node[$i]['id'];
					$temp['level'] 	= $node[$i]['level']; # We need this once, to get the maximum level value of subnodes

					if ($this->node_item_field)
					{
						$temp_path_next = $path_prefix.$node[$i][$this->node_item_field];
						$temp[$this->node_item_field] = $temp_path_next;
					} else {
						$temp_path_next = $path_prefix.$node[$i]['id_alias'];
						$temp['id_alias'] = $temp_path_next;
					}

					$temp['list_order'	] = 2*$i+1;
					$temp['access_level'] = $node[$i]['access_level'];
					$temp['published'	] = $node[$i]['published'];
					$temp['archived'	] = $node[$i]['archived'];

					// Add this node
					array_push($nodes_tree, $temp);

					// Go to next node
					$this->getNodes($node[$i]['id'],$excluded_id, $published,$access_level, $level_relative+1);
				}
			}
		}
		return $nodes_tree;
	}



	// Nodes-tree form options
	public function getNodesOptions(	$view_num_elements = false, $option_root = LANG_SELECT_OPTION_ROOT,
										$parent_id=0,$excluded_node=false, $published=false,$access_level=false )
	{
		$nodes_tree = $this->getNodes($parent_id, $excluded_node, $published, $access_level);

		if ($this->node_item_field)
		{
			$node_item_field = $this->node_item_field;
		} else {
			$node_item_field = 'id_alias';
		}

		$options = array();

		// Root option
		if ($option_root) {
			$options['0'] = $option_root;
		}

		// Select only published and required access level
		$published		? $query_published		= ' AND, where: published=1'					: $query_published		= '';
		$access_level	? $query_access_level	= " AND, where: access_level>=$access_level"	: $query_access_level	= '';

		// Select online, archive or both
	    if ($this->live_archive_status['element'] == 0) {
	    	$query_archived = ' AND, where: archived=0';
	    }
		elseif ($this->live_archive_status['element'] == 1) {
			$query_archived = ' AND, where: archived=1';
		}
		else {
			$query_archived = ''; # =2
		}

		global $db;
		for ($i=0; $i<count($nodes_tree); $i++)
		{
			// How many elements is there for this node ?
			if ($view_num_elements)
			{
				$num_elements = $db->selectCount($this->table_prefix.'element, where: node_id='.$nodes_tree[$i]['id'].$query_published.$query_access_level.$query_archived);
				($num_elements != 0) ? $num_elements = " ($num_elements)" : $num_elements = '';
			} else {
				$num_elements = '';
			}
			$options[$nodes_tree[$i]['id']] = $nodes_tree[$i][$node_item_field].$num_elements;
		}

		return $options;
	}



	/**
	 * Get Elements
	 */

	// Return elements
	public function getElements( $node_id ) # TODO - il faut tenir compte de la date de création de l'item ANTIDATEE !!!! Voir aussi elementsSumary() ...
	{
		$elements_list = array();

		// Select online, archive or both
		if ($this->live_archive_status['element'] == 0) {
			$query_archived = ' AND, where: archived=0';
		}
		elseif ($this->live_archive_status['element'] == 1) {
			$query_archived = ' AND, where: archived=1';
		}
		else {
			$query_archived = ''; # =2
		}

		if ($this->element_item_field)
		{
			$db_query = $this->table_prefix."element, id,id_alias,access_level,published,archived,list_order(asc),date_online,date_offline,date_creation,author_id, where: node_id=$node_id".$query_archived.", join: id>;" . # Global
						$this->table_prefix."element_item, $this->element_item_field, join: <element_id;"; # Specific
		} else {
			$db_query = $this->table_prefix."element, id,id_alias,access_level,published,archived,list_order(asc),date_online,date_offline,date_creation,author_id, where: node_id=$node_id".$query_archived; # For generic-test
		}

		global $db;
		if ($element = $db->select($db_query))
		{
			$mktime = time();
			for ($i=0; $i<count($element); $i++)
			{
				$temp = array();
				$temp['id'] 			= $element[$i]['id'];

				if ($this->element_item_field)
				{
					$temp[$this->element_item_field] = $element[$i][$this->element_item_field];		# Specific
				} else {
					$temp['id_alias'] = $element[$i]['id_alias'];									# For generic-test
				}

				$temp['list_order'	] = 2*$i+1;
				$temp['access_level'] = $element[$i]['access_level'	];
				$temp['published'	] = $element[$i]['published'	];
				$temp['archived'	] = $element[$i]['archived'		];

				if ($element[$i]['date_online'])
				{
					if ($element[$i]['date_online'] < $mktime)
					{
						$temp['date_online'] = getTime($element[$i]['date_online'], 'time=no');
					} else {
						$temp['date_online'] = '<span style="color:red;">'.getTime($element[$i]['date_online'], 'time=no').'</span>'; # still not online
					}
				}
				else {
					$temp['date_online'] = '';
				}

				$expired = false;
				if ($element[$i]['date_offline'])
				{
					if ($element[$i]['date_offline'] > $mktime)
					{
						$temp['date_offline'] = getTime($element[$i]['date_offline'], 'time=no');
					} else {
						$temp['date_offline'] = '<span style="color:red;">'.getTime($element[$i]['date_offline'], 'time=no').'</span>'; # already offline
						$expired = true;
					}
				}
				else {
					$temp['date_offline'] = '';
				}

				$temp['date_creation'] = getTime($element[$i]['date_creation'], 'time=no');

				$temp['author'] = $db->selectOne('user, username, where: id='.$element[$i]['author_id'], 'username');

				$temp['expired'] = $expired;

				array_push($elements_list, $temp);
			}
		}
		return $elements_list;
	}



	// Return nodes-tree with/without elements (this is a compilation of getNodes and getElements methods, with some modifications)
	public function summaryNodes(	$show_elements = true,
									$parent_id=0,$excluded_node_id=false, $published=false,$access_level=false,
									$level_relative = 0, $path_full = '' )
	{
		static $summary = array();
		if ($level_relative == 0) {
			$summary = array(); # Reset when begining
		}

		// Set prefix (full_path)
		if ($level_relative == 0) {
			$path_prefix = '';
		} else {
			$path_prefix = "$path_full | ";
		}

		// Select only published and required access level
		$published		? $query_published		= ' AND, where: published=1'					: $query_published		= '';
		$access_level	? $query_access_level	= " AND, where: access_level>=$access_level"	: $query_access_level	= '';

		// Select online, or both (online & archive)
		if ($this->live_archive_status['node'] == 0) {
			$query_archived = ' AND, where: archived=0';
		} else {
			$query_archived = ''; # =1 or =2
		}

		// Get node childs
		if ($this->node_item_field)
		{
			$db_query = $this->table_prefix."node, id,id_alias, level,list_order(asc), where: parent_id=$parent_id".$query_published.$query_access_level.$query_archived.", join: id>;" .
						$this->table_prefix."node_item, $this->node_item_field, join: <node_id;";
		} else {
			$db_query = $this->table_prefix."node, id,id_alias, level,list_order(asc), where: parent_id=$parent_id".$query_published.$query_access_level.$query_archived;
		}

		global $db;
		if ($node = $db->select($db_query))
		{
			// id_alias or $this->node_item_field
			$this->node_item_field ? $key = $this->node_item_field : $key = 'id_alias';

			for ($i=0; $i<count($node); $i++)
			{
				if ($node[$i]['id'] != $excluded_node_id)
				{
					$temp = array();
					$temp['type'	] = 'node';
					$temp['id'		] = $node[$i]['id'];

					$temp_path_next = $path_prefix.$node[$i][$key];
					$temp['id_alias'] = $temp_path_next;

					array_push($summary, $temp);

					// Update prefix
					$path_next = $temp_path_next;
					$element_prefix = "$path_next | ";

					// Get elements
					if ($show_elements) {
						$summary = array_merge($summary, $this->summaryElements($node[$i]['id'], $published, $access_level));
					}

					// Go to next node
					$this->summaryNodes($show_elements, $node[$i]['id'],$excluded_node_id, $published,$access_level, $level_relative+1,$path_next);
				}
			}
		}
		return $summary;
	}



	public function summaryElements( $node_id, $published = false, $access_level = false )
	{
		$summary = array();

		// Select only published and required access level
		$published		? $query_published		= ' AND, where: published=1'					: $query_published		= '';
		$access_level	? $query_access_level	= " AND, where: access_level>=$access_level"	: $query_access_level	= '';

		// Select online, archive or both
		if ($this->live_archive_status['element'] == 0) {
			$query_archived = ' AND, where: archived=0';
		}
		elseif ($this->live_archive_status['element'] == 1) {
			$query_archived = ' AND, where: archived=1';
		}
		else {
			$query_archived = ''; # =2
		}

		if ($this->element_item_field)
		{
			$db_query = $this->table_prefix."element, id,id_alias, list_order(asc), where: node_id=$node_id".$query_published.$query_access_level.$query_archived.", join: id>;" .
						$this->table_prefix."element_item, $this->element_item_field, join: <element_id;";
		} else {
			$db_query = $this->table_prefix."element, id,id_alias, list_order(asc), where: node_id=$node_id".$query_published.$query_access_level.$query_archived;
		}

		global $db;
		if ($element = $db->select($db_query))
		{
			// First, find the node_id full path...
			$node_full_path = $this->nodeFullPath($node_id);

			// id_alias or $this->element_item_field
			$this->element_item_field ? $key = $this->element_item_field : $key = 'id_alias';

			for ($i=0; $i<count($element); $i++)
			{
				$temp = array();
				$temp['type'	] = 'element';
				$temp['id'		] = $element[$i]['id'];
				$temp['id_alias'] = $node_full_path.$element[$i][$key];

				array_push($summary, $temp);
			}
		}
		return $summary;
	}



	public function nodeFullPath( $node_id, $sep = '|', $end_sep = true )
	{
		$full_path = '';

		$path = array();
		global $db;

		do {
			if ($this->node_item_field)
			{
				$db_query =	$this->table_prefix."node, parent_id, where: id=$node_id, join: id>; " .
							$this->table_prefix."node_item, $this->node_item_field AS id_alias, join: <node_id";
			} else {
				$db_query =	$this->table_prefix."node, parent_id, id_alias, where: id=$node_id";
			}

			if ($node = $db->selectOne($db_query))
			{
				$path[]		= $node['id_alias' ];
				$node_id	= $node['parent_id'];
			} else {
				break;
			}
		}
		while ($node_id != 0);

		for ($i=count($path)-1; $i>=0; $i--) {
			$full_path .= $path[$i];
			if ($i != 0 || $end_sep) {
				$full_path .= " $sep ";
			}
		}
		return $full_path;
	}



	/**
	 * Check the visibility of an element (if is published and not-archived and online, ...)
	 */

	public function isVisibleElement( $id, $specified_access_level = NULL )
	{
		global $db;
		if ($element = $db->selectOne($this->table_prefix."element, access_level,published, date_online,date_offline, archived,  where: id=$id"))
		{
			$access_level 	= $element[ 'access_level'	];
			$published 		= $element[ 'published'		];
			$date_online 	= $element[ 'date_online'	];
			$date_offline 	= $element[ 'date_offline'	];
			$archived 		= $element[ 'archived'		];

			// If no access_level specified, take the one of the current user
			if (!isset($specified_access_level))
			{
				global $g_user_login;
				$specified_access_level = $g_user_login->accessLevel();
			}

			if ( ($access_level >= $specified_access_level) && ($published) && (!$archived) && (comGeneric_::checkDates($date_online, $date_offline)) ) {
				return true;
			} else {
				return false;
			}
		}
		else {
			return NULL; # this $id doesn't exist !
		}
	}



	/**
	 * Static method: a very simple method to compare date_online and date_offline with current time()
	 */

	static function checkDates( $date_online = false, $date_offline = false )
	{
		$mktime = time();

		if ( (!$date_online || ($date_online < $mktime)) && (!$date_offline || ($date_offline > $mktime)) )
		{
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Get date of the last modified element
	 */

	public function getDateOfLastUpdate()
	{
		global $db;
		if ($date_modified = $db->selectOne($this->table_prefix.'element, date_modified(desc); limit: 1', 'date_modified'))
		{
			return getTime($date_modified, 'time=no');
		}
	}



	/**
	 * Get home_nde list
	 */

	public function getHomeNde()
	{
		/**
		 * Important notice :
		 * When 'home_nde_item' table is available, the name of the specific field must be $this->node_item_field (same as specific field name of 'node_item' table)
		 */

		global $db;
		if ($this->node_item_field)
		{
			$db_query = $this->table_prefix."home_nde, default_nde(desc), [id],id_alias, nodes_id, show_date_creation,show_date_modified,show_author_id,show_hits, join: id>;" .
						$this->table_prefix."home_nde_item, $this->node_item_field(asc), join: <home_nde_id;";
		} else {
			$db_query = $this->table_prefix."home_nde, default_nde(desc), [id],id_alias(asc), nodes_id, show_date_creation,show_date_modified,show_author_id,show_hits";
		}

		if ($home_nde = $db->select($db_query))
		{
			return $home_nde;
		} else {
			return array();
		}
	}



	public function getHomeNdeOptions( $select_by_id_alias = false )
	{
		$options = array();

		$home_nde = $this->getHomeNde();
		foreach($home_nde as $id => $details)
		{
			if (!$select_by_id_alias)
			{
				$key = $id;
			} else {
				$key = $details['id_alias'];
			}

			if ($this->node_item_field)
			{
				$options[$key] = $details[$this->node_item_field];
			} else {
				$options[$key] = $details['id_alias'];
			}
		}

		return $options;
	}



	public function getHomeElm( $home_nde_id, &$deleted_elm_id = array() )
	{
		global $db;

		$home_elm =
			$db->select(
				$this->table_prefix."home_elm, elm_id, elm_order(asc), elm_published, where: home_nde_id=$home_nde_id, join: elm_id>; " .
				$this->table_prefix."element, node_id, join: <id"
			);

		// Check that the node_id of each elm_id belong to an available node !
		if ($nodes_id = $db->selectOne($this->table_prefix."home_nde, nodes_id, where: id=$home_nde_id", 'nodes_id'))
		{
			$nodes_id = explode(';', $nodes_id);

			$temp = array();
			for ($i=0; $i<count($home_elm); $i++)
			{
				if (!in_array($home_elm[$i]['node_id'], $nodes_id))
				{
					if ($db->delete($this->table_prefix."home_elm; where: elm_id={$home_elm[$i]['elm_id']} AND home_nde_id=$home_nde_id"))
					{
						$deleted_elm_id[] = $home_elm[$i]['elm_id'];
					}
				}
				else {
					$temp[] = $home_elm[$i];
				}
			}
			$home_elm = $temp;
		}

		return $home_elm;
	}


}



/*
 * Load comGeneric_frontend class (just because the following script is too long to be added here...)
 */
require(sitePath().'/components/com_generic/generic_class_frontend.php');


?>