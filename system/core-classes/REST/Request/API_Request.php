<?php /*

-----------------------------------------
------ About the API_Request Class ------
-----------------------------------------

This class is used to connect with other UniFaction APIs. It treats APIs like they're a function and will automatically encrypt and decrypt the APIs so that no custom security work is necessary.

To set up an API, you will need to review the API class.


--------------------------------
------ How to call an API ------
--------------------------------

There are two ways to call APIs: a private API, and a public API. If you connect with a private API that you don't have a shared key with, UniFaction's Auth system will attempt to create a set of shared keys between the two sites. To call a private API you only need to run one line:
	
	$response = API_Request::to($siteHandle, $apiName, $dataToSend);
	
	
For example, your API may look like this:
	
	// Check if you have a connection established with "unifaction" (the Authentication site)
	$response = API_Request::to("unifaction", "API_IsConnected", "hello!");
	
	
The parameters do the following:

	$siteHandle		// The handle of the site you're trying to call (such as "unifaction", "social", "avatar", etc.)
	$apiName		// The name of the API that you're connecting to
	$dataToSend		// The variable (array, string, integer, etc) that you're passing to the API
	
You can only send one variable to the API (with $dataToSend), but it can be an array of data (which is the most common type of variable to send).

The API will provide a response, such as to indicate true or return the values you were expecting.


---------------------------------
------ Calling Public APIs ------
---------------------------------

Calling public APIs requires the use of the API_Request::call() method. You must enter the full URL path to the public API. For example:
	
	$packet = "ping";
	$response = API_Request::call(URL::unifaction_com() . "/api/PingPong", $packet);
	
You will have to know the URL public API, but the standard convention is to use the /api segment followed by the name of the API you are attempting to call.
	
	
------------------------------
------ Receiving Alerts ------
------------------------------

Sometimes an API needs to give you more of a response than just TRUE or FALSE. API's can also send alerts, such as error codes. This will provide additional information to the response in case there is something that needs to be learned about why the API failed (or, in rare cases, why it succeeded).

To access the alerts that are sent by the API, just refer to the API_Request::$alert value after the response has been gathered. For example:
	
	$response = API_Request::to($siteHandle, $apiName, $dataTosend)
	
	if($response === false)
	{
		if(API_Request::$alert)
		{
			echo "The API encountered the following error: " . API_Request::$alert;
		}
	}
	
	
--------------------------------------------------
------ Additional Settings and Instructions ------
--------------------------------------------------

You can provide additional settings and instructions with your API call to adjust it's behavior. For example:

	$settings = array(
		"encryption"	=> "fast"				// Use "fast" encryption algorithm rather than default
	,	"filepath"		=> "./path/image"		// Send a file with the API call
	);

The following settings are recognized:
	
	"encryption"	// A string. You can set the "Security_Encrypt" class algorithm you want to use. Default is "default".
	"filepath"		// A string. The filepath to the file you want to send with the API call.

	
For example, if you want to pass a file and use POST instead of GET:

	$settings = array("filepath" => $_FILES['image']['tmp_name']);
	$response = API_Request::to($siteHandle, $apiName, $dataTosend, $settings);
	
	
------------------------------
------ Debugging an API ------
------------------------------

To debug an API, you'll often need to know the data that was sent. Doing that will require you to use the URL that you sent. To retrieve the URL that was loaded, you can use the API_Request::$url value. For example:
	
	// Connect to the API
	$response = API_Request::to($siteHandle, $apiName, $dataTosend, $settings);
	
	// Get the URL that was just accessed
	echo API_Request::$url;


There are several potential issues with debugging, however. They include:
	
	1. Using POST (or PATCH, PUT, etc) instead of GET.
		
		If the connection settings used POST, the URL that you return will be invalid. You'll need to set the API settings to GET in order to get an accurate URL.
		
	2. Production servers will prevent any re-use of the same API code, and thus render this technique useless. There is an additional layer of protection for production servers, which ensures that APIs cannot be reused (to prevent illegitimate use).
	
	3. API connections have an encryption that prevents them from being valid roughly 2 minutes after they were created. This is done for security reasons. Therefore, while debugging locally, you will have to refresh the URL after 2 minutes has passed.
	
	
-------------------------------
------ Methods Available ------
-------------------------------

// Connects to an API (and returns the response)
$response = API_Request::to($siteHandle, $apiName, $apiData, [$settings]);

// This method grants more control than API_Request::to, but is more complicated to use
$response = API_Request::call($apiFullURL, $apiData, $apiKey, [$settings]);

*/

abstract class API_Request {
	
	
/****** Class Variables ******/
	public static $alert = "";		// <str> The alert received by the last API connection, if applicable.
	public static $meta = array();	// <str:mixed> Additional meta data that was sent. Will be non-encrypted.
	public static $url = "";		// <str> The exact URL that was used for the API call
	
