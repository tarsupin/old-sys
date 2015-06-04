<?php /*

-----------------------------------------
------ About the Return-URL Script ------
-----------------------------------------

This script will return the user to the last known "Return URL", which is the location that was previously stored by the user's session to identify where they should return to after a redirect.

This is most likely called after an attempt for automatically logging in to Auth that failed (due to Auth not being logged in).
	
*/

// Make sure the Return URL exists
if(!isset($_SESSION['login']['return_url']))
{
	unset($_SESSION['login']);
	
	header("Location: /"); exit;
}

// If you're already logged in, return to the Return URL
if(Me::$id)
{
	header("Location: /" . $_SESSION['login']['return_url']); exit;
}

// Retrieve the Site Key
$apiData = API_Data::get("auth");

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
$queryStringPacket = API_PacketEncrypt::queryString($customData, $apiData['site_key']);

// Redirect to Auth's Automatic Login Page (Get credentials and return)
header("Location: " . $apiData['site_url'] . "/login-auto?" . $queryStringPacket); exit;
