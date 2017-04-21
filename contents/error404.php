<?php

/*
 * Do not delete this script. It is used by the system to redirect error 404.
 */

// Inform user
$box = new boxManager();
$box->echoMessage(LANG_ERROR_404, 'error', true, '100%');

// Display sitmap
include(sitePath().'/components/com_menu/pages/sitemap.php');

?>