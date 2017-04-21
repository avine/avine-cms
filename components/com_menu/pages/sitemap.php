<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<h1>'.LANG_COM_MENU_SITEMAP_TITLE.'</h1>';

$sitemap = new comMenu_siteMap();
echo $sitemap->display();

?>