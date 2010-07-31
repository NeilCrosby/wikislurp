<?php

/**
 * Controller page for the WikiSlurp application.
 *
 * @param secret    A key used to allow access to the WikiSlurper.
 * @param query     The base query to search MediaWiki for.
 * @param context   Optional. Extra context to use to clarify the query.
 * @param section   Optional. The section number you would like data for. If
 *                  not given, the entire article is returned.
 * @param xpath     Optional. XPath describing the data to be returned. If
 *                  Not given, all data will be returned.
 * @param output    Optional.  The output type for this page. Options are
 *                  php, json.  Default is php.
 * @param keepjunk  Optional.  If any value is given then footnote, needs
 *                  citation etc links will be kept if they exist. If no
 *                  value is given then they will be stripped.
 *
 * @return An array containing the following keys: 'url', 'title', 'html'.
 *                  
 * @author  Neil Crosby <neil@neilcrosby.com>
 * @license Creative Commons Attribution-Share Alike 3.0 Unported 
 *          http://creativecommons.org/licenses/by-sa/3.0/
 **/
 
require_once('config/config.php');
require_once('classes/MediaWiki.php');

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

$error = false;

// secret must be set in the config
if ( 0 == strlen($WIKI_SLURP_CONFIG['SECRET']) ) {
    $error = array(
        "error"  => "Forbidden - Secret not set in config",
        "status" => 403,
    );
}

// Check that required parameters are given

if (!$error && ( !isset($_GET['secret']) || !$_GET['secret'] )) {
    $error = array(
        "error" => "Bad request - Secret not given",
        "status" => 400,
    );
}

if (!$error && ( !isset($_GET['query']) || !$_GET['query'] )) {
    $error = array(
        "error" => "Bad request - Query not given",
        "status" => 400,
    );
}

// check that secret matches config secret

if (!$error && ( $WIKI_SLURP_CONFIG['SECRET'] != $_GET['secret'] )) {
    $error = array(
        "error" => "Unauthorised - Secret is not valid",
        "status" => 401,
    );
}

if ( $error ) {
    $obj = $error;
} else {
    $wiki = new MediaWiki($WIKI_SLURP_CONFIG);
    $obj = $wiki->getArticle( $_GET );
}

if ( isset($obj['status']) && 200 != $obj['status'] ) {
    $error = $obj['error'];
    if (is_array($error)) {
        $error = implode($error, ', ');
    }
    $error = str_replace("\n", '', $error);
    
    header("HTTP/1.0 {$obj['status']} {$error}");
}

$output = isset($_GET['output']) ? $_GET['output'] : '';
switch ( $output ) {
    case 'json':
        header("Content-type: application/json");
        echo json_encode($obj);
        break;
    default:
        // is this a suitable mimetype?
        //header("Content-type: text/x-php");
        echo serialize($obj);
}
