<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


// Instanciate .htaccess manager
$com_rewrite = new admin_comRewrite_config();



///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');

$submit = formManager::isSubmitedForm('config_', 'post');



// (0) Case 'restore_default'
if ($submit && $filter->requestValue('restore_default')->get())
{
	admin_informResult( $com_rewrite->switchEngine(0, false) );
}



// (1) Case 'update_htaccess_user'
if ($submit && $filter->requestValue('update_htaccess_user')->get())
{
	admin_informResult( $com_rewrite->updateHtaccessUser($filter->requestValue('htaccess_user')->get()) );
}



// (2) Case 'switch_engine'
if ($submit && $filter->requestValue('switch_engine')->get())
{
	admin_informResult( $com_rewrite->switchEngine(!$com_rewrite->isEnabled(), true) );
}



//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_REWRITE_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'config_');

	// Rewrite engine status
	$html .= '<p>'.LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_STATUS;
	if (!$com_rewrite->isEnabled()) {
		$html .= '<span class="red"><b>'.LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_OFF.'</b></span></p>';
		$html .= $form->submit('switch_engine', LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_ENABLE).'<br /><br />'; // (2)
	} else {
		$html .= '<span class="green"><b>'.LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_ON.'</b></span></p>';
		$html .= '<p>'.$form->submit('switch_engine', LANG_ADMIN_COM_REWRITE_CONFIG_ENGINE_DISABLE).'</p>'; // (2)
	}

	$box = new boxManager();

	// Get .htaccess
	$htaccess = $com_rewrite->getHtaccess(true);
	if (!$htaccess)
	{
		if ($htaccess === NULL) {
			$html .= $box->message(LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_UNAVAILABLE);								# $htaccess === NULL;
		} else {
			$html .= $box->message(LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_SEPARATOR_MISSING, 'error', true, '380');	# $htaccess === false;
		}
		$html .= $form->submit('restore_default', LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_RESTORE).'<br /><br />'; // (0)
	}
	else
	{
		$fieldset = $box->message(LANG_ADMIN_COM_REWRITE_WARNING_FOR_EXPERTS, 'warning', true, '440');

		// View .htaccess
		$fieldset .= $form->textarea('htaccess_system'	, $htaccess['system'], LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_SYSTEM.'<br />', '', 'cols=60;rows=6;readonly').'<br /><br />';
		$fieldset .= $form->textarea('htaccess_user'	, $htaccess['user'	], LANG_ADMIN_COM_REWRITE_CONFIG_HTACCESS_USER	.'<br />', '', 'cols=60;rows=6;').'<br />';
		$fieldset .= $form->submit('update_htaccess_user', LANG_ADMIN_BUTTON_UPDATE); // (1)
		$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_REWRITE_WARNING_FOR_EXPERTS_LEGEND);
	}

	$html .= $form->end();

	// Test the rewrite engine on the current server
	$html .=
		'<p><img src="'.WEBSITE_PATH.'/admin/components/com_rewrite/images/wrench.png" alt="" /> &nbsp;'.
		'<a href="'.WEBSITE_PATH.'/admin/components/com_rewrite/test/" class="external">'.LANG_ADMIN_COM_REWRITE_CONFIG_TEST_REWRITE_ENGINE.'</a></p>';

	echo $html;
}

echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>