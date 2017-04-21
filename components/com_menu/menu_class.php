<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// The powerfull urlAnalizer!
function comMenu_() # this function could be named: comMenu_linkDecoder() because it's the reverse of comMenu_linkEncoder()
{
	global $g_page;
	global $db;

	/*
	 * Process of Url-Analysis :
	 * We are searching informations from $_GET[] variable, to match them with an existing record of 'menu_link' table.
	 *
	 * First, We try to match the 'href' field (of the 'menu_link' table).
	 * If we success, we get of course in the same time the 'id' field.
	 *
	 * If everything ok, this 'id' field is exactly the $link_id = $_GET['link_id'], that we know from the begining (we get it from $_GET[]) !
	 * But we are using $link_id only in case, too many records where founded into 'menu_link' table (ie: many links to the same target).
	 *
	 * That mean, if we can't match the 'href' field, we are not using $link_id information, even if could leads us to an existing 'menu_link' record.
	 */

	/*
	 * Search for : $link_type_id & $get_href
	 */
	$link_type_id = false;
	$get_href = false;

	// url //
	/*
	 * In that case, the $g_page[] informations will be hybrid :
	 * Because the url must be interpreted as a regular link_type_id, like: file (=1), or content_item (=2), ...
	 * Then we can't simply set : $g_page['link_type_id'] = 7 (url)
	 */
	$url = str_replace('&', '&amp;', $_SERVER['QUERY_STRING']);

	$href_maxlengh = '';
	$id_maxlengh = false;
	$select_menu_link = $db->select('menu_link, href, id(asc), where: link_type_id=7');
	for ($i=0; $i<count($select_menu_link); $i++)
	{
		// According to the rules, if ($select_menu_link[$i]['href'] == strlen($href_maxlengh)) we stay with wich his id is smallest
		if ( (preg_match('~^('.pregQuote($select_menu_link[$i]['href']).')~i', $url)) && (strlen($select_menu_link[$i]['href']) > strlen($href_maxlengh)) )
		{
			$href_maxlengh = $select_menu_link[$i]['href'];
			$id_maxlengh = $select_menu_link[$i]['id'];
		}
	}

	$link_type_url = false;
	if ($id_maxlengh !== false)
	{
		$select_menu_link = $db->select("menu_link, *, where: id=$id_maxlengh");

		// Alternative page title (if not defined)
		$g_page['link_name'			] = $select_menu_link[0]['name'];

		// About the template if different of default
		$g_page['link_template_id'	] = $select_menu_link[0]['template_id'];

		// About the main content (and also 'active-link' css class for menus links)
		# $g_page['link_type_id'	] not setted here (see before for explanation)
		$g_page['link_href'			] = $select_menu_link[0]['href'];
		$g_page['link_params'		] = $select_menu_link[0]['params'];

		// Usefull to get this page modules
		$g_page['link_id']			= $select_menu_link[0]['id'];

		// Clean $g_page['link_href'] from url prefix ( ex. : http://www.mysite.com/subdir/index.php? )
		$g_page['link_href'			] = preg_replace('~^'.pregQuote(siteUrl().'/index.php?').'~', '', $g_page['link_href']);

		$link_type_url = true; # Some keys of $g_page[] array are now already setted
	}

	// file //
	if ( (isset($_GET['file'])) && (formManager_filter::isPathFile($_GET['file'],0)) )
	{
		$link_type_id = 1;
		$get_href = 'file='.$_GET['file'];
	}
	// component //
	elseif ( ((isset($_GET['com'])) && (formManager_filter::isVar($_GET['com'],0))) && ((isset($_GET['page'])) && (formManager_filter::isVar($_GET['page'],0))) )
	{
		$link_type_id = 5;
		$get_href = 'com='.$_GET['com'].'&amp;page='.$_GET['page'];
	}

	// com_content (special component) //

	// Instanciate comContent_frontend class object (using quick launch function of: comContent_frontend::scope() method)
	$com_content = comContent_frontendScope();

	$com_in_url = $com_content->findComponentInUrl();
	if ($com_in_url['all_in_one']) # In that case, $link_type_id=5 and $get_href will be overwritten
	{
		if ($com_in_url['element']) {
			$link_type_id = 2;
		} elseif ($com_in_url['node']) {
			$link_type_id = 3;
		} else {
  			$link_type_id = 5; # in reality, this case can not appen because comContent_ using a well known redirection ('com=content&page=index' has been removed from the url's)
  		}
		$get_href = $com_in_url['all_in_one'];
	}
	// End of : special component

	/*
	 * Search for : a record into 'menu_link' table ($founded)
	 */

	$try_again = false;
	do {

		if (($get_href) && ($try_again == false))
		{
			// Go to specified url
			$select_menu_link = $db->select("menu_link, *, id(asc), where: link_type_id=$link_type_id AND, where: href=".$db->str_encode($get_href));
			$try_again = true;
		}
		else		# New version of the else block : go to "eroor 404" page #
		{
			$website_path = pregQuote(WEBSITE_PATH.'/'); # Alias
			if (preg_match("~(^$website_path$|^{$website_path}index\.php(\?)?)~", $_SERVER['REQUEST_URI'])) # Home page pattern
			{
				// Go to home page (notice : link_type_id=7 (url) {and of course =6 (separator)} are not allowed for home-page)
				$select_menu_link = $db->select(comMenu_homePageQuery());
			} else {
				// Page not found !
				$select_menu_link = array();
			}
			$try_again = false;
		}
		/*else		# Previous version of the else block : always go to home page #
		{
			// Go to home page (notice : link_type_id=7 (url) {and of course =6 (separator)} are not allowed for home-page)
			$select_menu_link = $db->select(comMenu_homePageQuery());
			$try_again = false;
		}*/

		$link_type_not_url_unique_id = false;
		$founded = false;
		if (count($select_menu_link) >= 1)
		{
			// Many records - Use now the unique_id and match record
			if ((isset($_GET['link_id'])) && (formManager_filter::isInteger($_GET['link_id'],0)))
			{
				$get_link_id = $_GET['link_id'];
			} else {
				$get_link_id = NULL;
			}
			if ((count($select_menu_link) >= 1) && ($get_link_id))
			{
				for ($i=0; $i<count($select_menu_link); $i++)
				{
					if ($select_menu_link[$i]['id'] == $get_link_id) {
						$founded = $i;
						$link_type_not_url_unique_id = true;
					}
				}
			}

			// Still not founded - Take the first record (consequence: for the system, this is the link clicked by the user, even if it's wrong!)
			if ($founded === false) {
				$founded = 0;
			}

			// Record founded into 'menu_link' table
			if ($founded !== false)
			{
				/*
				 * Rules :
				 * Simple link_type_id=7 (url) have priority on others link_type_id (That mean: link_type_id=7 is like a link wich have automatically a unique_id).
				 * But, others link_type_id wich have a unique_id have priority on link_type_id=7 (url).
				 */
				$take_this = ( (!$link_type_url) || ($link_type_not_url_unique_id) );

				if ($take_this)
				{
					// Alternative page title (if not defined)
					$g_page['link_name'			] = $select_menu_link[$founded]['name'];

					// About the template if different of default
					$g_page['link_template_id'	] = $select_menu_link[$founded]['template_id'];
				}

				// About the main content (and also 'active-link' css class for menus links)
				$g_page['link_type_id'			] = $select_menu_link[$founded]['link_type_id']; 	# Should be exactly our : $link_type_id

				if ($take_this)
				{
					// About the main content (suite)
					$g_page['link_href'			] = $select_menu_link[$founded]['href'];			# Should be exactly our : $get_href
					$g_page['link_params'		] = $select_menu_link[$founded]['params'];

					// Usefull to get this page modules
					$g_page['link_id'			] = $select_menu_link[$founded]['id']; 				# Should be exactly our : $get_link_id (if it was specified)
				}

				$try_again = false;
			}
		}

		if ($founded === false)
		{
			$take_this = !$link_type_url;

			if ($take_this)
			{
				$g_page['link_name'			] = false;
				$g_page['link_template_id'	] = false;
				$g_page['link_params'		] = '';
				$g_page['link_id'			] = false;
			}

			// No record founded! But perhaps this url leads somewhere !?!
			if (($g_page['config_no_linked_content_access']) || ($link_type_url))
			{
				$g_page['link_type_id'		] = $link_type_id;

				if ($take_this) {
					$g_page['link_href'		] = $get_href;
				}

				$try_again = false;
			}
			else
			{
				$g_page['link_type_id'		] = false;

				if ($take_this) {
					$g_page['link_href'		] = false;
				}
			}
		}

	} while ($try_again);

	/*
	 * Search for : Page-title & Main-content
	 */

	$g_page['title'				] = $g_page['link_name']; 		# Default : use link_name

	$g_page['main_content'		] = false;						# Default : no main-content founded
	$g_page['main_access_level'	] = comUser_getLowerStatus();	# Default : lower-level
	$g_page['main_published'	] = 1;							# Default : published

	$_GET_emulated = comMenu_emulateGET($g_page['link_href']);

	if ($g_page['link_href']) 				# Try to find a more specific Page-title, and get the Main-content
	{
		global $g_user_login;

		switch($g_page['link_type_id'])
		{
			// file
			case 1:
				if ($main_content = $db->selectOne('file, *, where: path='.$db->str_encode('/'.$_GET_emulated['file'])))
				{
					$g_page['main_access_level'	] = $main_content['access_level'];
					$g_page['main_published'	] = $main_content['published'];

					if (($g_user_login->accessLevel() <= $g_page['main_access_level']) && ($g_page['main_published'])) {
						$main_content['title'] != "" ? $g_page['title'] = $main_content['title'] : '';
					}
					$filepath = sitePath().'/contents'.$main_content['path'];
					is_file($filepath) ? $g_page['main_content'] = $filepath : '';
				}
				break;

			// content_element // content_node // content_element_id // content_node_id
			case 2: case 3:
				// Default component informations
				if ($main_content = $db->selectOne("components, *, where: com='content' AND, where: page='index'"))
				{
					$g_page['main_access_level'	] = $main_content['access_level'];
					$g_page['main_published'	] = $main_content['published'];

					if (($g_user_login->accessLevel() <= $g_page['main_access_level']) && ($g_page['main_published'])) {
						$main_content['title'] != "" ? $g_page['title'] = $main_content['title'] : '';
					}
				}

				// Specifics com_content informations
				$page_details = $com_content->pageContentDetails($com_in_url['node_alias_array'], $com_in_url['element_alias']);

				if ($page_details['access_level'] < $g_page['main_access_level']) {
					$g_page['main_access_level'] = $page_details['access_level'];
				}
				if (!$page_details['published']) {
					$g_page['main_published'] = $page_details['published'];
				}

				if (($g_user_login->accessLevel() <= $g_page['main_access_level']) && ($g_page['main_published']))
				{
					$page_details['title'] != "" ? $g_page['title'] = $page_details['title'] : '';

					// Overwrite Metas datas (will have effect only for case 2 :)
					$page_details['meta_key' ] ? comConfig_getMetaHtml('key' , $page_details['meta_key' ]) : '';
					$page_details['meta_desc'] ? comConfig_getMetaHtml('desc', $page_details['meta_desc']) : '';
				}

				$filepath = sitePath().'/components/com_content/pages/index.php';
				is_file($filepath) ? $g_page['main_content'] = $filepath : '';
				break;

			// component
			case 5:
				if ($main_content = $db->selectOne('components, *, where: com='.$db->str_encode($_GET_emulated['com']).' AND, where: page='.$db->str_encode($_GET_emulated['page'])))
				{
					$g_page['main_access_level'	] = $main_content['access_level'];
					$g_page['main_published'	] = $main_content['published'];

					if (($g_user_login->accessLevel() <= $g_page['main_access_level']) && ($g_page['main_published'])) {
						$main_content['title'] != "" ? $g_page['title'] = $main_content['title'] : '';
					}

					$filepath = sitePath().'/components/com_'.$_GET_emulated['com'].'/pages/'.$_GET_emulated['page'].'.php';
					if (is_file($filepath))
					{
						$g_page['main_content'] = $filepath;

						/*
						 * Special process for comGeneric_ component (and extended) :
						 * Try to find specifics page_title, access_level, published and metas
						 */
						$setup_path = sitePath().'/components/com_'.$_GET_emulated['com'].'/com_setup.php';
						if (is_file($setup_path))
						{
							$com_generic = comGeneric_frontend::scope($setup_path);
							$com_generic->setPageName($_GET_emulated['page']);

							// Specifics com_content informations
							$com_in_url = $com_generic->findComponentInUrl();
							$page_details = $com_generic->pageContentDetails($com_in_url['node_alias_array'], $com_in_url['element_alias']);

							if ($page_details['access_level'] < $g_page['main_access_level']) {
								$g_page['main_access_level'] = $page_details['access_level'];
							}
							if (!$page_details['published']) {
								$g_page['main_published'] = $page_details['published'];
							}

							if (($g_user_login->accessLevel() <= $g_page['main_access_level']) && ($g_page['main_published']))
							{
								$page_details['title'] != "" ? $g_page['title'] = $page_details['title'] : '';

								// Overwrite Metas datas (will have effect only for case 2 :)
								$page_details['meta_key' ] ? comConfig_getMetaHtml('key' , $page_details['meta_key' ]) : '';
								$page_details['meta_desc'] ? comConfig_getMetaHtml('desc', $page_details['meta_desc']) : '';
							}
						}
					}
				}
				break;
		}
	}

	// Special feature : remove the page title from the home page (because the home page title should be simply the $g_page['config_site_name'] and nothing more)
	if (comMenu_isHomePage())
	{
		$g_page['title'] = ''; # We are on home-page !
	}

	// Remove Tags from page title
	$g_page['title'] = strip_tags($g_page['title']);
}



