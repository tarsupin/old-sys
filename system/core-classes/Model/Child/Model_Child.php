<?php /*

-----------------------------------------
------ About the Model_Child Class ------
-----------------------------------------

A "Child Model" is used to identify a parent-child relationship between two classes. The class that extends Model_Child will be recognized as the child to a parent class.

By identifying this relationships, many functions and services are easier to process and understand (such as HTML forms).

There are very few differences between "Model" and "Model_Child" classes, which are listed here:
	
	1. You must set a $parentClass class variable.
		- Set this value to the name of the parent class.
	
	2. You can set a $displayFormat class variable, which helps with readability.
		- This is used to provide better readability for dropdowns and displays.
		- The format used is to wrap columns in "{" and "}"
			
			// Example Format:
			$displayFormat = "{category}: '{title}' by {author}";
				
				"Action: 'Harry Potter' by J. K. Rowling"
				"Adventure: 'The Sword of Truth' by Terry Goodkind"
		
	3. You must set $canBeEmpty to TRUE or FALSE
		- True means that this child class doesn't need any records in it for the parent.
		- False means that the child MUST have records, because the parent requires at least one.
		
	4. You must set $canHaveMany to TRUE or FALSE
		- True means that there can be multiple records for the parent.
		- False means there can only be one record for the parent.
	

-------------------------------------------------------
------ Example content for extending Model_Child ------
-------------------------------------------------------

abstract class Example_Child extends Model_Child {
	
	// Class Variables
	protected static $table = "example";		// <str> The name of the table to access.
	protected static $lookupKey = "id";			// <str> Table's lookup key (column); usually primary key.
	
	protected static $parentClass = "Example";	// <str> The parent class (that this class is a child of).
	
	protected static $canBeEmpty = true;		// <bool> TRUE if this content doesn't have to be included.
	protected static $canHaveMany = true;		// <bool> TRUE if there can be multiple children for the parent.
	
	// Provides a human readable format for these records.
	// For example: "{category}: '{title}' by {author}"
	protected static $displayFormat = "";	// <str>
	
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
			'parent_id'		=> ['int'],
			'category'		=> ['enum-string', 'Action', 'Adventure', 'Mystery', 'Other'],
			'title'			=> ['string', 1, 45, 'variable', " -'"],
			'author'		=> ['string', 1, 32, 'variable', ' .'],
		],
		
		'defaults' => [
			'category' => 'Other'
		],
		
		'tags' => [
			'parent_id'		=> [self::CANNOT_SET, self::CANNOT_MODIFY]
		],
		
		'index' => [
			['index', 'parent_id']
		],
		
		'special' => []
	];
}

*/

abstract class Model_Child extends Model {
	
	
/****** Class Variables ******/
	protected static $parentClass = "";		// <str> The parent class (that this class is a child of).
	protected static $canBeEmpty = true;	// <bool> TRUE if this content doesn't have to be included.
	protected static $canHaveMany = true;	// <bool> TRUE if there can be multiple children for the parent.
	
	// Provides a human readable format for these records.
	// For example: "{category}: '{title}' by {author}"
	protected static $displayFormat = "";	// <str>
	
}
