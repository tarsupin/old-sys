<?php /*

---------------------------------------------------
------ Example content for extending a Model ------
---------------------------------------------------

abstract class Example extends Model {
	
	// Class Variables
	protected static $table = "example";	// <str> The name of the table to access.
	protected static $lookupKey = "id";		// <str> Table's lookup key (column); usually primary key.
	
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
			'id'		=> [self::CANNOT_SET, self::CANNOT_MODIFY],
			'my_enum'	=> [self::CANNOT_MODIFY]
		],
		
		'index' => [
			['primary', 'id'],
			['unique', 'category, title']
		],
		
		'special' => []
	];
}

---------------------------------
------ Schemas for a Model ------
---------------------------------
Each class has a schema to define it's columns and behaviors. This will be used in forms, verification, etc.

Each column has settings, whose attributes are based on the first value (the type of the column).
Columns can be defined with the following format:
	
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
	/model/{MyClass}/view/1			// Read the contents of a single record where the lookup column == 1
	/model/{MyClass}/update/1		// Update the contents of a record where the lookup column == 1
	
	GET /api/{MyClass}				// A "GET" REST request
	POST /api/{MyClass}				// A "POST" REST request
	PUT /api/{MyClass}/1			// A "PUT" REST request where the lookup column == 1
	PATCH /api/{MyClass}/1			// A "PATCH" REST request where the lookup column == 1
	DELETE /api/{MyClass}/1			// A "DELETE" REST request where the lookup column == 1


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
	$searchArgs['sort'] = 'user_group,-username';
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
	

-------------------------------
------ Methods Available ------
-------------------------------

Model::get($lookupID, $manyRows = false, $columns = "*");
Model::exists($lookupID);
Model::search($searchArgs);

Model::create($insertData);
Model::read($lookupID, $manyRows = false);
Model::update($lookupID, $updateData);
Model::upsert($lookupID, $upsertData);	// Will insert a new row, or update existing one that it overlaps
Model::delete($lookupID);

*/

abstract class Model extends Model_Utilities {
	
	
/****** Class Variables ******/
	protected static $table = "";			// <str> The name of the table to access.
	protected static $lookupKey = "";		// <str> Table's lookup key (column); usually primary key.
	protected static $tokenExpire = 45;		// <int> Token expiration - deterrent of replay attacks.
	
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
		$lookupID			// <T> The ID of the row to retrieve (based on table's $lookupKey)
	,	$manyRows = false	// <bool> TRUE if this get will return multiple rows (for many children).
	,	$columns = "*"		// <mixed> The columns (array) or single column (string) to retrieve. Default is all.
	)						// RETURNS <str:mixed> The data from the row.
	
	// $fetchRow = static::get($lookupID, $manyRows = false, $columns = "*");
	{
		// If we're retrieving multiple columns, we need to delimit them
		if(is_array($columns))
		{
			$columns = implode(", ", $columns);
		}
		
		// If we're only retrieving one column, which is the standard
		if(!$manyRows)
		{
			return Database::selectOne("SELECT " . $columns . " FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ? LIMIT 1", array($lookupID));
		}
		
		return Database::selectMultiple("SELECT " . $columns . " FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ?", array($lookupID));
	}
	
	
/****** Check if a record exists ******/
	public static function exists
	(
		$lookupID		// <T> The ID of the row to retrieve (based on table's $lookupKey)
	)					// RETURNS <bool> The data from the row.
	
	// $recordExists = static::exists($lookupID);
	{
		return (bool) Database::selectOne("SELECT " . static::$lookupKey . " FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ? LIMIT 1", array($lookupID));
	}
	
	
/****** Traverse the class, including all of its children and joined tables ******/
	public static function traverse
	(
		$searchArgs = ['_GET']	// <array> An array of search arguments.
	,	&$rowCount = 0			// <int> The number of rows that were located in this search.
	)							// RETURNS <str:mixed> The data from the row.
	
	// $results = static::search($searchArgs = $_GET, $rowCount = 0);
	{
		return static::search($searchArgs, $rowCount, true);
	}
	
	
