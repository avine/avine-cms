<?php


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
$publish_status = $filter->requestValue('publish_status', 'get')->getInteger();

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
$new_submit = formManager::isSubmitedForm('new_', 'post');



// Case 'publish_status'
if ($publish_status)
{
	$published = $db->select("addrbook_filter, published, where: id=$publish_status");
	if ($published)
	{
		$published[0]['published'] == 1 ? $published = '0' : $published = '1';
		$db->update("addrbook_filter; published=$published; where: id=$publish_status");
	}
}



// Case 'del'
if ($del)
{
	if (!$db->select("addrbook_filter_option, id, where: filter_id=$del"))
	{
		admin_informResult( $db->delete("addrbook_filter; where: id=$del") );
	} else {
		admin_message(LANG_ADMIN_COM_ADDRBOOK_FILTER_DEL_ERROR, 'error');
	}
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$id_alias	= $filter->requestValue('id_alias'	)->getVar		(1, '', LANG_ADDRBOOK_FILTER_ID_ALIAS	);
	$name		= $filter->requestValue('name'		)->getNotEmpty	(1, '', LANG_ADDRBOOK_FILTER_NAME		);

	// Duplicate name or id_alias ?
	$id_alias	&& $db->selectOne("addrbook_filter, id, where: id_alias="	.$db->str_encode($id_alias	), 'id') ? $filter->set(false, 'id_alias'	)->getError(LANG_ADMIN_COM_ADDRBOOK_FILTER_ALIAS_ERROR	) : '';
	$name		&& $db->selectOne("addrbook_filter, id, where: name="		.$db->str_encode($name		), 'id') ? $filter->set(false, 'name'		)->getError(LANG_ADMIN_COM_ADDRBOOK_FILTER_NAME_ERROR	) : '';

	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult( $db->insert('addrbook_filter; col: name,id_alias; '.$db->str_encode($name).','.$db->str_encode($id_alias)) );
	} else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_FILTER_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('id_alias', '', LANG_ADDRBOOK_FILTER_ID_ALIAS.'<br />').'<br /><br />';
	$html .= $form->text('name'	, '', LANG_ADDRBOOK_FILTER_NAME.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	// Fields validation
	$upd_id	= $filter->requestValue('id')->getInteger();
	$name	= $filter->requestValue('name')->getNotEmpty();

	// Duplicate name ?
	if ($db->selectOne("addrbook_filter, id, where: id!=$upd_id AND, where: name=".$db->str_encode($name), 'id')) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_ADDRBOOK_FILTER_NAME_ERROR);
	}

	// Database process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult( $db->update('addrbook_filter; name='.$db->str_encode($name)."; where: id=$upd_id") );
	} else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_FILTER_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("addrbook_filter, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= '<p>'.LANG_ADDRBOOK_FILTER_ID_ALIAS.' : <strong>'.$current['id_alias'].'</strong></p>';
	$html .= $form->text('name'	, $current['name'], LANG_ADDRBOOK_FILTER_NAME.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Update published status
if ($submit)
{
	$order = array();
	$id = formManager_filter::arrayOnly($filter->requestName('filter_order_')->getInteger());
	for ($i=0; $i<count($id); $i++) {
		$o = $filter->requestValue('filter_order_'.$id[$i])->getInteger();
		if ($o !== false) {
			$order[$o] = $id[$i];
		}
	}
	ksort($order);
	reset($order);
	$o = 0;
	foreach ($order as $id) {
		$o = 2*$o+1;
		$db->update("addrbook_filter; filter_order=$o; where: id=$id");
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_ADDRBOOK_FILTER_TITLE_START.'</h2>';

	$html = '';

	// Database
	$list = $db->select('addrbook_filter, id, id_alias, name, filter_order(asc), published');

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($list); $i++)
	{
		$list[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;publish_status='.$list[$i]['id'].'">'.admin_replaceTrueByChecked($list[$i]['published']).'</a>';

		$list[$i]['filter_order'] = $form->text('filter_order_'.$list[$i]['id'], 2*$i+1, '', '', 'size=1');

		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');

		$list[$i]['id'		] = "<span class=\"grey\">{$list[$i]['id']}</span>";		# Carefull : ID info no more available
		$list[$i]['id_alias'] = "<span class=\"grey\">{$list[$i]['id_alias']}</span>";	# Carefull : ID_ALIAS info no more available
	}

	// Table
	$table = new tableManager($list,
		array(
			'ID',
			LANG_ADDRBOOK_FILTER_ID_ALIAS,
			LANG_ADDRBOOK_FILTER_NAME,
			LANG_ADDRBOOK_FILTER_ORDER,
			LANG_ADDRBOOK_FILTER_PUBLISHED
		)
	);
	$table->delCol(0); # Delete the 'id' column

	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_BUTTON_SUBMIT);
	$html .= $form->submit('new', LANG_BUTTON_CREATE);
	$html .= $form->end();
	echo $html;
}



echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>