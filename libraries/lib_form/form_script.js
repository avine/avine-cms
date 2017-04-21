/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */



/*
 * Select text inside an readonly-input field on user focus
 */

$(document).ready(function(){
	$(".form-text-readonly").focus(function(){
		this.select();
	});
});



/*
 * Handle the bad user entries with some javascript behaviours
 */

function addErrorHighlight_callback()
{
	// Find the form elements to highlight
	// In the PHP method formManager_filter::requestName(), the "highlight" feature is by-passed
	// So, we safely can process a full match of the name attribute ( instead of matching by prefix like that : '[name^="' + this + '"]' )
	$('[name="' + this + '"]').not('[type="image"]').each(function(){
		// Add class to label
		var id = $(this).attr('id');
		if (id) $('label[for=' + id + ']').addClass("form-add-error-label");

		// Choose the event to bind
		if ($(this).attr('type') == 'text' || $(this).attr('type') == 'password' || $(this).is('textarea')) var event = 'keyup';
		else var event = 'change';

		// Add class to input
		$(this).addClass('form-add-error-input').bind(event, function(){
			// Remove class from inputs and labels
			var name = $(this).attr('name');
			$('[name="' + name + '"]').each(function(){ // each() required for radios buttons
				$(this).removeClass("form-add-error-input");
				var id = $(this).attr('id');
				if (id) $('label[for=' + id + ']').removeClass("form-add-error-label");
			});
		});
	});
}
