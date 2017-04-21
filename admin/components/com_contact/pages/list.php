<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


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

$new_submit = formManager::isSubmitedForm('new_', 'post');
$upd_submit = formManager::isSubmitedForm('upd_', 'post');



// Case 'del'
if ($del)
{
	$db->delete("contact; where: user_id=$del");
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;

	$filter->reset();

	$user_id	= $filter->requestValue('user_id')->getInteger(1, '', LANG_COM_USER_USERNAME);
	($user_id != 0) or $filter->set(false, 'user_id')->getError(LANG_FORM_MANAGER_FILTER_IS_NOT_FILLED, LANG_COM_USER_USERNAME);

	$title		= $filter->requestValue('title')->get();

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult( $db->insert("contact; $user_id, 9999, ".$db->str_encode($title)) );
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTACT_LIST_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	// User options (short code, but even unactivated users are included in the list)
	$options = array_diff_key(admin_comUser_getUserOptions(), $db->select('contact, [user_id]'));
	/*
	// User options (long code, but only the activated users are included in the list)
	$options = array('' => LANG_SELECT_OPTION_ROOT);
	$current_contact = $db->select('contact, [user_id]');
	$user = $db->select('user, [id], username(asc), where: activated=1');
	foreach($user as $id => $info)
	{
		if (!array_key_exists($id, $current_contact)) {
			$options[$id] = $info['username'];
		}
	}
	*/
	$html .= $form->select('user_id', $options, LANG_COM_USER_USERNAME.'<br />').'<br /><br />';

	// Title
	$html .= $form->text('title', '', LANG_ADMIN_COM_CONTACT_TITLE.' <span class="grey">('.LANG_ADMIN_COM_CONTACT_LIST_NEW_TITLE_OPTIONAL.')</span><br />');
	$html .= '<br /><span class="grey">'.LANG_ADMIN_COM_CONTACT_LIST_NEW_TITLE_OPTIONAL_TIPS.'</span><br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	$upd_id	= $filter->requestValue('upd_id')->getInteger(1, '', LANG_COM_USER_USERNAME);
	$title	= $filter->requestValue('title')->get();

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult( $db->update("contact; title=".$db->str_encode($title)."; where: user_id=$upd_id") );
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTACT_LIST_TITLE_UPD.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');

	// user_id
	isset($upd_id) or $upd_id = $upd;
	$html .= $form->hidden('upd_id', $upd_id);

	// Current contact
	$contact = $db->selectOne("contact, *, where: user_id=$upd_id");

	// User options
	$html .= LANG_COM_USER_USERNAME.' : <strong>'.$db->selectOne("user, username, where: id=$upd_id", 'username').'</strong><br /><br />';

	// Title
	$html .= $form->text('title', $contact['title'], LANG_ADMIN_COM_CONTACT_TITLE.' <span class="grey">('.LANG_ADMIN_COM_CONTACT_LIST_NEW_TITLE_OPTIONAL.')</span><br />');
	$html .= '<br /><span class="grey">'.LANG_ADMIN_COM_CONTACT_LIST_NEW_TITLE_OPTIONAL_TIPS.'</span><br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Update contact order
if ($contact_id = formManager_filter::arrayOnly($filter->requestName('order_')->get()))
{
	for ($i=0; $i<count($contact_id); $i++)
	{
		$order = $filter->requestValue('order_'.$contact_id[$i])->getInteger();
		if ($order !== false) {
			$db->update('contact; contact_order='.$order.'; where: user_id='.$contact_id[$i]);
		}
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_CONTACT_LIST_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	// Contacts list
	$list = array();
	$contact = $db->select('contact, user_id, contact_order(asc), title');
	for ($i=0; $i<count($contact); $i++)
	{
		$uid = $contact[$i]['user_id'];
		$user = new comUser_details($uid);

		if ($user->isInvalidUserID())
		{
			// The user account has gone ! Remove this contact.
			$db->delete("contact; where: user_id=$uid");
		}
		else
		{
			$username = $user->get('username');
			$user->get('activated') or $username = "<span class=\"grey\"><i>$username</i></span>"; # Unactivated user will not appear in frontend

			$list[] =
				array(
					$form->submit("del_$uid", LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'),
					$contact[$i]['user_id'],
					"<a href=\"".comMenu_rewrite("com=contact&amp;page=index&amp;id=$uid")."\" class=\"external\">$username</a>",
					$form->text("order_$uid", 2*$i+1, '', '', 'size=2'),
					$contact[$i]['title'],
					$form->submit("upd_$uid", LANG_ADMIN_BUTTON_UPDATE, '', 'image=update')
				);
		}
	}

	$headers = array(
		'',
		LANG_COM_USER_ID,
		LANG_COM_USER_USERNAME,
		LANG_ADMIN_COM_CONTACT_ORDER,
		LANG_ADMIN_COM_CONTACT_TITLE,
		''
	);

	// Table
	$table = new tableManager($list, $headers);
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>