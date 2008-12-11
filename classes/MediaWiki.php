<?php

require_once('PhpCache.php');
require_once('CurlCall.php');

/**
 * Queries Wikipedia to retrieve html data from the best article page
 * related to the query and context given.
 *
 * TODO: Solve "The Cuckoo Problem".
 *
 * @author  Neil Crosby <neil@neilcrosby.com>
 * @license Creative Commons Attribution-Share Alike 3.0 Unported 
 *          http://creativecommons.org/licenses/by-sa/3.0/
 **/
class MediaWiki {
    
    private $lastErrors = null;
    
    public function __construct( $conf = null ) {
		$this->conf = array();
		if ( $conf ) {
			$this->conf = array_merge($this->conf, $conf);
		}
    }
    
    /**
     * Gets an article, or part thereof, from wikipedia.
     *
     * @param query     The base query to search MediaWiki for.
     * @param context   Optional. Extra context to use to clarify the query.
     * @param section   Optional. The section number you would like data for. If
     *                  not given, the entire article is returned.
     * @param xpath     Optional. XPath describing the data to be returned. If
     *                  Not given, all data will be returned.
     *                  e.g. /html/body/p[position()<=3] would return the
     *                  first three <p> elements that are direct children of
     *                  the article's body.
     * @param keepjunk  Optional.  If any value is given then footnote, needs
     *                  citation etc links will be kept if they exist. If no
     *                  value is given then they will be stripped.
     *
     * @return An array containing the following keys: 'url', 'title', 'html'.
     **/
    public function getArticle( $options = array() ) {
        
        $this->lastErrors = array();
        
        $title = $this->getArticleTitle( $options );
        $url   = $this->getArticleUrl( $options );
        $html  = $this->getArticleAsHtml( $options );
        $html  = $this->reduceHtml($html, $options);
        
        if ( !$title || !$url || !$html ) {
            $errors = (isset($this->lastErrors)) ? $this->lastErrors : "Something went wrong slurping the data.  I bet you want to know why, don't you? For now, soz, but that's all the info you're getting.";

            return array(
                'error'=>$errors,
                'status'=>400, // A generic 400 for now since we're not entirely sure what went wrong
            );
        }
        
        return array(
            'title' => $title,
            'url'   => $url,
            'html'  => $html,
        );
    }
    
	public function getArticleTitle( $options = array() ) {
        if ( !isset($options['query']) || !$options['query'] ) {
            array_push($this->lastErrors, "No query was given");
            return;
        }
        $context=(isset($options['context'])) ? ' '.$options['context'] : '';
        
        $searchText = $options['query'].$context;
        $section = (isset($options['section'])) ? $options['section'] : null;

        $wikiUrlTitle = urlencode($searchText);

        $wikiText = $this->getWikiText($wikiUrlTitle, $section);

        // first, if wikipedia gives us nothing then search using Yahoo! BOSS for a wiki page
        if ( !$wikiText) {
            $searchTextNoUnderscore = preg_replace( '/_/', ' ', $searchText );

            if ( $wikiUrlTitle = $this->getWikiTitleFromBoss($searchTextNoUnderscore) ) {
                $wikiText = $this->getWikiText($wikiUrlTitle, $section);
            }
        }

        if ( $wikiText ) {
            $page = $this->getWikiPageInfo($wikiUrlTitle, $section);
            return $page['title'];
        }

        array_push($this->lastErrors, "We couldn't find an article title");
        return null;
    }

	public function getArticleUrl( $options = array() ) {
        if ( !isset($options['query']) || !$options['query'] ) {
            array_push($this->lastErrors, "No query was given");
            return;
        }
        $context=(isset($options['context'])) ? ' '.$options['context'] : '';
        
        $searchText = $options['query'].$context;
        $section = (isset($options['section'])) ? $options['section'] : null;

        $wikiUrlTitle = urlencode($searchText);

        $wikiText = $this->getWikiText($wikiUrlTitle, $section);

        // first, if wikipedia gives us nothing then search using Yahoo! BOSS for a wiki page
        if ( !$wikiText) {
            $searchTextNoUnderscore = preg_replace( '/_/', ' ', $searchText );

            if ( $wikiUrlTitle = $this->getWikiTitleFromBoss($searchTextNoUnderscore) ) {
                $wikiText = $this->getWikiText($wikiUrlTitle, $section);
            }
        }

        if ( $wikiText ) {
            $page = $this->getWikiPageInfo($wikiUrlTitle, $section);
            return 'http://'.$this->conf['WIKI_DOMAIN'].$this->conf['WIKI_BASE_DIR'].$page['title'];
        }

        array_push($this->lastErrors, "We couldn't find an article URL");
        return null;
    }

