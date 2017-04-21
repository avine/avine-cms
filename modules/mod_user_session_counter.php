<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Website activity : counter of guests and users
echo comUser_login::userSession_counter($counter);


// Website popularity : counter of visitors from the begining
echo '<p>'.LANG_COM_USER_CONFIG_VISIT_COUNTER.$counter['visit_counter'].'</p>';


// For debugging
#alt_print_r($counter);

?>