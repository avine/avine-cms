<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// Direct access authorized
define('_DIRECT_ACCESS', 1);


// Includes
require('../../../config.php'); # relative path

require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/global/include.php');			# Frontend includes
require($_SERVER['DOCUMENT_ROOT'].WEBSITE_PATH.'/admin/global/include.php');	# Backend includes

loaderManager::directAccessBegin(ADMIN_DEBUG_MODE);



// Protocol info
global $g_protocol;
$g_protocol = 'http://'; # Required by the class 'comNewsletter_tmpl'


// Database connection
global $db;


// Get the current send id
$session = new sessionManager(sessionManager::BACKEND, 'newsletter_send');

($send_id = $session->get('send'))
	or die(
		'<!DOCTYPE html><html><head><title>'.LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE.'</title></head><body style="font:12px Arial;"><p>'.
		LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_SESSION_MISSING.
		'</p></body></html>'
	);



// Get the newsletter details
$newsletter =
	$db->selectOne(
		"newsletter_send, date_begin,date_end,sent_count, where: id=$send_id, join: newsletter_id>; ".
		'newsletter, id AS newsletter_id, tmpl_id,subject,message,sender,reply_to, join: <id'
	);

$newsletter_id = $newsletter['newsletter_id']; # Alias



// Get the global configuration
$config = $db->selectOne('newsletter_config, return_path, batch_size, refresh_time');

($config['return_path'] && $config['batch_size'] && $config['refresh_time'])
	or die(
		'<!DOCTYPE html><html><head><title>'.LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE.'</title></head><body style="font:12px Arial;"><p>'.
		LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_CONFIG_MISSING.
		'</p></body></html>'
	);



// Build the newsletter (once per session)
if ($newsletter && !$session->get('message'))
{
	$session->set('message', comNewsletter_tmpl::getMessage($newsletter_id, $send_id));
}



// Get the subscriber list (once per session)
if (!$session->get('subscriber'))
{
	$nl = new comNewsletter(); // Resolve potential conflict

	$subscriber = $db->select('newsletter_subscriber, *, where: activated=1');
	$user = $db->select("user, [id],email, where: email != ''");

	$list = array();
	for ($i=0; $i<count($subscriber); $i++)
	{
		if ($subscriber[$i]['email'])
		{
			$list[] = $subscriber[$i]['email'];
		}
		elseif ($user[ $subscriber[$i]['user_id'] ]['email']) # Simple security, but should be useless...
		{
			$list[] = $user[ $subscriber[$i]['user_id'] ]['email'];
		}
	}
	$list = array_values(array_unique($list)); # Simple security, but should be useless...

	// Here the subscribers list !
	$session->set('subscriber', $list);
}

// Check the subscriber count
$subscriber_count = count($session->get('subscriber')) # Alias
	or die(
		'<!DOCTYPE html><html><head><title>'.LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE.'</title></head><body style="font:12px Arial;"><p>'.
		LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_SUBSCRIBER_MISSING.
		'</p></body></html>'
	);



