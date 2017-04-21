<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$menu_id = 2;
$param = comMenu_behaviour();

echo comMenu_menu($menu_id, $param);

?>
