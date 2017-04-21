<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


$menu_id = 1;
$param = comMenu_behaviour('superfish');

echo comMenu_menu($menu_id, $param);

?>