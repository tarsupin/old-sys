<?php

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Make sure you have a designated handle
if(!$ownerHandle = Cookie_Server::get("admin-handle", ""))
{
	header("Location: /install/connect-handle"); exit;
}

// Prepare Values
$_POST['unifaction-api-key'] = (isset($_POST['unifaction-api-key']) ? Sanitize::text($_POST['unifaction-api-key']) : "");

// If we are connected to UniFaction, we can go to the next page
if($apiData = API_Data::get("auth"))
{
	if($response = API_Connect::to("auth", "API_IsConnected"))
	{
		Alert::saveSuccess("Connected", "You are connected to UniFaction!");
		header("Location: /install/app-custom"); exit;
	}
}

// Run the Form
if(Form::submitted("install-connect-auth"))
{
	// Create the necessary network_data value
	$key = API_Data::setData("auth", "Auth", URL::unifaction_com(), $_POST['unifaction-api-key'], true);
	
	// Set the appropriate clearance level for the auth server
	API_Data::setClearance("auth", 9);
	
	// Check if we are now connected to Auth
	$response = API_Connect::to("auth", "API_IsConnected");
	
	// "/api/private", array("run" => "validate-site"), $apiData['site_key']);
	
	if($response == true)
	{
		Alert::saveSuccess("UniFaction Connection", "You have successfully connected to UniFaction!");
		header("Location: /install/app-custom"); exit;
	}
}

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/connect-auth" method="post">' . Form::prepare("install-connect-auth");

// Display the Page
echo '
<h1>Installation: Confirm Authentication Key</h1>

<h3>Step #1 - Log into @' . $ownerHandle . ' and retrieve your Site\'s API Key</h3>
<p>Now that you\'ve set up the database on your site, you will need to connect to UniFaction\'s Authentication Server with your site\'s API key. To get the key, follow these steps:</p>
<p>&nbsp; &nbsp; &bull; <a href="' . URL::unifaction_com() . '/login">Log into your UniFaction account</a></p>
<p>&nbsp; &nbsp; &bull; Switch to @' . $ownerHandle . '\'s profile</p>
<p>&nbsp; &nbsp; &bull; Go to your <a href="' . URL::unifaction_com() . '/user-panel">Settings</a></p>
<p>&nbsp; &nbsp; &bull; Click on <a href="' . URL::unifaction_com() . '/user-panel/my-sites">"My Sites"</a></p>
<p>&nbsp; &nbsp; &bull; Click on the <a href="' . URL::unifaction_com() . '/user-panel/my-sites?confirm=' . SITE_HANDLE . '">Confirm ' . SITE_HANDLE . '</a> button.</p>
<p>&nbsp; &nbsp; &bull; Copy the API Key.</p>

<h3>Step #2 - Enter your Site\'s API Key</h3>

<p>Once you\'ve acquired your Site\'s API key from the steps above, paste it into the textbox below.</p>

<p>
	Your UniFaction API Key:<br />
	<textarea name="unifaction-api-key" style="width:95%; height:80px;">' . htmlspecialchars($_POST['unifaction-api-key']) . '</textarea>
<p>

<p><input type="submit" name="submit" value="Verify API Key" /></p>';

echo '
</form>';

// Display the Footer
require(FOOTER_PATH);