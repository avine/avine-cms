<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'menu_link');


// Special prefix reserved for the form fields, when creating or updating a link
define('NEW_OR_UPD_', "new_or_upd_");



///////////////////////////////////////////////////////////////
// Functions for the specifics fields (depends of link_type_id)

// Return params for the specified menu_link_type (and be ready to insert or update the database !)
function admin_comMenu_linkTypeIDProcess( $link_type_id, $link_id = false, formManager_filter $filter )
{
	/*
	 * There's 2 fields in the 'menu_link' table wich are : href and params
	 * This function return those 2 informations : $return['href'] and $return['params']
	 * They will be integrated into the insert or update query
	 */
	$return = array(
		'href'		=> '',
		'params'	=> ''
	);

	switch ($link_type_id)
	{
		// file
		case 1:
			if ($href = $filter->requestValue('href')->getNotEmpty(1, LANG_ADMIN_COM_MENU_LINK_MENUTYPE1_NO_FILE_SELECTED, LANG_ADMIN_COM_MENU_LINK_FIELD_SPECIFIC))
			{
				$return['href'] = htmlspecialchars('file='.$href); # Carefull: the url is now encoded !
			}
			return $return;
			break;

		// content_element // content_node
		case 2: case 3:
			$link_type_id == 2 ? $params_type = 'element' : $params_type = 'node';
			$params_id = $filter->requestValue('href')->getInteger(1, LANG_ADMIN_COM_MENU_LINK_MENUTYPE23_NO_ITEM_SELECTED, LANG_ADMIN_COM_MENU_LINK_FIELD_SPECIFIC);
			$return['params'] = "type=$params_type;id=$params_id";

			// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
			$com_content = comContent_frontendScope();

			$link_type_id == 2 ? $url_encoded = $com_content->elementUrlEncoder($params_id) : $url_encoded = $com_content->nodeUrlEncoder($params_id);

			if ($page_url_request = $com_content->pageUrlRequest()) {
				$return['href'] = $com_content->pageUrlRequest().'&amp;'.$url_encoded['href'];
			} else {
				$return['href'] = $url_encoded['href'];
			}
			return $return;
			break;

		// component
		case 5:
			global $db;
			if ($com_page = $db->selectOne("components, com, page, where: id=".$filter->requestValue('href')->getInteger(1, LANG_ADMIN_COM_MENU_LINK_MENUTYPE5_NO_COMPONENT_SELECTED, LANG_ADMIN_COM_MENU_LINK_FIELD_SPECIFIC)))
			{
				$return['href'] = "com={$com_page['com']}&amp;page={$com_page['page']}";
			}
			return $return;
			break;

		// separator
		case 6:
			return $return;
			break;

		// url
		case 7:
			$return['href'] = trim( $filter->requestValue('href')->getNotEmpty(1, LANG_ADMIN_COM_MENU_LINK_MENUTYPE7_NO_URL, LANG_ADMIN_COM_MENU_LINK_FIELD_SPECIFIC) );
			/*
			 * The internal system doesn't support static url (ie: '/user/login/')
			 * Such url must be replaced by it's associated dynamic url (ie: 'com=user&amp;page=login')
			 * Notice : this dynamic url is simplified (without the prefix '/index.php?')
			 */
			$type = comRewrite_::isLocalHostUrl($return['href'], $url_dyn_part);
			if ($type === 'static' || $type === 'dynamic') {
				$return['href'	] = $url_dyn_part;
				$return['params'] .= 'dynamic;';
			}

			// New window target
			$filter->requestValue('new_window')->get() ? $return['params'] .= 'new_window;' : '';
			return $return;
			break;
	}
}



