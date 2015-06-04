<?php /*

--------------------------------------------------
------ About the Security_Fingerprint Class ------
--------------------------------------------------

This class provides a fingerprint method to help resist fake sessions and session hijacking.


-------------------------------
------ Methods Available ------
-------------------------------

// Run this to help resist fake sessions.
Security_Fingerprint::run()

*/

abstract class Security_Fingerprint {
	
	
/****** Fingerprint User & Force Session Update if Necessary ******/
	public static function run(
	)					// RETURNS <bool> TRUE if successful, FALSE otherwise.
	
	// Security_Fingerprint::run();
	{
		// Check if the user agent matches up between page loads.
		// If it doesn't, that's suspicious - let's destroy the session to avoid potential hijacking.
		if(isset($_SESSION['user_agent']))
		{
			if($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['user_agent'])
			{
				session_destroy();
			}
		}
		elseif(isset($_SERVER['HTTP_USER_AGENT']))
		{
			// Keep track of the current user agent
			$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
		
		// Prepare a session-based CSRF token if not present
		// Note: if the user logs out (or times out), this will reset, causing existing pages to fail functionality.
		if(!isset($_SESSION[SITE_HANDLE]['csrfToken']))
		{
			$_SESSION[SITE_HANDLE]['csrfToken'] = Security_Hash::random(64);
		}
		
		return true;
	}
	
}