	public function getArticleAsHtml( $options = array() ) {
        if ( !isset($options['query']) || !$options['query'] ) {
            array_push($this->lastErrors, "No query was given");
            return;
        }
        $context=(isset($options['context'])) ? ' '.$options['context'] : '';
        
        $searchText = $options['query'].$context;
        $section = (isset($options['section'])) ? $options['section'] : null;
        
        $wikiUrlTitle = urlencode($searchText);
        $wikiText = null;

        $wikiText = $this->getWikiText($wikiUrlTitle);

        // first, if wikipedia gives us nothing then search using Yahoo! BOSS for a wiki page
        if ( !$wikiText) {
            $searchTextNoUnderscore = preg_replace( '/_/', ' ', $searchText );
            
            if ( $wikiUrlTitle = $this->getWikiTitleFromBoss($searchTextNoUnderscore) ) {
                $wikiText = $this->getWikiText($wikiUrlTitle, $section);
            }
        }

        if ( $wikiText ) {
            $page = $this->getWikiPageInfo($wikiUrlTitle, $section);
            $wikiText = $this->getWikiHtml($wikiText);
        }

        return $wikiText;
    }
    
    /**
     * @param a search term as entered by "the user".
     **/
    private function getWikiTitleFromBoss($searchTerm) {
        if ( !$this->conf['SEARCH_API_KEY'] ) {
            array_push($this->lastErrors, "No SEARCH_API_KEY was set");
            return false;
        }
        
        $curl = new CurlCall();

        $method = "yahoo.boss";
        $url = 'http://'
             . $this->conf['SEARCH_DOMAIN']
             . $this->conf['SEARCH_API']
             . urlencode($searchTerm)
             . '?appid=' . $this->conf['SEARCH_API_KEY']
			 . '&sites='.urlencode($this->conf['WIKI_DOMAIN']);
        $result = $curl->getFromJsonSource($url, array('cache-ident'=>$method, 'cache-time'=>$this->conf['SEARCH_CACHE_TIME']));

        if ( $aData = $this->getDataFromArray($result, array('ysearchresponse', 'resultset_web')) ) {
			$bestDataPoint = 0;
			$bestCount = 0;
			for ( $i=0; $i < sizeof($aData); $i++ ) {
				$nTitle    = preg_match_all('/<b>/', $aData[$i]['title'], $matches);
				$nAbstract = preg_match_all('/<b>/', $aData[$i]['abstract'], $matches);
				$count = 10 * $nTitle + $nAbstract;
				
				if ( $count > $bestCount ) {
					$bestDataPoint = $i;
					$bestCount = $count;
				}
			}
			
			$data = $aData[$bestDataPoint];
			
            $url = $data['url'];
            $lastSlashPos = strrpos($url, $this->conf['WIKI_BASE_DIR']);
            $wikiQuery = substr($url, $lastSlashPos + strlen($this->conf['WIKI_BASE_DIR']));

            $wikiUrlTitle = (strrchr( $wikiQuery, '_' )) ? urlencode($wikiQuery) : urlencode(ucwords($wikiQuery));
            return $wikiUrlTitle;
        }
        
        return false;
    }
    
    private function getWikiPageInfo($wikiUrlTitle, $section=null) {
        $curl = new CurlCall();

        $urlSection = (null == $section) ? '' : '&rvsection='.$section;

        $method = "wiki.query.title";
        $url = 'http://'.$this->conf['WIKI_DOMAIN'].$this->conf['WIKI_API']
             . '?format=php&action=query&rvprop=content&prop=revisions&redirects=1'
             . $urlSection
             . '&titles='.$wikiUrlTitle;
        $result = $curl->getFromPhpSource($url, array('cache-ident'=>$method, 'cache-time'=>$this->conf['WIKI_CACHE_TIME']));

        $page = false;

        if ( $this->getDataFromArray($result, array('query','pages')) && !$this->getDataFromArray($result, array('query','pages',-1)) ) {
            $page = array_shift($result['query']['pages']);
        }
        
        return $page;
    }
    
