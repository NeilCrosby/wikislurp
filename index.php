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
 * TODO: Normalise magicquotes.
 * TODO: Return proper error codes.
 *
 * @author Neil Crosby <neil@thetenwordreview.com>
 * @licence Creative Commons Attribution-Share Alike 3.0 Unported 
 *          http://creativecommons.org/licenses/by-sa/3.0/
 **/
 
require_once('config/config.php');
require_once('classes/MediaWiki.php');

// TODO normalise magicquotes


// secret must be set in the config

if ( 0 == strlen($WIKI_SLURP_CONFIG['SECRET']) ) {
    echo "die - secret not set in config";
    die;
}

// Check that required parameters are given

if ( !isset($_GET['secret']) || !$_GET['secret'] ) {
    echo "die - secret not given in request";
    die;
}

if ( !isset($_GET['query']) || !$_GET['query'] ) {
    echo "die - query not given in request";
    die;
}

// check that secret matches config secret

if ( $WIKI_SLURP_CONFIG['SECRET'] != $_GET['secret'] ) {
    echo "die - secrets didn't match";
    die;
}

$wiki = new MediaWiki($WIKI_SLURP_CONFIG);
$obj = $wiki->getArticle( $_GET );


//header("Content-type: text/text");
switch ( $_GET['output'] ) {
    case 'json':
        echo json_encode($obj);
        break;
    default:
        echo serialize($obj);
}
