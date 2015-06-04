<?php /*

-------------------------------------
------ About the Logout Class ------
-------------------------------------

This plugin allows users to log out of the site and Auth system.

*/

abstract class Logout {
	
	
/****** Log a user out of the server ******/
	public static function server (
	)						// RETURNS <void>
	
	// Logout::server();
	{
		// Log the user out of the server
		Cookie_Server::deleteAll();
		
		// Remove the Session Values
		unset($_SESSION['login']);
		unset($_SESSION['uni_id']);
		unset($_SESSION['user']);
	}
	
}
