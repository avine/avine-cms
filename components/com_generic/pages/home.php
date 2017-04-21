<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Get component setting
global $init;
!isset($init) or trigger_error(LANG_COM_GENERIC_INIT_OVERWRITTEN, E_USER_WARNING);
require(comGeneric_::comSetupPath(__FILE__));


// Instanciate class object
global $com_gen;
$com_gen = new comGeneric_frontend($init);


// Unset temporary variable
$init = NULL;


// Call specific comGeneric_frontend method
$com_gen->configFrontend();


// Redirection Url (advanced feature - only if you know the criticals consequences)
#$url_redirection = '{set_your_redirection}';	/* this url part : 'com=content&amp;page=index' is replaced by '{set_your_redirection}' in all generated urls */
#$com_gen->setRedirection($url_redirection);	/* Specific comGeneric_frontend method */

?>
<!-- comGeneric_ : autoSubmit homeNdeSelector() -->
<script type="text/javascript">
$(document).ready(function(){
	$("input#home_nde_selector_submit"	).hide(); // hide submit button

	$("form#home_nde_selector_ select"	).change(function(){$("form#home_nde_selector_").submit();});
});
</script>
<?php

// Include file
require(sitePath().'/components/com_generic/static_home.php');

?>