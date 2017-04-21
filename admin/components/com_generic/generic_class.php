<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/////////////
// Sub-Class

class admin_comGeneric extends comGeneric_
{

	// Nodes-tree header
	public function getNodesHeader()
	{
		if ($this->lang_node_item_field == "")
		{
			$lang_node_item_field = $this->translate(LANG_ADMIN_COM_GENERIC_NODE_ID_ALIAS);			# For generic-test
		} else {
			$lang_node_item_field = $this->lang_node_item_field;									# Specific
		}

		return
			array(
				LANG_ADMIN_COM_GENERIC_ID,
				LANG_ADMIN_COM_GENERIC_NODE_LEVEL,
				$lang_node_item_field,
				LANG_ADMIN_COM_GENERIC_LIST_ORDER,
				LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL,
				LANG_ADMIN_COM_GENERIC_PUBLISHED,
				LANG_ADMIN_COM_GENERIC_ARCHIVED
			);
	}



	// Elements header
	public function getElementsHeader()
	{
		if ($this->lang_element_item_field == "")
		{
			$lang_element_item_field = $this->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS);	# For generic-test
		} else {
			$lang_element_item_field = $this->lang_element_item_field;								# Specific
		}

		return
			array(
				LANG_ADMIN_COM_GENERIC_ID,
				$lang_element_item_field,
				LANG_ADMIN_COM_GENERIC_LIST_ORDER,
				LANG_ADMIN_COM_GENERIC_ACCESS_LEVEL,
				LANG_ADMIN_COM_GENERIC_PUBLISHED,
				LANG_ADMIN_COM_GENERIC_ARCHIVED,
				LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_ONLINE,
				LANG_ADMIN_COM_GENERIC_ELEMENT_DATE_OFFLINE,
				LANG_ADMIN_COM_GENERIC_SHOW_DATE_CREATION,
				LANG_ADMIN_COM_GENERIC_ELEMENT_AUTHOR,
				'DATE EXPIRED'
			);
	}



	// Home header
	public function getHomeHeader()
	{
		if ($this->lang_element_item_field == "")
		{
			$lang_element_item_field = $this->translate(LANG_ADMIN_COM_GENERIC_ELEMENT_ID_ALIAS);	# For generic-test
		} else {
			$lang_element_item_field = $this->lang_element_item_field;								# Specific
		}

		return
			array(
				$lang_element_item_field,
				LANG_ADMIN_COM_GENERIC_ID,
				LANG_ADMIN_COM_GENERIC_LIST_ORDER,
				LANG_ADMIN_COM_GENERIC_PUBLISHED
			);
	}



	// Update subnodes 'level' field
	public function updateNodeLevel( $parent_id, $level = false )
	{
		static $result = true;

		// Get nodes
		global $db;
		$node = $db->select($this->table_prefix."node, id,level, where: parent_id=$parent_id");

		// Increment level
		if ($level !== false)
		{
			$level++; # Add level
		} else {
			$level = $db->selectOne($this->table_prefix."node, level, where: id=$parent_id", 'level') +1; # Get start
		}

		// Update 'level' field
		for ($i=0; $i<count($node); $i++)
		{
			$r = $db->update($this->table_prefix."node; level=$level; where: id=".$node[$i]['id']);
			!$r ? $result = false : '';

			// Go to subnode
			$this->updateNodeLevel($node[$i]['id'], $level);
		}

		return $result;
	}



	public function updateArchivedField ( $node_id )
	{
		static $result = true;

		global $db;

		// Get & archive elements
		$element = $db->select($this->table_prefix."element, id, where: node_id=$node_id");
		for ($i=0; $i<count($element); $i++)
		{
			$r = $db->update($this->table_prefix."element; archived=1; where: id=".$element[$i]['id']);
			!$r ? $result = false : '';
		}

		// Get & archive nodes
		$node = $db->select($this->table_prefix."node, id, where: parent_id=$node_id");
		for ($i=0; $i<count($node); $i++)
		{
			$r = $db->update($this->table_prefix."node; archived=1; where: id=".$node[$i]['id']);
			!$r ? $result = false : '';

			// Go to subnode
			$this->updateArchivedField($node[$i]['id']);
		}

		return $result;
	}



	// Return an image : true => archived.png ; false => no-image
	public function replaceTrueByArchived ( $test )
	{
		// Images Html
		$png_archived = '<img src="'.WEBSITE_PATH.'/admin/components/com_generic/images/archived.png" alt="archived" title="'.LANG_ADMIN_COM_GENERIC_ARCHIVE_IMG_TITLE.'" border="0" />';

		if ($test) {
			return $png_archived;
		} else {
			return false;
		}
	}



	// Archive Nodes options for select form 
	public function liveArchiveStatusNodesOptions ( $archive_node = false )
	{
		$options =
			array(
				'0' => LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_0, 
				'2' => LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_2
			);

		reset($options);
		while (list($value, $txt) = each($options)) {
			if ($value == $archive_node) $options[$value] = '['.$txt.']';
		}
		return $options;
	}

	// Archive Elements options for select form 
	public function liveArchiveStatusElementsOptions ( $archive_node = false )
	{
		$options =
			array(
				'0' => LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_0, 
				'1' => LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_1, 
				'2' => LANG_ADMIN_COM_GENERIC_ARCHIVE_SELECTION_2
			);

		reset($options);
		while (list($value, $txt) = each($options)) {
			if ($value == $archive_node) $options[$value] = '['.$txt.']';
		}
		return $options;
	}



	// Is a node and all his parents are non-archived ?
	public function isNotArchivedNode( $node_id )
	{
		$not_archived = true;

		global $db;
		$current_node = $node_id; # Notice that if $node_id=0, then $not_archived=true
		while (($current_node != 0) && ($not_archived))
		{
			$node = $db->select($this->table_prefix."node, parent_id, where: id=$current_node AND, where: archived=0");

			if ($node) {
				$current_node = $node[0]['parent_id'];
			} else {
				$not_archived = false;
			}
		}
		return $not_archived;
	}



	// Get the full path to a file into '/admin/components/com_*' directory
	public function volatileFilePath( $file, &$part )
	{
		$path_file = sitePath()."/admin/components/com_$this->com_name/$file";

		if (file_exists($path_file))
		{
			// Give the required file the $db usefull valiable
			global $db;

			$part = true;
			require($path_file);

			return true;
		} else {
			return false;
		}
	}



	// After archiving an element or a node, remove them from 'home_elm' table
	public function removeArchivedFromHomeElm()
	{
		global $db;
		if ($home_archived = $db->select($this->getTablePrefix().'home_elm, elm_id, join: elm_id>; '.$this->getTablePrefix().'element, where: archived=1, join: <id'))
		{
			for ($i=0; $i<count($home_archived); $i++) {
				$db->delete($this->getTablePrefix().'home_elm; where: elm_id='.$home_archived[$i]['elm_id']);
			}
		}
	}



	// Push all archived nodes after non-archived ones (including the subnodes)
	public function pushArchivedNodes( $parent_id )
	{
		global $db;
		$node_list = $db->select($this->table_prefix."node, id, archived(asc),list_order(asc), where: parent_id=$parent_id");

		for ($i=0; $i<count($node_list); $i++)
		{
			$db->update($this->table_prefix.'node; list_order='.(2*$i+1).'; where: id='.$node_list[$i]['id']);

			// Go to subnode
			$this->pushArchivedNodes($node_list[$i]['id']);
		}
	}



	// Push all archived elements after non-archived ones
	public function pushArchivedElements( $node_id )
	{
		global $db;
		$element_list = $db->select($this->table_prefix."element, id, archived(asc),list_order(asc), where: node_id=$node_id");

		for ($i=0; $i<count($element_list); $i++)
		{
			$db->update($this->table_prefix.'element; list_order='.(2*$i+1).'; where: id='.$element_list[$i]['id']);
		}
	}
	
}



// Compare 2 timestamp by days (without caring about the hours)
function admin_comGeneric_compareDays( $time_inf, $time_sup )
{
	$inf = mktime(0,0,0, date('n', $time_inf), date('j', $time_inf), date('Y', $time_inf));
	$sup = mktime(0,0,0, date('n', $time_sup), date('j', $time_sup), date('Y', $time_sup));

	if ($inf <= $sup) {
		return true;
	} else {
		return false;
	}
}



?>