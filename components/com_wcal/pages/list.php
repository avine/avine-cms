<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Config
$months = 6; # false to disable
$time_begin = time() - 60*60*24*30* $months; # 6 months


if ($months == 1) {
	$title = LANG_COM_WCAL_LIST_TITLE_MONTH;
}
elseif ($months >= 2) {
	$title = str_replace('{months}', $months, LANG_COM_WCAL_LIST_TITLE_MONTHS);
}


echo '<h1>'.LANG_COM_WCAL_LIST_TITLE." $title</h1>\n";


if ($match = wcal::matchDedicateByTime($time_begin))
{
	echo wcal::matchDedicateHTML($match); # TODO - Add multipage...
}
else {
	echo '<p>'.LANG_COM_WCAL_LIST_EMPTY.'</p>';
}


?>