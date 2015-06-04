<?php

// Prepare Values
$dbConfigPath = "/config/Database-" . ucfirst(ENVIRONMENT) . ".php";
$userAccess = true;

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Get the database configurations
$dbConfig = Config::get("Database-" . ucfirst(ENVIRONMENT));

$dbName = Sanitize::variable(Database::$databaseName);

// Attempt to cheat
if(Database::initRoot('mysql'))
{
	Database::exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . '`');
}

// Check if the standard user is properly configured after POST values were used
if(Database::initialize($dbName))
{
	Alert::success("DB User", "The database user has access to the `" . $dbName . "` database!");
}
else
{
	Alert::error("DB User", "The `" . $dbName . "` database does not exist, or the user does not have access to it.");
	$userAccess = false;
}

// Check if the admin user is properly configured after POST values were used
if(Database::initRoot($dbName))
{
	Alert::success("DB Admin", "The administrative database user has access to the `" . $dbName . "` database!");
}
else if($userAccess)
{
	Alert::error("DB Admin", "The `" . $dbName . "` database exists, but you do not have administrative privileges.");
}
else
{
	Alert::error("DB Admin", "The `" . $dbName . "` database does not exist, or you do not have administrative privileges.");
}

// If everything is successful:
if(Validate::pass())
{
	// Check if the form was submitted (to continue to the next page)
	if(Form::submitted("install-db-connect"))
	{
		header("Location: /install/classes-core"); exit;
	}
}

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/config-database" method="post">' . Form::prepare("install-db-connect");

echo '
<h3>Update Your Database Configurations:</h3>
<p>Edit the Config File: ' . $dbConfigPath . '</p>
<p>You are working with the `<span style="font-weight:bold;">' . $dbName . '</span>` database.</p>';

if(Validate::pass())
{
	echo '
	<p><input type="submit" name="submit" value="Continue to Next Step" /></p>';
}

echo '
</form>';

// Display the Footer
require(FOOTER_PATH);