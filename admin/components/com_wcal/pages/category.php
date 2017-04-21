<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;

$node_error_message = '<img src="'.WEBSITE_PATH.'/admin/components/com_wcal/images/node_error.png" alt="" title="'.LANG_ADMIN_COM_WCAL_CATEGORY_NODE_ID_MISSING.'" style="cursor:help;" />';


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
$new_submit = formManager::isSubmitedForm('new_', 'post');



/////////////////////
// Load color picker

# Notice : the link to css file is not at the right place (in the header). But nobody's perfect...

$color_picker_path = WEBSITE_PATH.'/plugins/js/colorpicker';

echo <<< END

<!-- jQuery : colorpicker -->
<link rel="stylesheet" media="screen" type="text/css" href="$color_picker_path/css/colorpicker.css" />
<script type="text/javascript" src="$color_picker_path/js/colorpicker.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	$("input[name*=\'color\']").ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	})
	.bind("keyup", function(){
		$(this).ColorPickerSetColor(this.value);
	});
});
</script>

END;

// end
//////



// Case 'del'
if ($del)
{
	if (!$db->selectOne("wcal_event, id, where: category_id=$del", 'id'))
	{
		admin_informResult($db->delete("wcal_category; where: id=$del"));
	} else {
		admin_message(LANG_ADMIN_COM_WCAL_CATEGORY_DEL_ERROR, 'error');
	}
}



// Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	// Get id
	$upd_id = $filter->requestValue('id')->getInteger();

	$title		= $db->str_encode($t = $filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_WCAL_CATEGORY_TITLE));
	$author		= $db->str_encode($filter->requestValue('author'	)->get());

	// Duplicate title-author ?
	if ($t && $db->selectOne("wcal_category, id, where: title=$title AND, where: author=$author AND, where: id!=$upd_id", 'id'))
	{
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_WCAL_CATEGORY_DUPLICATE_TITLE_AUTHOR);
	}

	$comment	= $db->str_encode($filter->requestValue('comment'	)->get());
	$color		= $db->str_encode($filter->requestValue('color'		)->get());

	$node_id	= $filter->requestValue('node_id')->getInteger();
	$node_id or $node_id = 'NULL';

	// Format comment
	$comment = strip_tags($comment, '<a><span><strong><em><b><i><br><img>');

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		admin_informResult($db->update("wcal_category; title=$title, author=$author, comment=$comment, color=$color, node_id=$node_id; where: id=$upd_id"));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_CATEGORY_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("wcal_category, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= $form->text('title'		, $current['title'	], LANG_COM_WCAL_CATEGORY_TITLE		.'<br />', '', 'size=36').'<br /><br />';
	$html .= $form->text('author'		, $current['author'	], LANG_COM_WCAL_CATEGORY_AUTHOR	.'<br />').'<br /><br />';
	$html .= $form->textarea('comment'	, $current['comment'], LANG_COM_WCAL_CATEGORY_COMMENT	.'<br />', '', 'cols=70').'<br /><br />';
	$html .= $form->text('color'		, $current['color'	], LANG_COM_WCAL_CATEGORY_COLOR		.'<br />', '', 'size=7').'<br /><br />';

	$com_content = comContent_frontendScope();
	$node_options = $com_content->getNodesOptions();
	$html .= $form->select('node_id'	, formManager::selectOption($node_options, $current['node_id']), LANG_COM_WCAL_CATEGORY_NODE_ID.'<br />');
	if ($current['node_id'] && !array_key_exists($current['node_id'], $node_options))
	{
		$html .= " $node_error_message";
	}
	$html .= '<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	$title		= $db->str_encode($t = $filter->requestValue('title')->getNotEmpty(1, '', LANG_COM_WCAL_CATEGORY_TITLE));
	$author		= $db->str_encode($filter->requestValue('author'	)->get());

	// Duplicate title-author ?
	if ($t && $db->selectOne("wcal_category, id, where: title=$title AND, where: author=$author", 'id'))
	{
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_WCAL_CATEGORY_DUPLICATE_TITLE_AUTHOR);
	}

	$comment	= $db->str_encode($filter->requestValue('comment'	)->get());
	$color		= $db->str_encode($filter->requestValue('color'		)->get());

	$node_id	= $filter->requestValue('node_id')->getInteger();
	$node_id or $node_id = 'NULL';

	// Format comment
	$comment = strip_tags($comment, '<a><span><strong><em><b><i><br>');

	if ($new_submit_validation = $filter->validated())
	{
		admin_informResult($db->insert("wcal_category; NULL, $title,$author,$comment,$color,$node_id,999"));
	} else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_CATEGORY_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('title'		, '', LANG_COM_WCAL_CATEGORY_TITLE	.'<br />', '', 'size=36').'<br /><br />';
	$html .= $form->text('author'		, '', LANG_COM_WCAL_CATEGORY_AUTHOR	.'<br />').'<br /><br />';
	$html .= $form->textarea('comment'	, '', LANG_COM_WCAL_CATEGORY_COMMENT.'<br />', '', 'cols=70').'<br /><br />';
	$html .= $form->text('color'		, '', LANG_COM_WCAL_CATEGORY_COLOR	.'<br />', '', 'size=7').'<br /><br />';

	$com_content = comContent_frontendScope();
	$html .= $form->select('node_id'	, $com_content->getNodesOptions(), LANG_COM_WCAL_CATEGORY_NODE_ID.'<br />').'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



// Update category_order
if ($submit && ($cat_id = formManager_filter::arrayOnly($filter->requestName('cat_order_')->getInteger())))
{
	$order = array();
	for ($i=0; $i<count($cat_id); $i++)
	{
		$o = $filter->requestValue('cat_order_'.$cat_id[$i])->getInteger();
		($o === false) or $order[$cat_id[$i]] = $o;
	}
	asort($order);
	$cat_id = array_keys($order);

	for ($i=0; $i<count($cat_id); $i++)
	{
		$db->update('wcal_category; category_order='.(2*$i+1).'; where: id='.$cat_id[$i]);
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_WCAL_CATEGORY_TITLE_START.'</h2>';

	$html = '';

	// Database
	$list = $db->select('wcal_category, id, title, author, color, category_order(asc)');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	for ($i=0; $i<count($list); $i++)
	{
		// Color preview
		if ($color = $list[$i]['color']) {
			$list[$i]['color'] = '<div ' .wcal::eventBgStyle("#$color", '', 'border:1px solid #aaa; padding:2px 0;'). '>&nbsp;</div>';
		}

		// order
		$list[$i]['category_order'] = $form->text('cat_order_'.$list[$i]['id'], 2*$i+1, '', '', 'size=2;update=no');

		$update[$i] = $form->submit('upd_'.$list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');
		$delete[$i] = $form->submit('del_'.$list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete');
	}

	// Table
	$table = new tableManager($list);
	$table->delCol(0); # Delete the 'id' column

	if (count($list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}
	$table->header(array('', LANG_COM_WCAL_CATEGORY_TITLE, LANG_COM_WCAL_CATEGORY_AUTHOR, LANG_COM_WCAL_CATEGORY_COLOR, LANG_COM_WCAL_CATEGORY_ORDER, ''));
	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE);
	$html .= $form->end();
	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>