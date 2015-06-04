<?php /*

-----------------------------------------
------ About the Text_Search Class ------
-----------------------------------------

This class provides a series of methods that allow you to parse through text.


-------------------------------
------ Methods Available ------
-------------------------------

Text_Search::pos($haystack, $needle.., [$needle..])			// Returns the integer position of the last needle
Text_Search::positionsOf($haystack, $needle);				// Returns every position of $needle in the $haystack
Text_Search::posLastTwo($haystack, $needle.., [$needle..])	// Returns the last two needle positions

Text_Search::between($haystack, $needle.., [$needle..])		// Returns the content between the last two needles, non-inclusive
Text_Search::through($haystack, $needle.., [$needle..])		// Returns the content between the last two needles, inclusive
Text_Search::before($haystack, $needle.., [$needle..])		// Returns the content before the last needle, non-inclusive
Text_Search::after($haystack, $needle.., [$needle..])		// Returns the content after the last needle, inclusive

*/

abstract class Text_Search {
	
	
/****** Find the last position in text ******/
	public static function pos
	(
		$text		// <str> The text to parse.
					// <args> <str> The position of the next needle to check for.
	)				// RETURNS <int> position of the last needle provided.
	
	// $pos = Text_Search::pos("This is a Top hat.", "a", "T");		// Returns position of the "T" in "Top Hat"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return false; }
			
			$position = $nextPosition;
		}
		
		if(count($args) <= 1) { return false; }
		
		return $position;
	}
	
	
/****** Find all positions of $needle in the $haystack ******/
	public static function positionsOf
	(
		$haystack	// <str> The text to parse.
	,	$needle		// <str> The positions to check for with the needle.
	)				// RETURNS <int:int> the positions (int) where the needle was found.
	
	// $pos = Text_Search::positionsOf("@joe and @bob, how are you?", "@");	// Returns "0" and "9"
	{
		// Prepare Values
		$positions = array();
		$curPos = -1;
		
		while(true)
		{
			$nextPos = strpos($haystack, $needle, $curPos + 1);
			
			if($nextPos > $curPos)
			{
				$curPos = $nextPos;
				
				array_push($positions, $curPos);
				
				continue;
			}
			
			break;
		}
		
		return $positions;
	}
	
	
/****** Retrieve the positions of the last two needles ******/
	public static function posLastTwo
	(
		$text		// <str> The text to parse.
	)				// RETURNS <int:int> the text between the last two needles.
	
	// list($pos1, $pos2) = Text_Search::posLastTwo("Such a hat!", "Such", "hat");	// Returns pos of "Such" and "hat"
	{
		// Prepare Values
		$args = func_get_args();
		$lastPosition = -1;
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return array(); }
			
			$lastPosition = $position;
			$position = $nextPosition;
		}
		
		if(count($args) <= 1) { return array(); }
		
		if($lastPosition == -1)
		{
			return array();
		}
		
		return array($lastPosition, $position);
	}
	
	
/****** Retrieve the string between the last two needles ******/
	public static function between
	(
		$text		// <str> The text to parse.
	)				// RETURNS <str> the text between the last two needles.
	
	// echo Text_Search::between("How are you doing today?", "are", "today");	// Returns " you doing "
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		$strlen = 0;
		
		// Loop through every needle until we've located the final set
		for( $i = 1; $i < $argsLength; $i++ )
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			// If we couldn't locate the next needle, stop processing
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, $position + $strlen, $nextPosition - $position - $strlen);
			}
			
			$strlen = strlen($args[$i]);
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Retrieve the string through the last two needles (inclusive) ******/
	public static function through
	(
		$text		// <str> The text to parse.
	)				// RETURNS <str> the text through the last two needles.
	
	// echo Text_Search::through("How are you doing today?", "are", "today");	// Returns "are you doing today"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		$strlen = 0;
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			$strlen = strlen($args[$i]);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, $position, $nextPosition - $position + $strlen);
			}
			
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Retrieve the string after the final needle ******/
	public static function after
	(
		$text		// <str> The text to parse.
	)				// RETURNS <str> the text after the last needle.
	
	// echo Text_Search::after("How are you doing today?", "are", "doing");	// Returns " today?"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, $nextPosition);
			}
			
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Retrieve the string after the final needle ******/
	public static function before
	(
		$text		// <str> The text to parse.
	)				// RETURNS <str> the text after the last needle.
	
	// echo Text_Search::after("How are you doing today?", "are", "doing");	// Returns " today?"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, 0, $nextPosition);
			}
			
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Extract text located between two strings ******/
	private static function getTextBetween
	(
		$text				// <str> The text to parse.
	,	$start				// <str> The string to start the search with.
	,	$end				// <str> The string to end the search with.
	,	$inclusive = false	// <bool> TRUE if the search is inclusive.
	)						// RETURNS <str> the text between the last two needles.
	
	// $betweenText = self::getTextBetween($text, $start, $end, $inclusive = true);
	{
		// Run the regex expression
		preg_match_all("/" . preg_quote($start) . "([\s\S]+)" . preg_quote($end) . "/", $str, $matches);
		
		// If we're running an INCLUSIVE check, return with the strings included
		if($inclusive) { return $matches[0][0]; }
		
		// Return the text BETWEEN the matched strings (non-inclusive)
		return $matches[1][0];
	}
	
}
