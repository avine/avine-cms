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

$submit = formManager::isSubmitedForm('config_', 'post'); // (1.1) & (1.2)
$activated = $filter->requestValue('activated', 'get')->getVar(); // (2)
$required  = $filter->requestValue('required' , 'get')->getVar(); // (3)


// (1) Update
if ($submit)
{
	// Simple security : username & password always required
	$force_username_required = $db->update('user_field; activated=1, required=1; where: field=\'username\'');
	$force_password_required = $db->update('user_field; activated=1, required=1; where: field=\'password\'');


	/**
	 * (1.1) user_config table
	 */
	$filter->reset();

	$visit_counter = $filter->requestValue('visit_counter')->getInteger(0);

	$filter->requestValue('registration_silent'		)->get() ? $registration_silent = 1 	: $registration_silent = 0;
	$filter->requestValue('allow_duplicate_email'	)->get() ? $allow_duplicate_email = 1 	: $allow_duplicate_email = 0;

	$activation_method = $filter->requestValue('activation_method')->get();
	if (!array_key_exists($activation_method , admin_comUser_SelectActivationMethod()))
	{
		$filter->set(false, 'activation_method')->getError('Invalid activation_method field');
	}

	($session_maxlifetime = $filter->requestValue('session_maxlifetime')->getInteger(0) *60) or ($session_maxlifetime = 'NULL');

	$force_email_required = false;
	if ($filter->validated())
	{
		if ( ($registration_silent == 1) || (($activation_method == 'email') || ($activation_method == 'admin')) )
		{
			$force_email_required = $db->update('user_field; activated=1, required=1; where: field=\'email\''); # Update Email field to be activated and required
		} else {
			$force_email_required = true; # we do nothing
		}

		$result_config =
			$db->update(
					"user_config; registration_silent=$registration_silent, allow_duplicate_email=$allow_duplicate_email, activation_method=".$db->str_encode($activation_method).
					", session_maxlifetime=$session_maxlifetime".($visit_counter ? ", visit_counter=$visit_counter" : '')
			);
	}
	else
	{
		echo $filter->errorMessage();
		$result_config = false;
	}

	$force_field_required = $force_username_required && $force_password_required && $force_email_required;


	/**
	 * (1.2) user_field order
	 */
	$filter->reset();

	$field_order = formManager_filter::arrayOnly($filter->requestName('field_order_')->getVar());

	if ($filter->validated() && $field_order)
	{
		$result_field = true;
		for ($i=0; $i<count($field_order); $i++)
		{
			$order = $filter->requestValue('field_order_'.$field_order[$i])->getInteger();
			if ($order !== false)
			{
				$local_result_field = $db->update('user_field; field_order='.$order.'; where: field='.$db->str_encode($field_order[$i]));
			} else {
				$local_result_field = false;
			}
			!$local_result_field ? $result_field = false : '';
		}

		// Format numerotation
		$field = $db->select('user_field, field, field_order(asc)');
		for ($i=0; $i<count($field); $i++)
		{
			$db->update('user_field; field_order='.(2*$i+1).'; where: field='.$db->str_encode($field[$i]['field']));
		}
	}
	else {
		$result_field = false;
	}

	/**
	 * Final result
	 */
	admin_informResult($result_config && $force_field_required && $result_field, '', LANG_ADMIN_COM_USER_CONFIG_SUBMIT_FAILED);
}



// (2)
if ($activated)
{
	$reverse = $db->select('user_field, activated, where: field='.$db->str_encode($activated));
	if ($reverse)
	{
		$reverse[0]['activated'] ? $reverse = 0 : $reverse = 1;
		$db->update("user_field; activated=$reverse; where: field=".$db->str_encode($activated));
	}
}



// (3)
if ($required)
{
	$reverse = $db->select('user_field, required, where: field='.$db->str_encode($required));
	if ($reverse)
	{
		$reverse[0]['required'] ? $reverse = 0 : $reverse = 1;
  	
		$db->update("user_field; required=$reverse; where: field=".$db->str_encode($required));
	}
}



