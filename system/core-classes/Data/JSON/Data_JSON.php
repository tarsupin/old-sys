<?php /*

---------------------------------------
------ About the Data_JSON Class ------
---------------------------------------

This plugin provides tools for working with JSON data.


-------------------------------
------ Methods Available ------
-------------------------------

// Converts a JSON package into pretty text (for human readability)
$prettyJSON = Data_JSON::prettyText($jsonData);

// Handle JSON with files
Data_JSON::encodeFile($filepath, $dataToSerialize);
$value = Data_JSON::decodeFile($filepath)

// Change the keys on an array or object (to reduce serialized storage size)
$array = Data_JSON::changeKeys($array, $keyChanges);
$object = Data_JSON::changeKeys($object, $keyChanges);

// Pack and unpack numeric ranges (to reduce serialized storage size)
$numericArray = Data_JSON::numericArrayPack($numericArray);
$numericArray = Data_JSON::numericArrayUnpack($numericArray);

*/

abstract class Serialize {
	
	
/****** Change JSON to Pretty Format ******/
	public static function prettyText
	(
		$jsonData	// <str> A valid JSON-formatted string.
	)				// RETURNS <str> An easily readable JSON format.
	
	// echo Data_JSON::prettyText($jsonData);
	{
		
	}
	
	
/****** Encode Serialized Text into a File ******/
	public static function encodeFile
	(
		$filepath			// <str> The path to save the serialized data.
	,	$dataToSerialize	// <mixed> The value to serialize.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Data_JSON::encodeFile($filepath, $value);
	{
		return File::write($filepath, json_encode($value));
	}
	
	
/****** Load Value from a Serialized File ******/
	public static function decodeFile
	(
		$filepath			// <str> The path to the serialized data to decode.
	,	$toArray = true		// <bool> FALSE if you want to change it to an object instead of an array.
	)						// RETURNS <mixed> the data in its original form, or NULL if not a serialized value
	
	// $value = Data_JSON::decodeFile($filepath, [$toArray]);
	{
		// Attempt to retrieve the file content
		if($serializedData = File::read($filepath))
		{
			return self::decode($serializedData, $toArray);
		}
	}
	
	
/****** Rename an object's keys, generally for minification purposes ******/
	public static function changeKeys
	(
		$object			// <mixed> The object or array whose keys you're going to modify.
	,	$keyChanges		// <str:str> KEY = key to rename, VAL = the new key name.
	)					// RETURNS <mixed> the original object or array, but with modified keys.
	
	// $array = Data_JSON::changeKeys($array, $keyChanges);
	// $object = Data_JSON::changeKeys($object, $keyChanges);
	{
		// Change the properties if an object
		if(is_object($object))
		{
			foreach($keyChanges as $key => $val)
			{
				if(isset($object->$key))
				{
					$object->$val = $object->$key;
					
					unset($object->$key);
				}
			}
			
			return $object;
		}
		
		// Change the object
		foreach($keyChanges as $key => $val)
		{
			if(isset($object[$key]))
			{
				$object[$val] = $object[$key];
				
				unset($object[$key]);
			}
		}
		
		return $object;
	}
	
	
/****** Minimize a numeric array into ranges ******/
	public static function numericArrayPack
	(
		$numericArray	// <int:int> An array of numbers that might be in a range.
	)					// RETURNS <int:mixed> an array with range minifiers, if applicable.
	
	// $numericArray = Data_JSON::numericArrayPack($numericArray);
	{
		// Prepare Values
		$lastPos = -2;
		$rangeSequence = 0;
		
		$ranges = array();
		
		// Loop through array and determine any available ranges
		foreach($numericArray as $val)
		{
			// Check if the range is broken
			if($lastPos !== $val - 1)
			{
				$rangeSequence++;
			}
			
			$ranges[$rangeSequence][] = $val;
			
			$lastPos = $val;
		}
		
		// Prepare the new minified value
		$newArray = array();
		
		foreach($ranges as $rng)
		{
			if(count($rng) > 2)
			{
				$start = $rng[0];
				$end = $rng[count($rng) - 1];
				
				$newArray[] = $start . "-" . $end;
			}
			else
			{
				foreach($rng as $val)
				{
					$newArray[] = $val;
				}
			}
		}
		
		return $newArray;
	}
	
	
/****** Unpack a minimized numeric array ******/
	public static function numericArrayUnpack
	(
		$numericArray	// <int:mixed> An array that was minimized.
	)					// RETURNS <int:int> the unpacked array.
	
	// $numericArray = Data_JSON::numericArrayUnpack($numericArray);
	{
		// Prepare Variables
		$newArray = array();
		
		// Loop through each entry
		foreach($numericArray as $value)
		{
			if(is_string($value))
			{
				$exp = explode("-", $value);
				
				$start = $exp[0] + 0;
				$end = $exp[1] + 0;
				
				for($a = $start;$a <= $end;$a++)
				{
					$newArray[] = $a;
				}
			}
			else
			{
				$newArray[] = $value;
			}
		}
		
		return $newArray;
	}
}