/****** Retrieve a row (or multiple rows) from this model's table based on search parameters ******/
	public static function search
	(
		$searchArgs = ['_GET']	// <array> An array of search arguments.
	,	&$rowCount = 0			// <int> The number of rows that were located in this search.
	,	$pullChildren = false	// <bool> TRUE if you want to pull all child records, FALSE if not.
	)							// RETURNS <str:mixed> The data from the row.
	
	// $results = static::search($searchArgs = $_GET, $rowCount = 0, $pullChildren = false);
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
		
		// Prepare the Limit / Pagination
		$limitStr = $limit ? " LIMIT " . ($offset + 0) . ", " . ($limit + 0) : "";
		
		// Get the number of rows that were possible to retrieve (for pagination purposes) 
		$rowCount = Database::selectValue("SELECT COUNT(*) as totalNum FROM `" . static::$table . "`" . ($whereStr ? " WHERE " . $whereStr : ""), $sqlArray);
		
		// Pull the rows located by the search
		$results = Database::selectMultiple("SELECT " . $columns . " FROM `" . static::$table . "`" . ($whereStr ? " WHERE " . $whereStr : "") . $orderStr . $limitStr, $sqlArray);
		
		// For searches that are not traversing the tree, we can end here.
		// Also, if there were no results, return now before trying to locate children classes.
		if($pullChildren == false or !$results)
		{
			return $results;
		}
		
		// Now for the complicated work....
		
		/*
			We have an important mapping behavior to consider:
			
			The lookup ID's of the child results are important for mapping child records BACK to their parent records.
			Therefore, we need to map the child records to the result ID's of the parent using the lookup key
			that was used to call them.
			
			For example:
				The first RESULT ID is always equal to 0. The LOOKUP KEY is "id" and it matched a LOOKUP ID of "7".
				By mapping LOOKUP ID of 7 to RESULT ID of 0, we can save the records properly.
		*/
		
		// Extract the lookup ID's from the results
		// This is essentially doing array_map()'s work, but we need to pass the lookup key, which it doesn't allow.
		$mapLookupIDToResultID = [];
		
		foreach($results as $key => $res)
		{
			$mapLookupIDToResultID[$res[static::$lookupKey]] = $key;
		}
		
		// Now that we have the IDs that were tracked, we can load them into the relationships.
		// Find the relationship classes and include them with the traversal
		foreach(static::$schema['relationships'] as $relColumn => $relatedClass)
		{
			// Get insight into the type of class that we're joining
			$relationshipType = get_parent_class($relatedClass);
			
			if($relationshipType == "Model_Join")
			{
				$joinedClass = $relatedClass::$relatedClass;
				$canHaveMany = true;
				
				// Get the child results through the join table
				$childResults = Database::selectMultiple("SELECT t1." . $relatedClass::$lookupKey . " as _t1_lookupID, t2.* FROM " . $relatedClass::$table . " t1 INNER JOIN " . $joinedClass::$table . " t2 ON t1." . $relatedClass::$relatedKey . "=t2." . $joinedClass::$lookupKey . " WHERE t1." . $relatedClass::$lookupKey . " IN (?" . str_repeat(', ?', count($mapLookupIDToResultID) - 1) . ")", array_keys($mapLookupIDToResultID));
				
				// We need to set the keys in the child results to match the ID's of the parent results
				// so that we know how they relate.
				$newChildResults = [];
				
				foreach($childResults as $row)
				{
					// We need to remove the lookup ID from the list, but preserve the value
					$recordID = $mapLookupIDToResultID[$row['_t1_lookupID']];
					unset($row['_t1_lookupID']);
					
					$newChildResults[$recordID][] = $row;
				}
				
				// Now we load the child results into the parent records
				foreach($newChildResults as $resultID => $row)
				{
					$results[$resultID][$relColumn] = $row;
				}
			}
			
			else if ($relationshipType == "Model_Child")
			{
				// Get the child records
				$childResults = Database::selectMultiple("SELECT t1.* FROM " . $relatedClass::$table . " t1 WHERE " . $relatedClass::$lookupKey . " IN (?" . str_repeat(', ?', count($mapLookupIDToResultID) - 1) . ")", array_keys($mapLookupIDToResultID));
				
				// We need to set the keys in the child results to match the ID's of the parent results
				// so that we know how they relate.
				$newChildResults = [];
				
				foreach($childResults as $row)
				{
					$newChildResults[$mapLookupIDToResultID[$row[$relatedClass::$lookupKey]]][] = $row;
				}
				
				// Now we load the child results into the parent records
				// However, we first check if there can be multiple children rows - since this
				// will affect how we append each row.
				if($relatedClass::$canHaveMany == true)
				{
					foreach($newChildResults as $resultID => $row)
					{
						$results[$resultID][$relColumn][] = $row;
					}
				}
				
				// If there is only one child record allowed, we can include the child as an exact match
				else
				{
					foreach($newChildResults as $resultID => $row)
					{
						$results[$resultID][$relColumn] = $row;
					}
				}
			}
		}
		
		return $results;
	}
	
	
