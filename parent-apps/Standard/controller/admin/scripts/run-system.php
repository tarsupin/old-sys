<?php /*

-------------------------------------------------------
------ About the "System Script" Activation Page ------
-------------------------------------------------------

File Path to System Script: {SYS_PATH}/system-script

This page will run a system script for each of the sites on the current server simultaneously. It does this by activating the "system-script" page on every site.

The server must have the "{SYS_PATH}/system-script" script prepared for this to function as expected.

*/

// Run Permissions & Header
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Run Global Script (if applicable)
if(isset($_GET['action']) and $_GET['action'] == "run")
{
	Database::initRoot();
	
	echo "Running Script:<br />";
	
	// Prepare Valuess
	define("PROTECTED", true);
	Config::$dataList = array();
	$siteHandle = array();
	
	// Begin tracking sites on this server (by capturing their config file)
	foreach(glob(dirname(SYS_PATH) . "/*/config.php") as $filename)
	{
		Config::$dataList[] = $filename;
	}
	
	// Begin tracking sub-sites on the server (by capturing their config file)
	foreach(glob(dirname(SYS_PATH) . "/*/*/config.php") as $filename)
	{
		Config::$dataList[] = $filename;
	}
	
	// Capture each of the site handles in the config files
	foreach(Config::$dataList as $file)
	{
		$fileContents = File::read($file);
		
		$siteHandle[] = Text_Search::between($fileContents, 'Config::$data[\'database\'][\'name\'] = "', '";');
	}
	
	// Make sure the system was able to collect the appropriate site handles
	if($siteHandle != array())
	{
		foreach($siteHandle as $sh)
		{
			if($sh)
			{
				// Attempt to initialize another database
				Database::initialize($sh, Config::$siteConfig['database']['admin-user'], Config::$siteConfig['database']['admin-pass'], Config::$siteConfig['database']['host'], Config::$siteConfig['database']['type']);
				
				// Run the System Script
				include(SYS_PATH . "/system-script.php");
				
				echo "Ran the script for the " . $sh . " database.<br />";
			}
		}
	}
	
	echo "<br /><br />Script Complete.";
}
else
{
	echo '
	<p>Are you sure you want to have all sites run the system-wide script "{SYS_PATH}/system-script.php" on the server?</p>
	<p><a class="button" href="/admin/scripts/run-system?action=run">Yes, run the script</a></p>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
