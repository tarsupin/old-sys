<?php /*

---------------------------------
------ About the Me Class ------
---------------------------------

This plugin stores information regarding the "active user", or the user currently browsing the site. For example, if you log into a site with the handle "JoeSmith", Me::$vals['handle'] would be set to "JoeSmith", and Me::$id would be set to that user's UniID.
	
	// An example of what information is gathered
	Me::$vals = Database::selectOne("SELECT * FROM users WHERE id=?", array($_SESSION['uni_id']));


The Me:: plugin will prepare the following values to use on the page:
	
	Me::$id				// The user's UniID, or 0 if the user is not logged in.
	Me::$clearance		// Clearance level of the user. 0 = guest, 6+ = mod permissions, 8+ = admin permissions.
	Me::$device			// Value of the device (1 = mobile, 2 = tablet, 3 = desktop).
	Me::$vals[]			// Contains an array of the user's data.


-------------------------------
------ Methods Available ------
-------------------------------

Me::load($uniID)		// Loads the active user's data.

*/

abstract class Me {
	
	
/****** Prepare Variables ******/
	public static $id = 0;					// <int> The active user's UniID
	public static $clearance = 0;			// <int> The clearance level of the user (0 is guest)
	public static $device = 3;				// <int> Value of the device (1 = mobile, 2 = tablet, 3 = desktop).
	public static $vals = array();			// <str:str> The active user's data (from their database row)
	public static $getColumns = "";			// <str> The database columns to retrieve when loading the user.
	
	
/****** Load My Data ******/
	public static function load (
	)				// RETURNS <bool> TRUE if the user's data was loaded, FALSE if failed.
	
	// Me::load();
	{
		// Make sure the database is loaded
		if(!Database::$database) { return false; }
		
		// If you are logged in, run a "remember me" check
		if(!isset($_SESSION['uni_id']))
		{
			return false;
		}
		
		// Set your session ID, which corresponds to your database user ID
		self::$id = $_SESSION['uni_id'];
		
		// Prepare the columns to receive
		if(!self::$getColumns)
		{
			self::$getColumns = "uni_id, role, clearance, handle, display_name, date_joined";
		}
		
		// Retrieve the active user from the database - the user doesn't exist in the database, register them
		if(!self::$vals = Database::selectOne("SELECT " . self::$getColumns . " FROM users WHERE uni_id=? LIMIT 1", array(self::$id)))
		{
			// Make sure appropriate registration values are sent
			if(!isset($_SESSION['uni_id']) or !isset($_SESSION['user']['handle']) or !isset($_SESSION['user']['display_name']))
			{
				return false;
			}
			
			// Set timezone to empty if not sent
			if(!isset($_SESSION['user']['timezone']))
			{
				$_SESSION['user']['timezone'] = "";
			}
			
			// Register User (if necessary)
			if(!Register::user($_SESSION['user']['uni_id'], $_SESSION['user']['handle'], $_SESSION['user']['display_name'], $_SESSION['user']['timezone']))
			{
				return false;
			}
			
			// Try to load the user again (after registration)
			if(!self::$vals = Database::selectOne("SELECT " . self::$getColumns . " FROM users WHERE uni_id=? LIMIT 1", array(self::$id)))
			{
				return false;
			}
		}
		
		// Save your Clearance Level
		self::$clearance = (int) self::$vals['clearance'];
		
		// Handle Banned Accounts
		if(self::$clearance <= -3)
		{
			header("Location: /banned"); exit;
		}
		
		// Occasionally log activity (handles auro allotment)
		if(mt_rand(0, 25) == 22)
		{
			self::logActivity();
		}
		
		return true;
	}
	
	
/****** Run the karma, auro, and general activity log ******/
	public static function logActivity (
	)					// RETURNS <void>
	
	// Me::logActivity();
	{
		// Not ready yet
		API_Connect::to("karma", "KarmaActivityAPI", array("uni_id" => Me::$id, "site_handle" => SITE_HANDLE, "action" => "view"));
	}
}
