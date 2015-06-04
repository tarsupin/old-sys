<?php /*

-----------------------------------
------ About the User Class ------
-----------------------------------

This plugin retrieves information about the user.

This plugin is most frequently used to retrieve user information with the following methods:

	User::get($uniID);					// Retrieves the user's data (with their UniID)
	
	User::getDataByHandle($handle);		// Retrieves the user's data (with their user handle)


------------------------------
------ Clearance Levels ------
------------------------------

$clearances = User::clearance();

	//	9	superadmin (webmaster)
	//	8	staff admin
	//	7	management staff
	//	6	moderator, staff
	//	5	staff
	//	4	intern / assistant
	//	3	vip / trusted user
	//	2	user
	//	1	limited user
	//	0	guest
	//	-1	silenced user
	//	-2	restricted user
	//	-3	temporarily banned user
	//	-9	permanently banned user


------------------------------
------ Methods Available ------
------------------------------

$userData	= User::get($uniID, $columns = "*")		// Retrieves & verifies user info
$uniID		= User::getIDByHandle($handle);
$userData	= User::getDataByHandle($handle);		// Retrieves user info based on a handle

$clearances = User::clearance();

*/

abstract class User extends Model {
	
	
/****** Model Variables ******/
	protected static $table = "users";			// <str> The name of the table to call from.
	protected static $indexKey = "uni_id";		// <str> The column that acts as the table's key.
	protected static $allowRequests = [];		// <int:str> The list of requests to allow
	
	
/****** Class Variables ******/
	
	// On some pages, it will be useful to cache the user's data for later instances on that page.
	// In those instances, we will save them to User::$cache[$uniID], provided here:
	public static $cache = array();
	
	
/****** Get UniID from a Handle ******/
	public static function getIDByHandle
	(
		$handle		// <str> The handle to look up.
	)				// RETURNS <int> the UniID associated with the handle, or 0 if not found.
	
	// $uniID = User::getIDByHandle($handle);
	{
		return (int) Database::selectValue("SELECT uni_id FROM users_handles WHERE handle=? LIMIT 1", array($handle));
	}
	
	
/****** Get User's Data from a Handle ******/
	public static function getDataByHandle
	(
		$handle				// <str> The handle to look up.
	,	$columns = "uni_id"	// <str> The columns you want to retrieve from the users database.
	)						// RETURNS <str:mixed> user data associated with the handle provided, array() if empty.
	
	// $userData = User::getDataByHandle($handle, $columns);
	{
		if($uniID = self::getIDByHandle($handle))
		{
			return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,-*`") . " FROM users WHERE uni_id=? LIMIT 1", array($uniID));
		}
		
		return array();
	}
	
	
/****** Return Clearance Values ******/
	public static function clearance (
	)					// RETURNS <int:str> array of clearance levels
	
	// $clearances = User::clearance();
	{
		return array(
			9	=> "Superadmin, Webmaster"
		,	8	=> "Staff Administrator"
		,	7	=> "Staff Management"
		,	6	=> "Moderators, Staff"
		,	5	=> "Staff"
		,	4	=> "Interns, Assistants"
		,	3	=> "VIPs, Trusted Users"
		,	2	=> "User"
		,	1	=> "Limited User"
		,	0	=> "Guest"
		,	-1	=> "Silenced User"
		,	-2	=> "Restricted User"
		,	-5	=> "Temporarily Banned"
		,	-9	=> "Permanently Banned"
		);
	}
}
