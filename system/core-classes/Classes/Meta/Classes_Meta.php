<?php /*

------------------------------------------
------ About the Classes_Meta Class ------
------------------------------------------

This class is used to access the configurations and installation settings for other classes in the system.

If you look through the classes, you will generally notice two files in the main directory that look similar to:

	/Class/Class.php
	/Class/Class.config.php
	
The file that ends in config.php is referred to as the "Class Configuration File". This file identifies information that is valuable to the admin of the site, but which doesn't provide any functionality to the users.

The Class Configuration File performs several important tasks:

	1. Provides details about the plugin, such as the author and it's version (important for updating).

	2. Automatically installs and sets up the plugin on the site.
	
	3. Prepares links that are used in the administration panel.
	
Many of these functions are performed by accessing the "Classes" class, which acts as a handler for other classes and their configurations.


----------------------------
------ Class Actions ------
----------------------------

Some classes have "actions", which is a term that is unique to phpTelsa. "Actions" describe a class method that anyone can activate directly through a URL string. For security purposes, all actions must follow the naming convention of: {NAME_OF_ACTION} + "_TeslaAction".

For example, the action "Jump" would be created like this:
	
	public static function Jump_TeslaAction()
	
And when somebody is activating the action, they do not include "_TeslaAction" in the string (which would accomplish nothing). Instead, the function running the action would automatically append "_TeslaAction" to the action being called.

For example, if someone wanted to activate the "Jump" action on the Class "MyClass", they would use the following URL:
	
	/action/MyClass/Jump
	
If they wanted to include any parameters with the action, they can include it using the "param" parameters:
	
	/action/MyClass/Jump?param[0]=Ten Feet&param[1]=Horizontal
	
If you are creating an action for a class, it is essential that you sanitize any user input that is sent to the method.

The action controller will sanitize the class and action name automatically, such as to eliminate any null byte attacks.


------------------------------
------ Class Behaviors ------
------------------------------

Class behaviors are nearly identical to "Actions" (see "Class Actions" above), except that they are designed for private use by the server (or database), and are NOT intended to be accessible by the public in any way.

However, even though these behaviors are not accessible to the public, they are often methods that need to be heavily secured. Therefore, they are protected by the same means.

Class Behaviors use the naming convention of: {NAME OF BEHAVIOR} + "_TeslaBehavior". For example, the "RunThis" behavior would be created like this:
	
	public static function RunThis_TeslaBehavior()
	
When the system is attempting to launch the behavior, it will sanitize the plugin and behavior name automatically, such as to eliminate any null byte attacks.


-------------------------------
------ Methods Available ------
-------------------------------

// Install a plugin
Classes_Meta::install($class);

// Return the full list of classes available
$classList = Classes_Meta::getClassList();

// Get the configuration class for the plugin
$classConfig = Classes_Meta::getConfig($class);

// Get the admin controllers of the plugin
$controllerList = Classes_Meta::getAdminPages($classPath);

// Run a plugin "action" (unique to phpTesla)
Classes_Meta::runAction($class, $action, $parameters, [$clearance]);

// Run a plugin "behavior" (unique to phpTesla)
Classes_Meta::runBehavior($class, $behavior, $params);

// Check if the plugin has an installer
Classes_Meta::hasInstaller($class, [$classConfig]);

*/

abstract class Classes_Meta {
	
	
/****** Class Variables ******/
	public static $classPages = array();		// <str:[str:mixed]>
	
	// Installation Constants
	const ADMIN_NOT_FOUND = -3;
	const DEPENDENCIES_MISSING = -2;
	const INSTALL_FAILED = -1;
	const NO_INSTALL_NEEDED = 0;
	const INSTALL_SUCCEEDED = 1;
	const ALREADY_INSTALLED = 2;
	
	
/****** Check if a Class Exists ******/
	public static function install
	(
		$class			// <str> The dependency plugin that you need to prepare
	)					// RETURNS <int> The value of the plugin's status
	
