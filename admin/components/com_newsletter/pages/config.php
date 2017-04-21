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
	$filter->reset();

	$return_path	= $filter->requestValue('return_path'	)->getEmail(1, '', LANG_ADMIN_COM_NEWSLETTER_CONFIG_RETURN_PATH	);
	$reply_to		= $filter->requestValue('reply_to'		)->getEmail(1, '', LANG_ADMIN_COM_NEWSLETTER_CONFIG_REPLY_TO	);

	$batch_size		= $filter->requestValue('batch_size'	)->getInteger();
	$refresh_time	= $filter->requestValue('refresh_time'	)->getInteger();

	if ($filter->validated())
	{
		admin_informResult(
			$db->update(
				'newsletter_config; '.
				'return_path='	.$db->str_encode($return_path	).', '.
				'reply_to='		.$db->str_encode($reply_to		).', '.
				'batch_size='	.$db->str_encode($batch_size	).', '.
				'refresh_time='	.$db->str_encode($refresh_time	)
			)
		);
	}
	else {
		echo $filter->errorMessage();
	}
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_NEWSLETTER_CONFIG_TITLE_START.'</h2>';

	$config = $db->selectOne('newsletter_config, *');

	// First time ? Set default values
	if (!$config['return_path'] && !$config['reply_to'])
	{
		comConfig_getInfos($site_name, $system_email); # passed by reference
		if ($db->insert('newsletter_config; col: return_path, reply_to; '.$db->str_encode($system_email).', '.$db->str_encode($system_email)))
		{
			admin_message(LANG_ADMIN_COM_NEWSLETTER_CONFIG_DEFAULT_VALUES_INFO, 'info');
		}
		$config = $db->selectOne('newsletter_config, *');
	}

	$html = '';

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'start_');

	$html .= $form->text('return_path'	, $config['return_path'	], LANG_ADMIN_COM_NEWSLETTER_CONFIG_RETURN_PATH	.'<br />').'<br /><br />';
	$html .= $form->text('reply_to'		, $config['reply_to'	], LANG_ADMIN_COM_NEWSLETTER_CONFIG_REPLY_TO	.'<br />').'<br /><br />';

	$html .= $form->text('batch_size'	, $config['batch_size'	], LANG_ADMIN_COM_NEWSLETTER_CONFIG_BATCH_SIZE	.'<br />', '', 'size=4').'<span class="grey"> '.LANG_ADMIN_COM_NEWSLETTER_CONFIG_EMAILS.'</span><br /><br />';
	$html .= $form->text('refresh_time'	, $config['refresh_time'], LANG_ADMIN_COM_NEWSLETTER_CONFIG_REFRESH_TIME.'<br />').'<span class="grey"> '.LANG_ADMIN_COM_NEWSLETTER_CONFIG_SECONDES.'<br /><br />';

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>