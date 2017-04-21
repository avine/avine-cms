<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


##
## TODO - Prévenir si quelqu'un veut modifier une désignation utilisée dans un don réel, qu'il doit juste améliorer le texte mais pas changer du tout au tout !
##

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


$com_donate = new comDonate_();
$currency_name = $com_donate->currencyName(); # Alias


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

// Posted forms possibilities
$published_status = $filter->requestValue('published_status', 'get')->getInteger(); // (4)

$submit = formManager::isSubmitedForm('designation_', 'post');
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new')->get(); // (1)
	$order = $filter->requestValue('submit')->get(); // (0)
}
else
{
	$del = false;
	$upd = false;
	$new = false;
	$order = false;
}

$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// (4) Published status
if ($published_status)
{
	$published = $db->select("donate_designation, published, where: id=$published_status");
	if ($published)
	{
		$published[0]['published'] == 1 ? $published = 0 : $published = 1;
		$db->update("donate_designation; published=$published; where: id=$published_status");
	}
}



// (3) Delete
if ($del)
{
	$del_id = $del;

	// Database Process
	if ($db->select("donate_details, id, where: designation_id=$del_id"))
	{
		admin_message(LANG_ADMIN_COM_DONATE_DESIGNATION_ID_USED, 'error');
	}
	else
	{
		$result = $db->delete("donate_designation; where: id=$del_id");
		admin_informResult($result);
	}
}



// (2) Update
if ($upd_submit)
{
	$upd_submit_validation = true;
	$filter->reset();

	// Fields validation
	$upd_id 	= $filter->requestValue('id')->getInteger();

	$title 		= $filter->requestValue('title')->getNotEmpty();

	$comment 	= $filter->requestValue('comment')->get();

	$link 		= $filter->requestValue('link')->get();
	$link = preg_replace('~^((http://|https://)'. pregQuote($_SERVER['HTTP_HOST']) .')?'. pregQuote(WEBSITE_PATH) .'~i', '', $link); # Format link

	$image 		= $filter->requestValue('image')->getPathFile(0);
	$image = preg_replace('~^((http://|https://)'. pregQuote($_SERVER['HTTP_HOST']) .')?'. pregQuote(WEBSITE_PATH.RESOURCE_PATH) .'~i', '', $image); # Format image
	($image = preg_replace('~^/+~', '', $image)) ? $image = "/$image" : '';

	$amount 	= $filter->requestValue('amount')->getInteger(0, '', LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT);
	$amount ? $amount = money::convertAmountUnitsToCents($amount) : $amount = 'NULL';

	// Check unique 'title'
	if ($upd_id && $db->selectCount("donate_designation, where: id!=$upd_id AND, where: title=".$db->str_encode($title))) {
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_DONATE_DESIGNATION_DUPLICATE_TITLE);
	}

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update(
						'donate_designation; '.
						'title='	.$db->str_encode($title).
						', comment='.$db->str_encode($comment).
						', link='	.$db->str_encode($link).
						', image='	.$db->str_encode($image).
						", amount=$amount".
						"; where: id=$upd_id" );

		admin_informResult($result);
	}
	else
	{
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->select("donate_designation, *, where: id=$upd_id");
	$current = $current[0];

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	$html .= $form->text('title'		, $current['title']	, LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE.'<br />'	, '', 'size=30;maxlength=100').'<br />';
	$html .= $form->textarea('comment'	, $current['comment'], LANG_ADMIN_COM_DONATE_DESIGNATION_COMMENT.'<br />', '', '').'<br /><br />';
	$html .= $form->text('link'			, $current['link']	, LANG_ADMIN_COM_DONATE_DESIGNATION_LINK.'(right)'	, '', 'size=60').'<br /><br />';
	$html .= RESOURCE_PATH.$form->text('image', $current['image']	, LANG_ADMIN_COM_DONATE_DESIGNATION_IMAGE.'(right)'	, '', 'size=60').'<br /><br />';

	$html .= $form->text('amount', $current['amount'] ? money::convertAmountCentsToUnits($current['amount'],0) : '', LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT, '', 'size=5')." $currency_name".
				' <span class="grey">('.LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT_TIPS.')</span><br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end().'<br />';
	echo $html;
}



