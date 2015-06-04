<?php /*

-----------------------------------------------
------ About the API_PacketEncrypt Class ------
-----------------------------------------------

This class provides simple methods to prepare and send encrypted packets between UniFaction sites.

To decrypt an encrypted packet, you must use the API_PacketDecrypt class.


-------------------------------
------ Methods Available ------
-------------------------------

// Create a query string with valid packet data
$queryStringPacket = API_PacketEncrypt::queryString($customData, $siteKey);

// Generate a Public Packet and Private Packet
$publicPacket = API_PacketEncrypt::generatePacket();

*/

abstract class API_PacketEncrypt {
	
	
/****** Build a URL Query String for an Encrypted Packet ******/
	public static function queryString
	(
		$customData		// <mixed> The data to exchange.
	,	$siteKey		// <str> The site key to use between the two sites.
	)					// RETURNS <str> The resulting encrypted data.
	
	// $queryStringPacket = API_PacketEncrypt::queryString($customData, $siteKey);
	{
		// Generate the necessary data for an encrypted packet
		$publicPacket = self::generatePacket();
		
		// Prepare the Public Packet for URL's
		$queryString = "_pbd=" . urlencode(Security_Encrypt::run("", json_encode($publicPacket), "open"));
		
		// Encrypt the Private Packet
		$queryString .= "&_pvd=" . urlencode(Security_Encrypt::run($siteKey . $publicPacket['salt'] . $publicPacket['timestamp'], json_encode($customData)));
		
		return $queryString;
	}
	
	
/****** Generate data for an Encrypted Packet ******/
	private static function generatePacket (
	)				// RETURNS <str> The resulting encrypted data.
	
	// list($publicPacket) = API_PacketEncrypt::generatePacket();
	{
		// Prepare Important Values
		$salt		= Security_Hash::random(30, 62);
		$timestamp	= Time::unique();
		
		// Create a Public Packet
		$publicPacket = array(
			"salt"				=> $salt
		,	"site_handle"		=> SITE_HANDLE
		,	"timestamp"			=> $timestamp
		);
		
		return $publicPacket;
	}
	
}
