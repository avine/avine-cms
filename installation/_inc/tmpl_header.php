<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// If necessary, send the charset in the headers
header('Content-Type: text/html; charset=utf-8');


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo LANG_INSTALL_PAGE_TITLE; ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /><!-- For ie8 -->

	<link rel="shortcut icon" href="../global/favicon.ico" />

	<!-- css : global -->
	<link rel="stylesheet" type="text/css" href="../global/global_css.css" />

	<!-- css : libraries -->
	<link rel="stylesheet" type="text/css" href="../libraries/lib_database/database_style.css" />
	<link rel="stylesheet" type="text/css" href="../libraries/lib_form/form_style.css" />
	<link rel="stylesheet" type="text/css" href="../libraries/lib_table/table_style.css" />
	<link rel="stylesheet" type="text/css" href="../libraries/lib_ftp/ftp_style.css" />
	<link rel="stylesheet" type="text/css" href="../libraries/lib_box/box_style.css" />

	<link rel="stylesheet" type="text/css" href="_css/template_css.css" />

	<!--[if IE]>
	<style type="text/css">fieldset { padding-top: 0; } legend { margin-bottom: 15px; }</style>
	<![endif]-->

	<!-- jQuery -->
	<script src="../plugins/js/jquery-ui/js/jquery.min.js" type="text/javascript"></script>

	<!-- Check/uncheck -->
	<script type="text/javascript">
	$(document).ready(function() {
		var checked_status = true;
		$("#checked_status").addClass("checked_status").show().click(function(){
			checked_status = !checked_status;
			$('input[name^="db_install_"]').each(function(){
				this.checked = checked_status;
			});
		});					
	});
	</script>

</head>
<body>

<div id="wrapper">
	<div id="header"><div id="site-name"><a href="<?php echo $_SERVER['PHP_SELF']; ?>"><?php echo LANG_INSTALL_PAGE_TITLE; ?></a></div><div id="logo"></div></div>
	<div id="main">
	<!-- Template : end Header -->


