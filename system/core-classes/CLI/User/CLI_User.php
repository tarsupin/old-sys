<?php /*

--------------------------------------
------ About the CLI_User Class ------
--------------------------------------

This class is used to validate the CLI users, or to provide other functionality for them.


-------------------------------
------ Methods Available ------
-------------------------------

*/

abstract class CLI_User {
	
	
/****** Class Variables ******/
	const SCRIPT_DIES_ON_FAILURE = true;
	const SCRIPT_LIVES_ON_FAILURE = false;
	
	
/****** Request input from the command line (and retrieve it) ******/
	public static function verify
	(
		$strict = true			// <bool> TRUE if the script will die if the user is not verified.
	,	$allowedUsers = "root"	// <T> A user, or an array of users, to compare to the active CLI user.
	)							// RETURNS <str> the text entered into the command line.
	
	// CLI_User::verify(CLI_User::SCRIPT_DIES_ON_FAILURE, "root");
	{
		// Get the active user
		$activeUser = trim(shell_exec('whoami'));
		
		// If we're using a list of allowed users
		if(is_array($allowedUsers))
		{
			// Make sure the user is one that is allowed, or prevent further use
			if(!in_array($activeUser, $allowedUsers))
			{
				if($strict) { die("The current user does not have access to this script."); }
				
				return false;
			}
			
			return true;
		}
		
		// Make sure the user is allowed, or prevent further use
		if($activeUser !== $allowedUsers)
		{
			if($strict) { die("The current user does not have access to this script."); }
			
			return false;
		}
		
		return true;
	}
}
