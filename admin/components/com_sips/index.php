<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/*
 *  index using submenu template
 */

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// This page menu
$menu = new admin_menuManager('sips');


?>

<div id="admin_submenu" class="clearfix">
	<div id="submenu-header">
		<div id="header-top"></div>

<?php $menu->displayMenu(); ?>

		<div id="header-bottom"></div>
	</div>
	<div id="submenu-main" class="clearfix">

<?php $menu->includeTarget(); ?>

	</div>
	<div id="submenu-footer"></div>
</div>

<?php  ?>