	// Classes_Meta::install($class);
	{
		// Get the admin class for the dependency
		if(!$classConfig = self::getConfig($class))
		{
			return self::ADMIN_NOT_FOUND;
		}
		
		// Install all dependencies first
		if(isset($classConfig->dependencies))
		{
			foreach($classConfig->dependencies as $dep)
			{
				if(self::install($dep) < 0)
				{
					return self::DEPENDENCIES_MISSING;
				}
			}
		}
		
		// Run the installation for the dependency
		if(method_exists($classConfig, "install"))
		{
			$success = $classConfig->install() ? true : false;
			
			return ($success == true ? self::INSTALL_SUCCEEDED : self::INSTALL_FAILED);
		}
		
		return self::NO_INSTALL_NEEDED;
	}
	
	
/****** Load the full list of Classes available ******/
	public static function getClassList
	(
		$dir = ""		// <str> A strict directory to look it for plugins; disallows any other paths if set
	)					// RETURNS <int:str> a list of plugins located.
	
	// $classList = Classes_Meta::getClassList([$dir]);
	{
		// If a directory is provided, ONLY look through that directory for plugins
		if($dir !== "")
		{
			$dir = rtrim(Sanitize::filepath($dir), "/");
			return Dir::getFolders($dir);
		}
		
		// Get the full list of available plugins
		$classes = Dir::getFolders(APP_PATH . "/classes");
		$classes = array_merge(Dir::getFolders(SYS_PATH . "/core-classes"), $classes);
		$classes = array_merge(Dir::getFolders(ROOT_PATH . "/plugin-classes"), $classes);
		
		return $classes;
	}
	
	
/****** Load a Class Config ******/
	public static function getConfig
	(
		$class			// <str> The name of the plugin whose admin class needs to be loaded.
	,	$dir = ""		// <str> A strict directory to look it for plugins; disallows any other paths if set
	)					// RETURNS <mixed> PLUGIN class if it was found, FALSE on failure
	
	// $classConfig = Classes_Meta::getConfig($class, [$dir]);
	{
		// Prepare Values
		$class = Sanitize::variable($class);
		$slashClass = str_replace("_", "/", $class);
		$classConfig = $class . "_config";
		
		// If a directory is provided, ONLY look through that directory for plugins
		if($dir !== "")
		{
			$dir = rtrim(Sanitize::filepath($dir), "/");
			$fullDir = $dir . "/" . $slashClass . "/" . $class . ".config.php";
			
			if(is_file($fullDir))
			{
				if(!class_exists($classConfig)) { include($fullDir); }
				
				$classConfig = new $classConfig();
				$classConfig->data['path'] = $dir . "/" . $slashClass;
				$classConfig->data['type'] = "???";
				return $classConfig;
			}
			
			return false;
		}
		
		// Attempt to load an application class
		$dir = APP_PATH . "/classes/" . $slashClass . "/" . $class . ".config.php";
		
		if(is_file($dir))
		{
			if(!class_exists($classConfig)) { include($dir); }
			
			$classConfig = new $classConfig();
			$classConfig->data['path'] = APP_PATH . "/classes/" . $slashClass;
			$classConfig->data['type'] = "app";
			return $classConfig;
		}
		
		// Attempt to load a core class
		$dir = SYS_PATH . "/core-classes/" . $slashClass . "/" . $class . ".config.php";
		
		if(is_file($dir))
		{
			if(!class_exists($classConfig)) { include($dir); }
			
			$classConfig = new $classConfig();
			$classConfig->data['path'] = SYS_PATH . "/core-classes/" . $slashClass;
			$classConfig->data['type'] = "core";
			return $classConfig;
		}
		
		// Attempt to load a plugin class
		$dir = ROOT_PATH . "/plugin-classes/" . $slashClass . "/" . $class . ".config.php";
		
		if(is_file($dir))
		{
			if(!class_exists($classConfig)) { include($dir); }
			
			$classConfig = new $classConfig();
			$classConfig->data['path'] = ROOT_PATH . "/plugin-classes/" . $slashClass;
			$classConfig->data['type'] = "addon";
			return $classConfig;
		}
		
		return false;
	}
	
	
/****** Get a Class Directory ******/
	public static function getPath
	(
		$class			// <str> The name of the class to retrieve the path of
	)					// RETURNS <str> Path to the class if found, or "" on failure.
	
