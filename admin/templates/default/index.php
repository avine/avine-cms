<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
global $g_protocol;


// Admin template directory
global $g_admin_template_dir;
$admin_tmpl_link = siteUrl()."/admin/templates/$g_admin_template_dir";


// If necessary, send the charset in the headers
header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo LANG_ADMIN_TITLE.' - '.admin_siteName(); ?></title>

<meta name="robots" content="noindex,nofollow" />

<!-- <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />For ie8 -->

<link rel="shortcut icon" href="<?php echo WEBSITE_PATH; ?>/global/favicon.ico" />

<!-- jQuery UI -->
<link rel="stylesheet" type="text/css" href="<?php echo siteUrl(); ?>/plugins/js/jquery-ui/css/avine/jquery-ui.custom.css" media="screen" />
<script type="text/javascript" src="<?php echo siteUrl(); ?>/plugins/js/jquery-ui/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo siteUrl(); ?>/plugins/js/jquery-ui/js/jquery-ui.custom.min.js"></script>

<?php comTemplate_loadDefaultResource(); ?>

<link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/template_css.css" />

<!--[if IE]><link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/template_css_ie.css" /><![endif]-->
<!--[if lte IE 6]><style type="text/css">.clearfix{height:1%;}</style><![endif]-->
<!--[if lte IE 7.0]><style type="text/css">.clearfix{display:inline-block;}</style><![endif]-->

<!-- jfontResizer -->
<link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/scripts/jfontResizer/jfontResizer.css" media="screen" />
<script type="text/javascript" src="<?php echo $admin_tmpl_link; ?>/scripts/jfontResizer/jfontResizer.js"></script>
<script type="text/javascript">$(document).ready(function(){$('#jfontResizer').jfontResizer();});</script>

<!-- superfish -->
<link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/scripts/superfish/css/superfish.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/scripts/superfish/css/superfish-vertical.css" media="screen" />
<script type="text/javascript" src="<?php echo $admin_tmpl_link; ?>/scripts/superfish/js/hoverIntent.js"></script>
<script type="text/javascript" src="<?php echo $admin_tmpl_link; ?>/scripts/superfish/js/superfish.js"></script>
<script type="text/javascript">jQuery(function(){jQuery('ul.sf-menu').superfish();});</script>

<!-- jQuery-lightbox -->
<link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/scripts/lightbox/jquery.lightbox.css" media="screen" />
<script type="text/javascript" src="<?php echo $admin_tmpl_link; ?>/scripts/lightbox/jquery.lightbox.js"></script>
<script type="text/javascript">$(document).ready(function(){$('a.lightbox').lightBox();});</script>

<!-- All javaScripts -->
<link rel="stylesheet" type="text/css" href="<?php echo $admin_tmpl_link; ?>/scripts/template_js.css" />
<script type="text/javascript" src="<?php echo $admin_tmpl_link; ?>/scripts/template_js.js"></script>

</head>
<body>
<?php


// Login & logout Form
global $g_user_login;
$login_form = $g_user_login->displayForm($g_user_login->getform(), 'default/tmpl_admin_login.html', false);


// Login required
if (!$g_user_login->userID())
{
?>

<div id="admin_wrapper">
	<div id="admin_main">
		<div id="admin_main_header"><a href="../" class="external no-arrow"><img src="<?php echo WEBSITE_PATH; ?>/admin/images/goto-frontend.png" alt="Go to frontend" title="<?php echo LANG_ADMIN_GO_TO_FRONTEND; ?>" /></a></div>

		<div id="admin_main_content" class="center clearfix">
			<?php echo '<h1 id="admin_login-title"><span>'.admin_siteName()."</span></h1>\n$login_form"; ?>
			<script type="text/javascript">$(document).ready(function(){$("#user_login_username").focus();});</script>

			<p>Powered by <?php echo LANG_ADMIN_COPYRIGHT; ?></p>

			<?php !ADMIN_DEMO_MODE or admin_demoMode_message(LANG_ADMIN_DEMO_MODE_LOGIN); ?>

			<p><img src="<?php echo WEBSITE_PATH; ?>/admin/templates/default/images/welcome-bg.jpg" alt="" /></p>
		</div>

	</div>
</div>

<?php
}

// Enter site administration
else
{
	// This page menu
	$admin_menu = new admin_menuManager('root');
	$admin_menu->addSubmenus();

	// Manage the admin_menu direction
	$session = new sessionManager(sessionManager::BACKEND, 'switchmenu');
	$session->init('vertical', isset($_COOKIE['admin_menu_vertical']) ? 1 : 0);
	if (@$_GET['switch-menu']) {
		$session->set('vertical', $session->get('vertical') ? '0' : '1');
		if ($session->get('vertical')) {
			setcookie('admin_menu_vertical', 'yes', time()+(60*60*24*365*3));
		} else {
			setcookie('admin_menu_vertical');
			unset($_COOKIE['admin_menu_vertical']);
		}
	}

?>

<!-- Wrapper -->
<div id="admin_wrapper">

	<!-- Header -->
	<div id="admin_header" class="clearfix">
		<?php echo '<div id="logo"><a href="'.siteUrl().'/admin">'.LANG_ADMIN_TITLE.'</a></div><h1>'.admin_siteName().'<span>&nbsp;</span></h1>'; ?>

		<div id="admin_header_menu">
			<?php if (!$session->get('vertical')) $admin_menu->displayMenu('sf-menu'); # Use class 'admin_header_menu' for basic-menu or class 'sf-menu' for superfish-menu ?>
			<br class="admin_header_menu" />
		</div>
		<div class="admin_header_menu"></div>

		<div id="goto-frontend"><a href="../" class="external no-arrow"><img src="<?php echo WEBSITE_PATH; ?>/admin/images/goto-frontend.png" alt="Go to frontend" title="<?php echo LANG_ADMIN_GO_TO_FRONTEND; ?>" /></a></div>
		<div id="switchmenu"><a href="<?php echo formManager::reloadPage(true, 'switch-menu=yes'); ?>"><img src="<?php echo $admin_tmpl_link; ?>/images/switch-menu.png" alt="Switch Menu Position" title="<?php echo LANG_ADMIN_SWITCH_MENU; ?>" /></a></div>
		<div id="jfontResizer" title="Taille de la police de caractères"></div>
	</div>

	<?php if ($session->get('vertical')) { ?>
	<!-- Left -->
	<script type="text/javascript">
	$(document).ready(function(){
		$('#admin_wrapper').addClass('admin_wrapper-alt').css('max-width', '1440px');
		$('#admin_header_menu').addClass('admin_header_menu-alt');
	});
	</script>
	<div id="admin_left">
		<?php $admin_menu->displayMenu('sf-menu sf-vertical'); ?>
	</div>
	<?php } ?>

	<!-- Main -->
	<div id="admin_main">

		<div id="admin_logout-form"><?php echo $login_form; ?></div>

		<div id="admin_main_content" class="clearfix">
			<?php $admin_menu->includeTarget(); ?>
		</div>

		<!-- Pathway -->
		<div id="admin-pathway" class="clearfix"><?php echo admin_showPathway(); ?></div>

	</div>

	<!-- Footer -->
	<div id="admin_footer">
		<div class="clearfix">

		<?php echo LANG_ADMIN_COPYRIGHT; ?>

		</div>
	</div>

</div>

<?php
}

new debugManager(ADMIN_DEBUG_MODE);

?>
</body>
</html><?php

?>