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
$com_gen = new comContent_frontend($init); /* WARNING : use special extended class ! */


// Unset temporary variable
$init = NULL;


// Call specific comGeneric_frontend method
$com_gen->configFrontend();


// Redirection Url (advanced feature - only if you know the criticals consequences)
if (COM_CONTENT_ACTIVATE_REDIRECTION)
{
	$url_redirection = '';						# this url part : 'com=content&amp;page=index' is replaced by '' in all generated urls
	$com_gen->setRedirection($url_redirection);	# Specific comGeneric_frontend method
}


?>
<!-- comContent_ : autoSubmit nodeSelector() and acrchiveSelector() -->
<script type="text/javascript">
$(document).ready(function(){
	$("input#node_selector_submit"		).hide(); // hide submit button
	$("input#archive_selector_submit"	).hide(); // hide submit button

	$("form#node_selector_ select"		).change(function(){$("form#node_selector_"		).submit();});
	$("form#archive_selector_ select"	).change(function(){$("form#archive_selector_"	).submit();});
});
</script>
<?php

// Define the Title of the main page '/index.php?com=content&page=index'
global $com_generic_root_title;
$com_generic_root_title = LANG_COM_CONTENT_INDEX_ROOT_TITLE;

// Include file
require(sitePath().'/components/com_generic/static_index.php'); /* WARNING : use general generic file ! */

?>