// Return specific inputs (fields form) for the specified menu_link_type
function admin_comMenu_linkTypeIDForm( $link_type_id, $link_id = false ) # $link_id = false for new link
{
	$form = new formManager();
	$form->setForm('post', NEW_OR_UPD_);

	global $db;

	// Get the link params
	$link_id ? $params = setArrayOfParam( $db->selectOne("menu_link, params, where: id=$link_id", 'params') ) : $params = array();

	$html = '';
	switch ($link_type_id)
	{
		// file
		case 1:
			$file_list = admin_comFile_selectFileOptions();

			// We choose to not include the first '/' in the href field ( ex.: 'file=my_dir/my_file.php' and not 'file=/my_dir/my_file.php' )
			$temp = array();
			reset($file_list);
			foreach ($file_list as $href => $name) {
				$temp[preg_replace('~^(/)~', '', $href)] = $name;
			}
			$file_list = $temp;

			// If this is an update, search for existing href
			$selected_href = false;
			if ($link_id)
			{
				$selected_href = preg_replace('~^(file=)~', '', $db->selectOne("menu_link, href, where: id=$link_id", 'href'));

				if (!array_key_exists($selected_href, $file_list)) {
					$html .= str_replace('{file}', $selected_href, LANG_ADMIN_COM_MENU_LINK_MENUTYPE1_FILE_NOT_FOUND);
				}
			}

			(count($file_list) > 10) ? $size = 10 : $size = count($file_list);
			$html .= $form->select('href', formManager::selectOption($file_list, $selected_href), LANG_ADMIN_COM_MENU_LINK_MENUTYPE1_PARAMS.'<br />', '', "size=$size");
			return $html;
			break;

		// content_element // content_node
		case 2: case 3:
			// If this is an update, search for existing href
			if ($link_id)
			{
				$selected_type	= $params['type']; # Exists, but we don't need it...
				$selected_id	= $params['id'	];
			}
			else {
				$selected_id = false;
			}

			// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
			$com_content = comContent_frontendScope();

			// Get summary
			if ($link_type_id == 2) {
				// elements
				$show_elements = true;
				$summary_label = $com_content->translate(LANG_ADMIN_COM_MENU_LINK_MENUTYPE2_PARAMS);
			}
  			else {
  				// nodes
  				$show_elements = false;
  				$summary_label = $com_content->translate(LANG_ADMIN_COM_MENU_LINK_MENUTYPE3_PARAMS);
  			}
			$summary = $com_content->summaryNodes($show_elements);

			$summary_options = array();
			if ($link_type_id == 2) {
				for ($i=0; $i<count($summary); $i++) {
					if ($summary[$i]['type'] == 'element') {
						$summary_options[$summary[$i]['id']] = $summary[$i]['id_alias'];
					}
				}
			} else {
				for ($i=0; $i<count($summary); $i++) {
					$summary_options[$summary[$i]['id']] = $summary[$i]['id_alias'];
				}
			}

			(count($summary_options) > 10) ? $size = 10 : $size = count($summary_options);
			$html .= $form->select('href', formManager::selectOption($summary_options, $selected_id), $summary_label.'<br />', '', "size=$size");
			return $html;
			break;

		// component
		case 5:
			// If this is an update, search for existing href
			if ($link_id)
			{
				$link = $db->selectOne("menu_link, href, where: id=$link_id", 'href');
				$link = str_replace('&amp;', '&', $link); # Be sure about the separator
				$link = explode('&', $link);

				$com  = explode('=', $link[0]);
				$com  = $com[1];

				$page = explode('=', $link[1]);
				$page = $page[1];

				$selected_id = $db->selectOne('components, id, where: com='.$db->str_encode($com).' AND, where: page='.$db->str_encode($page), 'id');
			}
			else {
				$selected_id = false;
			}

			$temp = array();
			#$page_list = $db->select("components, id, com(asc), page(asc), title");	# Order by com= and page=
			$page_list = $db->select("components, id, com, page, title(asc)");			# Order by title
			for ($i=0; $i<count($page_list); $i++)
			{
				$page_list[$i]['title'] ? $com_page = $page_list[$i]['title'].' ~ ' : $com_page = '';
				$com_page .= preg_replace('~^(http(s)?://)'.pregQuote($_SERVER['HTTP_HOST'].WEBSITE_PATH).'~', '', comMenu_rewrite('com='.$page_list[$i]['com'].'&page='.$page_list[$i]['page']));

				$temp[$page_list[$i]['id']] = $com_page;
			}
			$page_list = $temp;

			(count($page_list) > 10) ? $size = 10 : $size = count($page_list);
			$html .= $form->select('href', formManager::selectOption($page_list, $selected_id), LANG_ADMIN_COM_MENU_LINK_MENUTYPE5_PARAMS.'<br />', '', "size=$size");
			return $html;
			break;

		// separator
		case 6:
			$html .= LANG_ADMIN_COM_MENU_LINK_MENUTYPE6_PARAMS;
			return $html;
			break;

		// url
		case 7:
			// Current values
			$link_id ? $href = $db->selectOne("menu_link, href, where: id=$link_id", 'href') : $href = '';
			if ($href && isset($params['dynamic'])) {
				$href = comMenu_rewrite($href);
			}
			isset($params['new_window']) ? $new_window = 1 : $new_window = 0;

			$html .= $form->text('href', $href, LANG_ADMIN_COM_MENU_LINK_MENUTYPE7_HREF.'<br />', '', 'size=80').'<br /><br />';
			$html .= $form->checkbox('new_window', $new_window, LANG_ADMIN_COM_MENU_LINK_MENUTYPE7_PARAMS);
			return $html;
			break;
	}
}



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger(); // (4)

