<?php


///////////////////////////
// Looking for RewriteBase

$rewrite_base = preg_replace('~'.preg_quote('/index.php', '~').'$~', '', $_SERVER['SCRIPT_FILENAME']);
$rewrite_base = preg_replace('~^'.preg_quote($_SERVER['DOCUMENT_ROOT'], '~').'~', '', $rewrite_base);

$rewrite_base .= '/';


/////////////////////////
// Create .htaccess file

$htaccess =
	"<IfModule mod_rewrite.c>\nOptions +FollowSymLinks\nOptions +Indexes\nRewriteEngine On\nRewriteBase $rewrite_base\n\n". # Enable rewrite engine
	"RewriteRule ^test.html$ test.php [L]\n</IfModule>\n\n"; # Add rule

file_put_contents('.htaccess', $htaccess) or die("Unable to create the .htaccess file.<br /><br />Sorry, can not run the test !");



//////////////////////////////////
// Starting rewrite-engine test !

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>URL rewriting test : config</title>

	<style type="text/css">
		* { margin: 0; padding: 0; }
		body { margin: 30px 15px; font: normal 16px/22px Arial; }
		a { color: blue; }
		.ok { color: green; }
		.error { color: red; }
		h1 { margin-bottom: 30px; }
		h3 { margin-bottom: 5px; clear: left; font-weight: normal; color: #999; }
		p { margin-bottom: 30px; }
		pre { margin-bottom: 30px; float: left; border: 1px solid #CCC; padding: 5px 10px; background-color: #FAFAFA; }
	</style>
</head>
<body>

<h1>URL rewriting test : config</h1>

<h3>The test will use the followed <b>.htaccess</b> content :</h3>
<?php echo '<pre>'.htmlentities($htaccess, ENT_COMPAT, 'UTF-8').'</pre>'; ?>

<h3>To run the test, open the following link :</h3>
<p><a href="<?php echo $rewrite_base."test.html"; ?>"><?php echo $rewrite_base."test.html"; ?></a></p>

</body>
</html>