<?php /*

----------------------------------
------ URL & MISC CONSTANTS ------
----------------------------------
	
	
	SUB_DOMAIN			// Only the subdomain, such as "forum"
	BASE_DOMAIN			// The base domain, such as "unifaction.com"
	FULL_DOMAIN			// The full domain, such as "forum.unifaction.com"
	
	SITE_URL			// The FULL_DOMAIN with "http://" in front of it, and the URL_SUFFIX (.local) if applicable
	
	CDN					// The URL of the CDN to call from.
	
	URL_SUFFIX			// A value to append to URL's for different environments (e.g. ".local")
	
	
---------------------------------
------ DIRECTORY CONSTANTS ------
---------------------------------
	
	ROOT_PATH			// The base web directory
	SYS_PATH			// The path to the system
	APP_PATH			// The path to the application being loaded
	PARENT_APP_PATH		// The path to the application's parent app (inherits any 
	SITE_PATH			// The directory that was loaded first and contains the configurations
	
	
-----------------------------
------ THEME CONSTANTS ------		# These get generated using the /Config/Theme.php files
-----------------------------

	THEME				// The name of the current theme being used (default is "basic")
	THEME_PATH			// The directory to the theme to use
		
		HEADER_PATH
		FOOTER_PATH
		STYLE_PATH
	
	
------------------------------
------ Important Values ------
------------------------------
	
	$url_relative			// The URL segments as one; e.g. "/friends/requests/Joe"
	$url					// An array of the URL segments; e.g. $url = array("friends", "requests", "Joe");
	
	$_SESSION[SITE_HANDLE]	// Store the active user's session data
	
*/

/****** Prepare Important Paths ******/
define("ROOT_PATH",		dirname(__DIR__));
define("SYS_PATH", 		ROOT_PATH . "/system");

/****** CLI Handler ******/
define("CLI", strpos(php_sapi_name(), "cli") !== false);

// If loading through the CLI, identify the site's location based on the active directory
if(CLI)
{
	$filesLoaded = get_included_files();
	
	$_SERVER['SERVER_NAME'] = basename(dirname($filesLoaded[0])) . '.' . basename(dirname(dirname($filesLoaded[0])));
	$_SERVER['REQUEST_URI'] = "/";
}


/****** Extract $url and $url_relative ******/
$url = $_SERVER['REQUEST_URI'];

// Strip out any query string data (if used)
$url_relative = explode("?", rawurldecode($url));

// Sanitize any unsafe characters from the URL
$url_relative = trim(preg_replace("/[^a-zA-Z0-9_\-\/\.\+]/", "", $url_relative[0]), "/");

// Section the URL into multiple segments so that each can be added to the array individually
$url = explode("/", $url_relative);


/****** Load Server Configurations ******/

// Make sure the Server-Config configuration exists.
if(!file_exists(ROOT_PATH . '/config/Server-Config.php'))
{
	die("Site Cannot Load: /config/Server-Config.php file does not exist.");
}

// Loop through each configuration and convert to constants
foreach(require(ROOT_PATH . '/config/Server-Config.php') as $config => $value)
{
	define(str_replace(" ", "_", strtoupper($config)), $value);
}

// Make sure that the environment was set
if(!ENVIRONMENT)
{
	die("Site Cannot Load: You must finish configuring /config/Server-Config.php");
}


/****** Error Reporting ******/
// Report errors locally, but not on staging or production 
error_reporting(E_ALL);
ini_set("display_errors", ENVIRONMENT == "local" ? 1 : 0);


// Set the local URL Suffix, if applicable
// This value is used to append a localhost domain, such as "mydomain.com.local" where ".local" is the URL_SUFFIX.
define("URL_SUFFIX", (ENVIRONMENT == "local" ? ".local" : ""));

// Prepare Domains
define("FULL_DOMAIN", str_replace(URL_SUFFIX, "", strtolower($_SERVER['SERVER_NAME'])));

/****** Determine Component Routing ******/

