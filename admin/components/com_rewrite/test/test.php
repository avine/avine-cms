<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>URL rewriting test : result</title>

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

<h1>URL rewriting test : result</h1>



<?php if (preg_match('~'.preg_quote('/test.html').'$~', $_SERVER['SCRIPT_URL'])) { ?>

<h3>You are currently reading the file :</h3>
<p>/admin/components/com_rewrite/test/test<b>.php</b></p>

<h2 class="ok">URL rewriting engine works well on this server !</h2>

<?php } else { ?>

<p class="error">To run the test, go to : <a href="index.php">index.php</a></p>

<?php } ?>



</body>
</html>