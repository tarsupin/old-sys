<?php /*

-----------------------------------
------ Making a REST request ------
-----------------------------------

	// Prepare REST values
	$requestMethod = "POST";
	$url = "http://example.com/api/SomeAPIToCall";
	$dataToSend = json_encode(array("some", "values"));
	
	// Get the Shared Key
	$sharedKey = "THE_SHARED_KEY_BETWEEN_THE_SERVER_AND_CLIENT";
	
	// Prepare Authentication Values
	$client = $_SERVER['SERVER_HOST'];
	$publicSalt = Security_Hash::random(22, 62);
	$timestamp = time();
	
	// Prepare Authentication Token
	$authToken = base64_encode(hash('sha512', $client . $sharedKey . $publicSalt . $timestamp . $requestMethod . $url));
	
	$headers = array(
			'Tesla-Auth-Token: ' . $authToken
		,	'Tesla-Auth-Salt: ' . $publicSalt
		,	'Tesla-Auth-Timestamp: ' . $timestamp
		,	'Tesla-Auth-Client: ' . $client
		,	'Tesla-Auth-Integrity: ' . md5($authToken . $data)
	);

---------------------------------------
------ Headers for REST requests ------
---------------------------------------
	
	"Tesla-Auth-Salt" : Random Public Salt
		
		# Example:		1Fdcj6FIMgVgjOkIJpwXJq
		* This header sends a public salt to randomize the authentication token.
		* Try to keep this salt at least 22 characters long, encoded in base 64.
		
	"Tesla-Auth-Timestamp" : Unix Timestamp
		
		# Example:		1432488409
		* This header tracks the unix timestamp of when this request was made.
		* It is used to expire authentication tokens to assist with the prevention of replay attacks.
	
	"Tesla-Auth-Client" : Client Host
		
		# Example:		example.com
		* This header is the client's host.
	
	"Tesla-Auth-Token" : SHA-512 hash
		
		# Example:		YWNmZDA4NzMwNzVhOWQ0MDBhNTZmMmJjYmMzM...
		* This header provides the authentication token for this request. It is a 64-base encoded SHA-512 hash.
		* The hash includes several pieces for security purposes and integrity testing.
		* The format is as follows:
			base64_encode(hash('sha512', $domain . $sharedKey . $publicSalt . $timestamp . $requestMethod . $url));
	
	"Tesla-Auth-Integrity" : MD5 Checksum
		
		# Example:		7129210f74101d67baed7e595d188a2b
		* This header provides an MD5 checksum of the authentication token (hash), and the input sent.
		* This is used to confirm that no changes to the input were made when an authentication check is performed.
		

-------------------------------
------ Methods Available ------
-------------------------------

Model::get($resourceID, [$columns]);
Model::search($searchArgs);

Model::create($insertData);
Model::replace($resourceID, $replaceData);
Model::update($resourceID, $updateData);
Model::delete($resourceID);

*/

abstract class Model {
	
	
/****** Class Variables ******/
	const OPEN = 0;					// <int> Sets this REST request as publicly accessible - anyone can access.
	const CLOSED = 1;				// <int> Sets a REST request as being closed (inaccessible).
	const AUTHENTICATED = 10;		// <int> Requires authentication.
	const INTEGRITY = 15;			// <int> Requires authentication + integrity checks.
	const ENCRYPTED = 20;			// <int> Requires authentication and the return data will be encrypted.
	const SECURE = 30;				// <int> Requires authentication + integrity + replay prevention.
	const ENCRYPTED_SECURE = 40;	// <int> Requires authentication + integrity + replay prevention + encryption.
	
	protected static $table = "";			// <str> The name of the table to access.
	protected static $lookupKey = "";		// <str> The column acting as the table's lookup key; usually primary key.
	protected static $tokenExpire = 45;		// <int> Each auth token expires after this value in seconds.
	
	// Set the API requests that are allowed with this model
	protected static $allowRequests = [				// <int:str> The list of requests to allow
		'GET'			=> self::AUTHENTICATED		// <int> Behavior for GET requests.
	,	'GET_SEARCH'	=> self::AUTHENTICATED		// <int> Behavior for GET requests that use search arguments.
	,	'POST'			=> self::SECURE				// <int> Behavior for POST requests.
	,	'PUT'			=> self::INTEGRITY			// <int> Behavior for PUT requests.
	,	'PATCH'			=> self::INTEGRITY			// <int> Behavior for PATCH requests.
	,	'DELETE'		=> self::AUTHENTICATED		// <int> Behavior for DELETE requests.
	];
	
	
/****** Retrieve a row from this model's table ******/
	public static function get
	(
		$resourceID		// <T> The ID of the row to retrieve (based on table's $lookupKey)
	,	$columns = "*"	// <mixed> The columns (array) or single column (string) to retrieve. Default is all.
	)					// RETURNS <str:mixed> The data from the row.
	