/*
 * We can't use $_GET informations to get the main content, because of the home-page,
 * wich his url can be simply : 'http://www.site.com' (without any $_GET details).
 *
 * Then we must use : $g_page_link['link_href'], and emulate the $_GET variable from him.
 * The difference between $g_page_link['link_href'] and a regular request-url is the separator:
 *     - in a regular request-url : '&'
 *     - in $g_page_link['link_href'] : '&amp;'
 * Because we have cared that all 'href' fields in the 'menu_link' table contains '&amp;' for fields separator
 *
 * Limitation : link_type url not allowed for home-page
 */
function comMenu_emulateGET( $g_page_link_href )
{
	$g_page_link_href = str_replace('&amp;', '&', $g_page_link_href); # Be sure about the separator
	$g_page_link_href = explode('&', $g_page_link_href);

	for ($i=0; $i<count($g_page_link_href); $i++)
	{
		$temp = explode('=', $g_page_link_href[$i]);

		if (count($temp) == 1)
		{
			$_GET_emulated[$temp[0]] = true;
		} else {
	  		$_GET_emulated[$temp[0]] = $temp[1];
	  	}
	}

	return $_GET_emulated;
}



/*
 * Code example : $db->select(comMenu_homePageQuery());
 */
function comMenu_homePageQuery()
{
	## FIXME - J'ai essayé sans la condition parent_id=0, pour avoir le premier lien même en sous-menu, mais c'est faux...
	## Je crois qu'il faut parcourir le menu de manière récursive...
	## Du coup pour le moment la home page de peut être en sous menu (après un séparator par exemple)...

	$query = 'menu_link, *, link_order(asc), parent_id(asc), where: menu_id=1 AND, where: parent_id=0 AND, where: link_type_id!=6 AND, where: link_type_id!=7 AND, where: published=1; limit: 1';

	return $query;
}



