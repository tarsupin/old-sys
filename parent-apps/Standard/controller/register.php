<?php /*

-----------------------------------------
------ About the Registration Form ------
-----------------------------------------

This page provides a registration form. It will send the registration data to Auth, at which point Auth will process it and return logged in.

*/

/****** Form Submission ******/
if(Form::submitted(SITE_HANDLE . "-register-form"))
{
	// Validate the Inputs
	Validate::variable("Username", $_POST['handle'], 6, 20);
	Validate::email($_POST['email']);
	Validate::password($_POST['password']);
	Validate::confirmation("Terms of Service", isset($_POST['tos']));
	
	// If the Form Validation Passed
	if(Validate::pass())
	{
		// Retrieve the Site Key
		$apiData = API_Data::get("auth");
		
		// Save the site handshake
		$_SESSION['login']['handshake'] = Security_Hash::random(30, 62);
		
		// Prepare Custom Data
		$customData = array(
			"handshake"		=> $_SESSION['login']['handshake']
		,	"handle"		=> $_POST['handle']
		,	"email"			=> $_POST['email']
		,	"password"		=> $_POST['password']
		,	"tos"			=> isset($_POST['tos'])
		);
		
		// Create a query string with valid packet data
		$queryStringPacket = API_PacketEncrypt::queryString($customData, $apiData['site_key']);
		
		// Redirect to Auth's Registration Page
		header("Location: " . $apiData['site_url'] . "/register-process?" . $queryStringPacket); exit;
	}
}

// Prepare Values
if(!isset($_POST['handle'])) { $_POST['handle'] = ""; }
if(!isset($_POST['email'])) { $_POST['email'] = ""; }

// Display the Header
require(HEADER_PATH);

// Display the Registration Form
echo '
<form class="uniform" action="/register" method="post">' . Form::prepare(SITE_HANDLE . "-register-form") . '
	<p><input type="text" name="handle" value="' . $_POST['handle'] . '" placeholder="Username . . ." autocomplete="off" tabindex="10" autofocus /></p>
	<p><input type="text" name="email" value="' . $_POST['email'] . '" placeholder="Email . . ." autocomplete="off" tabindex="15" /></p>
	<p><input type="password" name="password" value="" placeholder="Password . . ." autocomplete="off" tabindex="20" /></p>
	<p><input type="checkbox" name="tos" ' . (isset($_POST['tos']) ? 'checked' : '') . '  tabindex="30" target="_new" /> I agree to the <a href="/tos">Terms of Service</a></p>
	<p><input class="button" type="submit" name="submit" value="Sign Up" tabindex="30" /></p>
</form>';

// Display the Footer
require(FOOTER_PATH);