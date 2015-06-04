<?php /*

------------------------------------------
------ About the Security_Pad Class ------
------------------------------------------

This class provides allows you to pad a string with another string of the same base value.


-------------------------------
------ Methods Available ------
-------------------------------

// Pad a string and return the value
$paddedValue = Security_Pad::value($stringToPad, $paddingstring, [$base]);

*/

abstract class Security_Pad {
	
	
/****** Class Variables ******/
	public static $padChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+=_;:!-,.@*~|$%^&#()[]{}` ?'\"<>/\\"; // <str>
	
	public static $padArray = array("0" => 0, "1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5, "6" => 6, "7" => 7, "8" => 8, "9" => 9, "a" => 10, "b" => 11, "c" => 12, "d" => 13, "e" => 14, "f" => 15, "g" => 16, "h" => 17, "i" => 18, "j" => 19, "k" => 20, "l" => 21, "m" => 22, "n" => 23, "o" => 24, "p" => 25, "q" => 26, "r" => 27, "s" => 28, "t" => 29, "u" => 30, "v" => 31, "w" => 32, "x" => 33, "y" => 34, "z" => 35, "A" => 36, "B" => 37, "C" => 38, "D" => 39, "E" => 40, "F" => 41, "G" => 42, "H" => 43, "I" => 44, "J" => 45, "K" => 46, "L" => 47, "M" => 48, "N" => 49, "O" => 50, "P" => 51, "Q" => 52, "R" => 53, "S" => 54, "T" => 55, "U" => 56, "V" => 57, "W" => 58, "X" => 59, "Y" => 60, "Z" => 61, "+" => 62, "=" => 63, "_" => 64, ";" => 65, ":" => 66, "!" => 67, "-" => 68, "," => 69, "." => 70, "@" => 71, "*" => 72, "~" => 73, "|" => 74, "$" => 75, "%" => 76, "^" => 77, "&" => 78, "#" => 79, "(" => 80, ")" => 81, "[" => 82, "]" => 83, "{" => 84, "}" => 85, "`" => 86, " " => 87, "?" => 88, "'" => 89, "\"" => 90, "<" => 91, ">" => 92, "/" => 93, "\\" => 94);	// <str:int>
	
	
/****** Pad a String ******/
	public static function value
	(
		$valueToPad		// <str> The string to pad.
	,	$paddingStr		// <str> The string to pad with.
	,	$base = 64		// <int> The base value to pad with.
	)					// RETURNS <str> The resulting pad.
	
	// $padValue = Security_Pad::value("hello mister", "aaaaa cccccc"); // Returns "hello okuvgt"
	{
		// Prepare Values
		$chars_allowed = self::$padChars;
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
			// Pad the next character
			$padValue = self::$padArray[$valueToPad[$i]] + self::$padArray[$paddingStr[$i]];
			
			// Add the padded character to the new string
			$newString .= $chars_allowed[$padValue % $allowLen];
		}
		
		return $newString;
	}
	
}