if ($submit = formManager::isSubmitedForm('menu_', 'post')) // (0)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new'	)->get(); // (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$new_or_upd_submit = formManager::isSubmitedForm(NEW_OR_UPD_, 'post'); // (1) & (2)



// (4) Case 'publish_status' (change the publish status)
if ($publish_status)
{
	if ($published = $db->selectOne("menu_link, published, where: id=$publish_status"))
	{
		$published['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("menu_link; published=$published; where: id=$publish_status");
	}
}



// (3) Case 'del'
if ($del)
{
	if (!$db->select("menu_link, id, where: parent_id=$del"))
	{
		admin_informResult( $db->delete("menu_link; where: id=$del") );

		// Purge the 'module_xhref' table
		if (!$db->delete("module_xhref; where: link_id=$del")) {
			admin_message(LANG_ADMIN_COM_MENU_LINK_DEL_MODULES_XHREF_TROUBLES, 'error');
		}
	}
	else {
		admin_message(LANG_ADMIN_COM_MENU_LINK_DEL_LINK_HAVE_CHILD, 'error');
	}
}



// (2) Update link  &  (1) New link
if ($new_or_upd_submit)
{
	$filter->reset();

	// Init params
	$params = '';

	/*
	 * Generals fields
	 */

	// Update link or create a new one ?
	if ($filter->requestValue('id')->get())
	{
		$id = $filter->requestValue('id')->getInteger();

		// When update a link, the menu_id and link_type_id can't be changed. We already know them, and they will not be a part of the update query.
		//$menu_id		= $db->selectOne("menu_link, menu_id,      where: id=$id", 'menu_id');			# We know it, and we don't need it
		$link_type_id	= $db->selectOne("menu_link, link_type_id, where: id=$id", 'link_type_id');		# This one, we need it ! (see the below when)
	}
	else
	{
		$id = false;

		$menu_id 	  = $session->get('menu_id_selection');
		$link_type_id = $filter->requestValue('link_type')->getInteger();
	}

	$name 			= $filter->requestValue('name'			)->getNotEmpty(1, '', LANG_ADMIN_COM_MENU_LINK_NAME);
	$parent_id 		= $filter->requestValue('parent_id'		)->getInteger();
	$access_level 	= $filter->requestValue('access_level'	)->getInteger();

	$filter->requestValue('unique_id')->get() ? $unique_id = 1 : $unique_id = 0;
	$filter->requestValue('published')->get() ? $published = 1 : $published = 0;

	if ($css = $filter->requestValue('css')->get(0)) {
		$params = admin_comMenu_AddToParams("css=$css", $params);
	}

	$template_id = $filter->requestValue('template_id')->getInteger();
	!$template_id ? $template_id = "NULL" : '';

	!$id ? $link_order = 9999 : ''; # New links inserted at the end

	// Use default modules (notice that no params means "use the default modules")
	$filter->requestValue('use_default_module')->get() ? '' : $params = admin_comMenu_AddToParams('default_module=no', $params);

	/*
	 * Specifics fields (depends of link_type_id)
	 */

	$href_params	= admin_comMenu_linkTypeIDProcess($link_type_id, $id, $filter);
	$href			= $href_params['href'];
	$params			= admin_comMenu_AddToParams($href_params['params'], $params);

	// Database Process
	if ($new_or_upd_submit_validation = $filter->validated())
	{
		if (!$id)
		{
			$db_fields = "NULL, ".$db->str_encode($name).', '.$db->str_encode($href).', '.$db->str_encode($unique_id).", $link_type_id, $menu_id, $parent_id, $link_order, $access_level, $published, $template_id, ".$db->str_encode($params);
			$result = $db->insert("menu_link; $db_fields");
		}
		else {
			$result = $db->update('menu_link; name='.$db->str_encode($name).', href='.$db->str_encode($href).', unique_id='.$db->str_encode($unique_id).", parent_id=$parent_id, access_level=$access_level, published=$published, template_id=$template_id, params=".$db->str_encode($params)."; where: id=$id");
		}
		admin_informResult($result);

		// Page modules options
		if ($result)
		{
			if ($id) {
				$db->delete("module_xhref; where: link_id=$id");
			} else {
				$id = $db->insertID();
			}

			($page_modules_id_ = formManager_filter::arrayOnly($filter->requestValue('page_modules_id_')->getInteger())) or ($page_modules_id_ = array());

			if ($copy_from_link_id = $filter->requestValue('copy_from_link_id')->getInteger())
			{
				if ($copy_page_modules_id = array_keys($db->select("module_xhref, [mod_id], where: link_id=$copy_from_link_id")))
				{
					// Add the modules of the 'source' page to the modules of the 'target' page
					$page_modules_id_ = array_values(array_unique(array_merge($page_modules_id_, $copy_page_modules_id)));
				}
			}

			if ($page_modules_id_)
			{
				for ($i=0; $i<count($page_modules_id_); $i++) {
					$db->insert("module_xhref; NULL, {$page_modules_id_[$i]}, ".$db->str_encode($href).", $id");
				}
			}
		}
	}
	else {
		echo $filter->errorMessage();
	}
}



// 'new' button clicked, but no 'menu_link_type' selected (simply return to start view)
$new && !$filter->requestValue('link_type')->getInteger() ? admin_message(LANG_ADMIN_COM_MENU_LINK_NO_LINK_TYPE_SELECTED, 'error') : '';

if ( ($new && $filter->requestValue('link_type')->getInteger()) || ($upd) || ($new_or_upd_submit && !$new_or_upd_submit_validation) )
{
	$start_view = false;

	if ($new_or_upd_submit && !$new_or_upd_submit_validation)
	{
		$upd = $filter->requestValue('id')->getInteger();
	}
	$upd ? $new = false : $new = true;

	// Title
	if ($upd) {
		echo '<h2>'.LANG_ADMIN_COM_MENU_LINK_TITLE_UPDATE.'</h2>';
	} else {
		echo '<h2>'.LANG_ADMIN_COM_MENU_LINK_TITLE_NEW.'</h2>';
	}

	if ($upd)
	{
		// $current : get all informations about the link we need to update
		$current = $db->selectOne("menu_link, *, where: id=$upd");

		// menu_id & link_type_id
		$menu_id		= $current['menu_id'];
		$link_type_id	= $current['link_type_id'];
	}
	else
	{
		// $current : initialize
		$fields = array_keys($db->db_describe('menu_link'));
		for ($i=0; $i<count($fields); $i++) {
			$current[$fields[$i]] = '';
		}
		$current['access_level'	] = comUser_getLowerStatus();	# Lower status
		$current['published'	] = '1';						# Default value of the checkbox

		// menu_id & link_type_id
		$menu_id		= $session->get('menu_id_selection');
		$link_type_id	= $filter->requestValue('link_type')->getInteger();
	}

	$html = '';

	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, NEW_OR_UPD_); # Form begin

	if ($upd) {
		$html .= $form->hidden('id', $upd ); # Remeber the link id we need to update !
	} else {
		$html .= $form->hidden('link_type', $link_type_id ); # Remember the link_type we need to create !
	}

	// current params
	$upd ? $params = setArrayOfParam( $db->selectOne("menu_link, params, where: id=$upd", 'params') ) : $params = array();

	/*
	 * Generals fields
	 */

	$html_general = '';

	// Name
	$html_general .= $form->text('name', $current['name'], LANG_ADMIN_COM_MENU_LINK_NAME, '', 'maxlength=100');

	// Unique_id (not available for link type of separator or url)
	if ($link_type_id != 6 && $link_type_id != 7) {
		$html_general .= '&nbsp; &nbsp;'.$form->checkbox('unique_id', $current['unique_id'], LANG_ADMIN_COM_MENU_LINK_UNIQUE_ID.'(left)');
	}
	$html_general .= '<br /><br />';

	// Parent_id
	$options = array();
	$menu_link = admin_comMenu_getMenu($menu_id, $current['id']);
	$options['[0]'] = LANG_ADMIN_COM_MENU_LINK_CHANGE_ORDER_ROOT;
	for ($i=0; $i<count($menu_link); $i++) {
		$options[$menu_link[$i]['id']] = strip_tags($menu_link[$i]['name']);
	}
	(count($options) > 10) ? $size = 10 : $size = count($options);
	$html_general .= $form->select('parent_id', formManager::selectOption($options, $current['parent_id']), LANG_ADMIN_COM_MENU_LINK_CHANGE_ORDER.'<br />', '', "size=$size").'<br /><br />';

	// Access_level
	$html_general .= $form->select('access_level', comUser_getStatusOptions($current['access_level']), LANG_ADMIN_COM_MENU_LINK_ACCESS_LIST).'&nbsp; &nbsp;';

	// Published
	$html_general .= $form->checkbox('published', $current['published'], LANG_ADMIN_COM_MENU_LINK_PUBLISHED.'(left)').'<br /><br />';

	// CSS
	$html_general .= $form->text('css', @$params['css'], LANG_ADMIN_COM_MENU_LINK_ADD_CSS.'<br />').'<br /><br />';

	// Template_id
	$options = array();
	$tmpl_list = $db->select('template, id(asc), name');
	$options['[0]'] = LANG_ADMIN_COM_MENU_LINK_USE_DEFAULT_TMPL;
	for ($i=0; $i<count($tmpl_list); $i++) {
		$options[$tmpl_list[$i]['id']] = $tmpl_list[$i]['name'];
	}
	$html_general .= $form->select('template_id', formManager::selectOption($options, $current['template_id']), LANG_ADMIN_COM_MENU_LINK_TMPL_LIST.'<br />').'<br /><br />';

	// Page modules options
	if ($link_type_id != 6)
	{
		$page_modules = array();
		$menu_name = $db->selectOne("menu, name, where: id=$menu_id", 'name');

		// Current page modules
		$current_mod_id = array();
		if ($upd)
		{
			$module_xhref = $db->select('module_xhref, mod_id, where: link_id='.$current['id']);
			for ($i=0; $i<count($module_xhref); $i++) {
				$current_mod_id[$i] = $module_xhref[$i]['mod_id'];
			}
		}

		// List of default modules
		$module_default = array_keys( $db->select('module_default, [mod_id]') );

		// Let's find the $page_modules array !
		$mod_pos = $db->select("module_pos, pos(asc)");
		for ($j=0; $j<count($mod_pos); $j++)
		{
			$pos = $mod_pos[$j]['pos']; # Alias

			$module = $db->select("module, id,name(asc),comment, mod_file, where: mod_pos='$pos'");
			if (count($module))
			{
				$page_modules["$pos(optgroup)"] = '';

				for ($i=0; $i<count($module); $i++)
				{
					$opt = '';

					// Is default module ?
					if (in_array($module[$i]['id'], $module_default)) {
						$opt .= LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_BY_DEFAULT.' ';
					}

					// Choose between this :	Module name & mod_file
					$opt .= $module[$i]['name']." ({$module[$i]['mod_file']})";
					// And this :				Module name & description
					#$opt .= $module[$i]['name'].($module[$i]['comment'] ? " ({$module[$i]['comment']})" : '');

					// Is the module of the current edited menu ?
					if ($module[$i]['mod_file'] == "menu_$menu_name.php") {
						$opt .= ' '.LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_CURRENT_MENU;
						$module_of_current_menu = true;
					}

					$page_modules[$module[$i]['id']] = strip_tags($opt);
				}
			}
		}

		if ($page_modules)
		{
			(count($page_modules) > 15) ? $size = 15 : $size = count($page_modules);
			$html_general .= $form->select('page_modules_id_', formManager::selectOption($page_modules, $current_mod_id), LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES.'<br />', '', "multiple;size=$size");

			// Tips about the module of the current edited menu
			if (isset($module_of_current_menu)) {
				$html_general .= ' <span class="grey">'.LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_CURRENT_MENU_TIPS.'</span>';
			}

			// Primary link info
			if ($upd) {
				if ($primary_link = admin_comModule_findPrimaryLink($upd)) {
					$primary_link = array( '{id}' => $primary_link['id'], '{name}' => $primary_link['name'], '{menu_name}' => $primary_link['menu_name'] );
					$html_general .= searchAndReplace(LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_UPD_TIPS_NOT_PRIMARY_LINK, $primary_link);
					$link_type_id != 7 ? $html_general .= LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_PRIMARY_LINK_TIPS : '';
				}
			}
			else {
				$html_general .= LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_NEW_TIPS;
				$link_type_id != 7 ? $html_general .= LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_PRIMARY_LINK_TIPS : '';
			}

			// Use default modules
			if ($module_default || true) # Notice : it's better to always display the checbox even if there's no default module CURRENTLY. Think about it...
			{
				$use_default_module_label = LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_USE_DEFAULT.' '.LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_USE_DEFAULT_TIPS;
				$html_general .= '<br />'.$form->checkbox('use_default_module', @$params['default_module'] == 'no' ? 0 : 1, $use_default_module_label);
			}
		}

		// Add the modules wich are already associated to another page
		$options = array();
		$menu_link = admin_comMenu_getMenu($menu_id);
		$options['[0]'] = LANG_SELECT_OPTION_ROOT;
		for ($i=0; $i<count($menu_link); $i++)
		{
			// Exclude 'separator' wich don't have asociated modules; exclude also the current link_id itself
			if ( ($menu_link[$i]['link_type'] != 'separator') && ($new || $upd != $menu_link[$i]['id']) )
			{
				$options[$menu_link[$i]['id']] = strip_tags($menu_link[$i]['name']);
			}
		}
		if (count($options) > 1) {
			$html_general .= '<br /><br />'.$form->select('copy_from_link_id', $options, LANG_ADMIN_COM_MENU_LINK_PAGE_MODULES_COPY_FROM_LINK.'<br />');
		}
	}

	$html .= admin_fieldset($html_general, LANG_ADMIN_COM_MENU_LINK_FIELD_GENERAL);

	/*
	 * Specifics fields (depends of link_type_id)
	 */

	$html_specific = '';

	// Params
	$html_specific .= admin_comMenu_linkTypeIDForm($link_type_id, $upd).'<br />';

	$html .= admin_fieldset($html_specific, LANG_ADMIN_COM_MENU_LINK_FIELD_SPECIFIC);

	// Submit button
	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	$html .= $form->end(); # Form end

	echo $html;
}



