<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Site infos
comConfig_getInfos($site_name, $system_email); # passed by reference


// User infos
global $g_user_login;
if ($user_id = $g_user_login->userID()) {
	$user_details = new comUser_details($user_id);
}


// Newsletter
$newsletter = new comNewsletter();



/*
 * Logged user has already subscribed !
 */
if ( $user_id && $newsletter->isSentToUser($user_id, $exact_subscriber) )
{
	echo '<p>'.str_replace('{email}', $user_details->get('email'), LANG_COM_NEWSLETTER_SUBSCRIBE_STATUS_OK).'</p>';
}

/*
 * Anonymous user or logged user who have not subscribed !
 */
else
{
	if ($user_id && ($email = $user_details->get('email'))) # The logged user have an email !
	{
		$param	= 'readonly';
	} else {
		$email	= '';
		$param	= '';
	}
?>

<!-- Clear #newsletter_subscribe_email on focus -->
<script type="text/javascript">
$(document).ready(function()
{
	var label_email = $("label[for='newsletter_subscribe_email']").hide().text();

	if (!$('#newsletter_subscribe_email').attr("readonly"))
	{
		var clear_email = false;
		$.fn.clearEmail = function() {
			$(this).addClass('mod_newsletter_clear');
			return this.focus(function() {
				if( this.value == label_email ) {
					this.value = "";
					clear_email = true;

					$(this).removeClass('mod_newsletter_clear');
				}
			}).blur(function() {
				if( !this.value.length ) {
					this.value = label_email;
					clear_email = false;

					$(this).addClass('mod_newsletter_clear');
				}
			});
		};

		$("#newsletter_subscribe_email").val(label_email).clearEmail();
		$("#newsletter_subscribe_").submit(function(){
			if (!clear_email) {
				return false;
			}
		});		
	}
});
</script>

<?php
	$output = '';
	$form = new formManager();
	$output .= $form->form('post', comMenu_rewrite('com=newsletter&amp;page=subscribe'), comNewsletter::FORM_ID_);

	$output .= '<p>'.str_replace('{site_name}', $site_name, LANG_COM_NEWSLETTER_SUBSCRIBE_TIPS)."</p>\n";

	$output .= '<p>'.$form->text(comNewsletter::INPUT_NAME, $email, LANG_COM_NEWSLETTER_SUBSCRIBE_EMAIL, '', "size=20;$param");
	$output .= $form->submit('submit', LANG_COM_NEWSLETTER_SUBSCRIBE_SUBMIT)."</p>\n";

	$output .= $form->end();
	echo $output;
}

?>