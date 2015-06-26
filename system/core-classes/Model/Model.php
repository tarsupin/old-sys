<?php /*

------------------------------------------------
------ Example data for extending a Model ------
------------------------------------------------

abstract class Example extends Model {
	
	// Class Variables
	protected static $table = "example";	// <str> The name of the table to access.
	protected static $lookupKey = "id";		// <str> The column acting as the table's lookup key; usually primary key.
	protected static $tokenExpire = 45;		// <int> Token expiration in seconds - deterrent of replay attacks.
	
	// Set the API requests that are allowed with this model
	protected static $allowRequests = [				// <int:str> The list of requests to allow
		'GET'			=> self::OPEN				// <int> Behavior for GET requests.
	,	'GET_SEARCH'	=> self::OPEN				// <int> Behavior for GET requests that use search arguments.
	,	'POST'			=> self::SECURE				// <int> Behavior for POST requests.
	,	'PUT'			=> self::INTEGRITY			// <int> Behavior for PUT requests.
	,	'PATCH'			=> self::INTEGRITY			// <int> Behavior for PATCH requests.
	,	'DELETE'		=> self::AUTHENTICATED		// <int> Behavior for DELETE requests.
	];
	
	// Schema for this Class
	public static $schema = [
		'columns' => [
			'id'			=> ['int'],
			'category'		=> ['string', 1, 32, 'variable', ' -'],
			'title'			=> ['string', 1, 32, 'variable', ' -'],
			'gender'		=> ['enum-string', 'male', 'female', 'no response'],
			'my_enum'		=> ['enum-number', 'None', 'Minor', 'Major', 'Epic'],
			'description'	=> ['text'],
			'my_boolean'	=> ['boolean', 'True', 'False'],
		],
		
		'defaults' => [
			'description' => 'See label for details'
		],
		
		'tags' => [
			'id'		=> [Schema::SET_ONCE]
		]
	];
}

---------------------------------
------ Schemas for a Model ------
---------------------------------
Each object has a schema to define it's columns and behaviors. This will be used in forms, verification, etc.

Each column defined can have a 'type' setting, which includes the following:
	
	['string', $minimumLength, $maximumLength, $sanitizeMethod, $extraChars]
	
	['tinyint',   $minRange = null, $maxRange = null]
	['smallint',  $minRange = null, $maxRange = null]
	['mediumint', $minRange = null, $maxRange = null]
	['int',       $minRange = null, $maxRange = null]
	['bigint',    $minRange = null, $maxRange = null]
	
	['float',     $minRange = null, $maxRange = null]
	['double',    $minRange = null, $maxRange = null]
	
	['enum-number', $arg1, $arg2, $arg3...]
	['enum-string', $arg1, $arg2, $arg3...]
	
	['boolean', $nameOfTrueValue, $nameOfFalseValue]
	
	['reference', 'string', $nameOfMethodToCall]
	['reference', 'array',  $nameOfMethodToCall]


---------------------------------------
------ URLS to Manipulate Models ------
---------------------------------------
/model/{MyClass}/search			// Show a table of records for the model
/model/{MyClass}/create			// Form to create a new record
/model/{MyClass}/read			// Read the contents of a single record
/model/{MyClass}/update/1		// Update the contents of a record where the primary column == 1
/model/{MyClass}/delete/1		// Delete a record where the primary column == 1

GET /api/{MyClass}					// A "GET" REST request
POST /api/{MyClass}					// A "POST" REST request
PUT /api/{MyClass}/1				// A "PUT" REST request where the primary column == 1
PATCH /api/{MyClass}/1				// A "PATCH" REST request where the primary column == 1
DELETE /api/{MyClass}/1				// A "DELETE" REST request where the primary column == 1

------------------------------------
------ Process a REST request ------
------------------------------------
REST requests are simple with models - just run the ::processRequest() on a valid URL, such as:
	GET /REST/User?limit=5
	
	// Example of processing the request
	User::processRequest();
	
---------------------------
------ CRUD Handling ------
---------------------------
	
	### Retrieve an exact record (based on ID) ###
	User::get($userID);
	
	
	### Search for a record using parameters ###
	$searchArgs = [];
	
	// The Search Filters Required
	$searchArgs['username']['like'] = 'joes*';
	$searchArgs['first_name'] = 'Joe';
	$searchArgs['age']['gt'] = 25;
	$searchArgs['age']['lt'] = 30;
	
	// Columns to Return
	$searchArgs['columns'] = 'first_name, last_name';
	
	// Pagination Options
	$searchArgs['page'] = 2;		// Page to return - uses "limit" as records per page
	$searchArgs['offset'] = 0;		// The offset to start at
	$searchArgs['orderby'] = 'user_group,username';
	$searchArgs['order'] = 'ASC';
	$searchArgs['limit'] = 5;
	
	// Retrieve the Search Results
	$results = static::search($searchArgs);
	
	
	### Create a new record ###
	$request = [
		'username'		=> 'joesmith120'
	,	'first_name'	=> 'Joe'
	,	'last_name'		=> 'Smith
	];
	
	User::create($request);
	
	
	### Update an existing record ###
	$request = [
		'first_name'	=> 'Jimmy'
	,	'last_name'		=> 'John
	];
	
	User::update($userID, $request);
	
	
	### Delete a record ###
	User::delete($userID);
	

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
	$client = $_SERVER['SERVER_NAME'];
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

Model::get($lookupID, [$columns]);
Model::search($searchArgs);

Model::create($insertData);
Model::replace($lookupID, $replaceData);
Model::update($lookupID, $updateData);
Model::delete($lookupID);

*/

