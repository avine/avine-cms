<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// This page menu
$menu = new admin_menuManager('template');


?>
<h1><?php echo indexTitleIcon('template').LANG_ADMIN_COM_TEMPLATE_INDEX_TITLE; ?></h1>

<div id="admin_tabulator" class="clearfix">
	<div id="tab-header">

<?php $menu->displayMenu(); ?>

	</div>
	<div id="tab-main">
		<div id="tab-main2">

<?php $menu->includeTarget(); ?>

		</div>
	</div>
	<div id="tab-footer"><div>&nbsp;</div></div>
</div>

<?php  ?>