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
	 *
	 * @return An array containing the following keys: 'url', 'title', 'html'.
	 **/
	public function getData() {
		
	}
	
}

?>