	// $fetchRow = static::get($id, [$columns]);
	{
		// If we're retrieving multiple columns, we need to delimit them
		if(is_array($columns))
		{
			$columns = implode(", ", $columns);
		}
		
		// We're only retrieving one column
		return Database::selectOne("SELECT " . $columns . " FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ? LIMIT 1", array($resourceID));
	}
	
	
/****** Retrieve a row (or multiple rows) from this model's table based on search parameters ******/
	public static function search
	(
		$searchArgs		// <str:mixed> An array of search arguments.
	)					// RETURNS <str:mixed> The data from the row.
	
	// $results = static::search($searchArgs);
	{
		// Prepare values that handle reserved keywords
		list($limit, $offset, $orderBy, $ascending, $columns) = static::extractReservedKeywords($searchArgs);
		
		// Load the conversion
		list($whereStr, $sqlArray) = static::convertArgsToWhereSQL($searchArgs);
		
		// If we're retrieving multiple columns, we need to delimit them
		if(is_array($columns))
		{
			$columns = implode(", ", $columns);
		}
		
		// If we're only retrieving one column
		if($limit == 1)
		{
			return Database::selectOne("SELECT " . $columns . " FROM `" . static::$table . "`" . ($whereStr ? " WHERE " . $whereStr : "") . " LIMIT 1", $sqlArray);
		}
		
		// Prepare Ordering
		$ascStr = $ascending ? " ASC" : " DESC";
		$orderStr = $orderBy ? " ORDER BY " . implode($ascStr . ", ", $orderBy) . $ascStr : "";
		
		// If we're retrieving multiple columns
		return Database::selectMultiple("SELECT " . $columns . " FROM `" . static::$table . "`" . ($whereStr ? " WHERE " . $whereStr : "") . $orderStr . " LIMIT " . ($offset + 0) . ", " . ($limit + 0), $sqlArray);
	}
	
	
/****** Extract keywords from search arguments ******/
	protected static function extractReservedKeywords
	(
		&$searchArgs	// <str:mixed> An array of search arguments.
	)					// RETURNS <int:mixed> A list of important REST values.
	
	// list($limit, $offset, $orderBy, $ascending, $columns) = static::extractReservedKeywords($searchArgs);
	{
		// Prepare values to handle reserved keywords
		$limit = 1;
		$offset = 0;
		$orderBy = [];
		$ascending = true;
		$columns = "*";
		
		// Reserved Keyword: LIMIT
		if(isset($searchArgs['limit']))
		{
			$limit = $searchArgs['limit'];
			
			unset($searchArgs['limit']);
		}
		
		// Reserved Keyword: PAGE
		if(isset($searchArgs['page']))
		{
			$offset = $searchArgs['page'] * ($limit - 1);
			
			unset($searchArgs['page']);
		}
		
		// Reserved Keyword: OFFSET
		if(isset($searchArgs['offset']))
		{
			$offset += $searchArgs['offset'];
			
			unset($searchArgs['offset']);
		}
		
		// Reserved Keyword: ORDERBY
		if(isset($searchArgs['orderby']))
		{
			$orderBy = explode(",", $searchArgs['orderby']);
			
			foreach($orderBy as $key => $column)
			{
				$orderBy[$key] = Sanitize::variable($column, "-");
			}
			
			unset($searchArgs['orderby']);
		}
		
		// Reserved Keyword: ORDER
		if(isset($searchArgs['order']))
		{
			$ascending = ($searchArgs['order'] == "ASC" ? true : false);
			
			unset($searchArgs['order']);
		}
		
		// Reserved Keyword: COLUMNS
		if(isset($searchArgs['columns']))
		{
			$columns = explode(",", $searchArgs['orderby']);
			
			foreach($columns as $key => $col)
			{
				$columns[$key] = Sanitize::variable($col, "-");
			}
			
			unset($searchArgs['columns']);
		}
		
		// Return several important values for REST services
		return array($limit, $offset, $orderBy, $ascending, $columns);
	}
	
	
/****** Convert an array to the WHERE section of SQL ******/
	protected static function convertArgsToWhereSQL
	(
		$whereArray		// <str:mixed> An array of search parameters.
	)					// RETURNS <int:array> The WHERE string and the SQL Array.
	
