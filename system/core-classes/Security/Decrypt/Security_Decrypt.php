<?php /*

----------------------------------------------
------ About the Security_Decrypt Class ------
----------------------------------------------

This class provides allows you to decrypt data that originated from one of phpTesla's encryption algorithms. This is essential for private APIs and the like.

The only method you'll need to run with the Security_Decrypt class is Security_Decrypt::run(). The class will automatically use the data provided to determine how to read the encryption. This is automated because the Encrypt class will automatically include a value that indicates what type of encryption it's using.

For example, the encrypted data might look like this:
	
	"default|2t2LnNbJ4XvtFcM9EhQD9hJl=hO|c4S/fyDztw|F=OW290gIgnK6Ad+Clxsr=Yb3VWEsfiOli2otC"
	
The first segment says "default", which indicates that the Security_Decrypt class should use the "default" style of decryption on the string.


------------------------------------------
------ Examples of using this class ------
------------------------------------------

// Prepare an encryption key
$encryptionKey = "secret key";

// Encrypt some data
$encryptedData = Security_Encrypt::run($encryptionKey, "Some data to encrypt");
var_dump($encryptedData);

// Decrypt the data
$decryptedData = Security_Decrypt::run($encryptionKey, $encryptedData);
var_dump($decryptedData);


---------------------------------
------ Methods Available ------
---------------------------------

Security_Decrypt::run($key, $encryptedData);

*/

abstract class Security_Decrypt {
	
	
/****** Decrypt Data ******/
	public static function run
	(
		$key 			// <str> The original key that was used for the encryption.
	,	$encryptedData	// <str> The data to decrypt.
	)					// RETURNS <str> the decrypted string (or garbage, if decrypted improperly).
	
	// $decryptedData = Security_Decrypt::run("myPassword", $encryptedData);
	{
		// Gather Important Values
		$exp = explode("|", $encryptedData, 2);
		$algo = $exp[0];
		
		// Check the default algorithm first
		if($algo == "")
		{
			return self::type_default($key, $exp[1]);
		}
		
		$algo = "type_" . $algo;
		
		// Check if hash algorithm selected is valid
		if(method_exists("Security_Decrypt", $algo))
		{
			return self::$algo($key, $exp[1]);
		}
		
		return self::type_default($key, $exp[1]);
	}
	
	
/****** `default` Decryption Algorithm ******/
	public static function type_default
	(
		$key 			// <str> The original key that you used to encrypt the data.
	,	$encryptedData	// <str> The data to decrypt.
	)					// RETURNS <str> The decrypted string (or garbage, if decrypted improperly).
	
	// $data = self::type_default($key, $encryptedData);
	{
		// Only the first 32 characters of the key were sent, and done so with the Security_Hash::value method
		$key = Security_Hash::value($key, 32, 64);
		
		// Begin decryption
		$encryptedData = base64_decode($encryptedData);
		$vectorSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		
		$vector = substr($encryptedData, 0, $vectorSize);
		$encryptedData = substr($encryptedData, $vectorSize);
		$decryptedData = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_CBC, $vector);
		
		// mcrypt pads the return string with nulls, so we need to trim the end
		return rtrim($decryptedData, "\0");
	}
	
	
/****** `fast` Decryption Algorithm ******/
	public static function type_fast
	(
		$key 			// <str> The original key that you used to encrypt the data.
	,	$encryptedData	// <str> The data to decrypt.
	)					// RETURNS <str> The decrypted string (or garbage, if decrypted improperly).
	
	// $data = self::type_fast($key, $encryptedData);
	{
		return self::type_default($key, $encryptedData);
	}
	
	
/****** `open` Encryption Algorithm ******/
	private static function type_open
	(
		$key 		// <str> The key that you want to use for your encryption.
	,	$encData	// <str> The data that you want to encrypt.
	)				// RETURNS <str> The resulting encryption.
	
	// self::type_open("myKey", "Data to encrypt");
	{
		return base64_decode($encData);
	}
	
}
