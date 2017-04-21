<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );

global $g_user_login;



// Availables links

$login = '';
$create = '';
$forget = '';

if ($g_user_login->isLinkAvailable('login')) {
	$login	= comMenu_rewrite('com=user&amp;page=login');
}

if ($g_user_login->isLinkAvailable('create')) {
	$create	= comMenu_rewrite('com=user&amp;page=create');
}

if ($g_user_login->isLinkAvailable('forget')) {
	$forget	= comMenu_rewrite('com=user&amp;page=forget');
}



// Html output

$output = '';

if (!$g_user_login->userID())
{
	if ($login || $create) {
		$output .= '<p>';
		$login ? $output .= '<a href="'.$login.'" style="text-decoration:none;">&gt; Vous êtes déjà inscrit</a><br />' : '';
		$create ? $output .= '<a href="'.$create.'" style="text-decoration:none; color:#333;">&gt; S\'inscrire maintenant</a>' : '';
		$output .= "</p>\n";
	}

	if ($forget) {
		$output .= '<p style="margin:0; font-size:92%;"><a href="'.$forget.'">Mot de passe oublié ?</a></p>';
	}
}
else
{
	$output .= $g_user_login->displayForm( $g_user_login->getform(), 'default/tmpl_module_login.html', 'default/tmpl_module_logout.html' );
}

echo $output;


?>