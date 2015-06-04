<?php /*

-------------------------------------------
------ About the Security_Hash Class ------
-------------------------------------------

This plugin provides methods for hashing - such as for passwords.


-------------------------------
------ Methods Available ------
-------------------------------

// Hashes a value (and returns $length characters)
$hash = Security_Hash::value($value, $length = 64, $base = 64)

// Returns random hash of length $length
$hash = Security_Hash::random($length, $base = 64);

// Returns the hash of a given file
$fileHash = Security_Hash::file($filepath);

// Converts from one base to another
$convert = Security_Hash::convertBaseArbitrary($value, $fromBase, $toBase);

*/

abstract class Security_Hash {
	
	
/****** Hash a Value ******/
	public static function value
	(
		$value			// <str> The value to be hashed
	,	$length = 64	// <int> The length of the hash to return.
	,	$base = 64		// <int> The base to use.
	)					// RETURNS <str> The hashed value.
	
	// if(Security_Hash::value("test", 15) == "XdNve4kc4fJsc73") { echo "Test Passed!"; }
	{
		if($base == 64)
		{
			$hash = hash('sha512', $value, true);
			return substr(base64_encode($hash), 0, $length);
		}
		else if($base == 62)
		{
			$hash = hash('sha512', $value, true);
			return substr(str_replace(["+", "/", "="], ["", "", ""], base64_encode($hash)), 0, $length);
		}
		
		$hash = hash('sha512', $value);
		return substr(self::convertBaseArbitrary($hash, 16, $base), 0, $length);
	}
	
	
/****** Random Hash ******/
	public static function random
	(
		$length	= 64		// <int> The length of the hash that you'd like to return.
	,	$base = 64			// <int> The base to use for hash conversion.
	)						// RETURNS <str> A rand hash key of the desired length.
	
	// $hash = Security_Hash::random(25)		// Returns 25 random characters
	// $hash = Security_Hash::random(25, 62);	// Returns 25 random characters (of base 62 characters)
	{
		if($base == 64 and function_exists("openssl_random_pseudo_bytes"))
		{
			return substr(str_replace("=", "", base64_encode(openssl_random_pseudo_bytes($length))), 0, $length);
		}
		
		$result = "";
		
		for($a = 0;$a < $length;$a++)
		{
			$result .= Security_Pad::$padChars[mt_rand(0, $base - 1)];
		}
		
		return $result;
	}
	
	
/****** Get the hash of a designated file ******/
	public static function file
	(
		$filepath		// <str> The path to the file to get the hash of.
	)					// RETURNS <str> The hash of the file.
	
	// $filehash = Security_Hash::file($filepath);
	{
		return str_replace(["+", "/", "="], ["", "", ""], base64_encode(sha1_file($filepath, true)));
	}
	
	
/****** Prepare a jsHash Value ******/
	public static function jsHash
	(
		$key		// <str> The key for this encryption.
	,	$salt = ""	// <str> The salt for this encryption.
	)				// RETURNS <str> The resulting encrypted data.
	
	// $jsHash = Security_Hash::jsHash($key, [$salt]);
	{
		return self::value($key . date("yz") . $salt . ":myJSEncryption:cross_site_functionality", 20, 62);
	}
	
	
/****** Arbitrary Conversions Between Bases (thanks to stack overflow) ******/
	public static function convertBaseArbitrary
	(
		$value			// <str> The value to convert to another base.
	,	$fromBase		// <int> The base that you're converting from.
	,	$toBase 		// <int> The base that you're converting to.
	)					// RETURNS <str> The resulting base change.
	
	// $newValue = Security_Hash::convertBaseArbitrary("my awesome hash", 64, 10);
	{
		// Prepare other values
		$length = strlen($value);
		$nibbles = array();
		$result = '';
		
		for($i = 0;$i < $length;++$i)
		{
			$nibbles[] = Security_Pad::$padArray[$value[$i]];
		}
		
		do
		{
			$value = 0;
			$newlen = 0;
			
			for($i = 0;$i < $length;++$i)
			{
				$value = $value * $fromBase + $nibbles[$i];
				
				if ($value >= $toBase)
				{
					$nibbles[$newlen++] = (int)($value / $toBase);
					$value %= $toBase;
				}
				else if ($newlen > 0)
				{
					$nibbles[$newlen++] = 0;
				}
			}
			
			$length = $newlen;
			$result = Security_Pad::$padChars[$value] . $result;
		}
		while ($newlen !== 0);
		
		return $result;
	}
	
}
