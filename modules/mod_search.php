
<!-- Clear #search_mod_string on focus -->
<script type="text/javascript">
$(document).ready(function(){
	var clear_search = false;
	$.fn.clearSearch = function() {
		$(this).addClass('mod_search_clear');
		return this.focus(function() {
			if( this.value == this.defaultValue ) {
				this.value = "";
				clear_search = true;

				$(this).removeClass('mod_search_clear');
			}
		}).blur(function() {
			if( !this.value.length ) {
				this.value = this.defaultValue;
				clear_search = false;

				$(this).addClass('mod_search_clear');
			}
		});
	};
	$("#search_mod_string").clearSearch();
	$("#search_mod_").submit(function(){
		if (!clear_search) {
			return false;
		}
	});
});
</script>

<?php

// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );

// See '/components/com_search/' for more details
echo '<div class="comSearch_form hide-form-submit tiny-form">'.comSearch_::form('search_mod_', LANG_COM_SEARCH_FORM_SUBMIT).'</div>';

?>