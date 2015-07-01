<?php

// Display the Header
require(HEADER_PATH);

?>

<h2>Setting Up Your First Class</h2>
<p><a href="/phptesla"><-- Back to Webmaster Page</a></p>

<pre style="tab-size:4; -moz-tab-size: 4; -o-tab-size: 4;">
Classes can extend the "Model" class, which grants them a large number of methods. Once the $schema value is set up for a class, it will have CRUD and REST functionality available.

Once you've created your class, the first thing you should do is go to the URL: /model/MyClass

This page will provide a full searching mechanism your class contents, as well as allow you to perform CRUD functions.

------------------------------------------------
------ CRUD Functionality with your Class ------
------------------------------------------------
	
	MyClass::search($searchArgs);
	MyClass::create($submissionData);
	MyClass::read($lookupID);
	MyClass::update($lookupID, $submissionData);
	MyClass::upsert($lookupID, $submissionData);
	MyClass::delete($lookupID);

-------------------------------------------
------ Important URLS for Your Class ------
-------------------------------------------
	
	/model/MyClass              // Show a table of records for the model
	/model/MyClass/create       // Form to create a new record
	/model/MyClass/view/1       // Read a single record where the lookup value == 1
	/model/MyClass/update/1     // Update a record where the lookup value == 1

----------------------------------------
------ Using REST with your Class ------
----------------------------------------

	GET /api/MyClass           // A "GET" REST request
	POST /api/MyClass          // A "POST" REST request
	PUT /api/MyClass/1         // A "PUT" REST request where the lookup value == 1
	PATCH /api/MyClass/1       // A "PATCH" REST request where the lookup value == 1
	DELETE /api/MyClass/1      // A "DELETE" REST request where the lookup value == 1
	
-------------------------------------------
------ Example Content of your Class ------
-------------------------------------------

abstract class Example extends Model {
	
	// Class Variables
	protected static $table = "example";	// <str> The name of the table to access.
	protected static $lookupKey = "id";		// <str> Table's lookup key (column); usually primary key.
	protected static $tokenExpire = 45;		// <int> Token expiration - deterrent of replay attacks.
	
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
			'id'		=> [self::AUTO_INCREMENT, self::CANNOT_MODIFY],
			'my_enum'	=> self::HIDE
		],
		
		'index' => [
			['primary', 'id'],
			['unique', 'category, title']
		],
		
		'special' => []
	];
}
</pre>

<?php

// Display the Footer
require(FOOTER_PATH);
