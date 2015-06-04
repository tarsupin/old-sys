<?php

// Increase the amount of time allowed for this page to run
set_time_limit(180);	// Three minutes

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Connect to database with admin user
Database::initRoot();

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

// Display the Page
echo '
<h1>Installation: Classes</h1>

<h3>Step #1 - Install Application Classes</h3>

<p>These plugins will ONLY be used within the `' . Config::$siteConfig['Site Name'] . '` application you are setting up - NOT through the entire system. By default, they must be saved in your application in the /classes directory.</p>

<p>Note: Application plugins will overwrite the functionality of core and plugin classes if you use the same plugin name.</p>';

// Loop through each class and install it
$configPaths = File_Scan::scanRecursive(APP_PATH . "/classes", "*.config.php");

foreach($configPaths as $configPath)
{
	// Extract the name of this class
	$class = str_replace(".config.php", "", basename($configPath));
	
	// Load the Class's Config Class
	if(!$classConfig = Classes_Meta::getConfig($class, APP_PATH . "/classes"))
	{
		echo '<h4 style="color:red;">' . $class . '</h4>
		<p><span style="color:red;">The plugin\'s config class was inaccessible.</span></p>';
		continue;
	}
	
	// Bypass installation plugins
	if($class == "Install")
	{
		$installed = Install::setup() ? 1 : -1;
	}
	else
	{
		// Install Standard Class Types  (not installation)
		$installed = Classes_Meta::install($class);
	}
	
	switch($installed)
	{
		case Classes_Meta::DEPENDENCIES_MISSING:
			$details = '<span style="color:red; font-weight:700;">This installation requires dependencies that were not installed properly.</span>';
			break;
		
		case Classes_Meta::INSTALL_FAILED:
			$details = '<span style="color:red; font-weight:700;">Installation failed. Core functionality may be broken.</span>';
			break;
		
		case Classes_Meta::INSTALL_SUCCEEDED:
			$details = '<span style="color:green; font-weight:700;">Installation was completed successfully.</span>';
			break;
		
		case Classes_Meta::NO_INSTALL_NEEDED:
			$details = '<span style="color:blue;">No installation was necessary for this plugin.</span>';
			break;
	}
	
	// Display the Class
	echo '<h4>' . $class . ' - v' . number_format($classConfig->version, 2) . '</h4>
	<p>
		Author: ' . $classConfig->author . '
		<br />Description: ' . $classConfig->description . '
		<br />' . $details . '
	</p>';
}

echo '
<a class="button" href="/install/connect-auth">Continue with Installation</a>';

// Display the Footer
require(FOOTER_PATH);