// (0) Always check for : links order update
if ($submit && $link_id = formManager_filter::arrayOnly($filter->requestName('link_order_')->getInteger()))
{
	for ($i=0; $i<count($link_id); $i++) {
		$order = $filter->requestValue('link_order_'.$link_id[$i])->getInteger();
		if ($order !== false) {
			$db->update("menu_link; link_order=$order; where: id=".$link_id[$i]);
		}
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_MENU_LINK_TITLE_START.'</h2>';

	// Menu selection
	if ($menu_list = $db->select('menu, id, name(asc)'))
	{
		$html = '';

		// Form
		$form = new formManager(0);
		$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'menu_'); # Form begin

		// Session
		$default_menu_id = 1; # This is the 'mainmenu'. But, if you prefer use the first menu available in the list : $menu_list[0]['id']
		$session->init('menu_id_selection', $default_menu_id);
		$menu_id = $session->setAndGet('menu_id_selection', $submit ? $filter->requestValue('menu_id_selection')->getInteger() : false);

		// Inform that link_type url not allowed for home-page (first link)
		if ($menu_id == 1) {
			$home_page = $db->selectOne('menu_link, link_type_id, link_order(asc), parent_id(asc), where: menu_id=1 AND, where: parent_id=0 AND, where: published=1');

			if ($home_page && ($home_page['link_type_id'] == 6 || $home_page['link_type_id'] == 7)) {
				admin_message(LANG_ADMIN_COM_MENU_LINK_INVALID_FIRST_LINK_HOME_PAGE, 'warning', '360');
			}
		}

		// Menu selection
		$options = array();
		$options['0'] = LANG_SELECT_OPTION_ROOT;
		for ($i=0; $i<count($menu_list); $i++) {
			$options[$menu_list[$i]['id']] = $menu_list[$i]['name'];
		}
		$html .= $form->select('menu_id_selection', formManager::selectOption($options, $menu_id), LANG_ADMIN_COM_MENU_LINK_SELECT_MENU);
		$html .= $form->submit('select_submit', LANG_ADMIN_BUTTON_SUBMIT, 'select_submit_top').'<br /><br />'; # Submit-button for the start-view

		// Links details
		if ($menu_id)
		{
			/*
			 * View existing links
			 */

			// Templates names
			$template = $db->select('template, [id], name');
			!$template ? $template = NULL : '';

			// User status comment
			$user_status = $db->select('user_status, [id], comment');

			$menu_link = admin_comMenu_getMenu($menu_id);
			for ($i=0; $i<count($menu_link); $i++)
			{
				if ($menu_link[$i]['link_type'] == 'url')
				{
					$params = setArrayOfParam($menu_link[$i]['params']);
					if (isset($params['dynamic'])) {
						$full_href = comMenu_rewrite($menu_link[$i]['href']);
						$menu_link[$i]['href'] = preg_replace('~^(http(s)?://)'.pregQuote($_SERVER['HTTP_HOST']).'~', '', $full_href);
					} else {
						$full_href = $menu_link[$i]['href'];
					}
				}
				elseif ($menu_link[$i]['link_type'] != 'separator')
				{
					$full_href = comMenu_rewrite($menu_link[$i]['href']);
					$menu_link[$i]['href'] = preg_replace('~^(http(s)?://)'.pregQuote($_SERVER['HTTP_HOST']).'~', '', $full_href);
				}

				// Link name (with link)
				if ($menu_link[$i]['link_type'] != 'separator')
				{
					$menu_link[$i]['name'] = '<a href="'.$full_href.'" title="'.$full_href.'" class="external">'.$menu_link[$i]['name'].'</a>';
				}

				// Limit string lenght of href field
				strlen($menu_link[$i]['href'])>60 ? $menu_link[$i]['href'] = "<span style=\"cursor:help;\" title=\"{$menu_link[$i]['href']}\">".substr($menu_link[$i]['href'], 0, 60).' ...</span>' : '';

				// Input-text for the link_order field
				$menu_link[$i]['link_order'] = $form->text('link_order_'.$menu_link[$i]['id'], $menu_link[$i]['link_order'], '', '', 'size=2'); // (0)

				// Template_name (instead of template_id)
				if ($menu_link[$i]['template_id']) {
					if (array_key_exists($menu_link[$i]['template_id'], $template)) {
						$menu_link[$i]['template_id'] = $template[ $menu_link[$i]['template_id'] ]['name'];
					} else {
						$menu_link[$i]['template_id'] = '<span class="red">'.LANG_ADMIN_COM_MENU_LINK_TEMPLATE_IS_MISSING.'</span>';
					}
				}

				// Access_level (_name instead of _id) (get the access-level-name from user_status table)
				$menu_link[$i]['access_level'] = $user_status[ $menu_link[$i]['access_level'] ]['comment'];

				// Published (<a> tag with checked/unchecked image)
				$menu_link[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$menu_link[$i]['id'].'">'.admin_replaceTrueByChecked($menu_link[$i]['published']).'</a>'; // (4)

				// Update & Delete buttons
				$update[$i] = $form->submit('upd_'.$menu_link[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
				$delete[$i] = $form->submit('del_'.$menu_link[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)

				// ID style
				$menu_link[$i]['id'] = '<div class="grey center">'.$menu_link[$i]['id'].'</div>'; # Carefull : the ID info is not available anymore (must be the last line) !
			}
			$table = new tableManager($menu_link);
			$table->header(admin_comMenu_getMenuHeader());
			#$table->delCol('0'); # Delete the ID column
			//$table->delCol('8'); # Delete the params column

			if (count($menu_link)) {
				$table->addCol($delete, 0  , '');
				$table->addCol($update, 999, '');
			}

			$html .= $table->html();
			$html .= $form->submit('select_submit', LANG_ADMIN_BUTTON_SUBMIT, 'select_submit_bottom').'<br /><br />'; # Submit-button for the start-view (duplicate)

			/*
			 * Add new-link form
			 */

			// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
			$com_content = comContent_frontendScope();

			$fieldset = '';
			$link_type = $db->select('menu_link_type, *');
			for ($i=0; $i<count($link_type); $i++)
			{
				$fieldset .= $form->radio('link_type', $link_type[$i]['id'], $com_content->translate($link_type[$i]['comment'] /*.' - ('.$link_type[$i]['name'].')'*/ ), 'link_type_'.$link_type[$i]['id'])."<br />\n";
			}
			$fieldset .= '<br />'.$form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)

			$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_MENU_LINK_MENU_TYPE);
		}

		$html .= $form->end(); # Form end

		echo $html;
	}
	else {
		echo LANG_ADMIN_COM_MENU_LINK_NO_MENU_DEFINED;
	}
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>