/*
 * Check if we are on home-page ?
 */
function comMenu_isHomePage( $query_string = NULL )
{
	// By default check the query string of the current page
	isset($query_string) or $query_string = $_SERVER['QUERY_STRING'];

	global $db;
	if ($link_home = $db->selectOne(comMenu_homePageQuery()))
	{
		if ( ($query_string == "") || (str_replace('&amp;', '&', $query_string) == str_replace('&amp;', '&', $link_home['href'])) )
		{
			return true;
		}
	}
	return false;
}



// Find and include main content
function comMenu_loadMainContent()
{
	global $g_page;

	global $g_user_login;

	/*
	 * Now switch between link_type_id
	 */
	switch($g_page['link_type_id'])
	{
		// file // content_element // content_node // content_element_id // content_node_id // component
		case 1: case 2: case 3: case 5:
			if ($g_page['main_content'])
			{
				if ($g_user_login->accessLevel() <= $g_page['main_access_level'])
				{
					if ($g_page['main_published'])
					{
						if (preg_match('~(\.php)$~i', $g_page['main_content'])) 			# .php extension
						{
							require($g_page['main_content']);
						}
						elseif (preg_match('~(\.html|\.htm)$~i', $g_page['main_content'])) 	# .html or .htm extensions
						{
							echo file_get_contents($g_page['main_content']);
						}
						else $error_message = LANG_COM_MENU_MAIN_CONTENT_EXTENSION_NOT_VALID;
					}
					else $error_message = LANG_COM_MENU_MAIN_CONTENT_NOT_PUBLISHED;
				}
				else $error_message = LANG_COM_MENU_MAIN_CONTENT_NO_ACCESS_LEVEL;
			}
			else $error_message = LANG_ERROR_404;
			break;

		// No main content !
		default:
			$error_message = LANG_ERROR_404;
			break;
	}

	if (isset($error_message))
	{
		// Inform user
		$box = new boxManager();
		$box->echoMessage($error_message, 'error', true, '100%');

		// Display sitmap
		include(sitePath().'/components/com_menu/pages/sitemap.php');
	}
}



