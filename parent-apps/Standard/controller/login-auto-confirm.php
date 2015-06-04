<?php /*

-----------------------------------------------------------
------ About the Automatic Login Confirmation Script ------
-----------------------------------------------------------

This script will process the automatic login instructions from UniFaction's Auth server.

Once the script has processed, it will follow the HTTP_REFERER value to the last URL on the site.

The following steps are taken:
	
	1. Decrypt the response packet and extract the custom data.
	
	2. Check if the data sent is valid.
		
		a. Must have the proper handshake response
		
		b. Must have valid user data.
		
	3. Log out if the user is currently logged in.
	
	4. Process the user's data (such as if it needs to be registered on this system).
	
		a. Update the display name if applicable.
		
	5. Redirect to the appropriate page (based on the Return URL that was saved earlier).
	
*/

// Extract the Custom Data from the URL
list($customData, $publicPacket) = API_PacketDecrypt::fromURL();

// Make sure the appropriate data was recovered
if(!isset($publicPacket) or !isset($publicPacket['site_handle']) or !isset($customData) or !isset($customData['handshake']) or $publicPacket['site_handle'] != "auth" or !isset($customData['user']) or !isset($customData['user']['uni_id']))
{
	// Automatic Login Failed - return to login form
	header("Location: /login-form"); exit;
}

// Make sure that a handshake is present and that the login handshake exchanged is identical
if(!isset($_SESSION['login']['handshake']) or $customData['handshake'] != $_SESSION['login']['handshake'])
{
	// Automatic Login Failed - return to login form
	header("Location: /login-form"); exit;
}

// Set the Session Data for the server
$_SESSION['user'] = $customData['user'];
$_SESSION['uni_id'] = $customData['user']['uni_id'];

// Update the user's display name and last login
Database::query("UPDATE IGNORE users SET display_name=?, date_lastLogin=? WHERE uni_id=? LIMIT 1", array($customData['user']['display_name'], time(), $customData['user']['uni_id']));

// Retrieve the Return URL value if applicable
$redirectTo = (isset($_SESSION['login']['return_url']) ? $_SESSION['login']['return_url'] : '/');

// Remove Login Values
unset($_SESSION['login']);

// Return to the Return URL
header("Location: " . $redirectTo); exit;