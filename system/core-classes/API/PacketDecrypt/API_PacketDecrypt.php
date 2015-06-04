<?php /*

-----------------------------------------------
------ About the API_PacketDecrypt Class ------
-----------------------------------------------

This class provides simple methods to decrypt encrypted packets that were shared between UniFaction sites.

To encrypt a packet, you must use the EncryptPacket class.


-------------------------------
------ Methods Available ------
-------------------------------

// Extract the custom data from a URL-Encrypted Packet
list($customData, $publicPacket) = API_PacketDecrypt::fromURL([$siteKey]);

// Extract the custom data from a custom Query String
list($customData, $publicPacket) = API_PacketDecrypt::fromQueryString($queryStr, [$siteKey]);

*/

abstract class API_PacketDecrypt {
	
	
/****** Decrypt an Encrypted Packet from the URL ******/
	public static function fromURL
	(
		$siteKey = ""	// <str> The site key to use for decryption (if known).
	)					// RETURNS <mixed> custom data that was sent, FALSE on failure.
	
	// list($customData, $publicPacket) = API_PacketDecrypt::fromURL([$siteKey]);
	{
		// Make sure the necessary information was sent
		if(!isset($_GET['_pbd']) or !isset($_GET['_pvd']))
		{
			return false;
		}
		
		// Extract the public packet from the URL
		$publicPacket = json_decode(Security_Decrypt::run("", $_GET['_pbd']), true);
		
		// Check if the site data was already acquired
		if(!$siteKey)
		{
			// We don't know the site data - retrieve it now
			if(!$siteKey = API_Data::key($publicPacket['site_handle']))
			{
				return false;
			}
		}
		
		// Decrypt the custom data
		$customData = json_decode(Security_Decrypt::run($siteKey . $publicPacket['salt'] . $publicPacket['timestamp'], $_GET['_pvd']), true);
		
		return self::process($customData, $publicPacket);
	}
	
	
/****** Decrypt an Encrypted Packet from a custom Query String ******/
	public static function fromQueryString
	(
		$queryStr		// <str> A custom Query String to use
	,	$siteKey = ""	// <str> The site key to use for decryption (if known).
	)					// RETURNS <mixed> custom data that was sent, FALSE on failure.
	
	// list($customData, $publicPacket) = API_PacketDecrypt::fromQueryString($queryStr, [$siteKey]);
	{
		// Parse the query string used
		parse_str($queryStr, $output);
		
		// Make sure the necessary information was sent
		if(!isset($output['_pbd']) or (!isset($output['_pvd'])))
		{
			return false;
		}
		
		// Decrypt the public packet
		$publicPacket = json_decode(Security_Decrypt::run("", $output['_pbd']), true);
		
		// Check if the site data was already acquired
		if(!$siteKey)
		{
			// We don't know the site data - retrieve it now
			if(!$siteKey = API_Data::key($publicPacket['site_handle']))
			{
				return false;
			}
		}
		
		// Decrypt the custom data
		$customData = json_decode(Security_Decrypt::run($siteKey . $publicPacket['salt'] . $publicPacket['timestamp'], $output['_pvd']), true);
		
		return self::process($customData, $publicPacket);
	}
	
	
/****** Decrypt an Encrypted Packet's Data ******/
	public static function process
	(
		$customData			// <str:mixed> The custom data that was retrieved.
	,	$publicPacket		// <str:str> The public packet that was retrieved.
	)						// RETURNS <mixed> custom data that was sent, FALSE on failure.
	
	// list($customData, $publicPacket) = API_PacketDecrypt::process($publicPacket, $customData);
	{
		// Make sure the appropriate package data was sent
		if(!isset($publicPacket['salt']) or !isset($publicPacket['site_handle']) or !isset($publicPacket['timestamp']))
		{
			return array($publicPacket, null);
		}
		
		// Make sure the timestamp is within a valid range
		if(!Time::unique($publicPacket['timestamp'], 2))
		{
			return array($publicPacket, null);
		}
		
		// Return the custom data sent
		return array($customData, $publicPacket);
	}
	
}