// Return a link element switching by link_type
function comMenu_linkEncoder( $link, $id_attr = '', $class_attr = '', $active_class_attr = '', $add_content_inside_link = '' )
{
	global $g_page;

	// Define alias
	$id 			= $link['id'		  ];
	$name 			= $link['name'		  ];
	$href 			= $link['href'		  ];
	$unique_id 		= $link['unique_id'	  ];
	$link_type_id	= $link['link_type_id'];
	$params 		= setArrayOfParam($link['params']);

	// Merge all classes
	$class_final = $class_attr;

	// Is this link active ?
	if ( ($href == $g_page['link_href']) && (!$unique_id || ($unique_id && ($id == $g_page['link_id']))) ) {
		$class_final .= ' '.$active_class_attr;
	}

	// Is there's a special class addon for this link ?
	if (isset($params['css'])) {
		$class_final .= ' '.$params['css'];
	}

	// Get complete attributs
	$class_final = comMenu_css($class_final);
	$id_attr = comMenu_css($id_attr, 'id');

	// Content addon...
	if ($add_content_inside_link) {
		$name = "$add_content_inside_link $name";
	}

	// Special case : do not encode the full href of the home-page
	if (comMenu_isHomePage($href)) { # Important : test this before testing the $unique_id !
		$href = '';
	}

	// Add unique_id
	if ($href && $unique_id) {
		$href .= "&amp;link_id=$id";
	}

	switch($link_type_id)
	{
		// file // content_element // content_node // component
		case 1: case 2: case 3: case 5:
			$href = comMenu_rewrite($href);
			return "<a href=\"$href\"{$id_attr}$class_final>$name</a>";

		// content_separator
		case 6:
			$class_final = comMenu_addCSS($class_final, 'separator'); # Add a special class for the span separator to handle it by css
			return "<span{$id_attr}$class_final>$name</span>";

		// url
		case 7 :
			if (isset($params['dynamic'])) {
				$href = comMenu_rewrite($href);
			}
			isset($params['new_window']) ? $target = ' target="_blank"' : $target = ''; # TODO - not compatible with XHTML strict
			return "<a href=\"$href\"{$id_attr}$class_final{$target}>$name</a>";
	}
}



