<?php /*

---------------------------------------
------ About the Metadata Class ------
---------------------------------------

This plugin can inject text in the HTML header or footer, and is primarily used to add css files or scripts on necessary pages. Since some assets only need to be loaded on certain pages, this plugin allows them to be used only on the appropriate pages. This reduces the number of assets that need to be generated.

In addition to the META tags that it works with, it can also inject data into the footer, near or around the </body> ending tag (as long as the Metadata::footer() method is employed there). This is typically used to load non-blocking scripts at the end of the page, when async won't work within the available browser.

Another important element of the Metadata plugin is the self::$index and self::$follow values, which will allow you to individually enable or disable page indexing and following.


---------------------------------------
------ Using the Metadata Class ------
---------------------------------------

In order to use the Metadata plugin, you must make sure that your active theme uses the Metadata::header() and Metadata::footer() methods in their expected locations. The Metadata::header() method belongs inside of the <head> tag, and the Metadata::footer() method belongs at the end of the html script just before the </body> tag.

Then, on pages that you need to load a specific javascript library or css file, you can run the following line:

	Metadata::addHeader("<desired-tag>line to add to the META tag</desired-tag>");

You must run the line BEFORE the theme is loaded.


-------------------------------
------ Methods Available ------
-------------------------------

Metadata::addHeader("line to add in metaheader")
Metadata::addFooter("line to add in footer")

Metadata::openGraph($title, $image, $url, $desc, $type = "article")

Metadata::header()		// Adds the page's header metadata
Metadata::footer()		// Adds the page's footer metadata

*/

abstract class Metadata {
	
	
/****** Class Variables ******/
	public static $headerData = array();	// <int:str> contains the text to inject into the header.
	public static $footerData = array();	// <int:str> contains the text to inject into the footer.
	
	public static $follow = false;			// <bool> TRUE if you want search engines to follow this page.
	public static $index = false;			// <bool> TRUE if you want search engines to index this page.
	
	public static $jsData = array(			// <str:mixed> Special javascript data to save.
		"key"		=> ""
	);
	
	
/****** Load Metadata from the configurations ******/
	public static function load (
	)					// RETURNS <void>
	
	// Metadata::load();
	{
		$metaData = Config::get("Metadata");
		
		// Add Header Metadata
		if(isset($metaData['Header']) and is_array($metaData['Header']))
		{
			foreach($metaData['Header'] as $inc)
			{
				array_push(self::$headerData, $inc);
			}
		}
		
		// Add Footer Metadata
		if(isset($metaData['Footer']) and is_array($metaData['Footer']))
		{
			foreach($metaData['Footer'] as $inc)
			{
				array_push(self::$footerData, $inc);
			}
		}
	}
	
	
/****** Add Metadata Line to the Header ******/
	public static function addHeader
	(
		$metadata		// <str> The data line to add (e.g. '<link rel="stylesheet" href="sample.css">')
	)					// RETURNS <void>
	
	// Metadata::addHeader('<link rel="stylesheet" href="sample.css">');
	{
		array_push(self::$headerData, $metadata);
	}
	
	
/****** Add Metadata Line to the Footer ******/
	public static function addFooter
	(
		$metadata		// <str> The data line to add (e.g. "<script src="/somefile.js"></script>")
	)					// RETURNS <void>
	
	// Metadata::addFooter('<script src="/somefile.js"></script>');
	{
		array_push(self::$footerData, $metadata);
	}
	
	
/****** Add OpenGraph Settings ******/
	public static function openGraph
	(
		$title				// <str> The title to provide social engines. Up to 95 chars.
	,	$image				// <str> The URL to the image that will be displayed to the social engine.
	,	$url				// <str> The URL to link to.
	,	$desc = ""			// <str> The description to provide. Up to 297 characters.
	,	$type = "article"	// <str> The type of page to provide to social engines (article, blog, website, etc).
	)						// RETURNS <void>
	
	// Metadata::openGraph($title, $image, $url, $desc, $type = "article");
	{
		$tag = "";
		
		if($title)
		{
			$tag .= '
			<meta property="og:title" content="' . Sanitize::variable($title) . '" />';
		}
		
		if($image)
		{
			$tag .= '
			<meta property="og:image" content="' . urlencode($image) . '" />';
		}
		
		if($url)
		{
			$tag .= '
			<meta property="og:url" content="' . urlencode($url) . '" />';
		}
		
		if($desc)
		{
			$tag .= '
			<meta property="og:description" content="' . Sanitize::variable($desc) . '" />';
		}
		
		if($type)
		{
			$tag .= '
			<meta property="og:type" content="' . Sanitize::variable($type) . '" />';
		}
		
		// Add the tag to the header
		array_push(self::$headerData, $tag);
	}
	
	
/****** Output Meta-Header Metadata ******/
	public static function header (
	)				// RETURNS <str> the text to inject into the header.
	
	// Metadata::header()
	{
		$html = '
		<!-- Final Meta Data -->';
		
		foreach(self::$headerData as $line)
		{
			$html .= '
			' . $line;
		}
		
		// Prepare the robots tag
		if(!self::$index or !self::$follow)
		{
			$html .= '
			<meta name="robots" content="' . (self::$index == true ? "index" : "noindex") . ', ' . (self::$follow == true ? "follow" : "nofollow") . '">';
		}
		
		return $html;
	}
	
	
/****** Output Footer Metadata ******/
	public static function footer (
	)				// RETURNS <str> the text to inject into the footer.
	
	// Metadata::footer()
	{
		$html = "";
		
		foreach(self::$footerData as $line)
		{
			$html .= '
			' . $line;
		}
		
		return $html;
	}
	
	
/****** Handle special javascript chatting functionality ******/
	public static function JSChat (
	)				// RETURNS <str> the text to use for special javascript functionality.
	
	// Metadata::JSChat()
	{
		if(Me::$id)
		{
			// Prepare Values
			$jsEncrypt = Security_Hash::jsHash(Me::$vals['handle'], self::$jsData['key']);
			$jsUser = Me::$vals['handle'];
			$jsTime = microtime(true) - 90;
			
			return '<script>var JSUser = "' . $jsUser . '"; var JSEncrypt = "' . $jsEncrypt . '"; var JSChatTime = ' . $jsTime . '; var JSProfilePic = "' . ProfilePic::image(Me::$id, "small") . '";</script>';
		}
		
		return '';
	}
	
}
