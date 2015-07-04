<?php /*

---------------------------------------------
------ About the Model_Utilities Class ------
---------------------------------------------


------------------------------------
------ Process a REST request ------
------------------------------------
REST requests are simple with models - just run the ::processRequest() on a valid URL, such as:
	GET /REST/User?limit=5
	
	// Example of processing the request
	User::processRequest();
	

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
		

*/


abstract class Model_Utilities {
	
	
/****** Class Constants ******/
	
	// REST Permissions & Accessibility
	const OPEN = 0;					// <int> Sets this REST request as publicly accessible - anyone can access.
	const CLOSED = 1;				// <int> Sets a REST request as being closed (inaccessible).
	const AUTHENTICATED = 10;		// <int> Requires authentication.
	const INTEGRITY = 15;			// <int> Requires authentication + integrity checks.
	const ENCRYPTED = 20;			// <int> Requires authentication and the return data will be encrypted.
	const SECURE = 30;				// <int> Requires authentication + integrity + replay prevention.
	const ENCRYPTED_SECURE = 40;	// <int> Requires authentication + integrity + replay prevention + encryption.
	
	// Schema Tag Constants
	const CANNOT_SET = 1;			// <int> This value can be set on creation.
	const CANNOT_MODIFY = 2;		// <int> This value cannot be modified (updated).
	const ENTITY_CONVERT = 3;		// <int> Convert common windows-based entities to normal (", ', etc).
	
	
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
			$limit = $searchArgs['limit'] ? abs($searchArgs['limit']) : 0;
			
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
		$class = get_called_class();
		$schema = static::$schema;
		
		$columnSorted = isset($_GET['sort']) ? Sanitize::variable($_GET['sort']) : null;
		
		// If no search arguments are provided, default to using $_GET
		if($searchArgs == ['_GET']) { $searchArgs = $_GET; }
		
		// Prepare the SQL Statement
		$fetchRows = static::search($searchArgs, $rowCount);
		
		// Begin the Table
		// Note that we need to set a fake value ~opts~ for the options menu on the left
		$tableData = ['head' => ["~opts~" => "Options"], 'data' => []];
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			// If we're currently sorting by this column, provide special behavior to show this
			if($columnSorted == $columnName)
			{
				if($_GET['sort'][0] == '-')
				{
					$tableData['head'][$columnName] = '<a href="/model/' . $class . '?' . Link::queryHold("columns", "limit", "page") . "&sort=" . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . ' ^</a>';
				}
				else
				{
					$tableData['head'][$columnName] = '<a href="/model/' . $class . '?' . Link::queryHold("columns", "limit", "page") . "&sort=-" . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . ' v</a>';
				}
			}
			else
			{
				$tableData['head'][$columnName] = '<a href="/model/' . $class . '?' . Link::queryHold("columns", "limit", "page") . "&sort=" . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . '</a>';
			}
			
			// If this column has special visibility rules, update the values here
			
