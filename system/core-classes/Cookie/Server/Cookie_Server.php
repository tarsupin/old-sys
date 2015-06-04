<?php /*

-------------------------------------------
------ About the Cookie_Server Class ------
-------------------------------------------

This class will attempt to use cookies that work across as many sub-domains as possible, using the BASE_DOMAIN constant to identify what sub-domains are allow.

These cookies will only work across sub-domains hosted on this server - it will not affect sub-domains hosted on other servers.

This is primarily used to remember users for this server, thus avoiding the need for automatic logins for many sites. Any uses other than this might benefit from an alternative system.


-----------------------------------------
------ Example of using this class ------
-----------------------------------------

// To set the cookies, run Cookie_Server::set(...)
if($justLoggedIn == true OR $regenerateCookies == true)
{
	Cookie_Server::set("uniID", $user['uni_id'], $user['password'] . $user['auth_token']);
}

// Every page view, you'll want to check if your session forgets. If it does, use the reminder.
if(!isset($_SESSION['uni_id']))
{
	$retVal = Cookie_Server::get("uniID", $user['password'] . $user['auth_token']);
	
	if($retVal !== false)
	{
		$_SESSION['uni_id'] = $retVal;
	}
}


-------------------------------
------ Methods Available ------
-------------------------------

// Retrieve a cookie's value
$value = Cookie_Server::get($cookieName, [$salt])

// Create a cookie
Cookie_Server::set($cookieName, $valueToRemember, [$salt], [$expiresInDays = 90])

// Deletes a cookie
Cookie_Server::delete($cookieName);

*/

abstract class Cookie_Server {
	
	
/****** Get the variable name of the cookie ******/
	public static function varName
	(
		$cookieName		// <str> The name of the cookie you're returning.
	)					// RETURNS <str> variable name of the cookie.
	
	// $cookieVarName = Cookie_Server::varName("userID-auth");
	{
		return Security_Hash::value($cookieName, 5, 62) . '-' . $cookieName;
	}
	
	
/****** Get Cookie Value ******/
	public static function get
	(
		$cookieName		// <str> The name of the cookie you're trying to remember.
	,	$salt = ""		// <str> The unique identifier for the validation.
	)					// RETURNS <str> value to remember if successful, or "" if validation failed.
	
	// Cookie_Server::get("uniID", $user['password'] . "extraSalt");
	// Cookie_Server::get($cookieName, [$salt]);
	{
		// Prepare Values
		$cookieName = Security_Hash::value($cookieName, 5, 62) . '-' . $cookieName;
		
		// Check if Cookie is Valid
		if(isset($_COOKIE[$cookieName]) and isset($_COOKIE[$cookieName . "_key"]))
		{
			// Prepare Token
			$token = Security_Hash::value($_COOKIE[$cookieName] . (get_called_class() == "Cookie_Server" ? SERVER_SALT : SITE_SALT) . $salt, 50, 62);
			
			if($_COOKIE[$cookieName . "_key"] === $token)
			{
				return $_COOKIE[$cookieName];
			}
		}
		
		return "";
	}
	
	
/****** Create Cookies w/ Authentication ******/
	public static function set
	(
		$cookieName				// <str> The name of the cookie you're trying to remember.
	,	$valueToRemember		// <mixed> The value you want to remember.
	,	$salt = ""				// <str> The unique salt you want to use to keep the cookie safe.
	,	$expiresInDays = 30		// <int> The amount of time the cookie should last, in days.
	)							// RETURNS <void>
	
	// Cookie_Server::set('uniID', $user['id'], $user['password'] . "extraSalt");
	// Cookie_Server::set($cookieName, $cookieValue, [$cookieSalt], [$expiresInDays]);
	{
		// Prepare Values
		$cookieName = Security_Hash::value($cookieName, 5, 62) . '-' . $cookieName;
		
		// Prepare Values
		$timestamp = time();
		$token = Security_Hash::value($valueToRemember . (get_called_class() == "Cookie_Server" ? SERVER_SALT : SITE_SALT) . $salt, 50, 62);
		
		self::delete($cookieName);
		
		// Cookie_Server vs. Cookie_Site differences
		$domain = (get_called_class() == "Cookie_Server" ? BASE_DOMAIN : FULL_DOMAIN);
		
		// Set the cookie
		setcookie($cookieName, $valueToRemember, $timestamp + (86400 * $expiresInDays), "/", $domain . URL_SUFFIX);
		setcookie($cookieName . "_key", $token, $timestamp + (86400 * $expiresInDays), "/", $domain . URL_SUFFIX);
	}
	
	
/****** Delete Cookie ******/
	public static function delete
	(
		$cookieName		// <str> The name of the cookie you're trying to delete.
	)					// RETURNS <void>
	
	// Cookie_Server::delete("uniID");
	{
		// Prepare Values
		$cookieName = Security_Hash::value($cookieName, 5, 62) . '-' . $cookieName;
		$timestamp = time();
		
		// Remove Global Cookie Values
		if(isset($_COOKIE[$cookieName]))
		{
			unset($_COOKIE[$cookieName]);
		}
		
		if(isset($_COOKIE[$cookieName . "_key"]))
		{
			unset($_COOKIE[$cookieName . "_key"]);
		}
		
		// Cookie_Server vs. Cookie_Site differences
		$domain = (get_called_class() == "Cookie_Server" ? BASE_DOMAIN : FULL_DOMAIN);
		
		// Remove desired Cookie and its associated key
		setcookie($cookieName, "", $timestamp - 360000, "/", $domain . URL_SUFFIX);
		setcookie($cookieName . "_key", "", $timestamp - 360000, "/", $domain . URL_SUFFIX);
	}
	
	
/****** Delete All Cookies ******/
	public static function deleteAll (
	)					// RETURNS <void>
	
	// Cookie_Server::deleteAll();
	{
		$timestamp = time();
		
		if(!$_COOKIE) { return; }
		
		// Cookie_Server vs. Cookie_Site differences
		$domain = (get_called_class() == "Cookie_Server" ? BASE_DOMAIN : FULL_DOMAIN);
		
		// Loop through each cookie
		foreach($_COOKIE as $cookieName => $value)
		{
			// Remove Global Cookie Values
			if(isset($_COOKIE[$cookieName]))
			{
				unset($_COOKIE[$cookieName]);
			}
			
			if(isset($_COOKIE[$cookieName . "_key"]))
			{
				unset($_COOKIE[$cookieName . "_key"]);
			}
			
			// Remove desired Cookie and its associated key
			setcookie($cookieName, "", $timestamp - 360000, "/", $domain . URL_SUFFIX);
			setcookie($cookieName . "_key", "", $timestamp - 360000, "/", $domain . URL_SUFFIX);
		}
	}
}

