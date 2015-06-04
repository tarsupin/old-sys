<?php /*

----------------------------------------------
------ About the Security_Encrypt Class ------
----------------------------------------------

This plugin allows you to encrypt data.

To decrypt an encrypted value, you must use the Security_Decrypt plugin.


------------------------------------------
------ Example of using this plugin ------
------------------------------------------

// Prepare an encryption key
$encryptionKey = "secret key";

// Encrypt some data
$encryptedData = Security_Encrypt::run($encryptionKey, "Some data to encrypt");
var_dump($encryptedData);

// Decrypt the data
$decryptedData = Security_Decrypt::run($encryptionKey, $encryptedData);
var_dump($decryptedData);

-------------------------------
------ Methods Available ------
-------------------------------

Security_Encrypt::run($key, $dataToEncrypt, [$encryptionType]);

*/

abstract class Security_Encrypt {
	
	
/****** Encrypt Data ******/
	public static function run
	(
		$key	 		// <str> The key that you want to use for your encryption (required to decrypt).
	,	$encData		// <str> The data to encrypt.
	,	$encType = ""	// <str> The type of encryption you're using, specific to this framework.
	)					// RETURNS <str> The resulting encrypted data.
	
	// $encryptedData = Security_Encrypt::run("myKey", "Some data to encrypt");
	{
		// Check the default algorithm first
		if($encType == "")
		{
			return self::type_default($key, $encData);
		}
		
		// Prepare the Encryption Algorithm to use
		$hashAlgo = "type_" . $encType;
		
		// Check if hash algorithm selected is valid
		if(method_exists("Security_Encrypt", $hashAlgo))
		{
			return self::$hashAlgo($key, $encData);
		}
		
		return self::type_default($key, $encData);
	}
	
	
/****** `default` Encryption Algorithm ******/
	private static function type_default
	(
		$key 		// <str> The key that you want to use for your encryption.
	,	$encData	// <str> The data that you want to encrypt.
	)				// RETURNS <str> The resulting encryption.
	
	// self::type_default("myKey", "Data to encrypt");
	{
		// Can only send the first 32 characters of the key
		$key = Security_Hash::value($key, 32, 64);
		
		// Get the initialization vector (appends a public salt)
		$vectorSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$vector = mcrypt_create_iv($vectorSize, MCRYPT_RAND);
		
		// Encrypt the data
		$encData = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $encData, MCRYPT_MODE_CBC, $vector);
		
		return "|" . base64_encode($vector . $encData);
	}
	
	
/****** `fast` Encryption Algorithm ******/
	private static function type_fast
	(
		$key 		// <str> The key that you want to use for your encryption.
	,	$encData	// <str> The data that you want to encrypt.
	)				// RETURNS <str> The resulting encryption.
	
	// self::type_fast("myKey", "Data to encrypt");
	{
		return self::type_default($key, $encData);
	}
	
	
/****** `open` Encryption Algorithm ******/
# Note: This algorithm does NOT provide any encryption. It is generally used for passing URL data.
	private static function type_open
	(
		$key 		// <str> The key that you want to use for your encryption.
	,	$encData	// <str> The data that you want to encrypt.
	)				// RETURNS <str> The resulting encryption.
	
	// self::type_open("myKey", "Data to encrypt");
	{
		return "open|" . base64_encode($encData);
	}
	
}