	public static $requestMethod = "GET";	// <str> The request method used (GET, POST, PUT, PATCH, etc)
	
	
/****** Make a GET request with an API ******/
	public static function get
	(
		$apiURL				// <str> The domain of the site you're connecting to
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::get($apiURL);
	// $response = API_Request::get(URL::unifaction_com() . "/api/Users/5?field=username");
	{
		return self::prepare($apiURL, [], [], "GET");
	}
	
	
/****** Prepare an API Request ******/
	private static function prepare
	(
		$apiURL					// <str> The path to the API
	,	$apiData = array()		// <mixed> Any data to pass to the API call
	,	$settings = array()		// <str:mixed> Additional settings or instructions to provide
	,	$requestMethod = "GET"	// <str> The request method used for this API request
	)							// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::prepare($apiURL, [$apiData], [$settings], [$requestMethod]);
	// $response = API_Request::prepare(URL::unifaction_com() . "/api/Users?offset=10&limit=5", [], [], 'GET');
	{
		// Extract the API's URL data (such as domain, path, and query parameters)
		$URL = URL::parse($apiURL);
		
		$scheme = $URL['scheme'];
		$domain = $URL['host'];
		$path = isset($URL['path']) ? $URL['path'] : '';
		$queryString = isset($URL['query']) ? $URL['query'] : '';
		$queryParameters = isset($URL['queryValues']) ? $URL['queryValues'] : [];
		
		// Get the API Data
		if(!$apiData = API_Data::get($siteHandle))
		{
			// Attempt to sync the network connection automatically if the connection doesn't exist yet
			if(!API_Data::syncConnection($siteHandle, true, true))
			{
				Alert::saveError("Not Connected", "Cannot retrieve a valid connection with `" . $siteHandle . "`", 7);
				return false;
			}
			
			// Retrieve the updated information about the network connection (should have working key now)
			$apiData = API_Data::get($siteHandle);
		}
		
		// Run the API Call
		return self::call($apiData['site_url'] . "/api/" . $apiName, $apiData, $apiData['site_key'], $settings);
	}
	
	
/****** Make a POST request with an API ******/
	public static function post
	(
		$siteHandle			// <str> The handle of the site to connect to
	,	$apiName			// <str> The name of the API
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::post($siteHandle, $apiName, [$apiData], [$settings]);
	{
		self::$requestMethod = "POST";
		return self::to($siteHandle, $apiName, $apiData, $settings);
	}
	
	
/****** Make a PATCH request with an API ******/
	public static function patch
	(
		$siteHandle			// <str> The handle of the site to connect to
	,	$apiName			// <str> The name of the API
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::post($siteHandle, $apiName, [$apiData], [$settings]);
	{
		self::$requestMethod = "PATCH";
		return self::to($siteHandle, $apiName, $apiData, $settings);
	}
	
	
/****** Make a PUT request with an API ******/
	public static function put
	(
		$siteHandle			// <str> The handle of the site to connect to
	,	$apiName			// <str> The name of the API
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::put($siteHandle, $apiName, [$apiData], [$settings]);
	{
		self::$requestMethod = "PUT";
		return self::to($siteHandle, $apiName, $apiData, $settings);
	}
	
	
/****** Make a DELETE request with an API ******/
	public static function delete
	(
		$siteHandle			// <str> The handle of the site to connect to
	,	$apiName			// <str> The name of the API
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::delete($siteHandle, $apiName, [$apiData], [$settings]);
	{
		self::$requestMethod = "DELETE";
		return self::to($siteHandle, $apiName, $apiData, $settings);
	}
	
	
/****** Connect to an API ******/
	private static function to
	(
		$siteHandle			// <str> The handle of the site to connect to
	,	$apiName			// <str> The name of the API
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::to($siteHandle, $apiName, [$apiData], [$settings]);
	{
		// Get the network data
		if(!$apiData = API_Data::get($siteHandle))
		{
			// Attempt to sync the network connection automatically if the connection doesn't exist yet
			if(!API_Data::syncConnection($siteHandle, true, true))
			{
				Alert::saveError("Not Connected", "Cannot retrieve a valid connection with `" . $siteHandle . "`", 7);
				return false;
			}
			
			// Retrieve the updated information about the network connection (should have working key now)
			$apiData = API_Data::get($siteHandle);
		}
		
		// Run the API Call
		return self::call($apiData['site_url'] . "/api/" . $apiName, $apiData, $apiData['site_key'], $settings);
	}
	
	
/****** An ALL-IN-ONE call handler of an API ******/
	public static function call
	(
		$apiFullURL			// <str> The Full API URL that you're calling, including the host
	,	$apiData = ""		// <mixed> Any data to pass to the API call
	,	$apiKey = ""		// <str> The API Key that corresponds to the API you're calling
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = API_Request::call("http://example.com/api/this-api", $dataToSend, [$apiKey], [$settings]);
	{
		// If the user is calling a private API (that requires a key)
		if($apiKey != "")
		{
			// Make sure we're sending an array (so that we can pass additional encryption)
			if(!is_array($apiData))
			{
				$apiData = array('_orig' => $apiData);
			}
			
			$apiData['_enc'] = Time::unique();
			
			// Prepare API
			$apiSalt = Security_Hash::random(15, 62);
		}
		
		// Get important information about the address of the API
		$api = parse_url($apiFullURL);
		
		// Make sure the API is valid
		if(!isset($api['host']))
		{
			return false;
		}
		
		// Prepare Values
		$apiPost = "";
		$apiData = json_encode($apiData);
		$apiPath = ($api['path'] ? $api['path'] : '/');
		
		// Prepare a public API
		if($apiKey == "")
		{
			$apiPath .= (isset($api['query']) && $api['query'] != "" ? "?" . $api['query'] . '&' : "?")
					. "api=" . urlencode($apiData);
		}
		
		// Prepare a Private API
		else
		{
			// Prepare the "Encrypt" plugin algorithm to use for this encryption
			$algo = (isset($settings['encryption']) ? Sanitize::word($settings['encryption']) : "default");
			
			// If we're running in POST mode
			if(self::$requestMethod != "GET")
			{
				$apiPost = Security_Encrypt::run($apiKey . $apiSalt, $apiData, $algo);
			}
			
			// Prepare the URL String
			$apiPath .= (isset($api['query']) && $api['query'] != "" ? "?" . $api['query'] . "&" : "?")
					. "site=" . SITE_HANDLE
					. "&api=" . urlencode(Security_Encrypt::run($apiKey . $apiSalt, ($apiPost ? "" : $apiData), $algo))
					. "&salt=" . $apiSalt
					. "&conf=" . urlencode(Security_Hash::value($apiKey . $apiSalt . $apiData, 20));
		}
		
		// Set the URL that was most recently connected to
		self::$url = $api['scheme'] . "://" . $api['host'] . $apiPath;
		
		// Process the API and get the response
		$respPacket = self::processCall($api['host'], $apiPath, $apiPost, $settings);
		
		// Capture the data that was received by the API call
		$respPacket = json_decode($respPacket, true);
		
		// Retrieve the standard response from the packet returned
		if(isset($respPacket['enc']))
		{
			$response = json_decode(Security_Decrypt::run($apiKey . $apiSalt, $respPacket['resp']), true);
		}
		else
		{
			$response = $respPacket['resp'];
		}
		
		// Get the alert that was returned, if applicable
		self::$alert = isset($respPacket['alert']) ? $respPacket['alert'] : "";
		self::$meta = isset($respPacket['meta']) ? $respPacket['meta'] : array();
		
		return $response;
	}
	
	
/****** Process the Call ******/
	private static function processCall
	(
		$apiHost			// <str> The API Host that you're connecting to.
	,	$apiPath			// <str> The API Path that you're connecting to.
	,	$apiPost			// <str> The API Data that you're using (in JSON form).
	,	$settings = array()	// <str:mixed> Additional settings or instructions to provide.
	)						// RETURNS <mixed> the response of the API call
	
	// $response = self::processCall($apiHost, $apiPath, $apiPost, [$settings]);
	{
		/*
		
		
		
		Look at:
		
		https://support.ladesk.com/061754-How-to-make-REST-calls-in-PHP
		
		for details on how to make rest calls here.
		
		
		
		*/
		
		// Get CURL resource
		$curl = curl_init("http://" . $apiHost . $apiPath);
		var_dump($apiHost . $apiPath, self::$requestMethod, $apiPost);
		// If we're running a GET request
		if(self::$requestMethod == "GET")
		{
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1
			));
		}
		
		// If we're running a POST request
		else if(self::$requestMethod == "POST")
		{
			echo 'osijsdof';
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1
			,	CURLOPT_CUSTOMREQUEST => self::$requestMethod
			,	CURLOPT_POSTFIELDS => array("postData" => $apiPost)
			,	CURLOPT_POST => true
			));
			
			// If we're sending a file (such as an image)
			if(isset($settings['filepath']))
			{
				// This section should work according to php.net, but appears broken. Using deprecated option for now.
				if(false and function_exists('curl_file_create'))
				{
					$cfile = curl_file_create($settings['filepath']);
					
					$postData = array("filename" => $cfile);
				}
				else
				{
					// The same as using <input type="file" name="fileName" />
					$postData = array(
						"fileName"	=>	"@" . $settings['filepath']		// Requires the @ to send it as a file
					);
				}
				
				// Set the post sending options
				curl_setopt($curl, CURLOPT_POST, true);
				@curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			}
		}
		
		// If we're running a PUT, PATCH, or DELETE request
		else if(in_array(self::$requestMethod, ['PUT', 'PATCH', 'DELETE']))
		{
			$postData = array("postData" => $apiPost);
			
			if(self::$requestMethod == "PUT") { $postData = http_build_query($postData); }
			
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1
			,	CURLOPT_CUSTOMREQUEST => self::$requestMethod
			,	CURLOPT_POSTFIELDS => $postData
			));
		}
		
		// If the request was improper (or not yet included in our options), end here
		else
		{
			$response = false;
		}
		
		// Send the request and save the response
		$response = curl_exec($curl);
		
		// Get the error message, if applicable
		/*
		if($response === false)
		{
			$info = curl_info($curl);
			curl_close($curl);
		}
		*/
		
		// Close request to clear up some resources
		curl_close($curl);
		
		return $response;
	}
	
}