abstract class Model {
	
	
/****** Class Constants ******/
	
	// REST Permissions & Accessibility
	const OPEN = 0;					// <int> Sets this REST request as publicly accessible - anyone can access.
	const CLOSED = 1;				// <int> Sets a REST request as being closed (inaccessible).
	const AUTHENTICATED = 10;		// <int> Requires authentication.
	const INTEGRITY = 15;			// <int> Requires authentication + integrity checks.
	const ENCRYPTED = 20;			// <int> Requires authentication and the return data will be encrypted.
	const SECURE = 30;				// <int> Requires authentication + integrity + replay prevention.
	const ENCRYPTED_SECURE = 40;	// <int> Requires authentication + integrity + replay prevention + encryption.
	
	// Schema Constants
	const CANNOT_MODIFY = 1;		// <int> Means this value cannot be modified.
	
	
/****** Class Variables ******/
	protected static $table = "";			// <str> The name of the table to access.
	protected static $lookupKey = "";		// <str> The column acting as the table's lookup key; usually primary key.
	protected static $tokenExpire = 45;		// <int> Token expiration in seconds - deterrent of replay attacks.
	
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
		$lookupID		// <T> The ID of the row to retrieve (based on table's $lookupKey)
	,	$columns = "*"	// <mixed> The columns (array) or single column (string) to retrieve. Default is all.
	)					// RETURNS <str:mixed> The data from the row.
	
	// $fetchRow = static::get($lookupID, [$columns]);
	{
		// If we're retrieving multiple columns, we need to delimit them
		if(is_array($columns))
		{
			$columns = implode(", ", $columns);
		}
		
		// We're only retrieving one column
		return Database::selectOne("SELECT " . $columns . " FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ? LIMIT 1", array($lookupID));
	}
	
	