/****** Create a row in this model's table ******/
	public static function create
	(
		$insertData = array()	// <str:mixed> The data to include when creating this record.
	)							// RETURNS <int> The lookup ID (i.e. "last insert ID") created for this record.
	
	// $lookupID = static::create($insertData);
	{
		// Prepare Values
		$columns = [];
		$fields = [];
		
		// Loop through the submitted data and ensure its integrity
		foreach($insertData as $column => $field)
		{
			// Make sure the column exists in the schema
			if(!isset(static::$schema['columns'][$column]))
			{
				continue;
			}
			
			// Check if there are any essential tags that need to be identified before inserting to the database
			if(static::tagExists($column, self::ENTITY_CONVERT))
			{
				$field = Data_Format::convertWindowsText($field);
			}
			
			$columns[] = $column;
			
			// Set the field based on what column type is used
			switch(static::$schema['columns'][$column][0])
			{
				case "string":
				case "text":
					$fields[] = Data_Format::safeText($field);
					break;
					
				default:
					$fields[] = $field;
			}
		}
		
		// Insert the record
		Database::query("INSERT INTO `" . static::$table . "` (" . implode(", ", $columns) . ") VALUES (?" . str_repeat(", ?", count($fields) - 1) . ")", $fields);
		
		// If we included the lookup ID in the insertion, we return that value.
		// Otherwise, the lookup ID is auto-incrementing, and we return the last ID generated by the table.
		if(isset($insertData[static::$lookupKey]))
		{
			return $insertData[static::$lookupKey];
		}
		
		return Database::$lastID;
	}
	
	
/****** Retrieve a row from this model's table ******/
	public static function read
	(
		$lookupID			// <T> The ID of the row to retrieve (based on table's $lookupKey)
	,	$manyRows = false	// <bool> TRUE if this get will return multiple rows (for many children).
	)						// RETURNS <str:mixed> The data from the row.
	
	// $fetchRow = static::read($lookupID, $many = false);
	{
		return static::get($lookupID, $manyRows);
	}
	
	
/****** Update a row in this model's table ******/
	public static function update
	(
		$lookupID				// <T> The ID of the row to update (based on table's $lookupKey)
	,	$updateData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::update($lookupID, $updateData);
	{
		if(!$lookupID) { return false; }
		
		// Prepare Values
		$setSQL = "";
		$fields = [];
		
		// Prepare the SQL string for updating each column
		foreach($updateData as $column => $field)
		{
			// Make sure the column exists in the schema
			if(!isset(static::$schema['columns'][$column]))
			{
				continue;
			}
			
			// Check if there are any essential tags that need to be identified before inserting to the database
			if(isset(static::$schema['tags'][$column]))
			{
				foreach(static::$schema['tags'][$column] as $tag)
				{
					switch($tag)
					{
						// Fix common windows characters (can cause issues with database)
						case self::ENTITY_CONVERT:
							$field = Data_Format::convertWindowsText($field);
							break;
					}
				}
			}
			
			$setSQL .= (empty($setSQL) ? "" : ", ") . "`" . $column . "`=?";
			
			// Set the field based on what column type is used
			switch(static::$schema['columns'][$column][0])
			{
				case "string":
				case "text":
					$fields[] = Data_Format::safeText($field);
					break;
					
				default:
					$fields[] = $field;
			}
		}
		
		// Add the final index
		$fields[] = $lookupID;
		
		// Update the record
		return Database::query("UPDATE `" . static::$table . "` SET " . $setSQL . " WHERE `" . static::$lookupKey . "`=? LIMIT 1", $fields);
	}
	
	
/****** Update or insert a row in this model's table ******/
	public static function upsert
	(
		$lookupID				// <T> The ID of the row to upsert (based on table's $lookupKey)
	,	$replaceData = array()	// <str:mixed> The data to include when creating this entry.
	)							// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::upsert($lookupID, $replaceData);
	{
		die("This hasn't been updated with other functionality... fix it before using.");
		
		list($columns, $fields) = [array_keys($replaceData), array_values($replaceData)];
		
		// Set the appropriate index
		array_unshift($fields, $lookupID);
		
		return Database::query("REPLACE INTO `" . static::$table . "` (`" . static::$lookupKey . "`, " . implode(", ", $columns) . ") VALUES (?" . str_repeat(", ?", count($fields) - 1) . ")", $fields);
	}
	
	
/****** Delete a record (or multiple records) in this model's table ******/
	public static function delete
	(
		$lookupID	// <T> The ID of the row to update (based on table's $lookupKey)
	)				// RETURNS <bool> TRUE if the row is created, FALSE otherwise.
	
	// static::delete($lookupID);
	{
		return Database::query("DELETE FROM `" . static::$table . "` WHERE `" . static::$lookupKey . "` = ?", array($lookupID));
	}
}
