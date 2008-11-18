<?php

/**
 * A self-contained client class designed to make accessing a WikiSlurp
 * server easier.  The client does no caching, it simply makes the request to
 * your (hopefully local) server.
 *
 * @author  Neil Crosby <neil@neilcrosby.com>
 * @license Creative Commons Attribution-Share Alike 3.0 Unported 
 *          http://creativecommons.org/licenses/by-sa/3.0/
 **/
class WikiSlurpClient {
	
	/**
	 * Creates a new instance of the WikiSlurpClient.  Nothing interesting
	 * happens here.
	 **/
	public function __construct() {
		
	}
	
	/**
	 * Makes a request to a WikiSlurp server.
	 * 
	 * @param url       The URL for the server to be accessed.
	 * @param secret    A key used to allow access to the WikiSlurp server.
	 * @param query     The base query to search MediaWiki for.
	 * @param options	Optional, array.  Extra information used by the
	 *					WikiSlurp server.  Any options documented in the 
	 * 					server documentation not mentioned above are able to
	 *                  be given here.  The keys of the array become the
	 *                  option names, the values are their values.
	 * 					Obvious, huh?
	 *					The exception to this is the addition of an extra
	 *					'timeout' option, which takes an integer.  If given
	 * 					the WikiSlurp Server request will timeout after that
	 *					number of seconds and an array containing error
	 *					information will be returned.
	 *
	 * @return An array containing the following keys: 'url', 'title', 'html'.
	 *		   If an error occurs then an array containing the key 'error'
	 *         will be returned.
	 **/
	public function getData($url=null, $secret=null, $query=null, $options=null) {
		if (!$url) {
			return array('error'=>'No URL given');
		}
		
		if (!$secret) {
			return array('error'=>'No secret given');
		}
		
		if (!$query) {
			return array('error'=>'No query given');
		}
		
		$options['secret'] = $secret;
		$options['query']  = $query;
		$options['output'] = 'php';
		
		$queryOptions = array();
		foreach ( $options as $key=>$value) {
			array_push($queryOptions, $key.'='.urlencode($value));
		}
		
		$queryString = implode('&', $queryOptions);
		
		$url .= '?' . $queryString;

		$s = curl_init();
		curl_setopt($s,CURLOPT_URL, $url);
		curl_setopt($s,CURLOPT_HEADER,false);
		curl_setopt($s,CURLOPT_RETURNTRANSFER,1);
		
		if ( isset($options['timeout']) && is_numeric($options['timeout']) && $options['timeout'] > 0 ) {
			curl_setopt($s,CURLOPT_TIMEOUT,intval($options['timeout']));
		}

		$result = curl_exec($s);
		curl_close( $s );
		
		if ( !$result ) {
			return array('error'=>'Query timed out');
		}

		$result = unserialize($result);
		return $result;
		
	}
	
}

?>