/****** Retrieve a row (or multiple rows) from this model's table based on search parameters ******/
	public static function search
	(
		$searchArgs = ['_GET']	// <array> An array of search arguments.
	,	&$rowCount = 0			// <int> The number of rows that were located in this search.
	)							// RETURNS <str:mixed> The data from the row.
	
	// $results = static::search($searchArgs = $_GET, $rowCount = 0);
	{
		// If no search arguments are provided, default to using $_GET
		if($searchArgs == ['_GET']) { $searchArgs = $_GET; }
		
		// Prepare values that handle reserved keywords
		list($limit, $offset, $orderBy, $columns) = static::extractReservedKeywords($searchArgs);
		
		// Load the conversion
		list($whereStr, $sqlArray) = static::convertArgsToWhereSQL($searchArgs);
		
		// If we're retrieving multiple columns, we need to delimit them
		if(is_array($columns))
		{
			$columns = implode(", ", $columns);
		}
		
		// Prepare Ordering
		$orderStr = "";
		
		foreach($orderBy as $order)
		{
			$orderStr .= ($orderStr ? ", " : "") . $order[0] . " " . $order[1];
		}
		
		$orderStr = $orderStr ? " ORDER BY " . $orderStr : "";
		
		// Get the number of rows that were possible to retrieve (for pagination purposes) 
		$rowCount = Database::selectValue("SELECT COUNT(*) as totalNum FROM `" . static::$table . "`" . ($whereStr ? " WHERE " . $whereStr : ""), $sqlArray);
		
		// Pull the rows located by the search
		return Database::selectMultiple("SELECT " . $columns . " FROM `" . static::$table . "`" . ($whereStr ? " WHERE " . $whereStr : "") . $orderStr . " LIMIT " . ($offset + 0) . ", " . ($limit + 0), $sqlArray);
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
	
	
/****** Retrieve a row from this model's table ******/
	public static function read
	(
		$lookupID		// <T> The ID of the row to retrieve (based on table's $lookupKey)
	)					// RETURNS <str:mixed> The data from the row.
	
	// $fetchRow = static::read($lookupID);
	{
		return static::get($lookupID);
	}
	
	
/****** Update a row in this model's table ******/
	public static function update
	(
		$lookupID				// <T> The ID of the row to update (based on table's $lookupKey)
	,	$updateData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::update($lookupID, $updateData);
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
		$fields[] = $lookupID;
		
		return Database::query("UPDATE `" . static::$table . "` SET " . $setSQL . " WHERE `" . static::$lookupKey . "`=?", $fields);
	}
	
	
/****** Update or insert a row in this model's table ******/
	public static function upsert
	(
		$lookupID				// <T> The ID of the row to upsert (based on table's $lookupKey)
	,	$replaceData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::upsert($lookupID, $replaceData);
	{
		list($columns, $fields) = [array_keys($replaceData), array_values($replaceData)];
		
		// Set the appropriate index
		array_unshift($fields, $lookupID);
		
		return Database::query("REPLACE INTO `" . static::$table . "` (`" . static::$lookupKey . "`, " . implode(", ", $columns) . ") VALUES (?" . str_repeat(", ?", count($fields) - 1) . ")", $fields);
	}
	
	
/****** Delete a row in this model's table ******/
	public static function delete
	(
		$lookupID	// <T> The ID of the row to update (based on table's $lookupKey)
	)				// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::delete($lookupID);
	{
		return Database::query("DELETE FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ?", array($lookupID));
	}
	
	
/****** Extract keywords from search arguments ******/
	protected static function extractReservedKeywords
	(
		&$searchArgs	// <str:mixed> An array of search arguments.
	)					// RETURNS <int:mixed> A list of important REST values.
	
	// list($limit, $offset, $sort, $columns) = static::extractReservedKeywords($searchArgs);
	{
		// Prepare values to handle reserved keywords
		$limit = 25;
		$offset = 0;
		$sort = [];
		$columns = "*";
		
		// Reserved Keyword: LIMIT
		if(isset($searchArgs['limit']))
		{
			$limit = abs($searchArgs['limit']);
			
			unset($searchArgs['limit']);
		}
		
		// Reserved Keyword: PAGE
		if(isset($searchArgs['page']))
		{
			// Make sure you're not lower than page #1
			if($searchArgs['page'] < 1) { $searchArgs['page'] = 1; }
			
			$offset = ($searchArgs['page'] - 1) * $limit;
			
			unset($searchArgs['page']);
		}
		
		// Reserved Keyword: OFFSET
		if(isset($searchArgs['offset']))
		{
			$offset += $searchArgs['offset'];
			
			unset($searchArgs['offset']);
		}
		
		// Reserved Keyword: SORT
		if(isset($searchArgs['sort']))
		{
			$sort = explode(",", $searchArgs['sort']);
			
			// Loop through each sort option and determine if it is ascending or descending
			// Then add it to the $sort array
			foreach($sort as $key => $column)
			{
				$colPrep = Sanitize::variable($column, "-");
				$ascPrep = "ASC";
				
				if($colPrep[0] == "-")
				{
					$colPrep = substr($colPrep, 1);
					$ascPrep = "DESC";
				}
				
				$sort[$key] = [$colPrep, $ascPrep];
			}
			
			unset($searchArgs['sort']);
		}
		
		// Reserved Keyword: COLUMNS
		if(isset($searchArgs['columns']))
		{
			$columns = explode(",", $searchArgs['columns']);
			
			foreach($columns as $key => $col)
			{
				$columns[$key] = Sanitize::variable($col, "-");
			}
			
			unset($searchArgs['columns']);
		}
		
		// Return several important values for REST services
		return array($limit, $offset, $sort, $columns);
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
	
	
/****** Generate a search table (with pagination) for this schema ******/
	public static function searchForm
	(
		$searchArgs = ['_GET']	// <array> An array of search arguments.
	)							// RETURNS <str> HTML for the search table.
	
	// $tableHTML = static::searchForm($searchArgs)
	{
		// Prepare Values
		global $url, $url_relative;
		$schema = static::$schema;
		
		// If no search arguments are provided, default to using $_GET
		if($searchArgs == ['_GET']) { $searchArgs = $_GET; }
		
		// Prepare the SQL Statement
		$fetchRows = static::search($searchArgs, $rowCount);
		
		// Begin the Table
		$tableHTML = '
		<table border="1" cellpadding="4" cellspacing="0">
			<tr>
				<td>Options</td>';
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			/*
			// Identify and process tags that were listed for this column
			if(isset($schema['tags'][$columnName]))
			{
				$tags = is_array($schema['tags'][$columnName]) ? $schema['tags'][$columnName] : [$schema['tags'][$columnName]];
				
				// Check if there are any tags that the user needs to test
				foreach($tags as $tag)
				{
					switch($tag)
					{
						// If the tag cannot be modified, don't show it on the form
						//case Model::CANNOT_MODIFY: continue 3;
					}
				}
			}
			*/
			
			// Display the Column if it was located in the search
			if(isset($fetchRows[0][$columnName]))
			{
				$tableHTML .= '<td>' . ucwords(str_replace("_", " ", $columnName)) . '</td>';
			}
		}
		
		$tableHTML .= '
			</tr>';
		
		// Loop through each row
		$totalRows = count($fetchRows);
		
		for( $i = 0; $i < $totalRows; $i++ )
		{
			$row = $fetchRows[$i];
			$lookupID = $row[static::$lookupKey];
			
			$tableHTML .= '
			<tr>
				<td><a href="/' . $url_relative . '/create">C</a> <a href="/' . $url_relative . '/read?lookupID=' . $lookupID . '">R</a> <a href="/' . $url_relative . '/update?lookupID=' . $lookupID . '">U</a> <a href="/' . $url_relative . '/delete?lookupID=' . $lookupID . '">D</a></td>';
			
			// Loop through each schema column, and use that to place values appropriately
			foreach($schema['columns'] as $columnName => $columnData)
			{
				// Retrieve the appropriate value from the row and display it
				if(isset($row[$columnName]))
				{
					$tableHTML .= '
					<td>' . $row[$columnName] . '</td>';
				}
			}
			
			$tableHTML .= '
			</tr>';
		}
		
		$tableHTML .= '
		</table>';
		
		// Prepare Pagination
		$resultsPerPage = isset($searchArgs['limit']) ? (int) $searchArgs['limit'] : 25;
		$currentPage = (isset($searchArgs['page']) ? (int) $searchArgs['page'] : 1);
		
		// Construct the pagination object
		$paginate = new Pagination($rowCount, $resultsPerPage, $currentPage);
		
		// Display the Pagination
		$tableHTML .= '
		<div>Pages:';
		
		foreach($paginate->pages as $page)
		{
			if($paginate->currentPage == $page)
			{
				$tableHTML .= ' [' . $page . ']';
			}
			else
			{
				$tableHTML .= ' <a href="/' . $url_relative . '?' . Link::queryHold("columns", "sort", "limit") . "&page=" . $page . '">' . $page . '</a>';
			}
		}
		
		$tableHTML .= '
		</div>';
		
		return $tableHTML;
	}
	
	
/****** Generate a read table for this schema ******/
	public static function readForm
	(
		$lookupID		// <mixed> The ID of the record to look up.
	)					// RETURNS <str> HTML for the read form.
	
	// $tableHTML = static::readForm($lookupID)
	{
		// Prepare Values
		$schema = static::$schema;
		$table = [];
		
		// Prepare the SQL Statement
		$fetchRow = static::read($lookupID);
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			/*
			// Identify and process tags that were listed for this column
			if(isset($schema['tags'][$columnName]))
			{
				$tags = is_array($schema['tags'][$columnName]) ? $schema['tags'][$columnName] : [$schema['tags'][$columnName]];
				
				// Check if there are any tags that the user needs to test
				foreach($tags as $tag)
				{
					switch($tag)
					{
						// If the tag cannot be modified, don't show it on the form
						//case Model::CANNOT_MODIFY: continue 3;
					}
				}
			}
			*/
			
			// Display the Column if it was located in the search
			if(isset($fetchRow[$columnName]))
			{
				$table[] = [ucwords(str_replace("_", " ", $columnName)), $fetchRow[$columnName]];
			}
		}
		
		return $table;
	}
	
	
/****** Verify a CRUD form that was submitted ******/
	public static function verifyForm
	(
		$submittedData		// <str:mixed> The data submitted to the form.
	,	$lookupID = null	// <mixed> Only used for UPDATES: the value of the lookup key (to find a record).
	)						// RETURNS <void>
	
	// static::verifyForm($submittedData, $lookupID = null)
	{
		// If there is submitted data passed to the form
		if($submittedData)
		{
			$requestMethod = $lookupID ? "PATCH" : "POST";
			
			// Make sure the this CRUD action has proper handling set up
			if(!isset(static::$allowRequests[$requestMethod]))
			{
				Alert::error("CRUD Behavior", "The handling of the " . strtoupper($requestMethod) . " action is not set properly.");
				return "";
			}
			
			// Make sure this CRUD action is allowed
			if(static::$allowRequests[$requestMethod] == self::CLOSED)
			{
				Alert::error("CRUD Behavior", "The " . strtoupper($requestMethod) . " action is not allowed.");
				return "";
			}
			
			// Make sure the user is authenticated
			// <--- --->
			
			// Validate each Schema Column
			return static::verifySchema($submittedData);
		}
	}
	
	
/****** Verify schema data ******/
	public static function verifySchema
	(
		$submittedData		// <str:mixed> The data submitted to the form.
	)						// RETURNS <bool> TRUE if all tests passed, FALSE on failure.
	
	// static::verifySchema($submittedData)
	{
		// Prepare Values
		$columns = static::$schema['columns'];
		
		// Loop through each column and ensure proper verification
		foreach($columns as $columnName => $columnRules)
		{
			// Make sure this column is actually used in the submitted data - otherwise, ignore it
			if(!isset($submittedData[$columnName]))
			{
				continue;
			}
			
			// Check if the column is valid or not
			if(!static::verifySchemaColumn($columnRules, $submittedData[$columnName], $columnName))
			{
				return false;
			}
		}
		
		return true;
	}
	
	
/****** Verify a specific Schema Column ******/
	public static function verifySchemaColumn
	(
		$columnRules	// <array> The rules of the schema column to verify.
	,	$valueToSet 	// <mixed> The value to assign to this schema column.
	,	$column			// <str> The name of the column.
	)					// RETURNS <bool> TRUE if the schema is valid, FALSE if invalid.
	
	// static::verifySchemaColumn($columnRules, $valueToSet, $column)
	{
		$columnTitle = ucwords(str_replace("_", " ", $column));
		
		switch($columnRules[0])
		{
			### Strings and Text ###
			case "string":
			case "text":
				
				// Identify all string-related form variables
				$minLength = isset($columnRules[1]) ? (int) $columnRules[1] : 0;
				$maxLength = isset($columnRules[2]) ? (int) $columnRules[2] : ($columnRules[0] == "text" ? null : 250);
				
				// Make sure the data submitted is within the allowed character lengths
				$strLen = strlen($valueToSet);
				
				if($strLen < $minLength)
				{
					return Alert::error($columnTitle . ' Length', $columnTitle . " must be at least " . $minLength . " characters.");
				}
				else if($maxLength !== null and $strLen > $maxLength)
				{
					return Alert::error($columnTitle . ' Length', $columnTitle . " cannot exceed " . $maxLength . " characters.");
				}
				
				// If there is a sanitize method, run the appropriate checks
				if($sanitizeMethod = isset($columnRules[3]) ? $columnRules[3] : '')
				{
					$extraChars = isset($columnRules[4]) ? $columnRules[4] : '';
					
					return call_user_func(["IsSanitized", $sanitizeMethod], $valueToSet, $extraChars);
				}
				
				return true;
				
			### Integers ###
			case "tinyint":			// 256
			case "smallint":		// 65k
			case "mediumint":
			case "int":
			case "bigint":
				
				// Identify all string-related form variables
				$minRange = isset($columnRules[1]) ? (int) $columnRules[1] : null;
				$maxRange = isset($columnRules[2]) ? (int) $columnRules[2] : null;
				// $maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
				
				// Make sure the value is between the minimum and maximum ranges allowed
				if($valueToSet < $minRange)
				{
					return Alert::error($columnTitle . ' Range', $columnTitle . " cannot be less than " . $minRange . ".");
				}
				else if($maxLength !== null and $strLen > $maxLength)
				{
					return Alert::error($columnTitle . ' Range', $columnTitle . " cannot be greater than " . $maxRange . " characters.");
				}
				
				return true;
			
			### Floats ###
			case "float":
			case "double":
			
				// Identify all string-related form variables
				$minRange = isset($columnRules[1]) ? (double) $columnRules[1] : null;
				$maxRange = isset($columnRules[2]) ? (double) $columnRules[2] : null;
				// $maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
				
				// Make sure the value is between the minimum and maximum ranges allowed
				if($valueToSet < $minRange)
				{
					return Alert::error($columnTitle . ' Range', $columnTitle . " cannot be less than " . $minRange . ".");
				}
				else if($maxLength !== null and $strLen > $maxLength)
				{
					return Alert::error($columnTitle . ' Range', $columnTitle . " cannot be greater than " . $maxRange . " characters.");
				}
				
				return true;
			
			### Booleans ###
			case "bool":
			case "boolean":
				
				// Make sure the value is a boolean
				if($valueToSet !== 0 or $valueToSet !== 1)
				{
					return Alert::error($columnTitle . ' Boolean', $columnTitle . " must be a boolean value (0 or 1).");
				}
				
				return true;
			
			### Enumerators ###
			case "enum-number":
				
				// Get the available list of enumerators
				// These will have a numeric counter associated with each value
				$enums = array_slice($columnRules, 1);
				
				if($valueToSet < 0 or $valueToSet > count($enums))
				{
					return Alert::error($columnTitle . ' Enumerator', $columnTitle . " must be selected from the available options.");
				}
				
				return true;
				
			case "enum-string":
				
				// Get the available list of enumerators
				$enums = array_slice($columnRules, 1);
				
				if(!in_array($valueToSet, $enums))
				{
					return Alert::error($columnTitle . ' Enumerator', $columnTitle . " must be selected from the available options.");
				}
				
				return true;
		}
		
		// If we reach this point, something was wrong with the column rules formatting.
		return false;
	}
	
	
/****** Generate a creation form for this schema ******/
	public static function buildForm
	(
		$submittedData		// <str:mixed> The data submitted to the form.
	,	$lookupID = null	// <mixed> Only used for UPDATES: the value of the lookup key (to find a record).
	)						// RETURNS <str> HTML of the form.
	
	// $formHTML = static::buildForm($submittedData, $lookupID = null)
	{
		// Prepare Values
		$schema = static::$schema;
		
		// If a lookup ID was provided, retrieve database record and acknowledge as PATCH
		$resourceData = $lookupID ? static::get($lookupID) : [];
		
		// Begin the Form
		$formHTML = '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			// Identify and process tags that were listed for this column
			if(isset($schema['tags'][$columnName]))
			{
				$tags = is_array($schema['tags'][$columnName]) ? $schema['tags'][$columnName] : [$schema['tags'][$columnName]];
				
				// Check if there are any tags that the user needs to test
				foreach($tags as $tag)
				{
					switch($tag)
					{
						// If the tag cannot be modified, don't show it on the form
						case Model::CANNOT_MODIFY: continue 3;
					}
				}
			}
			
			// If data was submitted by the user, set the column's value to their input
			if(isset($submittedData[$columnName]))
			{
				$value = $submittedData[$columnName];
			}
			
			// If user input was not submitted, set a default value for the column
			else
			{
				// For update forms, use the database record as the default
				if(isset($resourceData[$columnName]))
				{
					$value = $resourceData[$columnName];
				}
				
				// Use the default value assigned by the class' schema
				else
				{
					$value = isset($schema['default'][$columnName]) ? $schema['default'][$columnName] : '';
				}
			}
			
			$formHTML .= '
			<div>
				<label for="' . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . '</label>';
			
			// Determine how to display the column 
			switch($columnRules[0])
			{
				### Strings and Text ###
				case "string":
				case "text":
					
					// Identify all string-related form variables
					$minLength = isset($columnRules[1]) ? (int) $columnRules[1] : 0;
					$maxLength = isset($columnRules[2]) ? (int) $columnRules[2] : ($columnRules[0] == "text" ? 0 : 250);
					$sanitizeMethod = isset($columnRules[3]) ? $columnRules[3] : '';
					$extraChars = isset($columnRules[4]) ? (int) $columnRules[4] : '';
					
					// Display a textarea for strings of 101 characters or more
					if(!$maxLength or $maxLength > 100)
					{
						$formHTML .= '
						<textarea id="' . $columnName . '" name="' . $columnName . '"'
							. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '>' . htmlspecialchars($value) . '</textarea>';
					}
					
					// Display a text input for a string of 100 characters or less
					else
					{
						$formHTML .= '
						<input id="' . $columnName . '" type="text"
							name="' . $columnName . '"
							value="' . htmlspecialchars($value) . '"'
							. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '
							/>';
					}
					
					break;
					
				### Integers ###
				case "tinyint":			// 256
				case "smallint":		// 65k
				case "mediumint":
				case "int":
				case "bigint":
					
					// Identify all string-related form variables
					$minRange = isset($columnRules[1]) ? (int) $columnRules[1] : null;
					$maxRange = isset($columnRules[2]) ? (int) $columnRules[2] : null;
					$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
					
					// Display the form field for an integer
					$formHTML .= '
					<input id="' . $columnName . '" type="number"
						name="' . $columnName . '"
						value="' . ((int) $value) . '"'
						. ($maxLength ? 'maxlength="' . $maxLength . '"' : '')
						. ($minRange ? 'min="' . $minRange . '"' : '')
						. ($maxRange ? 'max="' . $maxRange . '"' : '') . '
						/>';
					
					break;
				
				### Floats ###
				case "float":
				case "double":
				
					// Identify all string-related form variables
					$minRange = isset($columnRules[1]) ? (int) $columnRules[1] : null;
					$maxRange = isset($columnRules[2]) ? (int) $columnRules[2] : null;
					$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
					
					// Display the form field for an integer
					$formHTML .= '
					<input id="' . $columnName . '" type="text"
						name="' . $columnName . '"
						value="' . ((int) $value) . '"'
						. ($maxLength ? 'maxlength="' . ($maxLength + ceil($maxLength / 3)) . '"' : '') . '
						/>';
					
					break;
				
				### Booleans ###
				case "bool":
				case "boolean":
					
					// If the boolean types are not declared, set defaults
					$trueName = isset($columnRules[1]) ? $columnRules[1] : 'True';
					$falseName = isset($columnRules[2]) ? $columnRules[2] : 'False';
					
					// Display the form field for a boolean
					$formHTML .= str_replace('value="' . $value . '"', 'value="' . $value . '" selected', '
					<select id="' . $columnName . '" name="' . $columnName . '">
						<option value="1">' . htmlspecialchars($trueName) . '</option>
						<option value="0">' . htmlspecialchars($falseName) . '</option>
					</select>');
					break;
				
				### Enumerators ###
				case "enum-number":
				case "enum-string":
					
					// Get the available list of enumerators
					$enums = array_slice($columnRules, 1);
					
					// Display the form field for a boolean
					$formHTML .= '
					<select id="' . $columnName . '" name="' . $columnName . '">';
					
					// Handle numeric enumerators differently than string enumerators
					// These will have a numeric counter associated with each value
					if($columnRules[0] == "enum-number")
					{
						$enumCount = count($enums);
						
						for( $i = 0; $i < $enumCount; $i++ )
						{
							$formHTML .= '
							<option value="' . $i . '"' . ($value == $i ? ' selected' : '') . '>'  . htmlspecialchars($enums[$i]) . '</option>';
						}
					}
					
					// String Enumerators
					else
					{
						foreach($enums as $enum)
						{
							$formHTML .= '
							<option value="' . htmlspecialchars($enum) . '"' . ($value == $enum ? ' selected' : '') . '>' . htmlspecialchars($enum) . '</option>';
						}
					}
					
					$formHTML .= '
					</select>';
					
					break;
			}
			
			$formHTML .= '
			</div>';
		}
		
		// End the Form
		$formHTML .= '
		<div>
			<label for="submit">Submit</label>
			<input type="submit" name="submit" value="Submit" />
		</div>
		</form>';
		
		return $formHTML;
	}
	
	
/****** Determine the maximum length of a number based on the variable type ******/
	private static function getLengthOfNumberType
	(
		$numericType		// <str> The type of number: tinyint, mediumint, int, long, etc.
	,	$minRange = null	// <int> 
	,	$maxRange = null	// <int> 
	)						// RETURNS <str> HTML to insert into the form.
	
	// $lengthOfNumType = self::getLengthOfNumberType('int');
	{
		$baseSize = 0;
		$extraSize = 0;
		
		switch($numericType)
		{
			case "tinyint":		$baseSize = 3;
			case "smallint":	$baseSize = 5;
			case "mediumint":	$baseSize = 8;
			case "int":			$baseSize = 11;
			case "integer":		$baseSize = 11;
			case "long":		$baseSize = 16;
			
			// These values need to account for an additional "."
			case "float":		$baseSize = 10;   $extraSize = 1;
			case "double":		$baseSize = 20;   $extraSize = 1;
		}
		
		// If there is a minimum or maximum range provided, check custom behavior
		if($minRange or $maxRange)
		{
			// Set default ranges to 0
			if(!$minRange) { $minRange = 0; }
			if(!$maxRange) { $maxRange = 0; }
			
			// If negative values are allowed, the "-" can increase the field size by 1
			if($minRange < 0 or $maxRange < 0)
			{
				$extraSize += 1;
			}
			
			// Determine what the maximum length of the range allows
			$sizeOfRange = strlen((string) max(abs($minRange), abs($maxRange)));
			
			// Shrink the maximum size to the highest range allowed
			$baseSize = min($baseSize, $sizeOfRange);
		}
		
		// Return the maximum size of the field allowed
		return ($baseSize + $extraSize);
	}
	
	
/****** Process a REST request ******/
	public static function processRequest (
	)					// RETURNS <str> The response to return.
	
	// static::processRequest();
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
		
		// Get the lookup ID to see which record we're attempting to match
		if(!$lookupID = static::extractLookupID($resourcePath))
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
			case "GET":         $response = static::getRequest($lookupID, $request);       break;
			case "GET_SEARCH":  $response = static::getSearchRequest($request);              break;
			case "POST":        $response = static::postRequest($request);                   break;
			case "PUT":         $response = static::putRequest($lookupID, $request);       break;
			case "PATCH":       $response = static::patchRequest($lookupID, $request);     break;
			case "DELETE":      $response = static::deleteRequest($lookupID, $request);    break;
		}
		
		// Return a serialized response
		return json_encode($response);
	}
	
	
