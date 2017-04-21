/* Avine. Copyright (c) 2008 Stéphane Francel (http://avine.fr). Dual licensed under the MIT and GPL Version 2 licenses. */



/*
 * Use <a href="#" class="external">Link</a> to emulate <a href="#" target="_blank">Link</a>
 */
$(document).ready(function(){
	$(".external").click(function(){
		$(this).attr({target: "_blank"});
	});
});



/*
 * Replace <hr /> by <div class="hr"><hr /></div> and preserve class
 */

// Prevent the default behaviour as soon as possible
document.writeln('<style type="text/css">hr{display:none;}</style>');

// Wrap the hr tag
$(document).ready(function(){
	$("body").find("hr").each(function() {
		// Get properties
		var mt = $(this).css("margin-top");
		var mb = $(this).css("margin-bottom");
		var cl = $(this).attr("class");
		// Wrap target and add properties
		$(this).wrap('<div class="hr"></div>').parent().css("margin-top", mt).css("margin-bottom", mb).addClass(cl).children().removeClass(cl);
	})
	//.remove("hr") // Optional
	;
});



/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood{at}iinet.com.au) and Stéphane Nahmani (sholby@sholby.net). */
jQuery(function($){
	$.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: '&#x3c;Préc',
		nextText: 'Suiv&#x3e;',
		currentText: 'Courant',
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
		'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
		'Jul','Aoû','Sep','Oct','Nov','Déc'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['fr']);
});



/*
 * Init Superfish menu
 */

$(document).ready(function(){
	jQuery('ul.sf-menu').superfish();
});



/*
 * Init jFontResizer
 */

$(document).ready(function(){
	$('#jfontResizer').jfontResizer();
	$('#jfontResizer a#decreaseFont').hide(); // Hide the decrease font button (optional)
});



/*
 * Init lightbox
 */

$(document).ready(function(){
	$('a.lightbox').lightBox();
});



/*
 * Init jScroll Pane
 */

/*$(document).ready(function(){
	$('.scroll-pane').jScrollPane({showArrows:true});
});*/



/*
 * jCarousel configuration
 */

function avineCarousel_initCallback(carousel)
{
	// Pause autoscrolling on mouse hover the clip.
	carousel.clip.hover(function() {
		carousel.stopAuto();
	}, function() {
		carousel.startAuto();
	});

	// Create the markup of the controls in the DOM
	carousel.container.after(function() {
		var control = '';
		for (var index=1; index<=carousel.clip.find('ul > li').length; index++) {
			control += '<a href="#">'+index+'</a>';
		}
		return '<div class="jcarousel-control">' + control + '</div>';
	});

	// Step to controlled item
	jQuery('.jcarousel-control a').bind('click', function() {
		carousel.scroll(jQuery.jcarousel.intval(jQuery(this).text()));
		return false;
	});
};

// Add 'current' class to the control of the current item
function avineCarousel_itemLoadCallback(carousel) {
	$('.jcarousel-control a').removeClass('current').parent().find('a').each(function(){
		if ($(this).text() == carousel.last) { // TODO - Je ne sais pas si ça marche, dans le cas où il y a plusieurs item visibles...
			$(this).addClass('current');
		}
	});
};

function avineCarousel_animationStepCallback(carousel)
{
	
}

// Init carousel behaviour
$(document).ready(function() {
	jQuery('#carousel').jcarousel({
		scroll:					1,
		animation:				1500,
		easing:					"easeInOutQuint",
		auto:					4,
		wrap:					'both',
		initCallback:			avineCarousel_initCallback,
		itemLoadCallback:		avineCarousel_itemLoadCallback,
		animationStepCallback:	avineCarousel_animationStepCallback
	});
});

