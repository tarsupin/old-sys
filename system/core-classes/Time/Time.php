<?php /*

-----------------------------------
------ About the Time Class ------
-----------------------------------

This plugin provides you with tools to keep track of time and accurately sync between servers.

This plugin is relied upon by other critical plugins, such as the API plugin.


-------------------------------
------ Methods Available ------
-------------------------------

// Returns the current swatch time
$swatchTime = Time::swatch()

// Returns unique timestamp for the current time
$uniqueTime = Time::unique()

// TRUE if $match is within $range minutes of current unique()
$confirmTime = Time::unique($match, $range = 2)

*/

abstract class Time {
	
	
/****** Return the current cycle ******/
	public static function cycle
	(
		$type = "Ym"	// <str> The type of cycle (date format) such as: "Ym" for Year+Month
	,	$modifier = 0	// <int> The modifier, in seconds, to adjust the timestamp for.
	)					// RETURNS <int> The resulting cycle after modification
	
	// $cycle = Time::cycle([$type], [$modifier]);
	{
		return (int) date("Ym", time() + $modifier);
	}
	
	
/****** Return the current Swatch Time ******/
	public static function swatch (
	)			// RETURNS <int> the current swatch time.
	
	// $swatchTime = Time::swatch();
	{
		return (int) idate("B");
	}
	
	
/****** Return the current UniQUE Time ******/
	public static function unique
	(
		$match = -10	// <int> Set this to see if the unique time matches up.
	,	$range = 2		// <int> The range (in roughly minutes) to allow for a match to be considered valid.
	)					// RETURNS <mixed> the current swatch time.
	
	// $uniqueTime = Time::unique();			// Return a UniQUE timestamp
	// $uniqueTime = Time::unique($testTime);	// Check if $testTime is within range of the current UniQUE time
	{
		$stamp = (int) gmdate("zB");
		
		if($match != -10)
		{
			// Check if the timestamp is within a close range
			if(abs($match - $stamp) <= $range)
			{
				return true;
			}
			
			// On January 1st, check for the yearly switch values
			if($stamp <= $range)
			{
				if($match >= (364999 - $range))
				{
					return true;
				}
			}
			
			// None of the tests passed
			return false;
		}
		
		return $stamp;
	}
}
