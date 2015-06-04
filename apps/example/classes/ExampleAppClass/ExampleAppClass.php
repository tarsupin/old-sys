<?php /*

---------------------------------------------
------ About the ExampleAppClass Class ------
---------------------------------------------

This class is just an example. You can call it using:

	echo ExampleAppClass::myExampleMethod("some words");


-------------------------------
------ Methods Available ------
-------------------------------

// Outputs your word back to you, but with different capitalization.
ExampleAppClass::myExampleMethod($words);

*/

abstract class ExampleAppClass {
	
	
/****** Provide an example method ******/
	public static function myExampleMethod
	(
		$words		// <str> A string of words.
	)				// RETURNS <void>
	
	// ExampleAppClass::myExampleMethod($words);
	{
		return ucwords(strtolower($words));
	}
}
