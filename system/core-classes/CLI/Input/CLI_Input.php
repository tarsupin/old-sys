<?php /*

---------------------------------------
------ About the CLI_Input Class ------
---------------------------------------

This plugin is used to handle input taken from the command line.


-------------------------------
------ Methods Available ------
-------------------------------

*/

abstract class CLI_Input {
	
	
/****** Request input from the command line (and return it) ******/
	public static function getLine (
	)						// RETURNS <str> the text entered into the command line.
	
	// $input = CLI_Input::getLine();
	{
		readline_callback_handler_remove();
		return readline();
	}
	
	
/****** Request a single character from the command line ******/
	public static function getCharacter (
	)						// RETURNS <str> a single character from the command line.
	
	// $characterPressed = CLI_Input::getCharacter();
	{
		// Prepare Required Functions
		readline_callback_handler_install('', function() { });
		
		while(true)
		{
			$r = array(STDIN);
			$w = NULL;
			$e = NULL;
			$n = stream_select($r, $w, $e, 0);
			
			if($n && in_array(STDIN, $r))
			{
				$c = stream_get_contents(STDIN, 1);
				
				return $c;
			}
		}
	}
	
}
