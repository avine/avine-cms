<?php

/**
 * Simplified code from S.Francel
 */


session_start();


//////////
// Config

$captcha_input_name	= 'captcha';
$captcha_img_id		= 'siimage';

define( 'LANG_REFRESH_IMAGE', "Changer d'image" );

// end
//////

$sid_first = md5(rand());

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Test Captcha</title>

	<style type="text/css">
		body				{ padding: 50px; font: normal 12px "Trebuchet MS", Arial; color: #333; }
		.securimage-wrapper	{ }
		.securimage-image	{ float: left; margin: 0 5px 10px 0; border: 1px solid grey; }
		.securimage-play	{ }
		.securimage-refresh	{ }
		.clear				{ clear: left; }
		.success			{ color: green; }
		.failure			{ color: red; }
	</style>
</head>
<body>
	<h1>Test Captcha</h1>



<?php

if (!empty($_POST))
{
	require_once("securimage.php");
	$captcha = new Securimage();

	if ($captcha->check($_POST[$captcha_input_name]))
	{
		echo '<p class="success">Thanks, you entered the correct code !</p>';
	} else {
		echo '<p class="failure">Sorry, the code you entered was invalid !</p>';
	}

	echo "<p><a href=\"{$_SERVER['PHP_SELF']}\">Try again</a></p>";
}
else {

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

	<div class="securimage-wrapper">

		<!-- Image -->
		<img class="securimage-image" id="<?php echo $captcha_img_id ?>" src="securimage_show.php?sid=<?php echo $sid_first ?>" />

		<!-- Audio -->
		<object
			class="securimage-play"
			classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
			codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
			width="19"
			height="19"
			id="SecurImage_as3">

			<param name="allowScriptAccess" value="sameDomain" />
			<param name="allowFullScreen" value="false" />
			<param name="movie" value="securimage_play.swf?audio=securimage_play.php&amp;bgColor1=#777&amp;bgColor2=#fff&amp;iconColor=#000&amp;roundedCorner=5" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />

			<embed
				src="securimage_play.swf?audio=securimage_play.php&amp;bgColor1=#777&amp;bgColor2=#fff&amp;iconColor=#000&amp;roundedCorner=5"
				quality="high"
				bgcolor="#ffffff"
				width="19"
				height="19"
				name="SecurImage_as3"
				allowScriptAccess="sameDomain"
				allowFullScreen="false"
				type="application/x-shockwave-flash"
				pluginspage="http://www.macromedia.com/go/getflashplayer" />

		</object>

		<br />

		<!-- Refresh button -->
		<a class="securimage-refresh" tabindex="-1" href="#" title="<?php echo LANG_REFRESH_IMAGE ?>" onclick="document.getElementById('<?php echo $captcha_img_id ?>').src='securimage_show.php?sid='+Math.random();return false">
			<img src="images/refresh.gif" alt="<?php echo LANG_REFRESH_IMAGE ?>" onclick="this.blur()" /></a>

		<br class="clear" />

		<!-- Input -->
		<label for="captcha-input">Code : </label><input type="text" name="<?php echo $captcha_input_name ?>" id="captcha-input" />

	</div>

	<div><br /><input type="submit" value="Submit Form" /></div>
</form>
<?php } ?>

</body>
</html>