// What's the situation ?
if ( $newsletter['sent_count'] < $subscriber_count )
{
	// Start and stop numbers of the current batch
	$batch_start	= $newsletter['sent_count'];
	$batch_stop		= $newsletter['sent_count'] + $config['batch_size'];

	($batch_stop <= $subscriber_count) or $batch_stop = $subscriber_count;

	$batch_num_emails = $batch_stop - $batch_start;

	// Batch progression info
	$batch_number	= intval( $subscriber_count			/ $config['batch_size'] );
	$batch_current	= intval( $newsletter['sent_count']	/ $config['batch_size'] ) +1;

	!($subscriber_count % $config['batch_size']) or $batch_number++;
}
else
{
	if (!$db->selectOne("newsletter_send, date_end, where: id=$send_id", 'date_end'))
	{
		// Send operation finished !
		$db->update("newsletter_send; date_end=".time()."; where: id=$send_id");
	}

	die(
		'<!DOCTYPE html><html><head><title>'.LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE.'</title></head><body style="font:12px Arial;">'.
		'<p style="height: 48px; background: url(images/finish.gif) no-repeat left center; line-height: 48px; text-indent: 53px; font-size: 17px; color: #A6A6A6;">'.
		LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_FINISH.
		'</p></body></html>'
	);

	// Close the page access
	$session->reset();
}



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo LANG_ADMIN_COM_NEWSLETTER_SEND_BATCH_TITLE; ?></title>

	<meta name="robots" content="noindex,nofollow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<!-- Refresh the page for the next batch -->
	<meta http-equiv="Refresh" content="<?php echo $config['refresh_time']; ?>; URL=<?php echo WEBSITE_PATH.'/admin/components/com_newsletter/_batchsend.php'; ?>" />

	<style type="text/css">
	* {
		margin: 0;
		padding: 0;
	}
	body {
		font: normal 12px Arial;
		color: #333;
	}
	h3 {
		margin: 3px 0 3px 0;
		font-size: 14px;
		font-weight: normal;
		color: #9DBA4F;
	}
	p {
		margin: 0 0 15px 0;
	}
	#emails-list {
		margin: 0 0 15px 0;
		padding: 2px 6px;
		width: 250px;
		height: 200px;
		overflow: auto;
		border: 1px solid #CCC;
		background-color: #FAFAFA;
		line-height: 18px;
	}
	#wait {
		height: 48px;
		background: url(images/wait.gif) no-repeat left center;
		line-height: 48px;
		text-indent: 53px;
		font-size: 17px;
		color: #A6A6A6;
	}
	</style>

	<!-- countDown -->
	<script src="<?php echo WEBSITE_PATH; ?>/plugins/js/jquery-ui/js/jquery.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
		var countDown = <?php echo $config['refresh_time']; ?>;
		$('#countDown').html(countDown + ' sec');
		setInterval(
			function(){
				if (--countDown > 0) {
					$('#countDown').html(countDown + ' sec');
				} else {
					$('#countDown').html('0 sec');
					$('body').fadeOut(300);
				}
			}
			, 1000
		);
	});
	</script>
</head>
<body>
<?php



// Current task infos
$html = '';

$html .= '<h3>'.LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_SUBJECT."</h3>\n";
$html .= "<p>{$newsletter['subject']}</p>\n";

$html .= '<h3>'.LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_BATCH."</h3>\n";
$html .= "<p>$batch_current / $batch_number</p>\n";

$html .= '<h3>'.LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_COUNTDOWN."</h3>\n";
$html .= "<p><span id=\"countDown\">{$config['refresh_time']} sec</span>&nbsp;</p>\n";

$html .= "<h3>$batch_num_emails ".LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_EMAILS_LIST."</h3>\n";



// Send the emails in the current batch
$mail = new emailManager();

$mail	->addMessageHTML	($session->get('message'	))
		->setSubject		($newsletter['subject'		])
		->setFrom			($newsletter['sender'		])
		->setReplyTo		($newsletter['reply_to'		])
		->setReturnPath		($config['return_path'		]);

$html .= "<div id=\"emails-list\">\n";
$list = $session->get('subscriber');
for ($i=$batch_start; $i<$batch_stop; $i++)
{
	$mail->addTo($list[$i]);

	$html .= $list[$i]."<br />\n";
}
$html .= "</div>\n";
$html .= '<div id="wait">'.LANG_ADMIN_COM_NEWSLETTER_BATCHSEND_WAIT."</div>\n";

$mail->send(); # To debug this script (without sending emails) comment this line.



// Display infos
echo $html;



// Update database
if (!$newsletter['date_begin'])
{
	$date_begin = 'date_begin='.time().', ';
} else {
	$date_begin = '';
}

$db->update("newsletter_send; {$date_begin}sent_count=$batch_stop; where: id=$send_id");



?>
</body>
</html><?php



loaderManager::directAccessEnd();

?>