    private function getWikiText($wikiUrlTitle, $section=null) {
        $curl = new CurlCall();
        
        $urlSection = (null == $section) ? '' : '&rvsection='.$section;

        $method = "wiki.query.title";
        $url = 'http://'.$this->conf['WIKI_DOMAIN'].$this->conf['WIKI_API']
             . '?format=php&action=query&rvprop=content&prop=revisions&redirects=1'
             . $urlSection
             . '&titles='.$wikiUrlTitle;
        $result = $curl->getFromPhpSource($url, array('cache-ident'=>$method, 'cache-time'=>$this->conf['WIKI_CACHE_TIME']));

        $wikiText = false;

        if ( $this->getDataFromArray($result, array('query','pages')) && !$this->getDataFromArray($result, array('query','pages',-1)) ) {
            $page = array_shift($result['query']['pages']);
            $wikiText = array_shift($page['revisions'][0]);
        }
        
        return $wikiText;
    }
    
    private function getWikiHtml($wikiText) {
        $curl = new CurlCall();

        $method = "wiki.parse";
        $url = 'http://'.$this->conf['WIKI_DOMAIN'].$this->conf['WIKI_API'];
        $result = $curl->getFromPhpSourceAsPost(
            $url, 
            array(
                'post-fields'=>"format=php&action=parse&text=".urlencode($wikiText),
                'cache-ident'=>$method,
                'cache-time'=>$this->conf['WIKI_HTML_CACHE_TIME']
            )
        );

        if ( $wikiText = $this->getDataFromArray($result, array('parse','text','*')) ) {
            return $wikiText;
        }
        
        return false;
    }
    
    private function reduceHtml($html, $options) {
        $xPathQuery = (isset($options['xpath']) && $options['xpath']) ? $options['xpath']: '/html/body/*';
        
        $doc = new DOMDocument();
        // have to give charset otherwise loadHTML gets confused
        $doc->loadHTML(
            '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>'.
            $html.
            '</body></html>'
        );
        
        $xpath = new DOMXPath($doc);
        
        // remove "crap" if we need to
        if ( !isset($options['retaincrap']) ) {
            // need to review what needs to go into here
            $queries = array(
                '//*[contains(concat(" ",@class," "), " thumb ")]',
                '//*[contains(concat(" ",@class," "), " metadata ")]',
                '//*[contains(concat(" ",@class," "), " dablink ")]',
                '//*[contains(concat(" ",@class," "), " infobox ")]',
                '//sup[contains(concat(" ",@class," "), " reference ")]',
                '//sup[contains(concat(" ",@class," "), " Template-Fact ")]',
                '//table',
                '//dl',
                '//span[@id="coordinates"]',
            );
            
            foreach ($queries as $query) {
                $entries = $xpath->query($query);

                foreach ($entries as $entry) {
                    $entry->parentNode->removeChild($entry);
                }
            }
        }

        // got to make a temporary body before we remove the current one
        $tempBody = $doc->createElement('body');
        $tempChildren = $xpath->query($xPathQuery);
        foreach ( $tempChildren as $child ) {
            $tempBody->appendChild($child);
        }

        $bodies = $xpath->query('/html/body');
        foreach ( $bodies as $body ) {
            $body->parentNode->removeChild($body);
        }
        
        $htmls = $xpath->query('/html');
        foreach ( $htmls as $html ) {
            $html->appendChild($tempBody);
        }
        
        $html = $doc->saveHTML();
        $html = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $html);
        $html = str_replace('<html>', '', $html);
        $html = str_replace('<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>', '', $html);
        $html = str_replace('<body>', '', $html);
        $html = str_replace('</body>', '', $html);
        $html = str_replace('</html>', '', $html);
        
        $html = trim($html);

        return $html;
    }
    
    /**
     * Utility method used to parse down through an array and grab the value
     * at a certain depth.
     *
     * @param $aInput   The array to parse through.
     * @param $aKeys    An array of keys to parse with.
     *
     * @return the value at the requested depth, or false.
     **/
    private function getDataFromArray( $aInput, $aKeys=array() ) {
        $current = $aInput;
        foreach ( $aKeys as $key ) {
            if ( !isset($current[$key]) ) {
                return false;
            }
            $current = $current[$key];
        }
        return $current;
    }
    
}