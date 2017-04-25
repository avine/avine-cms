<?php
/* Avine. Copyright (c) 2008 Stéphane Francel (http://avine.io). Dual licensed under the MIT and GPL Version 2 licenses. */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$module_navbar		= comModule_('navbar'	);
$module_slideshow	= comModule_('slideshow');
$module_search		= comModule_('search'	);
$module_left		= comModule_('left'		);
$module_right		= comModule_('right'	);


// If necessary, send the charset in the headers
header('Content-Type: text/html; charset=utf-8');


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php comTemplate_headerAddon() ?>
<link rel="shortcut icon" href="<?php echo WEBSITE_PATH ?>/global/favicon.ico" />

<meta name="robots" content="index,follow" />
<?php

/*
 * Load .css and .js resources
 */

global $g_page;
$load = new comTemplate_loader();

$load->resources(
	array(
		'/plugins/js/jquery-ui/css/smoothness/jquery-ui.custom.css',
		'/plugins/js/jquery-ui/js/jquery.min.js',
		'/plugins/js/jquery-ui/js/jquery-ui.custom.min.js'
		#'/plugins/js/jquery-easing.js'
	)
);

comTemplate_loadTemplateResource(array('template_css'));
comTemplate_loadDefaultResource($g_page['template_dir'].'/overloading'); # For alt_base : Add only the current 'template_dir' (but not the 'template_default_dir')

$load->setBase($g_page['template_dir'])->resources(
	array(
		'/scripts/superfish/css/superfish.css',
		#'/scripts/superfish/css/superfish-vertical.css',
		#'/scripts/superfish/js/hoverIntent.js',
		'/scripts/superfish/js/superfish.js',

		'/scripts/jCarousel/slideshow.css',
		'/scripts/jCarousel/jquery.jcarousel.min.js',

		'/scripts/jfontResizer/jfontResizer.css',
		'/scripts/jfontResizer/jfontResizer.js',

		'/scripts/scrolltopcontrol/scrolltopcontrol.css',
		'/scripts/scrolltopcontrol/scrolltopcontrol.js',

		'/scripts/lightbox/jquery.lightbox.css',
		'/scripts/lightbox/jquery.lightbox.js',

		#'/scripts/jScrollPane/jquery.jscrollpane.css',
		#'/scripts/jScrollPane/jquery.mousewheel.js',
		#'/scripts/jScrollPane/jquery.jscrollpane.min.js',	# NOTICE : If you use 'jScrollPane' plugin, add this just after : <style type="text/css">.scroll-pane{overflow:auto;}</style>

		'/scripts/template_js.css',
		'/scripts/template_js.js'
	)
);

?>
<!--[if IE]><link rel="stylesheet" type="text/css" href="<?php echo siteUrl().$g_page['template_dir'] ?>/template_css_ie.css" /><![endif]-->
<!--[if lte IE 6]><style type="text/css">.clearfix{height:1%;}</style><![endif]-->
<!--[if lte IE 7]><style type="text/css">.clearfix{display:inline-block;}</style><![endif]-->
<!--[if lte IE 6]><style type="text/css"> *{font-style:normal!important;} #column-left{margin-right:-3px;} #column-right{margin-left:-3px;} /* ie6 3 pixel bug */ </style><![endif]-->

	<?php define('_GOOGLE_ANALYTICS', 1); if (is_file('../google-analytics.php')) require '../google-analytics.php'; ?>
</head>
<body>

<?php comTemplate_siteOfflineFlag() ?>

<div id="wrapper">
	<a id="topcontrol"></a>
	<div id="wrapper-main" class="clearfix">

		<div id="header">
			<?php if (count($module_search)) { ?>
			<div id="search">
				<?php comModule_show('search', $module_search) ?>
			</div><!-- /search -->
			<?php } ?>

			<div id="logo"><a href="<?php echo siteUrl() ?>"><img src="<?php echo siteUrl().$g_page['template_dir'] ?>/images/logo-avine.png" alt="<?php echo $g_page['config_site_name'] ?>" /></a></div>

			<div id="jfontResizer" title="Taille de la police de caractères"></div>
		</div><!-- /header -->

		<?php if (count($module_navbar)) { ?>
		<div id="navbar">
			<?php comModule_show('navbar', $module_navbar) ?>
		</div><!-- /navbar -->
		<?php } ?>

		<div id="main">

			<?php if (count($module_right)) { ?>
			<div id="column-right">
				<?php comModule_show('right', $module_right) ?>
			</div><!-- /column-right -->
			<?php } ?>

			<div id="main2" class="clearfix">

				<?php if (count($module_slideshow)) { ?>
				<div id="slideshow">
					<?php comModule_show('slideshow', $module_slideshow) ?>
				</div><!-- /slideshow -->
				<?php } ?>

				<?php if (count($module_left)) { ?>
				<div id="column-left">
					<?php comModule_show('left', $module_left) ?>
				</div><!-- /column-left -->
				<?php } ?>

				<div id="main3">
					<?php comMenu_pathway(' &raquo; ', true) ?>
					<div id="main-content">
						<?php comMenu_loadMainContent() ?>
						<div id="main-content-clear"></div>
					</div><!-- /main-content -->
				</div><!-- /main3 -->

			</div><!-- /main2 -->

			<div id="main-clear"></div>
		</div><!-- /main -->

	</div><!-- /wrapper-main -->

	<div id="wrapper-footer">
		<a href="#topcontrol" id="toplink">Top</a>
		<?php echo $g_page['config_site_name'] ?>
	</div><!-- /wrapper-footer -->

</div><!-- /wrapper -->

<div id="body-bottom"><a href="http://avine.io/">2012-<?php copyrightDate() ?> &copy; avine.io</a></div>

<?php ie6_no_more() ?>
<?php new debugManager($g_page['debug']) ?>
</body>
</html>