function comMenu_rewrite( $url, $simplified = true, $protocol = false )
{
	// Protocol
	if (!$protocol) {
		global $g_protocol;
		$protocol = $g_protocol;
	}
	elseif ($protocol !== 'http://' && $protocol !== 'https://') {
		trigger_error("Invalid parameter \$protocol=$protocol");
	}

	// Get relative url of the simplified url (ie: 'com=user&amp;page=login')
	if ($simplified) {
		// Warning : must be a full url, wich can be sent to others services (like links sent by email in com_user component or the return_url of com_sips component)
		$url = $protocol.$_SERVER['HTTP_HOST'].WEBSITE_PATH.($url ? "/index.php?$url" : '/');
	}

	$type = comRewrite_::isLocalHostUrl($url, $url_dyn_part);
	if ($type === 'dynamic') {
		return comRewrite_::dynToStat($url); # Dynamic to static conversion (will have no effect if the rewrite engine is turned off)
	}

	return $url;
}


// Return an html-list of a menu
/*
 *		Structure of $param : $param[{level}][{tag}][{attribut}]
 *
 *		level     =  0, 1, 2, ...  (or 'all' for all level)
 *		tag       =  ul, li, a  (and a special 'a:active', for the current activated link)
 *		attribut  =  id, class
 *
 *		Another important key is : $param['add_content_inside_link'] = [CONTENT];
 *		Wich will add the [CONTENT] value inside each <a> tag like this : <a href="#">[CONTENT]Link_name</a>
 *		Use this for example to add a span tag like this : <a href="#"><span>&nbsp;</span>Link_name</a>. So, you will be able to customize the menu by using css styles...
 */
