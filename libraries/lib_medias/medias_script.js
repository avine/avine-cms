/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */



/*
 * Because the medias preference button has been replaced by an image, the value of the input is now placed after the input inside a <span> tag.
 * A title attribute is also added to the input to inform about the purpose of the button.
 */
jQuery(function(){
	var value = $("#media_manager_pref_switch").attr("value");
	$("#media_manager_pref_switch").attr("title", "Modifier le format du média").after('<br /><span>'+value+'</span>');
});



/*
 * Use tabs to display medias
 * Add lightbox behaviour to images
 */

jQuery(function(){
	$("#medias-tabs").tabs({collapsible: true});
	$('.medias-manager-lightbox').lightBox();
});
