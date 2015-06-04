<?php /*

------------------------------------------
------ About the Cache_Memory Class ------
------------------------------------------

This class is used to cache variables system-wide, typically into memory. This should only be used to cache expensive operations or algorithms that only need to be updated infrequently, but which are read frequently.

This caching system will attempt to use the APC or Memcache modules. If neither of these modules are installed, it will use the database instead. The database is much less efficient than APC or Memcache, but it can still benefit your application when used properly.

To keep cached data in a text file, use the Cache_File:: plugin.

The primary mechanics of the Cache system include:

	// Retrieves a value from the cache
	Cache_Memory::get($key)
	
	// Caches a value that expires once $expire seconds pass
	Cache_Memory::set($key, $value, $expire)


------------------------------------
------ Simple Caching Example ------
------------------------------------

The cache system can only store strings. However, you can serialize data into strings with a serializer such as JSON. This gives your caching system more flexibility in what it can do.

In the example below, the code uses json_encode() to change an array into a string, and then json_decode() to convert the string back into the original array.


// Check if data is already cached
$checkData = Cache_Memory::get("pageData:" . $currentPageID);

if($checkData === false)		// If the result is set to false, the data is not cached
{
	$expensiveData = reallyLongAlgorithm();
	
	// This will cache the related items for 24 hours
	Cache_Memory::set("pageData:" . $currentPageID, json_encode($expensiveData), 3600 * 24);
}
else
{
	// Convert JSON to Array
	$expensiveData = json_decode($checkData, true);
}

// Dump the Content
var_dump($expensiveData);


-------------------------------
------ Methods Available ------
-------------------------------

// Returns "APC", "Database", or "Memcache" (whichever is being used)
Cache_Memory::type()

// Adds a variable to cache
Cache_Memory::set($key, $value, [$expire], [$flux])

// Retrieves a variable from the cache
Cache_Memory::get($key)

// Checks if the variable exists in the cache
Cache_Memory::exists($key)

// Deletes a variable from the cache
Cache_Memory::delete($key)

// Clears expired cache keys (for database-driven caching)
Cache_Memory::clearExpired()

// Clears the cache
Cache_Memory::clear()

*/

// Attempt to load Memcache caching
if(class_exists("Memcached"))
{
	require(__DIR__ . "/includes/Cache_Memcache.php");
}

// If previous attempt didn't work, attempt to load Database caching
else
{
	require(__DIR__ . "/includes/Cache_DB.php");
}