function comMenu_menu( $menu_id, $param = array(), $parent_id = 0, $level = 0 )
{
	static $link_html_list = '';
	$level == 0 ? $link_html_list = '' : ''; # Reset output when begining each menu

	/*
	 * Check $param
	 */

	// class : check this level / the 'all' levels parameters
	    if (isset($param[$level]['ul']['class'])) $ul_class = $param[$level]['ul']['class'];
	elseif (isset($param['all' ]['ul']['class'])) $ul_class = $param['all' ]['ul']['class'];
	else $ul_class = false;

	    if (isset($param[$level]['li']['class'])) $li_class = $param[$level]['li']['class'];
	elseif (isset($param['all' ]['li']['class'])) $li_class = $param['all' ]['li']['class'];
	else $li_class = false;

	    if (isset($param[$level]['a' ]['class'])) $a_class  = $param[$level]['a' ]['class'];
	elseif (isset($param['all' ]['a' ]['class'])) $a_class  = $param['all' ]['a' ]['class'];
	else $a_class = false;

	    if (isset($param[$level]['a:active']['class'])) $a_active_class = $param[$level]['a:active']['class'];
	elseif (isset($param['all' ]['a:active']['class'])) $a_active_class = $param['all' ]['a:active']['class'];
	else $a_active_class = false;

	// id : Check this level parameters
	isset($param[$level]['ul']['id']) ? $ul_id = $param[$level]['ul']['id'] : $ul_id = false;
	isset($param[$level]['li']['id']) ? $li_id = $param[$level]['li']['id'] : $li_id = false;
	isset($param[$level]['a' ]['id']) ? $a_id  = $param[$level]['a' ]['id'] : $a_id  = false;

	// Look after the special $param['add_content_inside_link']
	isset($param['add_content_inside_link']) ? $add_content_inside_link = $param['add_content_inside_link'] : $add_content_inside_link = '';

	/*
	 * Get menu
	 */

	global $g_user_login;
	global $db;
	$link =
		$db->select(
				"menu_link, id, name, href, unique_id, link_type_id, link_order(asc), params, " .
				"where: menu_id=$menu_id AND, where: parent_id=$parent_id AND, " .
				'where: access_level>='.$g_user_login->accessLevel().' AND, where: published=1'
		);

	// Let's go !
	if ($link)
	{
		$link_html_list .= '<ul'.comMenu_css($ul_id, 'id').comMenu_css($ul_class).'>';
		for ($i=0; $i<count($link); $i++)
		{
			$link_html_list .= '<li'.comMenu_css($li_id, 'id').comMenu_css($li_class).'>';

			$link_html_list .= comMenu_linkEncoder($link[$i], $a_id, $a_class, $a_active_class, $add_content_inside_link);
			comMenu_menu($menu_id, $param, $link[$i]['id'], $level+1);

			$link_html_list .= '</li>';
		}
		$link_html_list .= "</ul>\n";
	}
	return $link_html_list;
}



