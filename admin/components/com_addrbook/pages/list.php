<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'addrbook_list');

$load_ckeditor = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('start_', 'post');
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger();
	$upd = $filter->requestName ('upd_'	)->getInteger();
	$new = $filter->requestValue('new'	)->get();
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post');



// Case 'del'
if ($del)
{
	if ($result1 = $db->delete("addrbook_filter_search; where: addrbook_id=$del"))
	{
		$result2 = $db->delete("addrbook; where: id=$del");
	} else {
		$result2 = false;
	}
	admin_informResult($result1 && $result2);
}



if ($new)
{
	$filter->reset();

	if ($name = $filter->requestValue('new_name')->getNotEmpty(1, '', LANG_ADMIN_COM_ADDRBOOK_LIST_NEW_FIELDSET))
	{
		if ($db->selectOne('addrbook, id, where: name='.$db->str_encode($name), 'id'))
		{
			$filter->set(false, 'new_name')->getError(LANG_ADMIN_COM_ADDRBOOK_LIST_ERROR_DUPLICATE, LANG_ADMIN_COM_ADDRBOOK_LIST_NEW_FIELDSET);
		}
		elseif ($db->insert('addrbook; col: name; '.$db->str_encode($name)))
		{
			// Go now to the update form !
			$upd = $db->insertID();
		}
	}

	if (!$filter->validated()) {
		echo $filter->errorMessage();
	}
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	// Fields validation
	$upd_id 	= $filter->requestValue('id'		)->getInteger();

	$name 		= $filter->requestValue('name'		)->getNotEmpty();
	$address 	= $filter->requestValue('address'	)->get();
	$zip 		= $filter->requestValue('zip'		)->getInteger(0);
	$city 		= $filter->requestValue('city'		)->get();
	$state 		= $filter->requestValue('state'		)->get();
	$country 	= $filter->requestValue('country'	)->get();

	$phone 		= $filter->requestValue('phone'		)->getInteger(0);
	$fax 		= $filter->requestValue('fax'		)->getInteger(0);
	$email 		= $filter->requestValue('email'		)->getEmail(0);
	$web 		= $filter->requestValue('web'		)->getPath(0);

	$comment 	= $filter->requestValue('comment'	)->get();

	// Duplicate name ?
	if ($db->selectOne("addrbook, id, where: id!=$upd_id AND, where: name=".$db->str_encode($name), 'id')) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_ADDRBOOK_LIST_ERROR_DUPLICATE, LANG_ADMIN_COM_ADDRBOOK_LIST_NEW_FIELDSET);
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		// Merge all datas in the search field in fulltext
		$search =
			comSearch_::htmlEntitiesToChar(
				preg_replace('~(\n)+~', "\n",
					preg_replace(
						'~(\t|\n|\r)~', "\n",
							strip_tags( "$name\n$address\n$zip\n$city\n$state\n$country\n$phone\n$fax\n$email\n$web\n$comment" )
					)
				)
			);

		$result1 = $db->update(
			'addrbook;'.
			'  name='		.$db->str_encode($name		).
			', address='	.$db->str_encode($address	).
			', city='		.$db->str_encode($city		).
			', state='		.$db->str_encode($state		).
			', country='	.$db->str_encode($country	).
			', zip='		.$db->str_encode($zip		).
			', phone='		.$db->str_encode($phone		).
			', fax='		.$db->str_encode($fax		).
			', email='		.$db->str_encode($email		).
			', web='		.$db->str_encode($web		).
			', comment='	.$db->str_encode($comment	).
			', search='		.$db->str_encode($search	).
			"; where: id=$upd_id"
		);

		if ($result1)
		{
			$db->delete("addrbook_filter_search; where: addrbook_id=$upd_id");

			$addrbook = new comAddrbook('upd_', false);
			$addrbook->processFilters();
			$options = $addrbook->getOptions();

			$result2 = true;
			for ($i=0; $i<count($options); $i++)
			{
				$db->insert("addrbook_filter_search; col: addrbook_id,option_id; $upd_id, {$options[$i]}") or $result2 = false;
			}
		}

		admin_informResult($result1 && $result2);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_LIST_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("addrbook, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	/*
	 * Fieldset 1
	 */
	$fieldset = '';

	$fieldset .= $form->text('name'		, $current['name'	], LANG_ADDRBOOK_NAME		, '', 'wrapper=div.label-100px;size=50' ).'<hr />';

	$fieldset .= $form->text('address'	, $current['address'], LANG_ADDRBOOK_ADDRESS	, '', 'wrapper=div.label-100px' );
	$fieldset .= $form->text('zip'		, $current['zip'	], LANG_ADDRBOOK_ZIP		, '', 'wrapper=div.label-100px;size=5' );
	$fieldset .= $form->text('city'		, $current['city'	], LANG_ADDRBOOK_CITY		, '', 'wrapper=div.label-100px;size=default' );
	$fieldset .= $form->text('state'	, $current['state'	], LANG_ADDRBOOK_STATE		, '', 'wrapper=div.label-100px' );
	$fieldset .= $form->text('country'	, $current['country'], LANG_ADDRBOOK_COUNTRY	, '', 'wrapper=div.label-100px' ).'<hr />';

	$fieldset .= $form->text('phone'	, $current['phone'	], LANG_ADDRBOOK_PHONE		, '', 'wrapper=div.label-100px;size=10' );
	$fieldset .= $form->text('fax'		, $current['fax'	], LANG_ADDRBOOK_FAX		, '', 'wrapper=div.label-100px' );
	$fieldset .= $form->text('email'	, $current['email'	], LANG_ADDRBOOK_EMAIL		, '', 'wrapper=div.label-100px;size=50' );
	$fieldset .= $form->text('web'		, $current['web'	], LANG_ADDRBOOK_WEB		, '', 'wrapper=div.label-100px' ).'<hr />';

	$fieldset .= $form->textarea('comment', $current['comment'], LANG_ADDRBOOK_COMMENT	, '', 'cols=60;rows=6'.($load_ckeditor ? '' : ';wrapper=div.label-100px') );

	if ($load_ckeditor) {
		$my_CKEditor = new loadMyCkeditor();
		$fieldset .= $my_CKEditor->addName("comment");
	}

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_ADDRBOOK_LIST_UPD_FIELDSET_1);

	/*
	 * Fieldset 2
	 */

	$fieldset = '';

	$addrbook = new comAddrbook('upd_', false);
	$addrbook->bookOptions($upd_id);
	$fieldset .= $addrbook->getAllFilters();

	if ($fieldset) {
		$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_ADDRBOOK_LIST_UPD_FIELDSET_2);
	}

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	// For debugging, you can check the 'search' field (fulltext)
	$html .= '<br /><br />'.$form->textarea('search', $current['search'], LANG_ADDRBOOK_SEARCH.'<br />', '', 'cols=100;rows=10;disabled');

	$html .= $form->end();
	echo $html;

	?>
	<!-- Toggle the search textarea for debugging -->
	<script type="text/javascript">
	$(document).ready(function(){
		$("#upd_search").hide();
		$("label[for='upd_search']").css("cursor", "pointer").click(function(){
			$('#upd_search').toggle();
		});
	});
	</script>
	<?php
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_LIST_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Multipage
	$multipage = new simpleMultiPage( $db->selectCount('addrbook') );
	$multipage->setFormID('start_');
	$multipage->updateSession($session->returnVar('multipage'));

	$html .=
		admin_floatingContent(
			array(
				$multipage->numPerPageForm(),
				$multipage->navigationTool(false, 'admin_'),
				$form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT)
			)
		);

	// Database
	$list = $db->select('addrbook, id, name(asc), address, zip, city, state, country; limit:'.$multipage->dbLimit());

	for ($i=0; $i<count($list); $i++)
	{
		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

		$list[$i]['id'] = "<span style=\"color:#999;\">{$list[$i]['id']}</span>"; # Carefull : ID info no more available
	}

	// Table
	$table = new tableManager($list,
		array(
			'ID',
			LANG_ADDRBOOK_NAME,
			LANG_ADDRBOOK_ADDRESS,
			LANG_ADDRBOOK_ZIP,
			LANG_ADDRBOOK_CITY,
			LANG_ADDRBOOK_STATE, 
			LANG_ADDRBOOK_COUNTRY
		)
	);
	//$table->delCol(0); # Delete the 'id' column

	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$html .= $table->html();

	$fieldset = $form->text('new_name', '', LANG_ADDRBOOK_NAME, '', 'size=50');
	$fieldset .= $form->submit('new', LANG_ADMIN_COM_ADDRBOOK_LIST_NEW_BUTTON);
	$html .= '<br />'.admin_fieldset($fieldset, LANG_ADMIN_COM_ADDRBOOK_LIST_NEW_FIELDSET);

	$html .= $form->end();
	echo $html;
}



echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>