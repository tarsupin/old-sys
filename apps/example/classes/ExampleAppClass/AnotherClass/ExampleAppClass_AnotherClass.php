<?php /*

----------------------------------------------------------
------ About the ExampleAppClass_AnotherClass Class ------
----------------------------------------------------------

This class is very similar to the example class, except that it's organized as a subfolder.
It can be accessed with the following:

	echo ExampleAppClass_AnotherClass::anotherMethod("some words");


-------------------------------
------ Methods Available ------
-------------------------------

// Outputs your word back to you, but with different capitalization.
ExampleAppClass::anotherMethod($words);

*/

abstract class ExampleAppClass_AnotherClass {
	
	
/****** Provide an example method ******/
	public static function anotherMethod
	(
		$words		// <str> A string of words.
	)				// RETURNS <void>
	
	// ExampleAppClass::anotherMethod($words);
	{
		return lcwords(strtoupper($words));
	}
}