			// Numeric enumerators should be shown with their text values
			if($columnRules[0] == "enum-number")
			{
				foreach($fetchRows as $key => $row)
				{
					$fetchRows[$key][$columnName] = $row[$columnName] . ": " . static::$schema['columns'][$columnName][1 + $row[$columnName]];
				}
			}
		}
		
		// Loop through each row
		$totalRows = count($fetchRows);
		
		for( $i = 0; $i < $totalRows; $i++ )
		{
			$row = $fetchRows[$i];
			$lookupID = $row[static::$lookupKey];
			
			$tableData['data'][$i][] = '<a href="/model/' . $class . '/view/' . $lookupID . '">V</a> <a href="/model/' . $class . '/update/' . $lookupID . '">U</a>';
			
			// Loop through each schema column, and use that to place values appropriately
			foreach($tableData['head'] as $columnName => $_ignore)
			{
				// Make sure the value exists in the schema
				if(!isset($schema['columns'][$columnName])) { continue; }
				
				// Add a Standard Row to the table
				if(isset($row[$columnName]))
				{
					$tableData['data'][$i][] = $row[$columnName];
				}
			}
		}
		
		// Prepare Pagination
		$resultsPerPage = isset($searchArgs['limit']) ? (int) $searchArgs['limit'] : 25;
		$currentPage = (isset($searchArgs['page']) ? (int) $searchArgs['page'] : 1);
		
		// Construct the pagination object
		$paginate = new Pagination($rowCount, $resultsPerPage, $currentPage);
		
		// Display the Pagination
		$tableData['footer'] = '
		<div>Pages:';
		
		foreach($paginate->pages as $page)
		{
			if($paginate->currentPage == $page)
			{
				$tableData['footer'] .= ' [' . $page . ']';
			}
			else
			{
				$tableData['footer'] .= ' <a href="/model/' . $class . '?' . Link::queryHold("columns", "sort", "limit") . "&page=" . $page . '">' . $page . '</a>';
			}
		}
		
		$tableData['footer'] .= '
		</div>';
		
		return $tableData;
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
				// Check if there are any tags that the user needs to test
				foreach($schema['tags'][$columnName] as $tag)
				{
					switch($tag)
					{
						
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
	
	
/****** Process a CRUD form that was submitted ******/
	public static function processForm
	(
		$submittedData		// <str:mixed> The data submitted to the form.
	,	$lookupID = null	// <mixed> Only used for UPDATES: the value of the lookup key (to find a record).
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// static::processForm($submittedData, $lookupID = null)
	{
		// We need to track what child data gets submitted
		$classSubmissions = [];
		$relatedClassList = [];
		
		// Make sure there is submitted data passed to the form
		if(!$submittedData) { return false; }
		
		// Loop through the schema's relationships and see if they were part of the submission
		// For example, if the "User" form includes "User_ContactData" in its relationships, check to see if
		// any "User_ContactData" records were included in this form.
		foreach(static::$schema['relationships'] as $relationName => $relatedClass)
		{
			// If a record of the relationship was found, we need to verify those values.
			// Otherwise, skip to the next relationship.
			if(!isset($submittedData[$relationName]))
			{
				continue;
			}
			
			$relatedClassList[] = $relatedClass;
			
			// A related class has content posted in this form.
			// Loop through the submitted data to extract the relevant pieces and verify it.
			foreach($submittedData[$relationName] as $postedData)
			{
				$relatedClassType = get_parent_class($relatedClass);
				
				// Classes with the "Child" model are verified differently than classes with "Join" models
				if($relatedClassType == "Model_Child")
				{
					// If the user didn't change the default values (or left the row empty), we assume that they
					// didn't want to create that record. Therefore, we don't check that row for errors.
					if(!$relatedClass::checkIfSubmissionIsEmpty($postedData))
					{
						if($relatedClass::verifySchema($postedData))
						{
							// Track this submission data so that it can be processed into the database - but only
							// after the main table is processed (needs the lookup ID) and only if everything was
							// processed successfully.
							$classSubmissions[$relatedClass][] = $postedData;
						}
					}
				}
				else if($relatedClassType == "Model_Join")
				{
					// If the data provided isn't empty, just verify that it's a valid ID being linked
					if($postedData != "")
					{
						// Get the class that was being joined
						$joinedClass = $relatedClass::$relatedClass;
						
						// Make sure the record exists
						if(!$joinedClass::exists($postedData))
						{
							Alert::error($relationName . " Failed", "Provided an invalid option for `" . $relationName . "`.");
							continue;
						}
						
						// Track this submission data so that it can be processed later.
						$classSubmissions[$relatedClass][] = [$relatedClass::$relatedKey => $postedData];
					}
				}
			}
		}
		
		// Validate each Schema Column
		static::verifySchema($submittedData);
		
		// Check if the entire form validated successfully
		if(!Validate::pass())
		{
			// The form failed - end here with a failure
			return false;
		}
		
		// The form was successful. We need to run a series of updates on the database.
		// Begin a Database Transaction
		Database::startTransaction();
		
		// Create the Primary Table (if we were doing a "CREATE" form)
		if(!$lookupID)
		{
			$lookupID = static::create($submittedData);
		}
		
		// Update the Primary Table (if we were doing an "UPDATE" form)
		else
		{
			static::update($lookupID, $submittedData);
		}
		
		// Loop through each class that has child records and purge existing linked records
		foreach($relatedClassList as $relatedClass)
		{
			// Delete any existing child records related to this lookup ID
			$relatedClass::delete($lookupID);
		}
		
		// Add all of the related records
		foreach($classSubmissions as $relatedClass => $postedRecord)
		{
			// Loop through the records posted and insert them into the database
			foreach($postedRecord as $schemaEntry)
			{
				// Make sure that the child class is properly pointed at the primary class
				// We need to set the missing lookup key
				$schemaEntry[$relatedClass::$lookupKey] = $lookupID;
				
				// Run the creation process
				$relatedClass::create($schemaEntry);
			}
		}
		
		// Commit the transaction
		return Database::endTransaction();
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
		
		foreach($submittedData as $key => $value)
		{
			// If the schema does not include this value, ignore it
			if(!isset($columns[$key]))
			{
				continue;
			}
			
			// Check if the column is valid or not
			if(!static::verifySchemaColumn($columns[$key], $submittedData[$key], $key))
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
		// Prepare Values
		$columnTitle = ucwords(str_replace("_", " ", $column));
		$strLen = strlen($valueToSet);
		
		// Each schema column's first element is a type.
		// Based on which type the column is, we'll modify the behavior.
		switch($columnRules[0])
		{
			### Strings and Text ###
			case "string":
			case "text":
				
				// Identify all string-related form variables
				$minLength = isset($columnRules[1]) ? (int) $columnRules[1] : 0;
				$maxLength = ((isset($columnRules[2]) and $columnRules[2]) ? (int) $columnRules[2] : ($columnRules[0] == "text" ? null : 250));
				
				// Make sure the data submitted is within the allowed character lengths
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
					
					if(!call_user_func(["IsSanitized", $sanitizeMethod], $valueToSet, $extraChars))
					{
						return Alert::error($columnTitle . ' Invalid', $columnTitle . " contains illegal characters.");
					}
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
				$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
				
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
				$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
				
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
	
	
/****** Generate a CREATE or UPDATE form for this schema ******/
	public static function buildForm
	(
		$submittedData		// <str:mixed> The data submitted to the form.
	,	$lookupID = null	// <mixed> Only used for UPDATES: the value of the lookup key (to find a record).
	)						// RETURNS <str> HTML of the form.
	
	// $formHTML = static::buildForm($submittedData, $lookupID = null)
	{
		// Prepare Values
		$schema = static::$schema;
		$currentRow = 0;
		
		// If a lookup ID was provided, retrieve database record and acknowledge as PATCH
		$resourceData = $lookupID ? static::get($lookupID) : [];
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			// For create forms, if this tag cannot be set, bypass this entry
			if(!$lookupID and static::tagExists($columnName, self::CANNOT_SET))
			{
				continue;
			}
			
			$currentRow++;
			$table[$currentRow][0] = ucwords(str_replace("_", " ", $columnName));
			
			// For update forms, if this tag cannot be modified, show it as readonly
			if($lookupID and static::tagExists($columnName, self::CANNOT_MODIFY))
			{
				$table[$currentRow][1] = $resourceData[$columnName];
				continue;
			}
			
			// If data was submitted by the user, set the column's value to their input
			if(isset($submittedData[$columnName]))
			{
				$value = $submittedData[$columnName];
			}
			
			// If user input was not submitted, set a default value for the column
			// For update forms, use the database record as the default
			else if(isset($resourceData[$columnName]))
			{
				$value = $resourceData[$columnName];
			}
			
			// For creation forms, use the default value assigned by the class' schema
			else
			{
				$value = isset($schema['default'][$columnName]) ? $schema['default'][$columnName] : '';
			}
			
			$table[$currentRow][1] = static::buildInput($columnName, $columnRules, $value);
		}
		
		// Loop through each "relationship" in the schema, and load data accordingly
		if(isset($schema['relationships']))
		{
			foreach($schema['relationships'] as $relationName => $relatedClass)
			{
				// Get details about the related class
				$relatedSchema = $relatedClass::$schema;
				$relatedClassType = get_parent_class($relatedClass);
				
				// Provide the special handling for child models
				if($relatedClassType == "Model_Child")
				{
					$inputRules = [];
					
					// Get the records from the related child class that point to the parent
					$relatedClassData = $lookupID ? $relatedClass::search([$relatedClass::$lookupKey => $lookupID]) : [];
					
					// Loop through each column in the related schema
					foreach($relatedSchema['columns'] as $columnName => $columnRules)
					{
						// Get rid of the lookup keys, since it relates back to this one
						if($columnName == $relatedClass::$lookupKey)
						{
							continue;
						}
						
						// If the tag cannot be modified, don't show it on the form
						if(self::tagExists($columnName, self::CANNOT_MODIFY))
						{
							continue;
						}
						
						$inputRules[] = [$columnName, $columnRules];
					}
					
					// If any content was provided, add it to the overall table
					if($inputRules)
					{
						// Prepare Values
						$currentRow++;
						$inputHTML = "";
						
						// Prepare the number of child records to show
						// If the child allows multiple records, list three options
						// Otherwise, only show one option
						$numberOfRecords = $relatedClass::$canHaveMany ? 3 : 1;
						
						// If there is data that already exists, make sure the number of records reflects that.
						if($relatedClassData and $relatedClass::$canHaveMany)
						{
							$numberOfRecords = count($relatedClassData) + 2;
						}
						
						// Loop through a number of records
						for($i = 0;$i < $numberOfRecords;$i++)
						{
							$inputHTML .= '<tr>';
							
							foreach($inputRules as $rule)
							{
								list($columnName, $columnRules) = $rule;
								
								// If data was submitted by the user, set the column's value to their input
								if(isset($submittedData[$relationName][$i][$columnName]))
								{
									$value = $submittedData[$relationName][$i][$columnName];
								}
								
								// If user input was not submitted, set a default value for the column
								// For update forms, use the database record as the default
								else if(isset($relatedClassData[$i][$columnName]))
								{
									$value = $relatedClassData[$i][$columnName];
								}
									
								// For create forms, use the default value assigned by the related class' schema
								else
								{
									$value = isset($relatedSchema['defaults'][$columnName]) ? $relatedSchema['defaults'][$columnName] : '';
								}
								
								$inputHTML .= '<td>' . static::buildInput($columnName, $columnRules, $value, $relationName, $i) . '<br />' . ucwords(str_replace("_", " ", $columnName)) . '</td>';
							}
							
							$inputHTML .= '</tr>';
						}
						
						$table[$currentRow][0] = ucwords(str_replace("_", " ", $relationName));
						$table[$currentRow][1] = '<table>' . $inputHTML . '</table>';
					}
				}
				
				// Provide the special handling for join models
				else if($relatedClassType == "Model_Join")
				{
					// Need to identify the join class (the related class to this join class)
					$joinedClass = $relatedClass::$relatedClass;
					
					// Get the records from the join child class that point to the parent
					$relatedClassData = $lookupID ? $relatedClass::search([$relatedClass::$lookupKey => $lookupID]) : [];
					
					// Prepare the number of records to show.
					// Join tables can always have multiple records.
					// If there is data that already exists, make sure the number of records reflects that.
					$numberOfRecords = $relatedClassData ? count($relatedClassData) + 2 : 3;
					
					// Extract crucial information from the join class - it's lookup key and display format
					$joinKey = $joinedClass::$lookupKey;
					$displayFormat = $joinedClass::$displayFormat ? $joinedClass::$displayFormat : "";
					
					// Get the full list of records from the join class
					$joinRecords = $joinedClass::search(['limit' => 0]);
					
					// Create a dropdown based on the join records
					$dropdownPrep = [];
					
					foreach($joinRecords as $record)
					{
						// Prepare the key for the dropdown
						$key = $record[$joinKey];
						
						// Use the join class' display format to format the dropdown text
						if($displayFormat)
						{
							$text = $displayFormat;
							
							foreach($record as $k => $v)
							{
								$text = str_replace("{" . $k . "}", $v, $text);
							}
						}
						
						// If there is no display format provided, we'll fake one
						else
						{
							$text = $record[$joinKey] . ": ";
							
							unset($record[$joinKey]);
							
							foreach($record as $k => $v)
							{
								$text .= $v . ", ";
							}
							
							$text = substr(trim($text, ","), 0, 55);
						}
						
						$dropdownPrep[$key] = $text;
					}
					
					// Convert the list of records into selection options
					$dropdownOptions = "";
					
					foreach($dropdownPrep as $key => $text)
					{
						$dropdownOptions .= '
						<option value="' . $key . '">' . $text . '</option>';
					}
					
					// Prepare Values
					$currentRow++;
					$inputHTML = "";
					
					// Loop through a number of records
					for($i = 0;$i < $numberOfRecords;$i++)
					{
						// If data was submitted by the user, set the column's value to their input
						$value = "";
						
						if(isset($submittedData[$relationName][$i]))
						{
							$value = $submittedData[$relationName][$i];
						}
						
						// If user input was not submitted, set a default value for the column
						else
						{
							// For update forms, use the database record as the default
							if(isset($relatedClassData[$i]))
							{
								$value = $relatedClassData[$i][$relatedClass::$relatedKey];
							}
							
							// Use the default value assigned by the related class' schema
							else if(isset($relatedSchema['defaults'][$relatedClass::$relatedKey]))
							{
								$value = $relatedSchema['defaults'][$relatedClass::$relatedKey];
							}
						}
						
						// Create the selection dropdown
						$inputHTML .= '
						<tr><td>
						<select name="' . $relationName . '[' . $i . ']">
							<option value="">-- SELECT AN OPTION --</option>
							' . str_replace('value="' . $value . '"', 'value="' . $value . '" selected', $dropdownOptions) . '
						</select>
						</td></tr>';
					}
					
					$table[$currentRow][0] = ucwords(str_replace("_", " ", $relationName));
					$table[$currentRow][1] = '<table>' . $inputHTML . '</table>';
				}
			}
		}
		
		// End the Form
		$table[$currentRow + 1] = ['', '<input type="submit" name="submit" value="Submit" />'];
		
		return $table;
	}
	
	
/****** Generate an input for a schema column ******/
	public static function buildInput
	(
		$columnName			// <str> The name of the column being built.
	,	$columnRules		// <int:array> The rules associated with the schema column.
	,	$value				// <mixed> The value to assign to the input.
	,	$parentCol = ""		// <str> The parent relationship column.
	,	$number = 0			// <int> The number of the input.
	)						// RETURNS <str> HTML of the input.
	
	// $inputHTML = static::buildInput($columnName, $columnRules, $value, $parentCol = "", $number = 0)
	{
		// Prepare the name of the input
		$inputName = $parentCol == "" ? $columnName : $parentCol . "[" . $number . "][" . $columnName . "]";
		
		// Determine which column type the input is
		switch($columnRules[0])
		{
			### Strings and Text ###
			case "string":
			case "text":
				
				// Identify all string-related form variables
				$minLength = isset($columnRules[1]) ? (int) $columnRules[1] : 0;
				$maxLength = isset($columnRules[2]) ? (int) $columnRules[2] : ($columnRules[0] == "text" ? 0 : 250);
				
				// Display a textarea for strings of 101 characters or more
				if(!$maxLength or $maxLength > 100)
				{
					return '
					<textarea id="' . $columnName . '" name="' . $inputName . '"'
						. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '>' . htmlspecialchars($value) . '</textarea>';
				}
				
				// Display a text input for a string of 100 characters or less
				return '
				<input id="' . $columnName . '" type="text"
					name="' . $inputName . '"
					value="' . htmlspecialchars($value) . '"'
					. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '
					/>';
				
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
				return '
				<input id="' . $columnName . '" type="number"
					name="' . $inputName . '"
					value="' . ((int) $value) . '"'
					. ($maxLength ? 'maxlength="' . $maxLength . '"' : '')
					. ($minRange ? 'min="' . $minRange . '"' : '')
					. ($maxRange ? 'max="' . $maxRange . '"' : '') . '
					/>';
			
			### Floats ###
			case "float":
			case "double":
			
				// Identify all string-related form variables
				$minRange = isset($columnRules[1]) ? (int) $columnRules[1] : null;
				$maxRange = isset($columnRules[2]) ? (int) $columnRules[2] : null;
				$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
				
				// Display the form field for an integer
				return '
				<input id="' . $columnName . '" type="text"
					name="' . $inputName . '"
					value="' . ((int) $value) . '"'
					. ($maxLength ? 'maxlength="' . ($maxLength + ceil($maxLength / 3)) . '"' : '') . '
					/>';
			
			### Booleans ###
			case "bool":
			case "boolean":
				
				// If the boolean types are not declared, set defaults
				$trueName = isset($columnRules[1]) ? $columnRules[1] : 'True';
				$falseName = isset($columnRules[2]) ? $columnRules[2] : 'False';
				
				// Display the form field for a boolean
				return str_replace('value="' . $value . '"', 'value="' . $value . '" selected', '
				<select id="' . $columnName . '" name="' . $inputName . '">
					<option value="1">' . htmlspecialchars($trueName) . '</option>
					<option value="0">' . htmlspecialchars($falseName) . '</option>
				</select>');
			
			### Enumerators ###
			case "enum-number":
			case "enum-string":
				
				// Get the available list of enumerators
				$enums = array_slice($columnRules, 1);
				
				// Display the form field for a boolean
				$inputHTML = '
				<select id="' . $columnName . '" name="' . $inputName . '">';
				
				// Handle numeric enumerators differently than string enumerators
				// These will have a numeric counter associated with each value
				if($columnRules[0] == "enum-number")
				{
					$enumCount = count($enums);
					
					for( $i = 0; $i < $enumCount; $i++ )
					{
						$inputHTML .= '
						<option value="' . $i . '"' . ($value == $i ? ' selected' : '') . '>'  . htmlspecialchars($enums[$i]) . '</option>';
					}
				}
				
				// String Enumerators
				else
				{
					foreach($enums as $enum)
					{
						$inputHTML .= '
						<option value="' . htmlspecialchars($enum) . '"' . ($value == $enum ? ' selected' : '') . '>' . htmlspecialchars($enum) . '</option>';
					}
				}
				
				$inputHTML .= '
				</select>';
				
				return $inputHTML;
		}
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
	
	
/****** Check if a schema submission is empty ******/
	public static function checkIfSubmissionIsEmpty
	(
		$submittedData		// <str:mixed> The data submitted that needs to be tested.
	)						// RETURNS <bool> TRUE if all tests passed, FALSE on failure.
	
	// static::checkIfSubmissionIsEmpty($submittedData)
	{
		// Prepare Values
		$columns = static::$schema['columns'];
		$defaults = isset(static::$schema['defaults']) ? static::$schema['defaults'] : [];
		
		foreach($submittedData as $key => $value)
		{
			// If the schema does not include this element, ignore it
			if(!isset($columns[$key]))
			{
				continue;
			}
			
			// Check if the column was set to the default value
			if(isset($defaults[$key]))
			{
				if($defaults[$key] == $value)
				{
					continue;
				}
				
				return false;
			}
			
			// Check if the column is empty
			if($value === "")
			{
				continue;
			}
			
			// If the column is a number, we consider it "empty" if it defaults to 0
			switch($columns[$key][0])
			{
				case "tinyint":
				case "smallint":
				case "mediumint":
				case "int":
				case "bigint":
				case "float":
				case "double":
				case "bool":
				case "boolean":
				case "enum-number":
					
					// If it matches "0" or 0, the value is empty
					if($value == 0)
					{
						continue 2;
					}
					
					return false;
			}
			
			return false;
		}
		
		return true;
	}
	
	
/****** Check if a particular tag was set ******/
	public static function tagExists
	(
		$column		// <str> The column to check whether or not the tag exists on it.
	,	$tag		// <str> The tag to check.
	)				// RETURNS <bool> TRUE if the tag exists, FALSE if it doesn't.
	
	// static::tagExists($column, $tag)
	{
		// If the column doesn't have any tag sets, no need to go further
		if(!isset(static::$schema['tags'][$column]))
		{
			return false;
		}
		
		return in_array($tag, static::$schema['tags'][$column]);
	}
}
