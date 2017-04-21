/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


/*
 * Toggle behaviour
 */
jQuery(function(){
	// Init
	$(".toggle .toggle-content").hide();
	$(".toggle .toggle-title").css("cursor","pointer");

	// Toggle behaviour
	$(".toggle").children(".toggle-title").click(function(){
		$(this).next(".toggle-content").slideToggle("slow", function(){
			if ($(this).css("display") == 'none'){
				$(this).prev(".toggle-title").removeClass("toggle-title-active");
			}
		});
		if (!$(this).hasClass("toggle-title-active")){
			$(this).addClass("toggle-title-active");
		}
		return false;
	});

	// Show content on demand
	$(".toggle .toggle-title.show").trigger('click');
});
