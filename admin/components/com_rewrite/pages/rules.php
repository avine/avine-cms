<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */

# TODO - Allow adding new rule

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;
$session = new sessionManager(sessionManager::BACKEND, 'rewrite_rules');


// Box
$box = new boxManager();


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('rules_', 'post');



if ($submit)
{
	if ($filter->requestName('unlock')->get()) {
		$session->set('unlock', true);
	}
	elseif ($filter->requestName('lock')->get()) {
		$session->reset('unlock');
	}
}



// Case 'update'
if ($submit && $filter->requestValue('update')->get())
{
	$rules = $db->select('rewrite_rules, *, pos(asc)');

	$result = true;
	for ($i=0; $i<count($rules); $i++)
	{
		$id 		= $rules[$i]['id'];

		$pos		= $filter->requestValue('pos_'.$rules[$i]['id'])->get();

		$static		= $db->str_encode( $filter->requestValue('static_'	.$rules[$i]['id'])->get() );
		$dynamic	= $db->str_encode( $filter->requestValue('dynamic_'	.$rules[$i]['id'])->get() );

		for ($x=1; $x<=3; $x++) {
			$s[$x] = $db->str_encode( $filter->requestValue("s{$x}_"		.$rules[$i]['id'])->get() );
		}

		if (!$db->update("rewrite_rules; pos=$pos, static=$static, dynamic=$dynamic, s1={$s[1]}, s2={$s[2]}, s3={$s[3]}; where: id=$id")) {
			$result = false;
		}
	}

	admin_informResult($result, '', 'Error occured ! You should try to find and reload the default setting');
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_REWRITE_RULES_TITLE_START.'</h2>';

	$html = '';

	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'rules_');

	// Database
	$rules = $db->select('rewrite_rules, *, pos(asc)');

	if (!$session->get('unlock'))
	{
		$html .= $box->message(LANG_ADMIN_COM_REWRITE_WARNING_FOR_EXPERTS, 'warning', true, '440');

		$html .= '<p class="red">'.$form->submit('unlock'	, LANG_ADMIN_COM_REWRITE_RULES_UNLOCK	, '', 'image=lock'		).' &nbsp;'.LANG_ADMIN_COM_REWRITE_RULES_LOCK_STATUS.'</p>';

		for ($i=0; $i<count($rules); $i++)
		{
			$rules[$i]['static'	] = htmlspecialchars($rules[$i]['static']);
			$rules[$i]['dynamic'] = htmlspecialchars($rules[$i]['dynamic']);
		}
	}
	else
	{
		$html .= '<p class="green">'.$form->submit('lock'	, LANG_ADMIN_COM_REWRITE_RULES_LOCK		, '', 'image=lock_open'	).' &nbsp;'.LANG_ADMIN_COM_REWRITE_RULES_UNLOCK_STATUS.'</p>';

		for ($i=0; $i<count($rules); $i++)
		{
			$rules[$i]['pos'	] = $form->text('pos_'		.$rules[$i]['id'], $rules[$i]['pos'		], '', '', 'size=1');
			$rules[$i]['static'	] = $form->text('static_'	.$rules[$i]['id'], $rules[$i]['static'	], '', '', 'size=35');
			$rules[$i]['dynamic'] = $form->text('dynamic_'	.$rules[$i]['id'], $rules[$i]['dynamic'	], '', '', 'size=70');

			for ($x=1; $x<=3; $x++) {
				$rules[$i]["s$x"] = $form->text("s{$x}_"	.$rules[$i]['id'], $rules[$i]["s$x"		], '', '', 'size=10');
			}
		}
	}

	// Table
	$table = new tableManager($rules, admin_comRewrite_rulesHeader());
	$table->delCol(0); // Remove ID column
	!$session->get('unlock') ? $table->delCol(0) : ''; // Remove order column
	$html .= $table->html();

	if ($session->get('unlock')) {
		$html .= $form->submit('update', LANG_ADMIN_BUTTON_UPDATE);
	}

	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>