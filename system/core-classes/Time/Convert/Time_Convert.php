<?php /*

------------------------------------------
------ About the Time_Convert Class ------
------------------------------------------

This class allows you to convert dates and times to other values, such as when converting human-created words to numeric values.


------------------------------
------ Time Conversions ------
------------------------------

	// Returns numbers for each month
	echo Time_Convert::monthToNumber("January");		// 1
	echo Time_Convert::monthToNumber("September");	// 10
	
	// Returns numbers of week days
	echo Time_Convert::dayOfWeekToNumber("Monday");		// 1
	echo Time_Convert::dayOfWeekToNumber("Saturday");	// 6
	
	// Returns numbers of hours
	echo Time_Convert::hourToNumber("10 am");	// 10
	echo Time_Convert::hourToNumber("10 pm");	// 22
	echo Time_Convert::hourToNumber("2PM");		// 14
	echo Time_Convert::hourToNumber("1AM");		// 1


-------------------------------
------ Methods Available ------
-------------------------------

// Returns the month as a valid number
$month		= Time_Convert::monthToNumber($month)

// Returns the day of week as a valid number
$dayOfWeek	= Time_Convert::dayOfWeekToNumber($day)

// Returns an hour as a valid number
$hour		= Time_Convert::hourToNumber($hour)

*/

abstract class Time_Convert {
	
	
/****** Convert a month string to a valid number ******/
	public static function monthToNumber
	(
		$month	// <mixed> The month to convert.
	)			// RETURNS <int> the numeric value of the month (e.g. 2 for "February").
	
	// $month = Time_Convert::monthToNumber("March");	// Returns 3
	{
		// If the argument is a string (e.g. "January"), change it to a number.
		if(!is_numeric($month))
		{
			$translate = array("jan" => 1, "feb" => 2, "mar" => 3, "apr" => 4, "may" => 5, "jun" => 6, "jul" => 7, "aug" => 8, "sep" => 9, "oct" => 10, "nov" => 11, "dec" => 12);
			
			$month = substr(strtolower($month), 0, 3);
			
			// If the month couldn't be interpreted, end here
			if(!isset($translate[$month]))
			{
				return 0;
			}
			
			$month = $translate[$month];
		}
		
		// Restrict months from 1 to 12
		if($month < 1) { $month = 1; }
		else if($month > 12) { $month = 12; }
		
		return $month + 0;
	}
	
	
/****** Convert a day of the week string to a valid number ******/
	public static function dayOfWeekToNumber
	(
		$day	// <mixed> The day of the week to convert.
	)			// RETURNS <int> the numeric value of the day of the week (e.g. 3 for "Wednesday").
	
	// $day = Time_Convert::dayOfWeekToNumber("Friday");	// Returns 5
	{
		// If the argument is a string (e.g. "Monday"), change it to a number.
		if(!is_numeric($day))
		{
			$translate = array("mon" => 1, "tue" => 2, "wed" => 3, "thu" => 4, "fri" => 5, "sat" => 6, "sun" => 7);
			
			$day = substr(strtolower($day), 0, 3);
			
			if(!isset($translate[$day]))
			{
				return 0;
			}
			
			$day = $translate[$day];
		}
		
		// Prevent illegal days of the week
		if($day < 1) { $day = 1; }
		else if($day > 7) { $day = 7; }
		
		return $day + 0;
	}
	
	
/****** Convert an hour string to a valid number ******/
	public static function hourToNumber
	(
		$hour	// <mixed> The hour to convert.
	)			// RETURNS <int> the numeric value of the hour (e.g. 22 for "10 pm").
	
	// $hour = Time_Convert::hourToNumber("10 pm");		// Returns 22
	{
		// If the hour is a string (e.g. "3 pm", "1 am"), change it to a number.
		if(!is_numeric($hour))
		{
			$hour = strtolower($hour);
			
			if(strpos($hour, "pm") !== false)
			{
				$hour = (int) $hour + 12;
			}
		}
		
		return (int) $hour % 24;
	}
	
}