// Check if a base domain is being used, and if we have it registered
if(is_dir(ROOT_PATH . "/sites/" . FULL_DOMAIN . "/www"))
{
	// Prepare Domains
	define("SUB_DOMAIN",	"");
	define("BASE_DOMAIN",	FULL_DOMAIN);
	
	// Determine the SITE_PATH - identify additional components to be routed by URL segment
	if(is_file(ROOT_PATH . "/sites/" . FULL_DOMAIN . "/www/" . $url[0] . "/index.php"))
	{
		// Set the SITE_PATH to the component's directory
		define("SITE_PATH",		ROOT_PATH . "/sites/" . FULL_DOMAIN . "/www/" . $url[0]);
		
		// Remove the first segment from the URL
		array_shift($url);
		
		// Required to make sure a $url[0] exists
		if(!$url) { $url = array(""); }
	}
	else
	{
		// Set the SITE_PATH to the main directory
		define("SITE_PATH",		ROOT_PATH . "/sites/" . FULL_DOMAIN . "/www");
	}
}

// Check if a subdomain is being used, and if we have it registered
else
{
	// Use value to check domains
	$url_domain = explode(".", FULL_DOMAIN);
	
	// Prepare Domains
	define("SUB_DOMAIN",	$url_domain[0]);
	define("BASE_DOMAIN",	implode(".", array_slice($url_domain, 1)));
	
	// Determine the SITE_PATH - identify additional components to be routed by URL segment
	if(is_file(ROOT_PATH . "/sites/" . BASE_DOMAIN . "/" . SUB_DOMAIN . "/" . $url[0] . "/index.php"))
	{
		// Set the SITE_PATH to the subdomain's component directory
		define("SITE_PATH",		ROOT_PATH . "/sites/" . BASE_DOMAIN . "/" . SUB_DOMAIN . "/" . $url[0]);
		
		// Remove the first segment from the URL
		array_shift($url);
		
		// Required to make sure a $url[0] exists
		if(!$url) { $url = array(""); }
	}
	else
	{
		// Set the SITE_PATH to the subdomain's directory
		define("SITE_PATH",		ROOT_PATH . "/sites/" . BASE_DOMAIN . "/" . SUB_DOMAIN);
		
		// Make sure the site path exists, otherwise exit early with a 404 page
		if(!is_dir(SITE_PATH))
		{
			die("This domain has not been created yet: must create this entry in /sites/" . BASE_DOMAIN . "/" . SUB_DOMAIN);
		}
	}
}

// Set Site URL
define("SITE_URL",		"http://" . strtolower($_SERVER['SERVER_NAME']));

/****** Prepare the Auto-Loader ******/
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

// Assign the appropriate autoloader
require(SYS_PATH . "/autoloader" . (CLI ? "-cli" : "") . ".php");


/****** Directory-Based Environment Handler ******/
// Some webmasters may use directories (e.g. "C:/www/mysite/") instead of a host (e.g. "mysite.local")
// If we're on a localhost system, check for this behavior and remove if necessary
if(ENVIRONMENT == "local")
{
	$defSegments = explode("/", rtrim(str_replace("\\", "/", ROOT_PATH), "/"));
	
	for($a = count($defSegments);$a >= 0;$a--)
	{
		$lastSegment = $defSegments[$a - 1];
		
		// Remove the segment
		if(($key = array_search($lastSegment, $url)) === false)
		{
			break;
		}
		
		unset($url[$key]);
	}
	
	// Reset the URL values as necessary
	$url = array_values($url);
	$url_relative = implode("/", $url);
}

// Load the Config plugin early (before autoloader) - we require it for the setup process
require(SYS_PATH . "/core-classes/Config/Config.php");

/****** Run Site Setup ******/
if((!Config::$siteConfig = require(SITE_PATH . "/config/Site-Config.php")) or !Config::$siteConfig['Is Configured'])
{
	// Make sure the site configuration has been setup
	die("You must complete this site's configurations in /sites/{YOUR_SITE}/{SUBDOMAIN}/config/Site-Config.php");
}

// Set the application path
if(Config::$siteConfig['Application Path'])
{
	define("APP_PATH", Config::$siteConfig['Application Path']);
}
else
{
	define("APP_PATH", ROOT_PATH . "/apps/" . Config::$siteConfig['Application']);
}

