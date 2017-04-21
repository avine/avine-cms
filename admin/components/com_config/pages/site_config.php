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

$filter = new formManager_filter(true);
$filter->requestVariable('post');

// Posted forms possibilities
$submit = formManager::isSubmitedForm('config_', 'post'); // (0)


// (0) case 'submit'
if ($submit)
{
	$filter->reset();

	$filter->requestValue('online')->get() 						? $online = 1 					: $online = 0;
	$filter->requestValue('debug')->get() 						? $debug = 1 					: $debug = 0;
	$filter->requestValue('no_linked_content_access')->get() 	? $no_linked_content_access = 1 : $no_linked_content_access = 0;

	$offline_message 	= $filter->requestValue('offline_message')->get();
	$site_name 			= $filter->requestValue('site_name'		)->get();
	$meta_desc 			= $filter->requestValue('meta_desc'		)->get();
	$meta_keywords 		= $filter->requestValue('meta_keywords'	)->get();
	$meta_author 		= $filter->requestValue('meta_author'	)->get();
	$http_host 			= $filter->requestValue('http_host'		)->getPath(0);
	$system_email 		= $filter->requestValue('system_email'	)->getEmail(0);

	if ($filter->validated())
	{
		$result =
			$db->update( 'config; '.
				'site_name='.$db->str_encode($site_name).', '.
				'meta_keywords='.$db->str_encode($meta_keywords).', meta_desc='.$db->str_encode($meta_desc).', meta_author='.$db->str_encode($meta_author)  .', '.
			  	'online='.$online.', offline_message='.$db->str_encode($offline_message).', '.
			  	'http_host='.$db->str_encode($http_host).', no_linked_content_access='.$no_linked_content_access.', '.
			  	'system_email='.$db->str_encode($system_email).', '.
				'debug='.$debug
			);

		admin_informResult($result);
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
	echo '<h2>'.LANG_ADMIN_COM_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$config = $db->select('config, *'); $config = $config[0];

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'config_');

	// Fieldset - Status
	$fieldset  = '';
	$fieldset .= $form->checkbox('online', $config['online'], LANG_ADMIN_COM_CONFIG_ONLINE_STATUS);
	if ($config['online'])
	{
		$fieldset .= LANG_ADMIN_COM_CONFIG_ONLINE;
	} else {
		$fieldset .= LANG_ADMIN_COM_CONFIG_OFFLINE;
	}
	$fieldset .= '<br /><br />'.$form->textarea('offline_message', $config['offline_message'], LANG_ADMIN_COM_CONFIG_OFFLINE_MESSAGE.'<br />', '', 'cols=90; rows=2');
	$fieldset .= '<br /><br />'.$form->checkbox('debug', $config['debug'], LANG_ADMIN_COM_CONFIG_DEBUG);
	if ($config['debug'])
	{
		$fieldset .= ' <img src="'.siteUrl().'/admin/components/com_config/images/debug.png" alt="" title="'.LANG_ADMIN_COM_CONFIG_DEBUG_ON.'" />';
	}
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONFIG_FIELDSET_STATUS);

	// Fiedlset - Parameters
	$fieldset  = '';
	$fieldset .= $form->text('http_host', $config['http_host'], LANG_ADMIN_COM_CONFIG_HTTP_HOST.'<br />', '', 'size=50').' <span class="grey">('.LANG_ADMIN_COM_CONFIG_HTTP_HOST_IS_OPTIONAL.')</span><br /><br />';
	$fieldset .= $form->checkbox('no_linked_content_access', $config['no_linked_content_access'], LANG_ADMIN_COM_CONFIG_NO_LINKED_CONTENT_ACCESS);
	if ($config['no_linked_content_access'])
	{
		$fieldset .= LANG_ADMIN_COM_CONFIG_YES;
	} else {
		$fieldset .= LANG_ADMIN_COM_CONFIG_NO;
	}
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONFIG_FIELDSET_PARAMETERS);

	// Fiedlset - Metas
	$fieldset  = '';
	$fieldset .= $form->text('site_name', $config['site_name'], LANG_ADMIN_COM_CONFIG_SITE_NAME.'<br />', '', 'size=50');
	$fieldset .= '<br /><br />'.$form->textarea('meta_desc', $config['meta_desc'], LANG_ADMIN_COM_CONFIG_META_DESC	.'<br />', '', 'cols=90; rows=2');
	$fieldset .= '<br /><br />'.$form->textarea('meta_keywords', $config['meta_keywords'], LANG_ADMIN_COM_CONFIG_META_KEYWORDS.'<br />', '', 'cols=90; rows=2');
	$fieldset .= '<br /><br />'.$form->text('meta_author', $config['meta_author'], LANG_ADMIN_COM_CONFIG_META_AUTHOR.'<br />', '', 'size=50');
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONFIG_FIELDSET_META);

	// Fieldset - Email of system
	$fieldset  = '';
	$fieldset .= $form->text('system_email', $config['system_email'], LANG_ADMIN_COM_CONFIG_SYSTEM_EMAIL.'<br />', '', 'size=50');
	if (!$config['system_email'])
	{
		admin_message(LANG_ADMIN_COM_CONFIG_SYSTEM_EMAIL_NOT_DEFINED, 'warning');
	}
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_CONFIG_FIELDSET_EMAIL);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);

	$html .= $form->end(); // End of Form

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

//$path_back = admin_getPathway(2); // Button example to go back !
//echo '<p><a href="'.$_SERVER['PHP_SELF'].$path_back.'">Retour</a></p>';

?>