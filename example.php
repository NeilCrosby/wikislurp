<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<?php

/*
 * To make this example work, you'll need to be running wikislurp on the same
 * server as you're running this example off, and have them both running out
 * of the root.
 */

function removeMagicQuotes (&$postArray, $trim = false) {
	if (!get_magic_quotes_gpc()) {
		return;
	}

	foreach ($postArray as $key => $val){
		if (is_array($val)) {
			removeMagicQuotes ($postArray[$key], $trim);
		} else {
			if ($trim == true) {
				$val = trim($val);
			}
			$postArray[$key] = stripslashes($val);
		}
	}   
}

removeMagicQuotes($_GET);

$secret  = isset($_GET['secret'])  ? $_GET['secret']  : '';
$query   = isset($_GET['query'])   ? $_GET['query']   : '';
$context = isset($_GET['context']) ? $_GET['context'] : '';
$xpath 	 = isset($_GET['xpath'])   ? $_GET['xpath']   : '';
$section = isset($_GET['section']) ? $_GET['section'] : '';

$cookedSecret  = htmlentities($secret);
$cookedQuery   = htmlentities($query);
$cookedContext = htmlentities($context);
$cookedXpath   = htmlentities($xpath);
$cookedSection = htmlentities($section);

$cookedUrlSecret  = urlencode($secret);
$cookedUrlQuery   = urlencode($query);
$cookedUrlContext = urlencode($context);
$cookedUrlXpath   = urlencode($xpath);
$cookedUrlSection = urlencode($section);

// /html/body/p[position()%3C=3]
$url = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/?secret=$cookedUrlSecret&query=$cookedUrlQuery&context=$cookedUrlContext&xpath=$cookedUrlXpath&section=$cookedUrlSection&output=php";

$s = curl_init();
curl_setopt($s,CURLOPT_URL, $url);
curl_setopt($s,CURLOPT_HEADER,false);
curl_setopt($s,
    CURLOPT_RETURNTRANSFER,1);
// wait 1 second, then abort
//curl_setopt($s,CURLOPT_TIMEOUT,1);
$result = curl_exec($s);
curl_close( $s );

$result = unserialize($result);
?>
<html lang="en">
	<head>
		<title>WikiSlurp Tester</title>
	</head>
	<body>
		<h1>WikiSlurp Tester</h1>
		<form method="get">
			<p>
				<label for="secret">Secret</label>
				<input type="text" name="secret" id="secret" value="<?php echo $cookedSecret; ?>">
			</p>
			<p>
				<label for="query">Query</label>
				<input type="text" name="query" id="query" value="<?php echo $cookedQuery; ?>">
			</p>
			<p>
				<label for="context">Context</label>
				<input type="text" name="context" id="context" value="<?php echo $cookedContext; ?>">
			</p>
			<p>
				<label for="xpath">xPath</label>
				<input type="text" name="xpath" id="xpath" value="<?php echo $cookedXpath; ?>">
			</p>
			<p>
				<label for="section">Section</label>
				<input type="text" name="section" id="section" value="<?php echo $cookedSection; ?>">
			</p>
			<p>
				<input type="submit" value="Submit">
			</p>
		</form>
		<p><?php echo $url; ?></p>
		<pre><?php echo htmlentities(print_r($result, true)); ?></pre>
	</body>
</html>