// (1) New
if ($new_submit)
{
	$new_submit_validation = true;
	$filter->reset();

	// Fields validation
	$title 		= $filter->requestValue('title')->getNotEmpty();

	$comment 	= $filter->requestValue('comment')->get();

	$link 		= $filter->requestValue('link')->get();
	$link = preg_replace('~^((http://|https://)'. pregQuote($_SERVER['HTTP_HOST']) .')?'. pregQuote(WEBSITE_PATH) .'~i', '', $link); # Format link

	$image 		= $filter->requestValue('image')->getPathFile(0);
	$image = preg_replace('~^((http://|https://)'. pregQuote($_SERVER['HTTP_HOST']) .')?'. pregQuote(WEBSITE_PATH.RESOURCE_PATH) .'~i', '', $image); # Format image
	($image = preg_replace('~^/+~', '', $image)) ? $image = "/$image" : '';

	$amount 		= $filter->requestValue('amount')->getInteger(0, '', LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT);
	$amount ? $amount = money::convertAmountUnitsToCents($amount) : $amount = 'NULL';

	// Check unique 'title'
	if ($db->selectCount("donate_designation, where: title=".$db->str_encode($title))) {
		$filter->set(false, 'title')->getError(LANG_ADMIN_COM_DONATE_DESIGNATION_DUPLICATE_TITLE);
	}

	// Database Process
	if ($new_submit_validation = $filter->validated())
	{
		$result = $db->insert( 'donate_designation; NULL, '.$db->str_encode($title).', '.$db->str_encode($comment).', '.$db->str_encode($link).', '.$db->str_encode($image).", $amount, 9999,1" );

		admin_informResult($result);
	}
	else
	{
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$html .= $form->text('title'		, '', LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE.'<br />'	, '', 'size=30;maxlength=100').'<br />';
	$html .= $form->textarea('comment'	, '', LANG_ADMIN_COM_DONATE_DESIGNATION_COMMENT.'<br />', '', '').'<br /><br />';
	$html .= $form->text('link'			, '', LANG_ADMIN_COM_DONATE_DESIGNATION_LINK.'(right)'	, '', 'size=60').'<br /><br />';
	$html .= RESOURCE_PATH.$form->text('image', ''	, LANG_ADMIN_COM_DONATE_DESIGNATION_IMAGE.'(right)'	, '', 'size=60').'<br /><br />';

	$html .= $form->text('amount', '', LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT, '', 'size=5')." $currency_name".
				' <span class="grey">('.LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT_TIPS.')</span><br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end().'<br />';
	echo $html;
}



// (0) Order
if ($order)
{
	if ($design_id = formManager_filter::arrayOnly($filter->requestName('order_')->get()))
	{
		for ($i=0; $i<count($design_id); $i++)
		{
			if ($order = $filter->requestValue('order_'.$design_id[$i])->getInteger())
			{
				$db->update("donate_designation; design_order=$order; where: id={$design_id[$i]}");
			}
		}
	}
}

// Re-order
$design_order = $db->select('donate_designation, id, design_order(asc)');
for ($i=0; $i<count($design_order); $i++)
{
	$db->update('donate_designation; design_order='.(2*$i +1).'; where: id='.$design_order[$i]['id']);
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'designation_');

	$designation = $db->select('donate_designation, id,title,design_order(asc),amount,published');
	$one_published = false;
	for ($i=0; $i<count($designation); $i++)
	{
		$href = comMenu_rewrite('com=donate&amp;page=index&amp;'.comDonate_::GET_DESIGN_ID.'='.$designation[$i]['id']);
		$designation[$i]['title'] = '<a title="'.LANG_ADMIN_COM_DONATE_GET_DESIGN_ID_TITLE.'" href="'.$href.'" class="external">'.$designation[$i]['title'].'</a>';

		$designation[$i]['design_order'] = $form->text('order_'.$designation[$i]['id'], (2*$i +1), '', '', 'size=1;update=no'); // (0)

		if ($designation[$i]['amount']) {
			$designation[$i]['amount'] = money::convertAmountCentsToUnits($designation[$i]['amount'],0)." $currency_name";
		}

		if ($designation[$i]['published']) {
			$one_published = true;
		}
		$designation[$i]['published'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;published_status='.$designation[$i]['id'].'">'.admin_replaceTrueByChecked($designation[$i]['published']).'</a>'; // (4)

		$update[$i] = $form->submit('upd_'.$designation[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update');	 // (2)
		$delete[$i] = $form->submit('del_'.$designation[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)
	}
	if (!count($designation)) {
		admin_message(LANG_ADMIN_COM_DONATE_DESIGNATION_NO_DESIGN_ENTRY, 'warning');
	}
	elseif (!$one_published) {
		admin_message(LANG_ADMIN_COM_DONATE_DESIGNATION_NO_DESIGN_PUBLISHED, 'warning');
	}

	// Table
	$table = new tableManager($designation);
	$table->header(
				array(
					LANG_ADMIN_COM_DONATE_DESIGNATION_ID,
					LANG_ADMIN_COM_DONATE_DESIGNATION_TITLE,
					LANG_ADMIN_COM_DONATE_DESIGNATION_DESIGN_ORDER,
					LANG_ADMIN_COM_DONATE_DESIGNATION_AMOUNT,
					LANG_ADMIN_COM_DONATE_DESIGNATION_PUBLISHED
				)
			);

	$table->delCol('0'); // Delete id column
	if (count($designation)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}

	$html .= $table->html();

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (0)
	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)
	$html .= $form->end();

	echo $html;
}


echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';


?>