	// $classPath = Classes_Meta::getPath($class);
	{
		// Prepare Values
		$class = Sanitize::variable($class);
		$slashClass = str_replace("_", "/", $class);
		
		// Attempt to load an application class
		if(is_file(APP_PATH . "/classes/" . $slashClass . "/" . $class . ".config.php"))
		{
			return APP_PATH . "/classes/" . $slashClass;
		}
		
		// Attempt to load a core class
		if(is_file(SYS_PATH . "/core-classes/" . $slashClass . "/" . $class . ".config.php"))
		{
			return SYS_PATH . "/core-classes/" . $slashClass;
		}
		
		// Attempt to load an plugin class
		if(is_file(ROOT_PATH . "/plugin-classes/" . $slashClass . "/" . $class . ".config.php"))
		{
			return ROOT_PATH . "/plugin-classes/" . $slashClass;
		}
		
		return "";
	}
	
	
/****** Retrieve a list of the Class's Admin Pages ******/
	public static function getAdminPages
	(
		$classPath = ""		// <str> The base class directory to retrieve admin pages from.
	)						// RETURNS <int:str> an array of controller pages contained in the directory provided.
	
	// $controllerList = Classes_Meta::getAdminPages($classPath);
	{
		$controllerList = array();
		
		// Get the admin pages for this class, if availble
		if(is_dir($classPath . '/admin'))
		{
			$contFiles = Dir::getFiles($classPath . '/admin');
			
			foreach($contFiles as $filename)
			{
				if(strpos($filename, ".php") === false)
				{
					continue;
				}
				
				$fileName = Sanitize::variable(str_replace(".php", "", $filename), " -");
				$controllerList[] = $fileName;
			}
		}
		
		return $controllerList;
	}
	
	
/****** Run a class's action (if applicable) ******/
	public static function runAction
	(
		$class			// <str> The class to run the action for
	,	$action			// <str> The name of the class's action to run.
	,	$params			// <array> The parameters passed for this action.
	,	$clearance = 0	// <int> The level of clearance to activate the action with.
	)					// RETURNS <mixed> the response of the function, or void on failure.
	
	// Classes_Meta::runAction($class, $action, $parameters, [$clearance]);
	{
		// The name of the Class to run
		$class = Sanitize::variable($class);
		
		// The name of the Class's Action to run
		$action = Sanitize::variable($action);
		
		// Set clearance to user, if applicable
		if($clearance == 0)
		{
			$clearance = Me::$clearance;
		}
		
		// Set the parameters
		array_push($params, $clearance);
		
		// Make sure the action exists
		if(method_exists($class, $action . "_TeslaAction"))
		{
			// Run the plugin's action
			return call_user_func(array($class, $action . "_TeslaAction"), $params);
		}
	}
	
	
/****** Run a class's behavior (if applicable) ******/
	public static function runBehavior
	(
		$class			// <str> The class to run the behavior for
	,	$behavior		// <str> The name of the class's behavior to run.
	,	$params			// <array> The parameters passed for this behavior.
	)					// RETURNS <mixed> the response of the function, or void on failure.
	
	// Classes_Meta::runBehavior($class, $behavior, $params);
	{
		// The name of the Class to run
		$class = Sanitize::variable($class);
		
		// The name of the Class's Behavior to run
		$behavior = Sanitize::variable($behavior);
		
		// Make sure the behavior exists
		if(method_exists($class, $behavior . "_TeslaBehavior"))
		{
			// Run the class's behavior
			return call_user_func_array(array($class, $behavior . "_TeslaBehavior"), $params);
		}
	}
	
	
/****** Check if a Class has installations to run ******/
	public static function hasInstaller
	(
		$class				// <str> The class that you need to check if it has an installation process.
	,	$classConfig = null	// <mixed> The class config object, if already active.
	)						// RETURNS <bool> TRUE if it has an installer, FALSE if not.
	
	// Classes_Meta::hasInstaller($class, [$classConfig]);
	{
		// Make sure the config class for the class exists
		if($classConfig == null)
		{
			if(!$classConfig = self::getConfig($class))
			{
				return false;
			}
		}
		
		// Check if there is an install method
		return method_exists($classConfig, "install") ? true : false;
	}
}

