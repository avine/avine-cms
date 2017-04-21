<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


echo '<h1>'.LANG_COM_NEWSLETTER_ARCHIVED_TITLE.'</h1>';


// Display a message when coming from '/components/com_newsletter/online.php?id=xxx' with xxx is invalid
if (isset($_GET['founded']) && $_GET['founded'] == 0)
{
	echo userMessage(LANG_COM_NEWSLETTER_ARCHIVED_NOT_FOUNDED, 'warning');
}


$html  = '';

// List the sended newsletters
global $db;
if ($newsletter = $db->select("newsletter, id, subject, date_creation(desc), join:id>; newsletter_send, join: <newsletter_id, where: date_begin IS NOT NULL"))
{
	$html .= "\n<ul>\n";
	for ($i=0; $i<count($newsletter); $i++)
	{
		// Alias
		$online			= WEBSITE_PATH.'/components/com_newsletter/online.php?id='.$newsletter[$i]['id'];
		$date_creation	= getTime($newsletter[$i]['date_creation'], 'time=no');
		$subject		= $newsletter[$i]['subject'];

		$html .= "\t<li>$date_creation - <a href=\"$online\" class=\"external\">$subject</a></li>\n";
	}
	$html .= "</ul>\n\n";
}
else {
	$html .= '<p>'.LANG_COM_NEWSLETTER_ARCHIVED_EMPTY.'</p>';
}

echo $html;

?>