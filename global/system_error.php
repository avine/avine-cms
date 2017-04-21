<?php

isset($_GET['alias']) or die('Restricted access');

// Is the installation file still available ?
is_file('../installation/installation.php') ? $install = true : $install = false;

$message = '';

switch($_GET['alias'])
{
	case 'config':
		$message .= '<p>Required script is missing :<br /><i>config.php</i></p>';
		$install ? $message .= '<p>You should go to the <a href="../installation/installation.php">system installation</a>.</p>' : '';
		break;

	case 'include':
		$message .= '<p>Required script can not be found :<br /><i>/global/include.php</i></p><p>It might be a wrong configuration of the WEBSITE_PATH constant.</p>';
		$install ? $message .= '<p>You should go to the <a href="../installation/installation.php">system installation</a>.</p>' : '';
		break;

	case 'database':
		$message .= '<p>The database tables are missing.</p>';
		$install ? $message .= '<p>You should go to the <a href="../installation/installation.php">system installation</a>.</p>' : '';
		break;

	default :
		die('Restricted access');
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>System error</title>

	<meta name="robots" content="noindex,nofollow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<style type="text/css">
		body { margin: 0; padding: 100px; background-color: #F3F3F3; font: normal 14px/20px Monospace; text-align: center; color: #666; text-shadow: 2px 2px 0 #FFF; }
		h1,p { margin: 0 0 20px 0; }
		a { color: #222; text-decoration: underline; }
		#warning { margin: 0 auto 20px auto; width: 128px; height: 128px; }
	</style>
</head>
<body>

<h1>System error</h1>

<div id="warning"><img src="<?php echo preg_replace('~php$~', 'gif', $_SERVER['PHP_SELF']); ?>" alt="" /></div>

<?php echo $message; ?>

<p>&copy; avine.fr</p>

</body>
</html>