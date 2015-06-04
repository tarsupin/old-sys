<?php

// Create our custom Auto-Loader Function
function AutoLoader($class)
{
	$preClass = str_replace("_", "/", $class);
	
	// Search Application Classes
	if($classFile = realpath(APP_PATH . "/classes/$preClass/" . $class . ".php"))
	{
		require($classFile); return true;
	}
	
	// Search System Classes
	if($classFile = realpath(SYS_PATH . "/core-classes/$preClass/" . $class . ".php"))
	{
		require($classFile); return true;
	}
	
	// Search Plugin Classes
	if($classFile = realpath(ROOT_PATH . "/plugin-classes/$preClass/" . $class . ".php"))
	{
		require($classFile); return true;
	}
	
	// The plugin was not located. Return false.
	return false;
}

// Register our custom Auto-Loader
spl_autoload_register('AutoLoader');