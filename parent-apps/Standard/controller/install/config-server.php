<?php

// Get list of paths
$serverConfigPath = "/config/Server-Config.php";

$serverConfig = Config::get("Server-Config");

// Make sure an appropriate environment is being used
switch($serverConfig['Environment'])
{
	case "local":
	case "development":
	case "staging":
	case "production":
		break;
	
	default:
		Alert::error("Improper Environment", "You must select a valid 'Environment' in /config/Server-Config.php");
}

// Make sure a server handle is declared
if(!$serverConfig['Server Handle'])
{
	Alert::error("Improper Environment", "You must select a valid 'Environment' in /config/Server-Config.php");
}

// If the server configuration are acceptable
if(Validate::pass())
{
	// Check if the form was submitted (to continue to the next page)
	if(Form::submitted("install-server-config"))
	{
		header("Location: /install/config-site"); exit;
	}
	
	Alert::success("Server Config", "Your server is properly configured!");
}

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/config-server" method="post">' . Form::prepare("install-server-config");

echo '
<h3>Update Your Server Configurations:</h3>
<p>Edit the Config File: ' . $serverConfigPath . '</p>
<p style="margin-top:12px;">You MUST set the following values:</p>

<p>
<style>
	.left-tb-col { width:220px; font-weight:bold; text-align:right; padding-right:10px; }
</style>
<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td class="left-tb-col">Environment:</td>
		<td>' . ($serverConfig['Environment'] ? $serverConfig['Environment'] : '<span style="color:red;">Must assign a valid Environment</span>') . '</td>
	</tr>
	<tr>
		<td class="left-tb-col">Server Name:</td>
		<td>' . ($serverConfig['Server Handle'] ? $serverConfig['Server Handle'] : '<span style="color:red;">Must choose a valid Server Name</span>') . '</td>
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