/****** Extract the resource ID from the resource path ******/
	public static function extractLookupID
	(
		$resourcePath	// <str> The URL path remaining after /api/{class}/
	)					// RETURNS <mixed> resource ID for this request; NULL if specific resource isn't set.
	
	// $lookupID = static::extractLookupID($resourcePath)
	{
		// Make sure the resource path isn't empty
		if(!$resourcePath) { return null; }
		
		// If the resource path has /'s, return the first segment as the resource ID
		if(strpos($resourcePath, "/") === false)
		{
			$lookupID = substr($resourcePath, 0, strpos($resourcePath, "/"));
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
		$lookupID		// <T> The index ID used to designate the appropriate row used.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <str:mixed> The resulting data found.
	
	// $response = static::getRequest($lookupID, $request);
	{
		return static::get($lookupID, "*");
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
	
	// $response = static::postRequest($request);
	{
		return static::create($request['input']);
	}
	
	
/****** Handle a PUT request ******/
	public static function putRequest
	(
		$lookupID		// <T> The index ID used to designate the appropriate row to replace.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is updated (complete update), FALSE otherwise.
	
	// $response = static::putRequest($lookupID, $request);
	{
		return static::update($lookupID, $request['input']);
	}
	
	
/****** Handle a PATCH request ******/
	public static function patchRequest
	(
		$lookupID		// <T> The index ID used to designate the appropriate row to update.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is updated (partial update), FALSE otherwise.
	
	// $response = static::putRequest($lookupID, $request);
	{
		return static::update($lookupID, $request['input']);
	}
	
	
/****** Handle a DELETE request ******/
	public static function deleteRequest
	(
		$lookupID		// <T> The index ID used to designate the row to delete.
	,	$request		// <str:mixed> The information passed with the request.
	)					// RETURNS <bool> TRUE if the row is deleted, FALSE otherwise.
	
	// $response = static::deleteRequest($lookupID, $request);
	{
		return static::delete($lookupID);
	}
}
