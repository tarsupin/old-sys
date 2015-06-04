<?php /*

----------------------------------------------
------ About the Automatic Login Script ------
----------------------------------------------

This script will attempt to log the user in automatically. If the user cannot be logged in automatically, they will be sent to a manual page.

The following steps are taken:
	
	1. Check if we have a Return URL set (through the session).
		
		1a. If not, set the HTTP_REFERER URL (last URL visited) as the Return URL.
	
	2. Check if the user has a session on this server.
	
		2a. If they do, return to the Return URL.
	
	// ?? Do we want to have this part ??
	3. Check if the user has a cookie on this server.
	
		3a. If they do, set the session and return to the Return URL.
	// !! For now, let's avoid this step !!
	
	4. Begin process for validating a login with UniFaction's Auth system.
			
		4a. Get the site key to use when connecting to auth.
		
		4b. Create an encrypted packet to send for verification.
			
			I. Include a simple "AutoLogin" confirmation string.
		
		4c. Redirect to Auth's login, and pass the appropriate data.
		
			I. Auth will return to "login-auto-confirm" if the automatic login was successful.
			
			II. Auth will return to "login-manual" if the automatic login failed.
	
*/

// Determine the Return URL (if not already set)
if(!isset($_SESSION['login']['return_url']))
{
	// If this page was referred by a previous URL
	if(isset($_SERVER['HTTP_REFERER']))
	{
		$refURL = URL::parse($_SERVER['HTTP_REFERER']);
		
		$_SESSION['login']['return_url'] = '/' . (isset($refURL['path']) ? $refURL['path'] : '') . (isset($refURL['query']) ? '?' . $refURL['query'] : '');
	}
	
	// If this page was hit by a bookmark (not a link)
	else
	{
		$_SESSION['login']['return_url'] = '/';
	}
}

// If you're already logged in, return to the Return URL
if(Me::$id)
{
	unset($_SESSION['login']);
	
	header("Location: /" . $_SESSION['login']['return_url']); exit;
}

// Retrieve the Site Key
if(!$siteConfig = API_Data::get("auth"))
{
	die("Error: Unable to retrieve proper authentication.");
}

// Save the site handshake
$_SESSION['login']['handshake'] = Security_Hash::random(30, 62);

// Prepare Custom Data
$customData = array(
	"handshake" => $_SESSION['login']['handshake']
);

// If we're making an auto-login action, inform Auth so that it can react appropriately
if(isset($_GET['action']) and $_GET['action'] == "autolog")
{
	$customData['autolog'] = true;
}

// Create a query string with valid packet data
$queryStringPacket = API_PacketEncrypt::queryString($customData, $siteConfig['site_key']);

// Redirect to Auth's Automatic Login Page (Get credentials and return)
header("Location: " . $siteConfig['site_url'] . "/login-auto?" . $queryStringPacket); exit;
