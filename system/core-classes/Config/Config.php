<?php /*

-------------------------------------
------ About the Config Class ------
-------------------------------------

This plugin is used to call the appropriate configurations for the site. If a site does not overwrite configurations, it will call global configurations.


-------------------------------
------ Methods Available ------
-------------------------------

// Retrieves the site configuration if available; otherwise retrieves the global configuration
Config::$siteConfig = Config::get($configName);

*/

abstract class Config {
	
	
/****** Class Variables ******/
	public static $siteConfig = array();	// <str:mixed> Stores the site configurations.
	public static $pageConfig = array();	// <str:mixed> Stores configurations for the page being loaded.
	
	
/****** Get Config Values ******/
	public static function get
	(
		$configName			// <str> The name of the configuration you're retrieving.
	,	$parentDir = ""		// <str> If set, forces a specific configuration path to be used.
	)						// RETURNS <str:mixed> the configuration array (empty if unavailable).
	
	// $configData = Config::get($configName, $parentDir);
	{
		// If we're designating the configuration path manually
		if($parentDir)
		{
			if(file_exists($parentDir . '/config/' . $configName . '.php'))
			{
				return require($path . '/config/' . $configName . '.php');;
			}
		}
		
		// If we haven't chosen a configuration path manually
		else
		{
			// Check if the site, app, or system uses this configuration
			foreach([SITE_PATH, APP_PATH, ROOT_PATH] as $path)
			{
				// If we find a match, load the configurations as constants and return true
				if(file_exists($path . '/config/' . $configName . '.php'))
				{
					return require($path . '/config/' . $configName . '.php');
				}
			}
		}
		
		// Return an empty configuration if none were found
		return array();
	}
	
	
/****** Convert configuration values to constants that are used in the system ******/
	public static function convertToConstants
	(
		$configName			// <str> The name of the configuration you're converting.
	,	$configDir = ""		// <str> If set, forces a specific configuration path to be used.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Config::convertToConstants($configName, $configDir);
	{
		// If we're designating the configuration path manually
		if($configDir)
		{
			if(file_exists($configDir . '/config/' . $configName . '.php'))
			{
				return self::doConversionToConstants($configDir . '/config/' . $configName . '.php');
			}
		}
		
		// If we haven't chosen a configuration path manually
		else
		{
			// Search through the site, app, and server for this configuration - in that order:
			foreach([SITE_PATH, APP_PATH, ROOT_PATH] as $path)
			{
				// If we find a match, load the configurations as constants and return true
				if(file_exists($path . '/config/' . $configName . '.php'))
				{
					return self::doConversionToConstants($path . '/config/' . $configName . '.php');
				}
			}
		}
		
		return false;
	}
	
	
/****** Run the conversion from configurations to constants ******/
	private static function doConversionToConstants
	(
		$fullConfigPath		// <str> The full path to the configuration file.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Config::doConversionToConstants($fullConfigPath);
	{
		// Loop through each configuration and convert it to a constant
		foreach(require($fullConfigPath) as $config => $value)
		{
			define(str_replace(" ", "_", strtoupper($config)), $value);
		}
		
		return true;
	}
	
}
