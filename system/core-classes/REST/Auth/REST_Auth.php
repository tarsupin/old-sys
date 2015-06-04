<?php /*

---------------------------------------
------ About the REST_Auth Class ------
---------------------------------------

This class is used to authenticate any REST API's connecting to this site.

Each site tracked includes:
	
	1. The site's domain.
	2. The clearance level available to that site.
	3. The API key that it interacts with this site with
	
	
-------------------------------
------ Methods Available ------
-------------------------------

// Get the site data for a connected site
$siteConfig = REST_Auth::get($siteHandle, [$scanAuth]);

// Get the shared API key with another site
$siteKey = REST_Auth::key($siteHandle);

// Syncronize this site with another one
REST_Auth::syncConnection($siteHandle, [$syncBoth], [$newKey]);

// Set the data you will use with another site
$key = REST_Auth::setData($siteHandle, $siteName, $siteURL, [$siteClearance], [$siteKey], [$overwrite]);

// Check if you're connected with a particular site
REST_Auth::isConnected($siteHandle);

*/

abstract class REST_Auth {
	
	
/****** Retrieve the REST authentication data for a site ******/
	public static function get
	(
		$domain				// <str> The domain to retrieve REST authentication data for.
	,	$scanAuth = false	// <bool> If the site is not found, setting this to TRUE will scan AUTH for the site.
	)						// RETURNS <str:str> the data for the site, or array() on failure.
	
	// $authData = REST_Auth::get($domain, [$scanAuth]);
	{
		// Return the site data if you have the site available
		$authData = Database::selectOne("SELECT * FROM api_authentication WHERE domain=? LIMIT 1", array($domain));
		
		if($scanAuth == false or $authData)
		{
			return $authData;
		}
		
		// If the site wasn't found locally, connect to Auth so that we can retrieve the public information
		// If we don't have a connection to Auth setup, this step will fail
		if(!$authData = API_Connect::to("unifaction", "GetSiteInfo", $siteHandle))
		{
			return array();
		}
		
		// Update your local copy for this site
		$authData['site_key'] = self::setData($siteHandle, $authData['site_name'], $authData['domain']);
		
		// Return the site data (or false if something went wrong)
		return $authData;
	}
	
	
/****** Retrieve Network Data for a particular site ******/
	public static function key
	(
		$siteHandle			// <str> The site reference to return the data from.
	)						// RETURNS <str> the site key, or "" on failure.
	
	// $siteKey = REST_Auth::key($siteHandle);
	{
		return (string) Database::selectValue("SELECT site_key FROM network_data WHERE site_handle=? LIMIT 1", array($siteHandle));
	}
	
	
/****** Synchronize this site with another ******/
	public static function syncConnection
	(
		$siteHandle			// <str> The site handle of the DESTINATION site to synchronize this site with
	,	$syncBoth = false	// <bool> TRUE to also sync this site (keep updated / renew keys)
	,	$newKey = true		// <bool> TRUE will sync new keys, but ONLY if both sites are being synced.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// REST_Auth::syncConnection($siteHandle, [$syncBoth], [$newKey]);
	{
		// Prepare the Shared API Key
		if($newKey == true and $syncBoth == true)
		{
			$sharedKey = Security_Hash::random(mt_rand(65, 80), 75);
		}
		else if(!$sharedKey = self::key($siteHandle))
		{
			$syncBoth = true;
			$sharedKey = Security_Hash::random(mt_rand(65, 80), 75);
		}
		
		// Preparing the API
		$packet = array(
			"site_handle"	=> $siteHandle		// The site handle for the DESTINATION site
		,	"shared_key"	=> $sharedKey		// The shared key to use between the two sites
		,	"sync_both"		=> $syncBoth		// If TRUE, sync both sites - not just the destination
		);
		
		// Call the API
		if(!$siteConfig = self::get("unifaction"))
		{
			return false;
		}
		
		$response = API_Connect::call($siteConfig['site_url'] . "/api/NetworkSync", $packet, $siteConfig['site_key']);
		
		return $response ? true : false;
	}
	
	
/****** Set Site Data ******/
	public static function setData
	(
		$siteHandle			// <str> The site handle of the site to create.
	,	$siteName			// <str> The site name to set.
	,	$siteURL			// <str> The URL to set.
	,	$siteKey = ""		// <str> The key to set (random if none provided).
	,	$overwrite = true	// <bool> Any updates will overwrite the last one.
	)						// RETURNS <str> the site key, or "" on failure.
	
	// $key = REST_Auth::setData($siteHandle, $siteName, $siteURL, [$siteKey], [$overwrite]);
	// $key = REST_Auth::setData("unifaction", "UniFaction", URL::unifaction_com(), [$siteKey], [$overwrite]);
	{
		// If we're not overwriting the data, check if it already exists.
		$siteConfig = $overwrite ? array() : REST_Auth::get($siteHandle);
		
		// If data does exist, we'll return the existing key.
		if($siteConfig !== array() and isset($siteConfig['site_key']))
		{
			return $siteConfig['site_key'];
		}
		
		// Generate a new site key if one wasn't provided
		$siteKey = ($siteKey !== "" ? $siteKey : Security_Hash::random(mt_rand(65, 80), 75));
		
		$success = Database::query(($overwrite ? "REPLACE" : "INSERT IGNORE") . " INTO network_data (site_handle, site_name, site_url, site_key) VALUES (?, ?, ?, ?)", array($siteHandle, $siteName, $siteURL, $siteKey));
		
		return $success ? $siteKey : "";
	}
	
	
/****** Set Clearance level for a site ******/
	public static function setClearance
	(
		$siteHandle			// <str> The site handle of the site to create.
	,	$clearanceLevel		// <int> The site clearance to set.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// REST_Auth::setClearance($siteHandle, $clearanceLevel);
	{
		// Check if the site already exists
		if($siteConfig = REST_Auth::get($siteHandle))
		{
			return Database::query("UPDATE network_data SET site_clearance=? WHERE site_handle=? LIMIT 1", array($clearanceLevel, $siteHandle));
		}
		
		return Database::query("INSERT IGNORE INTO network_data (site_handle, site_clearance) VALUES (?, ?)", array($siteHandle, $clearanceLevel));
	}
	
	
/****** Test if another site is connected (registered) with this site ******/
	public static function isConnected
	(
		$siteHandle		// <str> The handle of the other site to test a connection with.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// REST_Auth::isConnected($siteHandle);
	{
		// Make sure the data exists on this site
		if(!$siteConfig = self::get($siteHandle))
		{
			return false;
		}
		
		// Check if the API is already connected
		$response = API_Connect::call($siteConfig['site_url'] . "/api/API_IsConnected", "", $siteConfig['site_key']);
		
		return $response ? true : false;
	}
	
}
