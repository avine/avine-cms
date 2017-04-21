<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/*
 * Setup
 *
 * Because com_content is one of the most used components of the system, a special feature have been developped for it.
 * To learn about this feature, here an example.
 *
 * - Instead of this full url :
 * '/index.php?com=content&page=index&node=my_node&item=my_item'
 *
 * - You can jump to this item with this simplified url :
 * '/index.php?node=my_node&item=my_item'
 *
 * So, the query part 'com=content&page=index' has been removed from the url !
 *
 * This behaviour affect the followed methods :
 * - comGeneric_frontend::pageUrlRequest()
 * - comGeneric_frontend::nodeSelector()
 *
 * Now, the com_menu component has integrated this beahaviour and some special link_type_id has been added :
 * - link_type_id=2 for an element request
 * - link_type_id=3 for a node request
 * So, the simplified url is correctly catched by the com_menu component
 *
 * Just to remember, notice that also the com_module component has integrated a special behaviour to improve the com_content component.
 * To learn more, go to : /components/com_module/module_class.php
 *
 * WARNING :	Know limitation of this setup !
 *				This feature was originaly designed to by like a little url-rewriting.
 *				But if the com_rewrite component is enabled, you should disable this feature.
 */
define('COM_CONTENT_ACTIVATE_REDIRECTION', false);



/*
 * Set if the user need to login to access available medias # TODO - Add this to the 'content_config' table...
 */
define('COM_CONTENT_LOGIN_TO_ACCESS_MEDIAS', true);



// Quick integration of the comContent_frontend::scope() method
function comContent_frontendScope()
{
	$com_setup_full_path = sitePath().'/components/com_content/com_setup.php';

	if (COM_CONTENT_ACTIVATE_REDIRECTION)
	{
		$redirection = '';		# The redirection is activated : 'com=content&amp;page=index' now removed from all generated urls
	} else {
		$redirection = false;	# Disabled feature
	}

	$com_content = comContent_frontend::scope($com_setup_full_path, $redirection);
	$com_content->setPageName('index');

	return $com_content;
}



/////////////
// Sub-Class

class comContent_frontend extends comGeneric_frontend
{
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

		# ...

		/**
		 * Costumize 'node_item' fields
		 */

		$link = $this->nodeUrlEncoder($node_details['id']);
		if ($this->pageUrlRequest())
		{
			$node_details['node_link'] = $this->pageUrlRequest().'&amp;'.$link['href'];
		} else {
			$node_details['node_link'] = $link['href'];
		}
		$node_details['node_link'] = comMenu_rewrite($node_details['node_link']);

		// Prepare img alt argument
		$img_alt = str_replace('"', '', $node_details['title']);

		// image
		if ($node_details['image']) {
			$node_details['image'] = '<img src="'.WEBSITE_PATH.$node_details['image'].'" alt="'.$img_alt.'" class="'.$this->config_com_node_name.'-image" />';
		}

