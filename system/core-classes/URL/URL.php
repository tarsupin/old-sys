<?php /*

----------------------------------
------ About the URL Class ------
----------------------------------

This plugin provides simple functionality for URLs, which is also essential for loading the URL segments into $url during system configuration.


-----------------------------------------------
------ Calling Environment-Specific URLs ------
-----------------------------------------------

Developers may use multiple environments where URLs point differently. To solve this problem, you can use this plugin to call a single URL that will adapt itself to the specific environment that you are using.

For example, the following behavior may occur when loading URL::example_com()

	Local Environment:			"http://example.com.local"
	Staging Environment:		"http://example.mydevserver.com"
	Production Environment:		"http://example.com"

In other words, when you're on your local server, URL::example_com() will point to "http://example.com.local" for your own testing purposes, but it will point to "http://example.com" on the live server. This allows you to run multiple sites without having to change hard-coded URLs between different environments.


-------------------------------
------ Methods Available ------
-------------------------------

$urlSegments = URL::getSegments([$url])		// Returns array of URL segments (e.g. domain.com/{segment1}/{segment2})

URL::toSlug($title);						// Change a value to a URL slug (e.g. "My Cool Post" to "my-cool-post")

$parsedURL = URL::parse($url)				// Parses a URL and returns an array of all its pieces

*/

abstract class URL {
	
	
/****** Returns an environment-specific domain ******/
	public static function __callStatic
	(
		$name		// <str> The name of the function being called.
	,	$args		// <array> Additional arguments being passed.
	)				// RETURNS <str> The URL of the chosen site.
	
	// $url = URL::unifaction_com();
	{
		return "http://" . str_replace("_", ".", $name) . URL_SUFFIX;
	}
	
	
/****** Change a value or title to a url slug ******/
	public static function toSlug
	(
		$value		// <str> The value you'd like to change.
	)				// RETURNS <str> a valid url slug.
	
	// echo URL::toSlug("The Best Blog Post");		// returns "the-best-blog-post"
	{
		// Transform the text into a friendly url
		$value = str_replace(" & ", " and ", trim($value));
		$value = str_replace(" ", "-", strtolower(Sanitize::variable($value, "- ")));
		
		// Cut the size of the url
		if(strlen($value) > 72)
		{
			while(strrpos($value, '-') > 62)
			{
				$value = substr($value, 0, strrpos($value, "-"));
			}
			
			// Final cut, in case the above didn't work
			if(strlen($value) > 72)
			{
				$value = substr($value, 0, 72);
			}
		}
		
		return $value;
	}
	
	
/****** Parse a URL ******/
	public static function parse
	(
		$url = ""			// <str> A URL to parse (default is current page)
	,	$simple = false		// <bool> TRUE if you only want to return simple URL data.
	,	$sanitize = false	// <bool> TRUE if you want to sanitize the paths values.
	)						// RETURNS <str:str> the data for the parsed URL, array() if misformed.
	
	// $parsedURL = URL::parse($url, [$simple]);
	{
		// Set the default URL to the current page if one hasn't been designated
		if(!$url)
		{
			$url = ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}
		
		$parseURL = parse_url($url);
		
		// Fix malformed hosts
		if(!isset($parseURL['host']) and isset($parseURL['path']))
		{
			$val = explode("/", $parseURL['path']);
			
			if($val[0] != "")
			{
				$parseURL['host'] = $val[0];
			}
			else
			{
				$parseURL['host'] = $_SERVER['SERVER_NAME'];
			}
			
			array_shift($val);
			
			// Sanitize the path values, if requested
			if($sanitize)
			{
				foreach($val as $key => $v)
				{
					$val[$key] = Sanitize::variable($v, " ./-+");
				}
			}
			
			// Prepare the Values
			$parseURL['urlSegments'] = $val;
			$parseURL['path'] = trim(implode("/", $val), "/");
		}
		else if(isset($parseURL['host']) and isset($parseURL['path']))
		{
			$val = explode("/", $parseURL['path']);
			
			array_shift($val);
			
			// Sanitize the path values, if requested
			if($sanitize)
			{
				foreach($val as $key => $v)
				{
					$val[$key] = Sanitize::variable($v, " ./-+");
				}
			}
			
			// Prepare the Values
			$parseURL['urlSegments'] = $val;
			$parseURL['path'] = trim(implode("/", $val), "/");
		}
		else if(isset($parseURL['host']))
		{
			// Do nothing
		}
		else
		{
			return array();
		}
		
		// Fix empty paths
		if(isset($parseURL['path']))
		{
			if($parseURL['path'] == "/" or $parseURL['path'] == "")
			{
				unset($parseURL['path']);
			}
		}
		
		// We can end here if we only need simple data
		if($simple) { return $parseURL; }
		
		// Get the Query Values
		if(isset($parseURL['query']))
		{
			parse_str($parseURL['query'], $parseURL['queryValues']);
		}
		
		// Get the Base Domain
		$hostSegments = explode(".", $parseURL['host']);
		$len = count($hostSegments);
		
		$parseURL["baseDomain"] = ($len > 2 ? $hostSegments[$len - 2] . '.' . $hostSegments[$len - 1] : $parseURL['host']);
		
		// Prepare Scheme
		$parseURL['scheme'] = isset($parseURL['scheme']) ? $parseURL['scheme'] : "http";
		
		// Prepare Full URL
		$parseURL['full'] = $parseURL['scheme'] . "://" . $parseURL['host'] . (isset($parseURL['path']) ? "/" . $parseURL['path'] : "") . (isset($parseURL['query']) ? "?" . $parseURL['query'] : "") . (isset($parseURL['fragment']) ? "#" . $parseURL['fragment'] : "");
		
		return $parseURL;
	}
}
