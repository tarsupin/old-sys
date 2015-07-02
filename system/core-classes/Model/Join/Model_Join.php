<?php /*

----------------------------------------
------ About the Model_Join Class ------
----------------------------------------

A "Join Model" is used to identify many-to-many relationship between classes. The class that extends Model_Join will be recognized as the intermediary "joining class" between two other classes.

By identifying this relationships, many functions and services are easier to process and understand (such as HTML forms).

There are a few differences between "Model" and "Model_Join" classes, which are listed here:
	
	1. You must set the $lookupKey and $relatedKey class variable.
		- These keys are the only values used on a JOIN table.
		- The "lookup key" is the primary record, with the "related key" pointing to the related class.
	
	2. You must set the $lookupClass and $relatedClass class variables.
		- These are the two classes that are being mapped to each other.
		- The $lookupClass is the class being accessed with the $lookupKey's column.
		- The $relatedClass is the class being called by the $relatedKey column.
		
	3. You must set $canBeEmpty to TRUE or FALSE
		- True means that this class doesn't need any records related to the $lookupClass.
		- False means that the child MUST have records, because the $lookupClass requires at least one.
	

-------------------------------------------------------
------ Example content for extending Model_Join -------
-------------------------------------------------------

abstract class Example_Child extends Model_Join {
	
	// Class Variables
	protected static $table = "example_child";		// <str> The name of the table to access.
	protected static $lookupKey = "example_id";		// <str> Table's lookup key (column); usually primary key.
	protected static $relatedKey = "related_id";	// <str> Table's related class' lookup key (column).
	
	protected static $lookupClass = "Example";		// <str> The class that we're mapping from.
	protected static $relatedClass = "Related";		// <str> The related class (mapping to).
	
	protected static $canBeEmpty = true;	// <bool> TRUE if a join record doesn't have to exist between the classes.
	
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
			'example_id'	=> ['int'],
			'related_id'	=> ['int']
		],
		
		'defaults' => [],
		'tags' => [],
		
		'index' => [
			['unique', 'parent_id, related_id']
		],
		
		'special' => []
	];
}

*/

abstract class Model_Join extends Model {
	
	
/****** Class Variables ******/
	protected static $lookupKey = "";		// <str> Table's lookup key (column); usually primary key.
	protected static $relatedKey = "";		// <str> Table's related class' lookup key (column).
	
	protected static $lookupClass = "";		// <str> The class that we're mapping from.
	protected static $relatedClass = "";	// <str> The related class (mapping to).
	
	protected static $canBeEmpty = true;	// <bool> TRUE if a join record doesn't have to exist between the classes.
	
}