		// Special header message for all elements of the node_id
		$node_details['header_msg'] = $this->getHeaderMessage($node_details['id']);
	}



	// Customize the specific 'element_item' controller
	public function elementsSpecificController_customize( &$element_detail, $single_element, $home_page )
	{
		/**
		 * Costumize 'element' fields
		 */

		// date_creation, date_modified (dates format)
		if ($element_detail['date_creation'] != '') {
			$element_detail['date_creation'] = getTime($element_detail['date_creation'], 'time=no');
		}
		if ($element_detail['date_modified'] != '') {
			$element_detail['date_modified'] = getTime($element_detail['date_modified'], 'time=no');
		}

		/**
		 * Costumize 'element_item' fields
		 */

		$link = $this->elementUrlEncoder($element_detail['id']);
		if ($this->pageUrlRequest())
		{
			$element_detail['element_link'] = $this->pageUrlRequest().'&amp;'.$link['href'];
		} else {
			$element_detail['element_link'] = $link['href'];
		}
		$element_detail['element_link'] = comMenu_rewrite($element_detail['element_link']);

		// Prepare img alt argument
		$img_alt = str_replace('"', '', $element_detail['title']);

		// image_thumb & image
		if ($single_element)
		{
			if ($element_detail['image']) {
				$element_detail['image'] = '<img src="'.WEBSITE_PATH.$element_detail['image'].'" alt="'.$img_alt.'" class="'.$this->config_com_element_name.'-image" />';
			}
			$element_detail['image_thumb'] = '';
		}
		else
		{
			if ($element_detail['image_thumb']) {
				$element_detail['image_thumb'] = '<img src="'.WEBSITE_PATH.$element_detail['image_thumb'].'" alt="'.$img_alt.'" class="'.$this->config_com_element_name.'-image" />';
			}
			$element_detail['image'] = '';
		}

		// New! icon
		if (isset($element_detail['new']) && $element_detail['new']) {
			$element_detail['new'] = '<img src="'.WEBSITE_PATH.'/components/com_content/images/new.png" alt="'.$element_detail['new'].'" />';
		}

		// text_main
		if (!$single_element)
		{
			if ($element_detail['text_main'] != '') {
				$element_detail['text_main'] = '';
				$element_detail['read_more'] = str_replace('{href}', $element_detail['element_link'], LANG_COM_CONTENT_READ_MORE);
			} else {
				$element_detail['read_more'] = '';
			}
		}
		else
		{
			// Share with "Add this button" (v2)
			$element_detail['share'] =
				'<!-- AddThis Button BEGIN -->' ."\n".
				'<div class="addthis_toolbox addthis_default_style ">' ."\n".
				'	<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>' ."\n".
				'	<a class="addthis_button_tweet"></a>' ."\n".
				#'	<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>' ."\n".
				'</div>' ."\n".
				'<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e6f105f41644a08"></script>' ."\n".
				'<!-- AddThis Button END -->' ."\n";

			/*// Share with "Add this button" (v1)
			$element_detail['share'] =
				'<!-- AddThis Button BEGIN -->' ."\n".
				'<div class="addthis_toolbox addthis_default_style ">' ."\n".
				'	<a class="addthis_button_preferred_1"></a>' ."\n". # Facebook
				'	<a class="addthis_button_preferred_4"></a>' ."\n". # Twitter
				'	<a class="addthis_counter addthis_bubble_style"></a>' ."\n".
				'</div>' ."\n".
				'<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e6f0e560709d137"></script>' ."\n".
				'<!-- AddThis Button END -->';*/

			// Facebook like button (http://developers.facebook.com/docs/reference/plugins/like)
			#$element_detail['share'] = '<script src="http://connect.facebook.net/fr_FR/all.js#xfbml=1"></script><div style="height:135px;"><fb:like href="'.$element_detail['element_link'].'" layout="button_count" action="recommend"></fb:like></div>';

			// Facebook share button (http://developers.facebook.com/docs/share)
			#$element_detail['share'] = '<a name="fb_share" type="button" share_url="'.$element_detail['element_link'].'">Partager</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>';
		}

		//////////
		// Medias

		$medias = new mediasManager();
		$medias->stringSet($element_detail['medias']);
		$medias_count = count($medias->arrayGet());						# Remember the count of medias

		if ($medias_count)
		{
			# Notice: comment the next line to disable this feature
			$medias_button = mediasManager::mediaPreferenceButton();	# Activate the medias preference feature

			if ($single_element)
			{
				global $g_user_login;
				if (!COM_CONTENT_LOGIN_TO_ACCESS_MEDIAS || $g_user_login->userID())
				{
					$element_detail['medias'] = $medias->showMedias(WEBSITE_PATH);

					if (count($medias->arrayGet()) != $medias_count) {
						$element_detail['medias_button'] = $medias_button;	# The feature has reduce the medias count. So, display the preference button !
					}
				}
				else
				{
					$login_message = searchAndReplace(
						LANG_COM_CONTENT_LOGIN_TO_ACCESS_MEDIAS,
						array(
							'{href}'	=> comMenu_rewrite('com=user&page=login'),
							'{id}'		=> 'com-content-login-link'
						)
					);
					$element_detail['medias'] = userMessage($login_message, 'warning');

					/*
					 * jQuery : login form in modal dialog
					 */
					if (is_file(sitePath().'/modules/mod_login.php'))
					{
						// Get the login form
						ob_start();
						require(sitePath().'/modules/mod_login.php');
						$login_form = ob_get_contents();
						ob_end_clean();

						// Prepare the Html to be inserted in javascript
						$login_form = str_replace("'", "\'", preg_replace('~(\n|\r|\t)~', ' ', $login_form));
						$login_form_title = str_replace("'", "\'", preg_replace('~(\n|\r|\t)~', ' ', LANG_COM_CONTENT_LOGIN_FORM_TITLE));

						// Show the login-form on login-process-failure
						(comUser_login::processResult() === false) ? $trigger_js = "$('#com-content-login-link').trigger('click')" : $trigger_js = '';

						/*
						 * On click, show login form !
						 *
						 * Notice : the .parent().parent().parent() methods are required for Safari
						 * to add the '<div id="com-content-login-form"></div>' outside of the '<p class="box"></p>' (generated by the PHP userMessage() function)
						 */
						$element_detail['medias'] .=
							"	\n\n<!-- jQuery : on click, show login form in modal dialog -->\n<script type=\"text/javascript\">//<![CDATA[\n".
							"	$(document).ready(function(){"																											."\n".
							"		$('#com-content-login-link').click(function() {"																					."\n".
							"			$(this).parent().parent().parent().after('<div id=\"com-content-login-form\" title=\"$login_form_title\">$login_form</div>');"	."\n".
							"			$(\"#com-content-login-form\").dialog({ position:['center',200], width:230, height:260, modal:true });"							."\n".
							"			return false;"																													."\n".
							"		});"																																."\n".
							"		$trigger_js"																														."\n".
							"	});"																																	."\n".
							"	//]]></script>\n\n";
					}
				}
			}
			else {
				$element_detail['medias'] = $medias->showMediasPreview(/*$element_detail['element_link']*/);
			}
		}
		// End of medias

		// Add all-in-one for : author, date_creation, date_modified, hits
		$infos =
			array(
				'author' 		=> LANG_COM_GENERIC_AUTHOR,
				'date_creation' => LANG_COM_GENERIC_DATE_CREATION,
				'date_modified' => LANG_COM_GENERIC_DATE_MODIFIED,
				'hits' 			=> LANG_COM_GENERIC_HITS
			);
		$item_infos = array();
		foreach ($infos as $key => $value) {
			(isset($element_detail[$key]) && $element_detail[$key]) ? $item_infos[] = "<span>$value&nbsp;:&nbsp;</span>".$element_detail[$key] : '';
		}
		if (count($item_infos)) {
			$element_detail['item_infos'] = '<p class="item-infos">'.implode(' &nbsp;&middot;&nbsp; ', $item_infos).'</p>';
		} else {
			$element_detail['item_infos'] = '';
		}

		if ($single_element)
		{
			// Add dedicate from wcal component
			if ($match = wcal::matchDedicate($element_detail['id']))
			{
				$element_detail['dedicate'] =
					#'<h3 class="content-dedicate-title">'.LANG_COM_WCAL_THE_DEDICATES."</h3>\n".
					wcal::matchDedicateHTML($match);
			}
		}

		// Special header message for all elements of the node_id
		$element_detail['header_msg'] = $this->getHeaderMessage($element_detail['node_id']);
	}

	// End of Cutomization
	//////////////////////



	public function getHeaderMessage( $node_id )
	{
		$html = '';

		global $db;
		if ($msg = $db->select($this->table_prefix."header_msg, message, date_creation(desc), where: node_id=$node_id")) {
			for ($i=0; $i<count($msg); $i++)
			{
				$date_creation = LANG_COM_WCAL_LIST_RECORDING_DATE.' : '.getTime($msg[$i]['date_creation'], 'time=no');

				/*
				 * Note : the HTML and CSS of this method come from the wcal component
				 * (see the method wcal::matchDedicateHTML() for more details)
				 */
				$html .= <<<END
<!-- Header message -->
<div class="wcal-comment-wrapper content-header-msg">
	<div class="wcal-comment wcal-comment-first wcal-comment-last">
		<p>{$msg[$i]['message']}</p>
		<h5>&nbsp; <span>$date_creation</span></h5>
		<div class="content-header-msg-icon"></div>
	</div>
</div>

END;
			}
		}
		return $html;
	}

}



