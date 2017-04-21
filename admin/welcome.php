<?php //echo '<div id="admin_welcome">'; ?>

<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


//////////////
// Start view

echo "<h1>".indexTitleIcon('home').LANG_ADMIN_ROOT_WELCOME.'<br /><span>'.admin_siteName()."</span></h1>\n";


$html = '';


// Installation link
if (file_exists(sitePath().'/installation/installation.php'))
{
	$html .= admin_message(LANG_ADMIN_WELCOME_INSTALL_DIR_WARNING, 'warning');
	$html .= '<img src="images/goto-install.png" style="vertical-align:middle;margin-left:25px;" alt="" /> &nbsp; <a href="'.WEBSITE_PATH.'/installation/installation.php">Installation/désinstallation</a>'.'<br /><br />';
}


// Go to frontend link
$html .= '<img src="images/goto-frontend.png" style="vertical-align:middle;margin-left:25px;" alt="" /> &nbsp; <a href="../" class="external">'.LANG_ADMIN_GO_TO_FRONTEND.'</a>'.'<br /><br />';


// Users stats
comUser_login::userSession_counter($counter);
$html .=
	'<img src="images/users.png" style="vertical-align:middle;margin-left:25px;" alt="" /> &nbsp; <strong>'.LANG_COM_USER_SESSION_TRAFFIC.'</strong> &nbsp;&nbsp; '.
	'<span class="grey">'.LANG_COM_USER_SESSION_COUNTER_GUESTS	.'</span>'.$counter['guest'			].' &nbsp;&middot;&nbsp; '.
	'<span class="grey">'.LANG_COM_USER_SESSION_COUNTER_USERS	.'</span>'.$counter['user'			].' &nbsp;&middot;&nbsp; '.
	'<span class="grey">'.LANG_COM_USER_CONFIG_VISIT_COUNTER	.'</span>'.$counter['visit_counter'	].'<br /><br />';


echo "<p>$html</p>\n\n";

# Note : all the following max-with are optimized for screen width : 1280px

// Logged users details
echo '<div style="max-width:230px; float:left; margin-right:20px;">'.admin_comUserSession_details()."</div>\n";


// Summary infos for authors and editors (might be empty)
$max_item = 50; # false to disable
echo '<div style="max-width:460px; float:left; margin-right:20px;">'.admin_comContent_pendingTasks($max_item)."</div>\n";
echo '<div style="max-width:460px; float:left; margin-right:20px;">'.admin_comContent_lastPublished($max_item)."</div>\n";

?>

<?php //echo '</div>'; ?>