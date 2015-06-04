<?php

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Run the Form
if(Form::submitted("install-connect-handle"))
{
	// Check if all of the input you sent is valid: 
	$_POST['handle'] = str_replace("@", "", $_POST['handle']);
	Validate::variable("UniFaction Handle", $_POST['handle'], 1, 22);
	
	if(Validate::pass())
	{
		// Make sure the handle is registered
		if($response = API_Connect::call(URL::unifaction_com() . "/api/UserRegistered", $_POST['handle']))
		{
			Cookie_Server::set("admin-handle", $_POST['handle'], "", 3);
			
			Alert::saveSuccess("Admin Chosen", "You have designated @" . $_POST['handle'] . " as the admin of your site.");
			
			header("Location: /install/config-app"); exit;
		}
		else
		{
			Alert::error("Handle Invalid", "That user handle does not exist on UniFaction.");
		}
	}
}
else
{
	$_POST['handle'] = (isset($_POST['handle']) ? Sanitize::variable($_POST['handle']) : "");
}

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

echo '
<form class="uniform" action="/install/connect-handle" method="post">' . Form::prepare("install-connect-handle");

// Display the Page
echo '
<h1>Installation: Site Admin</h1>

<h3>Step #1 - Connect Your UniFaction Handle</h3>
<p>Your desired UniFaction handle (one of your profiles) will be set as the administrator of this site, allowing that handle to access the admin functions. Note: you will need to verify that you own the handle.</p>

<p>If you don\'t have a UniFaction handle, you can set up a UniFaction account <a href="http://unifaction.com/sign-up">here</a>. The sign-up will prompt you to create a handle once you\'ve logged in for the first time.</p>

<p>Your UniFaction Handle: <input type="text" name="handle" value="' . htmlspecialchars($_POST['handle']) . '" maxlength="22" autocomplete="off" tabindex="10" autofocus /> (e.g. "@joesmith1")<p>

<p><input type="submit" name="submit" value="Continue" /></p>';

echo '
</form>';

// Display the Footer
require(FOOTER_PATH);