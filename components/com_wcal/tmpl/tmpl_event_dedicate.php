<?php

/*	---------------
	Availables tags
	---------------
	event_id
	wday
	time_begin
	time_end
	category_id
	unique_id
	title
	author
	comment
	color
	node_id
	node_href
	node_link
	current (addon)
	checkbox (addon)
	label (addon)
	--------------- */

if ($node_id)
{
	$goto =	'<a href="'.$node_href.'" title="'.LANG_COM_WCAL_INDEX_READ_MORE.'" class="wcal-link">'.
				'<img src="'.WEBSITE_PATH.'/components/com_wcal/images/magnifier.png" alt="'.LANG_COM_WCAL_INDEX_READ_MORE.'" /></a>';

	$title_author = "<div class=\"wcal-infos\">$label{$author}</div>"; # Notice : the <br /> has been already included in the label
} else {
	$title_author = "<div class=\"wcal-infos\"><strong>$title<br /></strong>$author</div>";
}



echo <<< END

<div class="wcal-event" style="background-color:$color;">
	<div class="wcal-time">$time_begin - $time_end</div>
	$title_author
	$goto
	$checkbox
</div>

END;

?>