function comMenu_css( $css, $attr = 'class' )
{
	$css = trim($css);

	if (!$css) {
		return '';
	}
	in_array($attr, array('class', 'id')) or trigger_error("Invalid \$attr=$attr");

	return " $attr=\"$css\"";
}



/**
 * @param string $attr_current = ' class="class1"'
 * @param string $css_new = 'class2'
 * @return string = ' class="class1 class2"'
 */
function comMenu_addCSS( $attr_current, $css_new, $attr = 'class' )
{
	$css_new = trim($css_new);

	if (!$css_new) {
		return $attr_current;
	}
	in_array($attr, array('class', 'id')) or trigger_error("Invalid \$attr=$attr");

	$css_current = '';
	if ($attr_current) {
		preg_match("~$attr=\"(.+)\"~", $attr_current, $matches);
		if (isset($matches[1])) {
			$css_current = trim($matches[1]);
		}
	}
	!$css_current or $css_current .= ' ';

	$attr_new = " $attr=\"$css_current{$css_new}\"";
	return $attr_new;
}



/*
 * Menu behaviour : css menu, using jQuery, ...
 */

function comMenu_behaviour( $alias = '' )
{
	$param = array();

	switch( $alias )
	{
		case 'accordion':
			$param[0]['ul']['class'] = 'accordion';
			$param[0]['a' ]['class'] = 'accordion-link';
			$param[1]['ul']['class'] = 'accordion-list';
			$param['all']['a:active']['class'] = 'accordion-active';
			break;

		case 'superfish':
			$param[0]['ul']['class'] = 'sf-menu';
			$param['all']['a:active']['class'] = 'active'; # simply add class="active" for the current activated link
			break;

		case 'horizontal':
	  		$param['add_content_inside_link'] = '<span>&nbsp;</span>';
			# Then, follow the default behaviour (no break; instruction)

		default:
			$param[0]['ul']['class'] = 'menu';
			$param['all']['a:active']['class'] = 'active'; # simply add class="active" for the current activated link
			break;
	}

	return $param;
}



function comMenu_behaviourList()
{
	return
		array(
			''			=> LANG_COM_MENU_BEHAVIOUR_DEFAULT,
			'superfish'	=> LANG_COM_MENU_BEHAVIOUR_SUPERFISH_HORIZONTAL,
			'accordion'	=> LANG_COM_MENU_BEHAVIOUR_ACCORDION,
		);
}



/*
 * Pathway
 */
function comMenu_pathway( $separator = ' &raquo; ', $not_on_home_page = false, $home_page_link_css_id_atrr = 'home-link' )
{
	$pathway_html = '';

	global $db;

	// Home page link
	$link_home = $db->select(comMenu_homePageQuery());
	if (count($link_home))
	{
		$link_home[0]['name'] = LANG_COM_MENU_PATHWAY_HOME; # Overwrite the link name
		$pathway_html = comMenu_linkEncoder($link_home[0], $home_page_link_css_id_atrr);
	}

	// Others links
	$pathway_link = array();
	global $g_page;
	$g_page['link_id'] ? $id = $g_page['link_id'] : $id = 0;

	while ($id != 0)
	{
		$link = $db->select("menu_link, id,name,href,unique_id,link_type_id,parent_id,params, where: id=$id");
		if (count($link))
		{
			# Why using strip_tags() ? Be sure there's no <span> tag in this 'name' ; then add css styles to <a> (link) and/or <span> (separator) tags as you need
			$link[0]['name'] = strip_tags($link[0]['name']);
			$pathway_link[] = comMenu_linkEncoder($link[0]);

			$id = $link[0]['parent_id'];
		}
		else {
			$id = 0;
		}
	}

	// $delta ? Do not include twice the 'home' link
	$delta = 0;
	if ( ((isset($link)) && (count($link))) && (count($link_home)) ) # Here the last "other" link (and also the home link)
	{
		if ($link[0]['id'] == $link_home[0]['id']) {
			$delta = 1;
		}
 	}

	// Add the others pages links
	for ($i=count($pathway_link)-1-$delta; $i>=0; $i--) {
		$pathway_html .= $separator.$pathway_link[$i];
	}

	if ( ($not_on_home_page) && (($g_page['link_id']) && (count($link_home))) )
	{
		if ($g_page['link_id'] == $link_home[0]['id']) {
			$pathway_html = ''; # Reset output
		}
	}

	$tmpl_path =
		array(
			sitePath().$g_page['template_dir'			].'/positions/pathway.html',
			sitePath().$g_page['template_default_dir'	].'/positions/pathway.html'
		);

	$template = new templateManager();
	$pathway_html = $template->setTmplPath($tmpl_path)->setReplacements(array('pathway' => $pathway_html))->process();

	echo $pathway_html;
}