// Get the latest com_content elements
class comContent_getLatest
{
	protected	$days_offset		= 30,
				$date_reference		= 'modified',	# 2 values : 'modified' or 'creation'

				$published			= '1',			# 2 values : '1' or '0'
				$access_level		= NULL,			# Initialized in the constructor

				$author_id			= false,

				$check_dates		= true,			# (boolean) Keep only the current online elements (date_online, date_offline)

				$limit				= false;		# If you need to limit the maximum number of results



	public function __construct() {
		$this->access_level = comUser_getLowerStatus();
	}

	public function daysOffset( $days_offset ) {
		$this->days_offset = $days_offset;
		return $this;
	}

	public function dateReference( $date_reference ) {
		if (in_array($date_reference, array('modified', 'creation')))
		{
			$this->date_reference = $date_reference;
		} else {
			trigger_error("Invalid parameter \$date_reference = $date_reference (expected : 'modified' or 'creation')");
		}
		return $this;
	}

	public function published( $published ) {
		$published ? $this->published = '1' : $this->published = '0';
		return $this;
	}

	public function accessLevel( $access_level ) {
		$this->access_level = $access_level;
		return $this;
	}

	public function authorID( $author_id ) {
		$this->author_id = $author_id;
		return $this;
	}

	public function checkDates( $check_dates ) {
		$check_dates ? $this->check_dates = true : $this->check_dates = false;
		return $this;
	}

