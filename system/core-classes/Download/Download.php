<?php /*

---------------------------------------
------ About the Download Class ------
---------------------------------------

This plugin allows downloading files from other URLs.


-------------------------------
------ Methods Available ------
-------------------------------

// Download a remote file, and optionally save it locally.
Download::from($fileURL, [$savePath], [$baseDir]);

*/

abstract class Download {
	
	
/****** Download a file (and, optionally, save it locally) ******/
# Note: if you are saving a downloaded file locally, you MUST be very careful to interpret any user input carefully.
# Failure to properly sanitize user input from a downloaded value is incredibly insecure.
	public static function get
	(
		$downloadURL		// <str> The full url path of the file to download.
	,	$savePath = ""		// <str> The path to save the file to (saves within SITE_PATH).
	,	$baseDir = false	// <bool> TRUE if you want to use the base directory instead of SITE_PATH.
	)						// RETURNS <mixed> the file content, or FALSE on failure.
	
	// Download::get("http://site.com/somefile.txt");		// Returns the text contents 
	// Download::get("http://site.com/somefile.txt", "/path/to/save/somefile.txt");
	{
		$fileContents = file_get_contents($downloadURL);
		
		// Return false if the download was unsuccessful
		if($fileContents === false)
		{
			return false;
		}
		
		// If you are attempting to save the file locally, run the save function
		if($savePath != "")
		{
			$basePath = $baseDir == false ? SITE_PATH : dirname(SYS_PATH);
			$savePath = Sanitize::filepath($savePath);
			$savePath = ltrim($savePath, "/");
			
			File::write($basePath . '/' . $savePath, $fileContents);
		}
		
		// Return the downloaded content
		return $fileContents;
	}
}