/*
 * Site map
 */
class comMenu_siteMap
{
	protected	$menu_order;

	public		$exclude_menu,
				$exclude_link;



	public function __construct()
	{
		global $db;

		$this->menu_order   = array_keys($db->select("menu_sitemap, [info_id],id(asc), where: info_type='menu_order'"  ));
		$this->exclude_menu = array_keys($db->select("menu_sitemap, [info_id],id,      where: info_type='exclude_menu'"));
		$this->exclude_link = array_keys($db->select("menu_sitemap, [info_id],id,      where: info_type='exclude_link'"));
	}



	public function getMenuOrder()
	{
		global $db;
		$menu = $db->select('menu, id(asc)');

		// Get an ordered list of all menu ID
		$menu_ordered = $this->menu_order;
		for ($i=0; $i<count($menu); $i++)
		{
			if (!in_array($menu[$i]['id'], $this->menu_order))
			{
				// Add the missing menu ID
				$menu_ordered[] = $menu[$i]['id'];
			}
		}

		return $menu_ordered;
	}



	public function getExcludeMenu()
	{
		return $this->exclude_menu;
	}



	public function getExcludeLink()
	{
		return $this->exclude_link;
	}



	public function display()
	{
		$html = '';

		global $db;
		$menu = $db->select('menu, [id], comment');

		$menu_ordered = $this->getMenuOrder();

		for ($i=0; $i<count($menu_ordered); $i++)
		{
			if (!in_array($menu_ordered[$i], $this->exclude_menu))
			{
				$html .= "\n<h2>{$menu[$menu_ordered[$i]]['comment']}</h2>\n".$this->links($menu_ordered[$i]);
			}
		}

		return $html;
	}



	/*
	 * This method is an adaptation of the function admin_comMenu_getMenu()
	 */
	protected function links( $menu_id, $parent_id = 0, $level = 0 )
	{
		static $menu_links = '';
		$parent_id == 0 ? $menu_links = '' : ''; # Reset when begining

		$level_tab = '';
		for ($i=0; $i<$level; $i++) {
			$level_tab .= "\t\t";
		}

		global $g_user_login;
		global $db;
		$link =
			$db->select(
				'menu_link, id,name,href,unique_id,link_type_id,link_order(asc),params, '.
				"where: menu_id=$menu_id AND, where: parent_id=$parent_id AND, ".
				'where: access_level>='.$g_user_login->accessLevel()
			);

		if ($link)
		{
			$menu_links .= "\n$level_tab<ul>\n";
			for ($i=0; $i<count($link); $i++)
			{
				if (!in_array($link[$i]['id'], $this->exclude_link))
				{
					$menu_links .= "$level_tab\t<li>\n";
					$menu_links .= "$level_tab\t\t".comMenu_linkEncoder($link[$i]);

					$this->links($menu_id, $link[$i]['id'], $level+1);

					$menu_links .= "</li>\n";
				}
			}
			$menu_links .= "$level_tab</ul>";
		}

		$level != 0 or $menu_links = "$menu_links\n\n";
		return $menu_links;
	}

}


?>