	public function limit( $limit ) {
		$this->limit = $limit ? $limit : false;
		return $this;
	}



	/**
	 * @return The keys of the returned array are the element_id
	 */
	public function elementsSummary()
	{
		$latest_elements = array();

		// Build author_id query
		$this->author_id ? $author_id = "where: author_id=$this->author_id AND," : $author_id = '';

		// Alias to 'date_modified' or 'date_creation'
		$date_key = "date_$this->date_reference";

		// Limit the maximum number of results
		$limit = $this->limit ? "limit: 0,$this->limit" : '';

		// Get latest elements
		global $db;
		$elements =
			$db->select(
				"content_node_item, title AS node_title,
					join: node_id>; ".

				"content_element, id, date_online, date_offline, $date_key(desc), author_id,
					$author_id
					where: $date_key>=".(time() - 60*60*24 *$this->days_offset)." AND,
					where: published=$this->published AND, where: access_level>=$this->access_level AND, where: archived=0,
					join: <node_id|id>; ".

				"content_element_item, title, title_alias, image_thumb, text_intro,
					join: <element_id; ".

				$limit
			);

		// Keep only the current online elements
		if ($this->check_dates)
		{
			$temp = array();
			for ($i=0; $i<count($elements); $i++)
			{
				// Check dates if defined
				if (comGeneric_::checkDates($elements[$i]['date_online'], $elements[$i]['date_offline'])) {
					$temp[] = $elements[$i];
				}
			}
			$elements = $temp;
			unset($temp);
		}

		// No element available !
		if (!count($elements)) {
			return $latest_elements;
		}

		// Instanciate comContent_frontend class object
		$com_content = comContent_frontendScope();

		for ($i=0; $i<count($elements); $i++)
		{
			// element_link
			$link = $com_content->elementUrlEncoder($elements[$i]['id']);
			if ($com_content->pageUrlRequest())
			{
				$element_link = $com_content->pageUrlRequest().'&amp;'.$link['href'];
			} else {
				$element_link = $link['href'];
			}
			$element_link = comMenu_rewrite($element_link);

			// image_thumb
			if ($elements[$i]['image_thumb']) {
				$image_thumb = '<img src="'.siteUrl().$elements[$i]['image_thumb'].'" alt="" />';
			} else {
				$image_thumb = '';
			}

			// date_modified or date_creation
			$date_reference = getTime($elements[$i][$date_key], 'time=no');

			// Fill element
			$latest_elements[$elements[$i]['id']] =
				array(
					'title'			=> $elements[$i]['title'],
					'title_alias'	=> $elements[$i]['title_alias'],
					'text_intro'	=> $elements[$i]['text_intro'],
					'image_thumb'	=> $image_thumb,
					'element_link'	=> $element_link,
					$date_key		=> $date_reference,
					'username'		=> $db->selectOne('user, username, where: id='.$elements[$i]['author_id'], 'username'),

					'node_title'	=> $elements[$i]['node_title'] # Special : node information !
				);
		}

		return $latest_elements;
	}

}


?>