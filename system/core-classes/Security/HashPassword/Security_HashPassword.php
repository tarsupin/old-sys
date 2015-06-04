<?php /*

---------------------------------------------------
------ About the Security_HashPassword Class ------
---------------------------------------------------

This class provides the ability to create and identify password types on the system.


-------------------------------
------ Methods Available ------
-------------------------------

// Get and set passwords
$passHash	= Security_HashPassword::set("myPassword");					// Creates a password hash
$success	= Security_HashPassword::get("myPassword", $passHash);		// Returns TRUE if the password validates

*/

abstract class Security_HashPassword {
	
	
/****** Set a Password ******/
	public static function set
	(
		$password 				// <str> The plaintext password that you want to encrypt
	,	$complexity = 5			// <int> The complexity of the password. Higher is more complex. [Exponential]
	,	$hashType = "default"	// <str> The type of hash you're using, specific to this framework.
	)							// RETURNS <str> The resulting hash.
	
	// $passHash = Security_HashPassword::set("myPassword");
	{
		// Prepare Salt
		$salt = SITE_SALT;
		
		// Prepare the Hash Algorithm to use
		$hashAlgo = "setPassAlgo_" . $hashType;
		
		// Check if hash algorithm selected is valid
		if(method_exists("Password", $hashAlgo))
		{
			return self::$hashAlgo($password, $salt, $complexity);
		}
		
		return self::setPassAlgo_default($password, $salt, $complexity);
	}
	
	
/****** Set `default` Hash Algorithm ******/
	private static function setPassAlgo_default
	(
		$password 		// <str> The plaintext password that you want to encrypt.
	,	$salt			// <str> The salt to use for the hash algorithm.
	,	$complexity		// <int> The amount of complexity used on the algorithm. [Exponential]
	)					// RETURNS <str> The resulting hash. 128 characters in length.
	
	// self::setPassAlgo_default("myPassword", "--otherSalts--");
	{
		// Append a Hash-Specific Salt
		$append = str_replace("$", "", Security_Hash::random(27));
		$complex = mt_rand(1, ($complexity * $complexity));
		
		// Create a randomized hash salt that will be saved with the final hash
		$prep1 = substr(hash('sha512', $password . $salt . $append . $complex), 0, mt_rand(66, 86));
		
		// Return the hash (Note: We're using base64_encode for optimization purposes)
		return "default$" . $complexity . "$" . $append . "$" . base64_encode(hash('sha512', $prep1 . $salt . $append . $complex, true));
	}
	
	
/****** Get a Password ******/
	public static function get
	(
		$password 		// <str> The plaintext password that you're testing.
	,	$passHash		// <str> The hashed value that you're trying to match.
	)					// RETURNS <bool> TRUE if the algorithm is valid based on the password provided.
	
	// $success = Security_HashPassword::get("myPassword", $passHash);
	{
		// Gather Important Values
		$exp = explode("$", $passHash, 2);
		$hashAlgo = $exp[0];
		
		// Prepare Salt
		$salt = SITE_SALT;
		
		// Prepare the Hash Algorithm to use
		$hashAlgo = "getPassAlgo_" . Sanitize::word($exp[0]);
		
		// Check if hash algorithm selected is valid
		if(method_exists("Password", $hashAlgo))
		{
			return self::$hashAlgo($password, $passHash, $salt);
		}
		
		return self::getPassAlgo_default($password, $passHash, $salt);
	}
	
	
/****** Get `default` Hash Algorithm ******/
	public static function getPassAlgo_default
	(
		$password 		// <str> The plaintext password that you want to encrypt
	,	$passHash		// <str> The hashed value that you're trying to match.
	,	$salt			// <str> The salt that was provided to the algorithm
	)					// RETURNS <bool> TRUE if the algorithm is valid based on the password provided.
	
	// $passHash = self::getPassAlgo_default("myPassword", $hashedValue, $salt);
	{
		// Gather Important Values
		$exp = explode("$", $passHash, 4);
		
		if(count($exp) < 4) { return false; }
		
		$complexity = $exp[1] * $exp[1];
		$soloKey = $exp[2];
		$passHash = $exp[3];
		
		$salt .= $soloKey;
		
		// Recreate the hash algorithm with all random length modifiers
		$matchPass = false;
		
		for($c = 1;$c <= $complexity;$c++)
		{
			$prep1 = hash('sha512', $password . $salt . $c);
			
			for($i = 66;$i <= 86;$i++)
			{
				if($passHash == base64_encode(hash('sha512', substr($prep1, 0, $i) . $salt . $c, true)))
				{
					$matchPass = true; // Sets to true, but keep running the algorithm to avoid time identification
				}
			}
		}
		
		return $matchPass;
	}
	
}
