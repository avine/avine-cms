/**
 * jQuery Font Resizer
 *
 * Author : S. Francel
 *
 * Adapted from :
 * 		http://www.fluidbyte.net/index.php?view=jquery-font-sizer
 * 		http://www.shopdev.co.uk/blog/font-resize.html
 */
jQuery.fn.jfontResizer = function(o) {

	// Cookie functions (not work on ie6)
	function setCookie( c_name, value, expiredays ) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + expiredays);
		document.cookie = c_name + "=" + escape(value) + ((expiredays==null) ? "" : ";expires=" + exdate.toGMTString());
	}

	function getCookie(c_name) {
		if (document.cookie.length>0) {
			c_start = document.cookie.indexOf(c_name + "=");
			if (c_start != -1) {
				c_start = c_start + c_name.length+1;
				c_end = document.cookie.indexOf(";", c_start);
				if (c_end == -1) c_end = document.cookie.length;
				return unescape(document.cookie.substring(c_start, c_end));
			}
		}
		return "";
	}


	// Defaults
	var o = jQuery.extend({
		applyTo: 'body',

		defaultSize: '12',	// default font size 
		minFontSize: '10',
		maxFontSize: '16',

		factor: true,		// (boolean )if true, use changeSmall (and changeLarge) to set the newFontSize=currentFontSize*changeSmall (factorial)
		changeSmall: '0.8',
		changeLarge: '1.2',

		changeScale: 1,		// no quote. if factor=false, use this to set the newFontSize=currentFontSize +/- changeScale (linear)

		animate: true,		// (boolean) animate the transition

		expire: 30			// cookie time
	},o);

	var s = ''; // to add css class to '#jfontResizer a' (s=small, m=medium, l=large)
	var m = '';
	var l = '';

	var d = parseFloat(o.defaultSize, 10); // default font size


	// Check cookie 
	var c;
	if (getCookie('jfontResizer_admin')) // modified by Avine
	{
		c = parseFloat(getCookie('jfontResizer_admin'), 10); // modified by Avine

		if (c <  d) {
			s = 'jfontResizer';
		}
		else if (c == d) {
			m = 'jfontResizer';
		}
		else if (c >  d) {
			l = 'jfontResizer';
		}
	}
	else {
		c = d;

		m = "jfontResizer";
	}
	$(o.applyTo).css('font-size', c); // apply cookie (or default value)


	// Create jfontResizer selector
	$(this).html('<a href="#" id="decreaseFont" class="'+s+'">A</a><a href="#" id="resetFont" class="'+m+'">A</a><a href="#" id="increaseFont" class="'+l+'">A</a>').hide().fadeIn(1000);


	/////////////////////
	// Change size event (default version with switch() operator)

	$('#jfontResizer a').click(function(){

		var t = $(this).attr('id'); // id of clicked link

		$('.jfontResizer').removeClass('jfontResizer');
		$(this).addClass('jfontResizer'); // update css

		var currentFontSize = parseFloat($(o.applyTo).css('font-size'), 10);

		switch(t)
		{
			// Reset Font Size
			case 'resetFont':
				var newFontSize = d;

				if (o.animate) {
					$(o.applyTo).animate({fontSize: newFontSize}, 600);
				} else {
					$(o.applyTo).css('font-size', newFontSize);
				}
				setCookie('jfontResizer_admin', newFontSize, o.expire); // modified by Avine

				return false;
				break;

			// Increase Font Size
			case 'increaseFont':
				if (o.factor) {
					var newFontSize = Math.round(parseFloat(currentFontSize*o.changeLarge, 10));
				} else {
					var newFontSize = parseFloat(currentFontSize+o.changeScale, 10);
				}

				if (newFontSize <= o.maxFontSize) {
					if (o.animate) {
						$(o.applyTo).animate({fontSize: newFontSize}, 600);
					} else {
						$(o.applyTo).css('font-size', newFontSize);
					}
					setCookie('jfontResizer_admin', newFontSize, o.expire); // modified by Avine
				}
				return false;
				break;

			// Decrease Font Size
			case 'decreaseFont':
				if (o.factor) {
					var newFontSize = Math.round(parseFloat(currentFontSize*o.changeSmall, 10));
				} else {
					var newFontSize = parseFloat(currentFontSize-o.changeScale, 10);
				}

				if (newFontSize >= o.minFontSize) {
					if (o.animate) {
						$(o.applyTo).animate({fontSize: newFontSize}, 600);
					} else {
						$(o.applyTo).css('font-size', newFontSize);
					}
					setCookie('jfontResizer_admin', newFontSize, o.expire); // modified by Avine
				}
				return false;
				break;
		}	
	});
};
