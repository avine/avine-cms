<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


echo '<h1>'.LANG_COM_WCAL_CAT_TITLE.'</h1>';


$html = '';

global $db;

$period		= wcal::periodDetails();
$category	= wcal::categoryDetails();
$wday		= wcal::wdayOptions();


foreach($category as $id => $details)
{
	$details['color'] ? $style = wcal::eventBgStyle($details['color'], '', 'margin-bottom:8px; padding:3px 6px;') : $style = '';

	// Title & author
	$html .= "<h3$style>".$details['title'].($details['author'] ? " - <span>{$details['author']}</span>" : '')."</h3>\n";

	// Comment
	$details['comment'] ? $comment = $details['comment'].' <br />' : $comment = '';
	if (isset($details['node_href'])) {
		$comment .= '<a href="'.$details['node_href'].'">'.LANG_COM_WCAL_INDEX_READ_MORE.'</a> ';
	}
	!$comment or $html .= "<p>$comment</p>\n\n";

	// Events
	if ($events = $db->select("wcal_event, *, period_id(asc), where: category_id=$id"))
	{
		// Get events in an array
		$event_array = array();
		$period_id = NULL;
		for ($i=0; $i<count($events); $i++)
		{
			$evnt = $events[$i]; # Alias

			if ($evnt['period_id'] != $period_id)
			{
				$period_id = $evnt['period_id'];
				if ($period_id != 1)
				{
					$event_array[] = array( 'title' => $event_title, 'list' => "\t<ul>\n$event_list\t</ul>\n\n" );

					$event_title = "\t<strong>".ucfirst($period[$period_id]['validity'])." :</strong><br />\n";
				} else {
					$event_title = '';
				}
				$event_list = '';
			}

			$time_begin	= wcal::timeDBtoHTML($evnt['time_begin'	]);
			$time_end	= wcal::timeDBtoHTML($evnt['time_end'	]);

			$event_list .= "\t\t<li>".LANG_COM_WCAL_CAT_THE.' '.mb_strtolower($wday[$evnt['wday']]).'s '.LANG_COM_WCAL_CAT_BETWEEN." $time_begin ".LANG_COM_WCAL_CAT_AND." $time_end</li>\n";
		}
		$event_array[] = array( 'title' => $event_title, 'list' => "\t<ul>\n$event_list\t</ul>\n\n" );

		// Get events in html and prevent duplicates
		$event_html = '';
		for ($i=0; $i<count($event_array); $i++)
		{
			if ($i!=0 && $event_array[$i]['list'] == $event_array[0]['list']) {
				continue; # skip duplicate
			}
			$event_html .= $event_array[$i]['title'].$event_array[$i]['list'];
		}
		$html .= "<div class=\"wcal-category-events\">\n\n$event_html<div class=\"wcal-category-events-title\">".LANG_COM_WCAL_CAT_SCHEDULES."</div></div>\n\n";
	}
}

$html or $html = '<p>'.LANG_COM_WCAL_CAT_EMPTY.'</p>';

echo $html;


echo	'<p style="margin-top:2em;">'.
			'<a class="button" href="'.comMenu_rewrite('com=wcal&page=dedicate').'">'.LANG_COM_WCAL_GOTO_DEDICATE	.'</a> &nbsp; '.
			'<a class="button" href="'.comMenu_rewrite('com=wcal&page=index'	).'">'.LANG_COM_WCAL_GOTO_INDEX		.'</a> '.
		'</p>';

?>