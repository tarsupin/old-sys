<?php /*

-------------------------------------------
------ About the Installation Class ------
-------------------------------------------

This plugin provides methods that are used during the installation of Uni-Sites. 

Every individual uni-site also has an "Install" plugin that extends this class. This plugin is used to keep all of the necessary installation details about the site, as well as contain pages for app-specific installation purposes.


----------------------------------------
------ Creating an Install Class ------
----------------------------------------

An app-specific "Install" plugin must be created on every application in the {APP_PATH}/classes directory.

The "Install" plugin has two requirements:
	
	1. It must contain the $pluginClasses array, which is structured like this:
	
		public static $pluginClasses = array(
			"ExampleClass"		=> true
		,	"AnotherClass"		=> true
		);
		
		Each plugin listed here will set the addon plugin to be automatically installed during the addon installation process.
		
	2. It must have the ::setup() method, which will be activated during the "application plugins" installation process.
	
		This method allows the application to set up any additional database tables or update an files necessary before installation is finished.
	
	
An installation plugin will look something like this:
	
	abstract class Install extends Installation
	{
		// Install Designated Plugin Classes
		public static $pluginClasses = array(
			"ExampleClass"		=> true
		,	"AnotherClass"		=> true
		);
		
		// This method will run automatically during the application step of installation
		public function setup()
		{
			// Automatically configure our base defaults
			SiteVariables::save("site-configs", "site-admin", "Me");
			SiteVariables::save("modules", "left-panel-module", "calendar-module");
			
			// Run a few important queries
			// ... etc, etc...
		}
	}

*/

abstract class Installation {
	
	
/****** Class Variables ******/
	
	// These plugin classes will be selected for installation during the "addon" installation process:
	public static $pluginClasses = array(	// <str:bool>
	//	"ExampleClass"		=> true
	//,	"AnotherClass"		=> true
	);
	
	
/****** App-Specific Installation Processes ******/
	public static function setup(
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	{
		return true;
	}
	
}
