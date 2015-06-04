<?php /*

--------------------------------------------
------ About the Security_Unpad Class ------
--------------------------------------------

This class provides methods to return a padded string back to its original value.


-------------------------------
------ Methods Available ------
-------------------------------

// Returns a padded string to its original value
$originalString = Security_Unpad::value($stringToSecurity_Unpad, $paddingString, [$base]]);

*/

abstract class Security_Unpad {
	
	
/****** Security_Unpad a String ******/
	public static function value
	(
		$valueToPad 	// <str> The string to unpad.
	,	$paddingStr		// <str> The string to unpad width.
	,	$base = 64		// <int> The base value to pad with.
	)					// RETURNS <str> The resulting pad.
	
	// $originalString = Security_Unpad::value($valueToPad, $paddingStr, [$base]);
	// $originalString = Security_Unpad::value("hello okuvgt", "aaaaa cccccc"); // Returns "hello mister"
	{
		// Get Defaults
		$chars_allowed = Security_Pad::$padChars;
		$length = strlen($valueToPad);
		$newString = "";
		
		if($base > 0)
		{
			$chars_allowed = substr($chars_allowed, 0, $base);
		}
		
		$allowLen = strlen($chars_allowed);
		
		// Increase Length of Padding String if Too Short
		$count = 1;
		$orig = $paddingStr;
		
		while(strlen($paddingStr) < $length)
		{
			$paddingStr .= self::hash($orig . $count++);
		}
		
		// Cycle through each character in the string to pad, and then pad it
		for($i = 0;$i < $length;$i++)
		{
			// Get Padding Value
			$padValue = Security_Pad::$padArray[$valueToPad[$i]] - Security_Pad::$padArray[$paddingStr[$i]];
			
			// Pad Character
			while($padValue < 0) { $padValue += $allowLen; }
			
			// Add the padded character to the new string
			$newString .= $chars_allowed[$padValue];
		}
		
		return $newString;
	}
	
}
