<?php

class Snoopyplus extends Snoopy {

// requires the Snoopy class of course! (https://sourceforge.net/projects/snoopy/)

	var $snoopyplus_directory = "cache/";
	var $snoopyplus_cache = TRUE;

	public function is_stored($url) {
		if ($this->snoopyplus_cache) {
			$localfilename = $this->snoopyplus_directory . sha1($url) . "." . pathinfo(parse_url($url)['path'], PATHINFO_EXTENSION);
			if (file_exists($localfilename)) {
				return $localfilename;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	private function store($content,$url) {
		if (!$this->snoopyplus_cache) {
			return FALSE;
		}
		$localfilename = $this->snoopyplus_directory . sha1($url) . "." . pathinfo(parse_url($url)['path'], PATHINFO_EXTENSION);
		if (file_exists($localfilename)) {
			return TRUE;
		}
		$handle = fopen($localfilename,"w");
		if (fwrite($handle, $content)) {
			return TRUE;
		}
		return FALSE;
	}

    /*======================================================================*\
        Function:	wrapper for Snoopy fetch - gzip encoding capable
        Purpose:	fetch the HTML from a web page
        Input:		$URI	where you are fetching from
        Output:		$this->results	a string or an array of the HTML
    \*======================================================================*/

	function fetch2($URI) {
		$localfile = $this->is_stored($URI);
		if ($localfile!==FALSE) {
			$this->results = file_get_contents($localfile);
			return TRUE;
		}
		if($this->fetch($URI)){
			if (is_array($this->results)) { // an array if the $URI has frames
				foreach ($this->results as $key => $value) {
					$this->results[$key] = $this->gzipcheck($value);
				}
			} else {
				$this->results = $this->gzipcheck($this->results);
			}
			$this->store($this->results, $URI); // store in a cache
			return TRUE;
		} else {
			return FALSE;
		} 
	}

	function gzipcheck($string) {
			// detect GZIP encoding by its first two bytes
			if (bin2hex(substr($string, 0,2))==="1f8b") {
				$string = substr($string, 10);
				$string = gzinflate($string);
			}
			return $string;
	}

    /*======================================================================*\
        Function:	fetchimgs
        Purpose:	fetch the image urls from a web page
        Input:		$URI	where you are fetching from
        Output:		$this->results	an array of the URLs
    \*======================================================================*/

    public function fetchimgs($URI)
    {
        if ($this->fetch($URI) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_stripimgs($this->results[$x]);
            } else
                $this->results = $this->_stripimgs($this->results);

            if ($this->expandlinks)
                $this->results = $this->_expandimglinks($this->results, $URI);
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	_expandimglinks
        Purpose:	expand each link into a fully qualified URL
        Input:		$links			the links to qualify
                    $URI			the full URI to get the base from
        Output:		$expandedLinks	the expanded links
    \*======================================================================*/

     function _expandimglinks($links, $URI)
    {

	$expandedLinks=[];
	foreach($links as $link) {
		$expandedLinks[] = $this->url2absolute($URI,$link);
	}

        return $expandedLinks;
    }


    /*======================================================================*\
        Function:	_stripimgs
        Purpose:	strip the image urls from an html document
        Input:		$document	document to strip.
        Output:		$match		an array of the image urls
    \*======================================================================*/

     function _stripimgs($document)
    {
        preg_match_all("'<\s*img.*?\s.*?src\s*=\s*	# find <img ... src=
	([\"\'])?					# find single or double quote
	(?(1) (.*?)\\1 | ([^\s\>]+))			# if quote found, match up to next matching
							# quote, otherwise match up to next space
	'isx", $document, $links);


        // catenate the non-empty matches from the conditional subpattern

        while (list($key, $val) = each($links[2])) {
            if (!empty($val))
                $match[] = $val;
        }

        while (list($key, $val) = each($links[3])) {
            if (!empty($val))
                $match[] = $val;
        }

        // return the links
        return $match;
    }

    /*======================================================================*\
        Function:	url2absolute
        Purpose:	return the absolute URL for a given relative URL and base URL
        Input:		$baseurl, $relativeurl	URLs
        Output:		string		absolute URL or "ERROR"
    \*======================================================================*/
	// source: my answer on Stackoverflow - http://stackoverflow.com/questions/11215440/relative-base-url-to-absolute-url/32055473#32055473
	function url2absolute($baseurl, $relativeurl) {

	    // if the relative URL is scheme relative then treat it differently
	    if(substr($relativeurl, 0, 2) === "//") {
		if(parse_url($baseurl, PHP_URL_SCHEME) != null) {
		    return parse_url($baseurl, PHP_URL_SCHEME) . ":" . $relativeurl;
		} else { // assume HTTP
		    return "http:" . $relativeurl;
		}
	    }

	    // if the relative URL points to the root then treat it more simply
	    if(substr($relativeurl, 0, 1) === "/") {
		$parts = parse_url($baseurl);
		$return = $parts['scheme'] . ":";
		$return .= ($parts['scheme'] === "file") ? "///" : "//";
		// username:password@host:port ... could go here too!
		$return .= $parts['host'] . $relativeurl;
		return $return;
	    }

	    // If the relative URL is actually an absolute URL then just use that
	    if(parse_url($relativeurl, PHP_URL_SCHEME) !== null) {
		return $relativeurl;
	    }

	    $parts = parse_url($baseurl);

	    // Chop off the query string in a base URL if it is there
	    if(isset($parts['query'])) {
		$baseurl = strstr($baseurl,'?',true);
	    }

	    // The rest is adapted from Puggan Se

	    $return = ""; // string to return at the end
	    $minpartsinfinal = 3; // for everything except file:///
	    if($parts['scheme'] === "file") {
		$minpartsinfinal = 4;
	    }

	    // logic for username:password@host:port ... query string etc. could go here too ... somewhere?      

	    $basepath = explode('/', $baseurl); // will this handle correctly when query strings have '/'
	    $relpath = explode('/', $relativeurl);

	    array_pop($basepath);

	    $returnpath = array_merge($basepath, $relpath);
	    $returnpath = array_reverse($returnpath);

	    $parents = 0;
	    foreach($returnpath as $part_nr => $part_value) {
		/* if we find '..', remove this and the next element */
		if($part_value == '..') {
		    $parents++;
		    unset($returnpath[$part_nr]);
		} /* if we find '.' remove this element */
		else if($part_value == '.') {
		    unset($returnpath[$part_nr]);
		} /* if this is a normal element, and we have unhandled '..', then remove this */
		else if($parents > 0) {
		    unset($returnpath[$part_nr]);
		    $parents--;
		}
	    }
	    $returnpath = array_reverse($returnpath);
	    if(count($returnpath) < $minpartsinfinal) {
		return FALSE;
	    }
	    return implode('/', $returnpath);
	}

	//Tests
	//print url2absolute("file:///path/to/some/file.html","another_file.php?id=5") . "<br>"; // original example
	//print url2absolute("file:///path/to/some/file.html","../../../../../another_file.php?id=5") . "<br>"; // should be an error!
	//print url2absolute("http://path/to/some/file.html?source=this/one","another_file.php?id=5") . "<br>"; // with query string on base URL
	//print url2absolute("http://path/to/some/file.html","//other-path/another_file.php?id=5") . "<br>"; // scheme relative

}

?>
