<?php /*

----------------------------------------
------ About the Time_Fuzzy Class ------
----------------------------------------

This class allows the user to read times in very easy forms, such as "in one hour" or "five minutes ago."

The Time_Fuzzy::run() method is very useful for providing simple, human-readable strings from timestamp conversions. Most people won't know that the timestamp "1400376805" refers to early morning on May 18th in UTC, despite how useful the value is while programming. This is why converting timestamps to fuzzy time can be so helpful.

When you convert a timestamp to "fuzzy time" a string is returned that describes how soon (or how long ago) the timestamp was, such as "in two minutes" or "yesterday".

Using fuzzy time is very easy. You just run the unix timestamp through the Time_Fuzzy::run($timestamp) method.

	// Returns "in one hour"
	echo Time_Fuzzy::run(time() + 3600);
	
	// Returns "in ten minutes"
	echo Time_Fuzzy::run(time() + 600);
	
	// Returns "five minutes ago"
	echo Time_Fuzzy::run(time() - 300);


------------------------------
------ Methods Available ------
------------------------------

// Returns fuzzy time (e.g. "four hours ago")
$fuzzyTime = Time_Fuzzy::run($timestamp)

*/

abstract class Time_Fuzzy {
	
	
/****** Fuzzy Time - Change a Timestamp to Simple Time Reference (i.e. "last week") ******/
	public static function run
	(
		$timestamp			// <int> The timestamp of the date to transfer to human-readable time.
	)						// RETURNS <str> a simple, fuzzy time reference.
	
	// echo Time_Fuzzy::run(time() - 3600);	 // Outputs "one hour ago"
	{
		// Determine Fuzzy Time
		$timeDiff = $timestamp - time();
		
		// If the time difference is in the past, run the past() function
		if($timeDiff <= 0)
		{
			return self::past(abs($timeDiff), $timestamp);
		}
		
		return self::future($timeDiff, $timestamp);
	}
	
	
/****** Fuzzy Time (Future) - Private Helper ******/
	private static function future
	(
		$secondsUntil		// <int> The duration (in seconds) until the due date.
	,	$timestamp			// <int> The time that the event is occuring.
	)						// RETURNS <str> the fuzzy time (or a standard, formatted time).
	
	{
		// If the timestamp is within the next hour
		if($secondsUntil < 3600)
		{
			// If the timestamp was within the last half hour
			if($secondsUntil < 1200)
			{
				if($secondsUntil < 45)
				{
					if($secondsUntil < 10)
					{
						return "in a few seconds";
					}
					
					return "in less than a minute";
				}
				
				return "in " . Data_Number::convert(ceil($secondsUntil / 60)) . " minutes";
			}
			
			return "in " . Data_Number::convert(round($secondsUntil / 60, -1)) . " minutes";
		}
		
		// If the timestamp is within the next month
		else if($secondsUntil < 86400 * 30)
		{
			// If the timestamp is within the day
			if($secondsUntil < 72000)
			{
				$hoursUntil = round($secondsUntil / 3600);
				
				if($hoursUntil == 1)
				{
					return "in an hour";
				}
				else if($hoursUntil > 12 && date('d', time()) != date('d', $timestamp))
				{
					return "tomorrow";
				}
				
				return "in " . Data_Number::convert($hoursUntil) . " hours";
			}
			
			// If the timestamp is within a week
			else if($secondsUntil < 86400 * 7)
			{
				$daysUntil = round($secondsUntil / 86400);
				
				if($daysUntil == 1)
				{
					return "in a day";
				}
				
				return "in " . Data_Number::convert($daysUntil) . " days";
			}
			
			// If the time is listed sometime next week
			if(date('W', $timestamp) - date('W', time()) == 1)
			{
				return "next week";
			}
			
			$weeksUntil = round($secondsUntil / (86400 * 7));
			
			if($weeksUntil == 1)
			{
				return "in a week";
			}
			
			return "in " . Data_Number::convert($weeksUntil) . " weeks";
		}
		
		// If the timestamp was listed in the next year
		else if($secondsUntil < 86400 * 365)
		{
			$monthsUntil = round($secondsUntil / (86400 * 30));
			
			if($monthsUntil == 1)
			{
				if(date('m', $timestamp) - date('m', time()) == 1)
				{
					return "next month";
				}
				
				return "in a month";
			}
			
			return "in " . Data_Number::convert($monthsUntil) . " months";
		}
		
		// Return the timestamp as a "Month Year" style
		return date("F Y", $timestamp);
	}
	
	
/****** Fuzzy Time (Past) - Private Helper ******/
	private static function past
	(
		$secondsAgo			// <int> The duration (in seconds) after the due date.
	,	$timestamp			// <int> The time that the event occurred.
	)						// RETURNS <str> the fuzzy time (or a standard, formatted time).
	
	{
		// If the timestamp was within the last hour
		if($secondsAgo < 3600)
		{
			// If the timestamp was within a minute or so
			if($secondsAgo <= 90)
			{
				if($secondsAgo < 50)
				{
					if($secondsAgo < 15)
					{
						return "just now";
					}
					
					return "seconds ago";
				}
				
				return "a minute ago";
			}
			else if($secondsAgo < 1200)
			{
				return Data_Number::convert(round($secondsAgo / 60)) . " minutes ago";
			}
			
			return floor($secondsAgo / 60) . " minutes ago";
		}
		
		// If the timestamp was within the last week
		elseif($secondsAgo < 86400 * 7)
		{
			// Several Hours Ago
			if($secondsAgo < 36000)
			{
				if($secondsAgo < 7200)
				{
					return "an hour ago";
				}
				
				return Data_Number::convert(floor($secondsAgo / 3600)) . " hours ago";
			}
			
			// Yesterday (or, several hours ago as a fallback)
			else if($secondsAgo < 72000)
			{
				if(date('d', time()) != date('d', $timestamp))
				{
					return "yesterday";
				}
				
				return Data_Number::convert(floor($secondsAgo / 3600)) . " hours ago";
			}
			
			// Days Ago
			if($secondsAgo < 86400 * 1.5)
			{
				return "a day ago";
			}
			
			return Data_Number::convert(round($secondsAgo / 86400)) . " days ago";
		}
		
		// If the timestamp was within the last month
		else if($secondsAgo < 86400 * 30)
		{
			// If the time was listed sometime last week
			if(date('W', time()) - date('W', $timestamp) == 1)
			{
				return "last week";
			}
			
			$weeksAgo = $secondsAgo / (86400 * 7);
			
			if($weeksAgo == 1)
			{
				echo "a week ago";
			}
			
			return Data_Number::convert(round($weeksAgo)) . " weeks ago";
		}
		
		// Any other timestamp time
		else
		{
			// If it's the same year:
			if(date('y', time()) === date('y', $timestamp))
			{
				$monthsAgo = date('m', time()) - date('m', time() - $secondsAgo);
				
				if($monthsAgo == 0)
				{
					return "early this month";
				}
				else if($monthsAgo == 1)
				{
					return "last month";
				}
				else if($monthsAgo <= 3)
				{
					return Data_Number::convert($monthsAgo) . " months ago";
				}
				
				return "this " . date('F', $timestamp);
			}
			
			// If it wasn't the same year
			$yearsAgo = date('Y', time()) - date('Y', $timestamp);
			
			if($yearsAgo == 1)
			{
				return "last " . date('F', $timestamp);
			}
		}
		
		// Return the timestamp as a "Month Year" style
		return date("F Y", $timestamp);
	}
}