// Set the site handle and salt
define("SITE_HANDLE", Config::$siteConfig['Site Handle']);

// Set the default timezone
date_default_timezone_set((Config::$siteConfig['Timezone'] ? Config::$siteConfig['Timezone'] : 'America/Los_Angeles'));

// Set the CDN (Content Delivery Network)
define("CDN", (Config::$siteConfig['CDN'] ? Config::$siteConfig['CDN'] : "http://" . FULL_DOMAIN . URL_SUFFIX));


/****** Build the Application Constants ******/
Config::convertToConstants("Setup", APP_PATH);

if(!defined("PARENT_APP") or empty(PARENT_APP))
{
	define("PARENT_APP", "Empty");
}

// Set the appropriate parent application path
define("PARENT_APP_PATH", ROOT_PATH . "/parent-apps/" . PARENT_APP);


/****** Set the database to connect to ******/
Database::$databaseName = Config::$siteConfig['Database Name'] ? Config::$siteConfig['Database Name'] : Config::$siteConfig['Site Handle'];


/****** Session Handling ******/
if(USE_SESSIONS)
{
	session_name(SERVER_HANDLE);
	session_set_cookie_params(0, '/', '.' .  BASE_DOMAIN . URL_SUFFIX);
	
	session_start();
}


/****** Prepare the Database Connection ******/
if(USE_DATABASE)
{
	Database::initialize(Database::$databaseName);
	
	// Make sure a connection to the database was created
	if(Database::$database)
	{
		// Make sure the base session value used is available
		if(!isset($_SESSION[SITE_HANDLE]))
		{
			$_SESSION[SITE_HANDLE] = array();
		}
		
		/****** Process Security Functions ******/
		Security_Fingerprint::run();
		
		/****** Setup Custom Error Handler ******/
		require(SYS_PATH . "/error-handler.php");
	}
	else
	{
		// If we're installing the system
		if(!$url[0] == "install")
		{
			die("There was an issue connecting to the database. Likely issues: wrong user/pass credentials or the table is missing.");
		}
	}
}


/****** CLI Routing ******/
if(CLI)
{
	// Load the appropriate script being called
	if(isset($_SERVER['argv'][1]))
	{
		if($scriptPath = realpath(SYS_PATH . "/cli/" . $_SERVER['argv'][1] . ".php"))
		{
			require($scriptPath); exit;
		}
	}
	
	// If no script was called, load the CLI menu
	require(SYS_PATH . "/cli/menu.php"); exit;
}

/****** Build the Theme ******/
Config::convertToConstants("Theme");

/****** Metadata Handler ******/
Metadata::load();

/****** Identify the Device (1 = mobile, 2 = tablet, 3 = device) ******/
if(!isset($_SESSION['device']))
{
	$device = new DetectDevice();
	
	if($device->isMobile())
	{
		$_SESSION['device'] = 1;
	}
	else if($device->isTablet())
	{
		$_SESSION['device'] = 2;
	}
	else
	{
		$_SESSION['device'] = 3;
	}
}

/****** Quick-Load and Unique Behavior for Certain Files ******/
switch($url[0])
{
	case "api":
	case "script":
		require(PARENT_APP_PATH . "/controller/" . $url[0] . ".php"); exit;
	
	case "login":
	case "ajax":
		break;
}

/****** Load the Active User ******/
if(USE_LOGIN)
{
	Me::load();
	
	// Check if we should attempt an automatic login
	if(!Me::$id and !isset($_SESSION['attempted_login']) and $url[0] != "install")
	{
		$_SESSION['attempted_login'] = true;
		
		header("Location: /login?action=autolog"); exit;
	}
}

// Determine which page you should point to, then load it
require(SYS_PATH . "/routes.php");

/****** Custom Routing ******/
// Check if the Application or Parent Application have any custom routing - process accordingly.
foreach([APP_PATH, PARENT_APP_PATH] as $path)
{
	if(file_exists($path . "/routes-custom.php"))
	{
		require($path . "/routes-custom.php");
	}
}

/****** 404 Page ******/
// If the routes.php file or dynamic URLs didn't load a page (and thus exit the scripts), run a 404 page.
require(PARENT_APP_PATH . "/controller/404.php");