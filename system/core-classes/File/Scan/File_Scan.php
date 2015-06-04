<?php /*

---------------------------------------
------ About the File_Scan Class ------
---------------------------------------

This class is used to scan through directories for files using wildcards and patterns.

For example:

	File_Scan::scan($directory, $pattern);		// Variables
	File_Scan::scan(__DIR__, "*.php");			// Example in practice


-------------------------------
------ Methods Available ------
-------------------------------

File_Scan::scan($directory, $pattern);
File_Scan::scanRecursive($directory, $pattern);

*/

abstract class File_Scan {
	
	
/****** Retrieve a list of files based on a search pattern ******/
	public static function scan
	(
		$directory				// <str> The parent directory to begin the search at.
	,	$pattern				// <str> The pattern to use for your search.
	,	$isRecursive = false	// <bool> TRUE if the search is recursive automatically.
	)							// RETURNS <int:str> A list of files.
	
	// $files = File_Scan::scan(__DIR__, "*.php");
	{
		// Prepend the directory to the pattern
		$pattern = $directory . '/' . $pattern;
		
		// Retrieve the files that this pattern matches
		$files = glob($pattern);
		
		// If we're running a recursive scan, loop through each directory found
		if($isRecursive)
		{
			foreach(glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
			{
				$files = array_merge($files, self::scan($dir, basename($pattern), true));
			}
		}
		
		return $files;
	}
	
	
/****** Retrieve a list of files based on a search pattern - also runs recursively ******/
	public static function scanRecursive
	(
		$directory				// <str> The parent directory to begin the search at.
	,	$pattern				// <str> The pattern to use for your search.
	)							// RETURNS <int:str> A list of files.
	
	// $files = File_Scan::scan(__DIR__, "*.php");
	{
		return self::scan($directory, $pattern, true);
	}
}

