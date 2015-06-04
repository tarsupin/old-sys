<?php /*

---------------------------------------
------ About the Register Class ------
---------------------------------------

This plugin allows users to register from any UniFaction site. This will register them on Auth.

*/

abstract class Register {
	
	
/****** Register a User ******/
	public static function user
	(
		$uniID				// <int> The Uni-Account ID.
	,	$handle				// <str> The handle for this Uni-Account.
	,	$displayName = ""	// <str> The display name of the account.
	,	$timezone = ""		// <str> The timezone of the account.
	)						// RETURNS <bool> TRUE if successful, FALSE if failed.
	
	// Register::user($uniID, $handle, "My Name", "America/Chicago");
	{
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO `users` (`uni_id`, `clearance`, `handle`, `display_name`, `timezone`, `date_joined`) VALUES (?, ?, ?, ?, ?, ?)", array($uniID, 2, $handle, $displayName, $timezone, time())))
		{
			$pass = Database::query("INSERT INTO `users_handles` (handle, uni_id) VALUES (?, ?)", array($handle, $uniID));
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Silently Register a User (as efficiently as possible, with graceful fail) ******/
# This method is designed to test Auth to see if the user exists and should be registered (since some sites will
# expect the user to exist). If the user is detected, register them.
	public static function silent
	(
		$user		// <mixed> UniID or Handle that you want to silently register.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Register::silent($user);
	{
		// Make sure the user doesn't exist
		if(is_numeric($user))
		{
			if($userData = User::get($user, "uni_id"))
			{
				return true;
			}
		}
		else if($userData = User::getIDByHandle($user))
		{
			return true;
		}
		
		// Get User Data from Auth
		$packet = array("user" => $user, "columns" => "uni_id, handle, display_name");
		
		if($response = API_Connect::to("auth", "UserData", $packet))
		{
			return self::register((int) $response['uni_id'], $response['handle'], $response['display_name'], "");
		}
		
		return false;
	}
	
	
/****** Retrieve a list of handle suggestions (during profile creation) ******/
	public static function getHandleSuggestions
	(
		$handle			// <str> The handle that was attempted.
	)					// RETURNS <int:str> list of handles that could be chosen.
	
	// $handleList = Register::getHandleSuggestions($handle);
	{
		$handleList = array();
		$count = 0;
		
		// Make 25 attempts
		for($a = 0;$a < 25;$a++)
		{
			$pos = mt_rand(1, 10);
			$underscore = mt_rand(-1, 1) == 1 ? "_" : "";
			$type = mt_rand(0, 9);
			
			$result = "";
			$attempt = "";
			
			switch($type)
			{
				case 0:
				case 1:
				case 2:
					$attempt = date("Y"); break;
					
				case 3:
				case 4:
					$attempt = ""; $underscore = "_"; break;
					
				case 5:
				case 6:
					$underscore = "_";
					$array = array("a", "the", "one", "my", "i_am", "go", "for", "epic", "only", "is", "are", "big");
					$attempt = $array[rand(0, count($array) - 1)]; $pos = 1; break;
					
				case 7:
				case 8:
				case 9:
					$attempt = mt_rand(1, 99);
					if($attempt == 69) { $attempt = "0"; }
					break;
			}
			
			// If we're adding the attempt BEFORE the handle
			if($pos <= 2)
			{
				$result = $attempt . $underscore . $handle;
			}
			else
			{
				$result = $handle . $underscore . $attempt;
			}
			
			// Check if the resulting handle is available
			if(!$handleTaken = User::getIDByHandle($result))
			{
				if(!in_array($result, $handleList))
				{
					$handleList[] = $result;
					$count++;
					
					if($count >= 6)
					{
						break;
					}
				}
			}
		}
		
		// Return the list of suggestions
		return $handleList;
	}
	
	
/****** Check if a handle is already taken ******/
	public static function handleTaken
	(
		$handle			// <str> The handle that you want to check for availability.
	)					// RETURNS <bool> TRUE if taken, FALSE if not.
	
	// Register::handleTaken($handle);
	{
		return (User::getIDByHandle($handle) ? true : false);
	}
	
}
