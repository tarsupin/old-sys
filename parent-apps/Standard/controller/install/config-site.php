<?php

// Get list of paths
$pathToSiteConfig = substr(SITE_PATH, strpos(SITE_PATH, "/sites")) . "/config/Site-Config.php";
$dbConfigPath = "/config/Database-{ENVIRONMENT}.php";
$appPath = "";

// Make sure the user has named the site
if(!Config::$siteConfig['Site Name'])
{
	Alert::error("Invalid Site Name", "You must provide a valid Site Name.");
}

// Make sure the database is named
else if(!Database::$databaseName)
{
	Alert::error("Improper DB Name", "You must provide a valid Database Name.");
}

// Make sure the "Is Configured" 
else if(!Config::$siteConfig['Is Configured'])
{
	Alert::error("Unset", "You have not set the 'Is Configured' value.");
}

// Make sure that there is a valid application path
if(Config::$siteConfig['Application Path'] or Config::$siteConfig['Application'])
{
	$appPath = Config::$siteConfig['Application Path'] ? Config::$siteConfig['Application Path'] : ROOT_PATH . "/apps/" . Config::$siteConfig['Application'];
	
	if(!Dir::exists($appPath))
	{
		Alert::error("Invalid App Path", "You must set a valid application or application path.");
	}
	
	// Make the app path more human-readable
	$appPath = substr($appPath, strpos($appPath, "/app"));
}
else
{
	Alert::error("Improper App Path", "You must set a valid application or application path.");
}

// If the server configuration are acceptable
if(Validate::pass())
{
	// Check if the form was submitted (to continue to the next page)
	if(Form::submitted("install-site-config"))
	{
		header("Location: /install/config-database"); exit;
	}
	
	Alert::success("Site Config", "Your site configurations are valid!");
}

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/config-site" method="post">' . Form::prepare("install-site-config");

echo '
<h3>Update Your Site Configurations:</h3>
<p>Edit the Config File: ' . $pathToSiteConfig . '</p>
<p style="margin-top:12px;">You MUST set the following values:</p>

<p>
<style>
	.left-tb-col { width:220px; font-weight:bold; text-align:right; padding-right:10px; }
</style>
<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td class="left-tb-col">Site Name:</td>
		<td>' . (Config::$siteConfig['Site Name'] ? Config::$siteConfig['Site Name'] : '<span style="color:red;">Must set a proper Site Name</span>') . '</td>
	</tr>
	<tr>
		<td class="left-tb-col">Application Path:</td>
		<td>' . ($appPath ? $appPath : '<span style="color:red;">Must point to a valid Application Path</span>') . '</td>
	</tr>
	<tr>
		<td class="left-tb-col">A Valid Database Name:</td>
		<td>' . (Database::$databaseName ? Database::$databaseName : '<span style="color:red;">Must set a valid Database Name</span>') . '</td>
	</tr>
</table>
</p>';

if(Validate::pass())
{
	echo '
	<p><input type="submit" name="submit" value="Continue to Next Step" /></p>';
}

echo '
</form>';

// Display the Footer
require(FOOTER_PATH);