	// list($whereStr, $sqlArray) = static::convertArgsToWhereSQL($whereArray);
	{
		// Prepare Values
		$whereString = "";
		$sqlArray = [];
		
		// Loop through the where array to identify the SQL required
		foreach($whereArray as $searchKey => $searchValue)
		{
			// Check if this search is for an equal match or not
			if(!is_array($searchValue))
			{
				$whereString .= " AND `" . Sanitize::variable($searchKey) . "` = ?";
				$sqlArray[] = $searchValue;
			}
			
			// Otherwise, we're making a specific type of search: !=, IN, LIKE, etc.
			else
			{
				// Loop through each search type for this key
				foreach($searchValue as $searchType => $value)
				{
					$tmp = null;
					
					// Determine the search type we're using
					switch(strtolower($searchType))
					{
						// NOT EQUAL TO
						case "not":
						case "!=":
							$whereString .= " AND `" . Sanitize::variable($searchKey) . "` != ?";
							$sqlArray[] = $value;
							break;
						
						// IN
						case "not in":
							$tmp = "not";
							
						case "in":
							
							$inList = explode(",", $value);
							
							// If there's only one value provided, we don't need an IN list
							if(count($inList) == 1)
							{
								$whereString .= " AND `" . Sanitize::variable($searchKey) . "` " . ($tmp == "not" ? "!" : "") . "= ?";
								$sqlArray[] = $inList[0];
							}
							
							// For multiple values, create the IN instruction
							else if(count($inList) > 1)
							{
								$whereString .= " AND `" . Sanitize::variable($searchKey) . "` " . ($tmp == "not" ? "NOT " : "") . "IN (" . rtrim(str_repeat('?, ', count($inList)), ", ") . ")";
								
								foreach($inList as $inL)
								{
									$sqlArray[] = $inL;
								}
							}
							
							// Otherwise, this command failed
							else
							{
								Alert::error("Failed Search", "Improper use of '" . ($tmp == "not" ? "NOT " : "") . "IN'.");
								return array();
							}
							
							break;
						
						// BETWEEN
						case "range":
						case "between":
							
							$value = explode(",", $value);
							
							// Make sure we're dealing with two values
							if(!is_array($value) or !isset($value[0]) or !isset($value[1]))
							{
								Alert::error("Failed Search", "Improper use of 'BETWEEN'.");
								return array();
							}
							
							$whereString .= " AND `" . Sanitize::variable($searchKey) . "` BETWEEN ? AND ?";
							$sqlArray[] = $value[0];
							$sqlArray[] = $value[1];
							
							break;
						
						// GREATER THAN
						case "gt":
						case ">":
							$whereString .= " AND `" . Sanitize::variable($searchKey) . "` > ?";
							$sqlArray[] = $value;
							break;
						
						// LESS THAN
						case "lt":
						case "<":
							$whereString .= " AND `" . Sanitize::variable($searchKey) . "` < ?";
							$sqlArray[] = $value;
							break;
					}
				}
			}
		}
		
		// Remove commas
		$whereString = substr($whereString, 4);
		
		return array($whereString, $sqlArray);
	}
	
	
/****** Create a row in this model's table ******/
	public static function create
	(
		$insertData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::create($insertData);
	{
		list($columns, $fields) = [array_keys($insertData), array_values($insertData)];
		
		return Database::query("INSERT INTO `" . static::$table . "` (" . implode(", ", $columns) . ") VALUES (?" . str_repeat(", ?", count($fields) - 1) . ")", $fields);
	}
	
	
/****** Replace a row in this model's table ******/
	public static function replace
	(
		$resourceID				// <T> The ID of the row to replace (based on table's $lookupKey)
	,	$replaceData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::replace($resourceID, $replaceData);
	{
		list($columns, $fields) = [array_keys($replaceData), array_values($replaceData)];
		
		// Set the appropriate index
		array_unshift($fields, $resourceID);
		
		return Database::query("REPLACE INTO `" . static::$table . "` (`" . static::$lookupKey . "`, " . implode(", ", $columns) . ") VALUES (?" . str_repeat(", ?", count($fields) - 1) . ")", $fields);
	}
	
	
/****** Update a row in this model's table ******/
	public static function update
	(
		$resourceID				// <T> The ID of the row to update (based on table's $lookupKey)
	,	$updateData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::update($resourceID, $updateData);
	{
		// Prepare Values
		$setSQL = "";
		$fields = [];
		
		// Prepare the SQL string for updating each column
		foreach($updateData as $column => $field)
		{
			$setSQL .= (empty($setSQL) ? "" : ", ") . "`" . $column . "`=?";
			$fields[] = $field;
		}
		
		// Add the final index
		$fields[] = $resourceID;
		
		return Database::query("UPDATE `" . static::$table . "` SET " . $setSQL . " WHERE `" . static::$lookupKey . "`=?", $fields);
	}
	
	
/****** Delete a row in this model's table ******/
	public static function delete
	(
		$resourceID	// <T> The ID of the row to update (based on table's $lookupKey)
	)				// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::delete($resourceID);
	{
		return Database::query("DELETE FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ?", array($resourceID));
	}
	
	
/****** Process a REST request ******/
	public static function processRequest (
	)					// RETURNS <str> The response to return.
	
	// static::processRequest($requestMethod, $resourceID, $queryString, $data, $domain, $resourcePath, $headers);
	{
		// Make sure that a proper table and lookup key have been provided.
		// If they aren't, this request can be ended.
		if(!static::$table or !static::$lookupKey)
		{
			return json_encode(["ERROR" => "This API does not have the proper lookup configurations set."]);
		}
		
		// Get Important Values
		$request = [
			'method'		=> $_SERVER['REQUEST_METHOD']
		,	'class'			=> get_called_class()
		,	'url'			=> URL::parse()
		,	'headers'		=> getallheaders()
		,	'input'			=> file_get_contents('php://input')
		];
		
		$requestMethod = $request['method'];
		$resourcePath = substr($request['url']['path'], strpos($request['url']['path'] . "/", $request['class'] . "/") + strlen($request['class']) + 1);
		
		// Get the resource ID to see which resource we're attempting to match
		if(!$resourceID = static::extractResourceID($resourcePath))
		{
			// This request has no specific resource to point to. There's a good chance that we're attempting
			// to GET data using an advanced search for multiple results. If this is the case, track that behavior.
			if($requestMethod == "GET")
			{
				$requestMethod = "GET_SEARCH";
			}
		}
		
		// Make sure the API Request has proper handling set up
		if(!isset(static::$allowRequests[$requestMethod]))
		{
			return json_encode(["ERROR" => "The handling of this request method is not set properly."]);
		}
		
		// Make sure this API is allowed
		if(static::$allowRequests[$requestMethod] == self::CLOSED)
		{
			return json_encode(["ERROR" => "This API is not accessible using the " . strtoupper($requestMethod) . " method."]);
		}
		
		// Check if this API requires authentication
		if(static::$allowRequests[$requestMethod] >= self::AUTHENTICATED)
		{
			// Make sure that the appropriate authentication procedures have been passed
			if(!static::authenticateRequest($request))
			{
				return json_encode(["ERROR" => "Unable to access this API: improper authentication used."]);
			}
		}
		
		// Prepare an empty response
		$response = array();
		
		// Handle the request
		switch($requestMethod)
		{
			case "GET":         $response = static::getRequest($resourceID, $request);       break;
			case "GET_SEARCH":  $response = static::getSearchRequest($request);              break;
			case "POST":        $response = static::postRequest($request);                   break;
			case "PUT":         $response = static::putRequest($resourceID, $request);       break;
			case "PATCH":       $response = static::patchRequest($resourceID, $request);     break;
			case "DELETE":      $response = static::deleteRequest($resourceID, $request);    break;
		}
		
		// Return a serialized response
		return json_encode($response);
	}
	
	
/****** Extract the resource ID from the resource path ******/
	public static function extractResourceID
	(
		$resourcePath	// <str> The URL path remaining after /api/{class}/
	)					// RETURNS <mixed> resource ID for this request; NULL if specific resource isn't set.
	
	// $resourceID = static::extractResourceID($resourcePath)
	{
		// Make sure the resource path isn't empty
		if(!$resourcePath) { return null; }
		
		// If the resource path has /'s, return the first segment as the resource ID
		if(strpos($resourcePath, "/") === false)
		{
			$resourceID = substr($resourcePath, 0, strpos($resourcePath, "/"));
		}
		
		// If the resource path has no /'s, return the full resource path as the resource ID
		return $resourcePath;
	}
	
	
/****** Authenticate the request ******/
	public static function authenticateRequest
	(
		$request	// <str:mixed> The information passed with the request.
	)				// RETURNS <bool> TRUE if the request has been authenticated, FALSE otherwise.
	
	// static::authenticateRequest($request)
	{
		// TODO: Get the configuration for which authentication system to use, then use that one
		
		// Make sure that all of the necessary headers exist
		if(
				!isset($request['headers'])
			or	!isset($request['headers']['Tesla-Auth-Client'])
			or	!isset($request['headers']['Tesla-Auth-Salt'])
			or	!isset($request['headers']['Tesla-Auth-Timestamp'])
			or	!isset($request['headers']['Tesla-Auth-Token'])
		)
		{
			return false;
		}
		
		// Extract the header data that was sent
		$clientDomain = $request['headers']['Tesla-Auth-Client'];
		$publicSalt = $request['headers']['Tesla-Auth-Salt'];
		$timestamp = $request['headers']['Tesla-Auth-Timestamp'];
		$authToken = $request['headers']['Tesla-Auth-Token'];
		
		// Make sure the timestamp isn't too old
		if(abs(time() - $timestamp) > static::$tokenExpire)
		{
			// The auth token has expired
			return false;
		}
		
		// Get the shared key between server and client
		if($sharedKey = static::getSharedKey($clientDomain))
		{
			// TODO: If there is currently no shared key, we need to generate one
			
			// No shared key - return false
			return false;
		}
		
		// Check if the Tesla Authentication passes
		if($authToken != base64_encode(hash('sha512', $clientDomain . $sharedKey . $publicSalt . $timestamp . $request['method'] . $request['url']['full'])))
		{
			// The authentication has failed
			return false;
		}
		
		// Run an integrity check to confirm data has not changed, if applicable
		if(true or in_array(static::$allowRequests[$request['method']], [self::INTEGRITY, self::SECURE, self::ENCRYPTED_SECURE]))
		{
			// Make sure that all of the necessary headers for integrity checks exist
			if(!isset($request['headers']['Tesla-Auth-Integrity']))
			{
				return false;
			}
			
			// Run the integrity test
			if($request['headers']['Tesla-Auth-Integrity'] != md5($authToken . $request['input']))
			{
				// The integrity test has failed
				return false;
			}
		}
		
		// If we're running in a secured mode, run a test against replay attacks
		if(in_array(static::$allowRequests[$request['method']], [self::SECURE, self::ENCRYPTED_SECURE]))
		{
			// Check to see if this is a replay attack
			if($test = Database::query("SELECT public_salt FROM authentication_tokens WHERE public_salt=? AND expires > ? LIMIT 1", array($publicSalt, time() - (static::$tokenExpire * 2))))
			{
				// This was identified as a potential replay attack - authentication failed
				return false;
			}
			
			// Everything has worked out; save the public salt to prevent any replay attacks
			Database::query("REPLACE INTO authentication_tokens (public_salt, expires) VALUES (?, ?)", array($publicSalt, time() + (static::$tokenExpire * 3)));
		}
		
		// The authentication has succeeded
		return true;
	}
	
	
/****** Retrieve a shared key ******/
	public static function getSharedKey
	(
		$domain		// <str> The domain of the site to get a shared key from.
	)				// RETURNS <str> The shared key.
	
	// $sharedKey = static::getSharedKey($domain);
	{
		return Database::selectValue("SELECT shared_key FROM authentication WHERE domain=? LIMIT 1", array($domain));
	}
	
	
/****** Handle a GET request ******/
	public static function getRequest
	(
		$resourceID		// <T> The index ID used to designate the appropriate row used.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <str:mixed> The resulting data found.
	
	// $response = static::getRequest($resourceID, $request);
	{
		return static::get($resourceID, "*");
	}
	
	
/****** Handle a GET request with advanced search parameters ******/
	public static function getSearchRequest
	(
		$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <str:mixed> The resulting data found.
	
	// $response = static::getSearchRequest($request);
	{
		return static::search((isset($request['url']['queryValues']) ? $request['url']['queryValues'] : []));
	}
	
	
/****** Handle a POST request ******/
	public static function postRequest
	(
		$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// $response = static::postRequest($data, $request);
	{
		return static::create($request['input']);
	}
	
	
/****** Handle a PUT request ******/
	public static function putRequest
	(
		$resourceID		// <T> The index ID used to designate the appropriate row to replace.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is updated (complete update), FALSE otherwise.
	
	// $response = static::putRequest($resourceID, $request);
	{
		return static::replace($resourceID, $request['input']);
	}
	
	
/****** Handle a PATCH request ******/
	public static function patchRequest
	(
		$resourceID		// <T> The index ID used to designate the appropriate row to update.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is updated (partial update), FALSE otherwise.
	
	// $response = static::putRequest($resourceID, $request);
	{
		return static::update($resourceID, $request['input']);
	}
	
	
/****** Handle a DELETE request ******/
	public static function deleteRequest
	(
		$resourceID		// <T> The index ID used to designate the row to delete.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is deleted, FALSE otherwise.
	
	// $response = static::deleteRequest($resourceID, $request);
	{
		return static::delete($resourceID);
	}
}
