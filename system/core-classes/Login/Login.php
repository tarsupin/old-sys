<?php /*

------------------------------------
------ About the Login Class ------
------------------------------------

This plugin allows users to connect to the Auth system and returns the appropriate user data to your site's "/login" page if it was successful.

The system needs to allow login from any site (and validate with the authentication server), as well as register from any site (which will create the user on the authentication server).


----------------------------
------ Session Values ------
----------------------------

$_SESSION
	['login']
		['handshake']			// Creates a handshake to validate with Auth - must receive same response back.
		['return_url']			// The URL to return to after a login attempt has been made.
	['uni_id']					// The UniID that the user is logged into.
	['device']					// A numerical value that represents the user's current device size
	['user_agent']				// A string that represents the user's browser type (and other data)
	
	
---------------------------------------
------ Understanding Soft Logins ------
---------------------------------------

"Soft Logins" refer to the users being logged onto our sites automatically, as long as they are logged into the Authentication site (or "Auth").

When visiting another site, you may have a value like "?slg=10" that appears. The "slg" is the "soft login" variable that is telling the site that it wants to silently log into the site you're visiting using that UniID.

For example, if you're currently logged into FastChat under the UniID of 10, you may see a link to Social that looks like this:
	
	http://unifaction.social?slg=10
	
In the phpTesla.php file, it will check to see if the "slg" value is present. If it is, it will then check if you're already logged in under that UniID. If you're not logged in with that UniID, it will communicate with the Auth server and confirm that you own the UniID and have permissions to log in with it. Then, it will log you in. This happens automatically without the user knowing it (hence the terminology "soft login").


-------------------------------------
------ User Login Redirections ------
-------------------------------------

A common requirement for pages is to make sure that the user is logged in. For example, you cannot view your "friends" page unless you're logged into it. There are two ways to handle a page that requires a login. You could either say that a login is required, or you could redirect the user to the opportunity to log in.

To redirect a user to login, use the Login::redirect() method. Here's an example of how it looks:

	Login::redirect("/friends?info=RequestsOnly", "/");

In this example, the user would first be asked to log in (or automatically logged in), and then redirected to the page "/friends?info=RequestsOnly". If something goes wrong during this process (such as an infinite loop), the fallback will cause the user to be redirected to "/" (the home page).


-------------------------------
------ Methods Available ------
-------------------------------

// Redirects to login, but will return the user to the $returnTo page after logging in
Login::redirect($returnTo);

*/

abstract class Login {
	
	
/****** User Login ******/
	public static function user
	(
		$uniID				// <int> The Uni-ID to log in as.
	,	$remember = false	// <bool> If set to true, add a remember me cookie.
	)						// RETURNS <bool> TRUE if login validation was successful, FALSE if not.
	
	// Login::user($uniID, true)
	{
		// Set the appropriate UniID
		$_SESSION['uni_id'] = $uniID;
		
		// Load Your Data
		Me::$getColumns = "*";	// Retrieve all of your data during login
		Me::load($uniID);
		
		if(Me::$id == 0) { return false; }
		
		// Prepare User Session
		if(isset($_SESSION[SITE_HANDLE]['site_login']))
		{
			// This retains the site login redirection for UniFaction (Auth)
			$_SESSION[SITE_HANDLE] = array("site_login" => $_SESSION[SITE_HANDLE]['site_login']);
		}
		else
		{
			$_SESSION[SITE_HANDLE] = array();
		}
		
		$_SESSION[SITE_HANDLE]['id'] = Me::$id;		// Required to load user each page.
		
		// Update the last login time (to right now)
		Database::query("UPDATE users SET date_lastLogin=? WHERE uni_id=? LIMIT 1", array(time(), Me::$id));
		
		// Set "Remember Me" cookie if applicable
		if($remember)
		{
			// Me::setCookie();
		}
		
		return true;
	}
	
	
/****** Forces user to login, then redirects back to page ******/
	public static function redirect
	(
		$returnTo		// <str> The page to return to after login.
	,	$fallback = "/"	// <str> The URL to fall back to if the redirect fails.
	)					// RETURNS <void> REDIRECTS to login.
	
	// Login::redirect("/page-to-return-to?extraVal=yep");
	{
		// If a Return URL exists, it will unset it and load that location
		if(isset($_SESSION['login']['return_url']))
		{
			unset($_SESSION['login']['return_url']);
			
			header("Location: " . Sanitize::url($fallback)); exit;
		}
		
		// Sets a temporary return URL value (if one doesn't exist)
		$_SESSION['login']['return_url'] = $returnTo;
		
		header("Location: /login"); exit;
	}
	
}
