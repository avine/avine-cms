<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );

// Login-logout Form
global $g_user_login;
echo $g_user_login->displayForm( $g_user_login->getform(), 'default/tmpl_module_login.html', 'default/tmpl_module_logout.html' );

?>