//////////////
// Start view
if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_USER_CONFIG_TITLE_START.'</h2>';

	$html = '';

	// Database
	$config = $db->selectOne('user_config, *');
	$field  = $db->select('user_field, field, activated, required, field_order(asc)');

	// Preview final result
	$preview = $db->select('user_field, field, required, field_order(asc), where: activated=1');
	$star = ' <span style="color:red;font-weight:bold;font-size:14px;">*</span>';
	$user_field_alias = comUser_getFieldsAlias();
	for ($i=0; $i<count($preview); $i++)
	{
		$preview[$i]['field'] = $user_field_alias[$preview[$i]['field']];
		if ($preview[$i]['required']) {
			$preview[$i]['field'] .= $star;
		}
	}
	$table = new tableManager($preview);
	$table->delCol('1,2');
	$table->header(array(LANG_ADMIN_COM_USER_CONFIG_PREVIEW));
	$preview = $table->html();

	// Form
	$form = new formManager(0);
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'config_');


	/**
	 * Fieldset - user_config
	 */
	$fieldset = '';

	// registration_silent
	$fieldset .= $form->checkbox('registration_silent', $config['registration_silent'], LANG_ADMIN_COM_USER_REGISTRATION_SILENT);
	if ($config['registration_silent']) {
		$fieldset .= LANG_ADMIN_COM_USER_EMAIL_REQUIRED_FOR_PARAMETERS;
	}

	// allow_duplicate_email
	$fieldset .= '<br /><br />'.$form->checkbox('allow_duplicate_email', $config['allow_duplicate_email'], LANG_ADMIN_COM_USER_ALLOW_DUPLICATE_EMAIL);

	// activation_method
	$fieldset .= '<br /><br />'.$form->select('activation_method', admin_comUser_SelectActivationMethod($config['activation_method']), LANG_ADMIN_COM_USER_ACTIVATION_METHOD_SELECT);
	if ($config['activation_method'] == 'email')
	{
		$fieldset .= LANG_ADMIN_COM_USER_EMAIL_REQUIRED_FOR_ACTIVATION;
	}
	elseif ($config['activation_method'] == 'admin')
	{
		$fieldset .= LANG_ADMIN_COM_USER_EMAIL_REQUIRED_FOR_INFORMATION;
	}

	// crypt_user_info
	# Not here, because we have a special page for it !

	// session_maxlifetime
	$config['session_maxlifetime'] ? $session_maxlifetime = $config['session_maxlifetime']/60 : $session_maxlifetime = '';
	$label = LANG_ADMIN_COM_USER_SESSION_MAXLIFETIME.' <span class="red">('.LANG_ADMIN_COM_USER_CONFIG_LEAVE_EMPTY_TO_DISABLE.')</span>';
	$fieldset .= '<br /><br />'.$form->text('session_maxlifetime', $session_maxlifetime, $label, '', 'size=2;update=1').'<span class="grey">'.LANG_ADMIN_COM_USER_CONFIG_MINUTES.'</span>';

	// Visit_counter
	$visit_counter = $db->selectOne('user_config, visit_counter', 'visit_counter');
	$fieldset .= '<br /><br />'.$form->text('visit_counter', $visit_counter, LANG_ADMIN_COM_USER_VISIT_COUNTER, '', 'disabled;size='.(strlen($visit_counter)+1));

$modify_visit_counter =
'<!-- Enable the modification of the visitors counter -->
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	$("#config_visit_counter").after(" <a href=\"#\" id=\"modify_visit_counter\" title=\"'.LANG_ADMIN_COM_USER_CONFIG_BUTTON_MODIFY_VISIT_COUNTER.'\"><img src=\"'.WEBSITE_PATH.'/admin/components/com_user/images/modify_visit_counter.png\" /><"+"/span>");
	$("#modify_visit_counter").click(function(){
		var disabled = $("#config_visit_counter").attr("disabled");
		$("#config_visit_counter").attr("disabled", !disabled);
		if (disabled){
			$("#config_visit_counter").removeClass("form-text-disabled").addClass("form-text").focus();
		}else{
			$("#config_visit_counter").addClass("form-text-disabled").removeClass("form-text");
		}
		return false;
	});
});
//]]></script>';

	$fieldset .= "\n$modify_visit_counter\n"; // end of visit_counter

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_USER_CONFIG_FIELDSET_CONFIG);


	// Fields transformations
	for ($i=0; $i<count($field); $i++)
	{
		if (	( ($field[$i]['field'] != 'username') && ($field[$i]['field'] != 'password') ) && 
				( (($config['registration_silent'] == 0) && ($config['activation_method'] == 'auto')) || ($field[$i]['field'] != 'email') )	)
		{
			// Activated & Required button
			$field[$i]['activated'] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;activated='.$field[$i]['field'].'">'.admin_replaceTrueByChecked($field[$i]['activated']).'</a>'; // (2)
			$field[$i]['required' ] = '<a href="'.$_SERVER['PHP_SELF'].$path.'&amp;required='.$field[$i]['field'].'">'.admin_replaceTrueByChecked($field[$i]['required']).'</a>'; // (3)
		}
		else
		{
			// username & password : always required  -  email : sometimes required
			$field[$i]['activated'] = LANG_ADMIN_COM_USER_CONFIG_FIELD_ALWAYS_REQUIRED;
			$field[$i]['required' ] = LANG_ADMIN_COM_USER_CONFIG_FIELD_ALWAYS_REQUIRED;
		}

		// Field order
		$field[$i]['field_order'] = $form->text('field_order_'.$field[$i]['field'], $field[$i]['field_order'], '', '', 'size=1'); // (1.2)

		// Field alias ( Warning: $field[$i]['field'] is like an id for inputs form. His transformation MUST BE the last operation )
		$user_field_alias = comUser_getFieldsAlias();
		$field[$i]['field'] = $user_field_alias[$field[$i]['field']];
	}


	/**
	 * Fiedlset - user_field
	 */
	$fieldset  = '';

	$table = new tableManager($field);
	$table->header( array(LANG_ADMIN_COM_USER_FIELD_FIELD, LANG_ADMIN_COM_USER_FIELD_ACTIVATED, LANG_ADMIN_COM_USER_FIELD_REQUIRED, LANG_ADMIN_COM_USER_FIELD_ORDER) );
	$fieldset .= "\n".'<div style="float:left; margin-right:100px;">'.$table->html().'</div>'."\n";
	$fieldset .= $preview.'<br style="clear:left;" />';

	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_USER_CONFIG_FIELDSET_FIELD);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT); // (1.1)

	$html .= $form->end();

	echo $html;
}

echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

?>