<?php /*

----------------------------------
------ About the Login Form ------
----------------------------------

This page provides a login form for manually logging in. It will send the handle and password to Auth, at which point Auth will process the login and return.

*/

// Determine the Return URL (if not already set)
if(!isset($_SESSION['login']['return_url']))
{
	// If this page was referred by a previous URL
	if(isset($_SERVER['HTTP_REFERER']))
	{
		$refURL = URL::parse($_SERVER['HTTP_REFERER']);
		
		$_SESSION['login']['return_url'] = '/' . (isset($refURL['path']) ? $refURL['path'] : '') . (isset($refURL['query']) ? '?' . $refURL['query'] : '');
	}
	
	// If this page was hit by a bookmark (not a link)
	else
	{
		$_SESSION['login']['return_url'] = '/';
	}
}

// If you're already logged in, return to the Return URL
if(Me::$id)
{
	unset($_SESSION['login']);
	
	header("Location: /" . $_SESSION['login']['return_url']); exit;
}

/****** Form Submission ******/
if(Form::submitted(SITE_HANDLE . "-login-form"))
{
	// Validate the Inputs
	Validate::variable("Username", $_POST['handle'], 1, 20);
	Validate::password($_POST['password']);
	
	// If the Form Validation Passed
	if(Validate::pass())
	{
		// Retrieve the Site Key
		$siteConfig = API_Data::get("auth");
		
		// Save the site handshake
		$_SESSION['login']['handshake'] = Security_Hash::random(30, 62);
		
		// Prepare Custom Data
		$customData = array(
			"handshake"		=> $_SESSION['login']['handshake']
		,	"handle"		=> $_POST['handle']
		,	"password"		=> $_POST['password']
		);
		
		// Create a query string with valid packet data
		$queryStringPacket = API_PacketEncrypt::queryString($customData, $siteConfig['site_key']);
		
		// Redirect to Auth's Login Page (get credentials and return)
		header("Location: " . $siteConfig['site_url'] . "/login-process?" . $queryStringPacket); exit;
	}
}

// Prepare Values
if(!isset($_POST['handle'])) { $_POST['handle'] = ""; }

// Display the Header
require(HEADER_PATH);

// Display the Login Form
echo '
<form class="uniform" action="/login-form" method="post">' . Form::prepare(SITE_HANDLE . "-login-form") . '
	<p><input type="text" name="handle" value="' . $_POST['handle'] . '" placeholder="Username . . ." autocomplete="off" tabindex="10" autofocus /></p>
	<p><input type="password" name="password" value="" placeholder="Password . . ." autocomplete="off" tabindex="20" /></p>
	<p><input class="button" type="submit" name="submit" value="Login with UniFaction" tabindex="30" /></p>
</form>';

// Display the Footer
require(FOOTER_PATH);