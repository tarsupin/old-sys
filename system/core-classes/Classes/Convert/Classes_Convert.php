<?php /*

---------------------------------------------
------ About the Classes_Convert Class ------
---------------------------------------------

This class, when run, will automatically convert a plugins from standard PHP to an HHVM equivalent (Hack Language) and insert it into the appropriate /hhvm directory.

------------------------------------------
------ Example of using this class ------
------------------------------------------



-------------------------------
------ Methods Available ------
-------------------------------

// Converts a single, designated class to work on HHVM
Classes_Convert::convert($class);


// Converts all plugins to work on HHVM (within desired directories)
// Classes_Convert::massConversion($core = false, $addon = false, $app = false);

*/

abstract class Classes_Convert {
	
	
/****** Class Variables ******/
	public static $typeList = array(
			"str"		=> "string"
		,	"int"		=> "int"
		,	"float"		=> "float"
		,	"bool"		=> "bool"
		,	"void"		=> "void"
		,	"array"		=> "array"
		,	"mixed"		=> "mixed"
		,	"gen"		=> "T"
		,	"T"			=> "T"
		);
	
	
/****** Convert a plugin from PHP to Hack Language ******/
	public static function convert
	(
		$class		// <str> The name of the plugin to convert.
	,	$dir = ""	// <str> The directory to search for the plugin at.
	)				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Classes_Convert::convert($class, [$dir]);
	{
		// Do not allow the Classes_Convert plugin to be converted
		if($class == "Classes_Convert") { return false; }
		
		// Get the plugin configurations, so that we can identify the file's directory
		if(!$classConfig = Classes_Meta::getConfig($class, $dir))
		{
			return false;
		}
		
		// Get the file contents
		if(!$contents = File::getLines($classConfig->data['path'] . '/' . $classConfig->pluginName . '.php'))
		{
			return false;
		}
		
		// Simple Conversions
		$contents = str_replace('<?php', '<?hh', $contents);
		
		// Line by Line Conversions
		foreach($contents as $key => $line)
		{
			// Method Parameters
			$param = "";
			
			if(strpos($line, '	$') !== false)
			{
				$param = '$';
			}
			else if(strpos($line, '	&$') !== false)
			{
				$param = '&$';
			}
			
			if($param !== "")
			{
				$type = self::varType($line, "// ");
				
				// Perform the update, if possible
				if($type != "")
				{
					$contents[$key] = str_replace("	" . $param, "	" . $type . " " . $param, $line);
					continue;
				}
			}
			
			// Class Variables
			if(strpos($line, '$') !== false)
			{
				$type = self::varType($line, "// ");
				// and (strpos($line, "public") !== false or strpos($line, "private") !== false or strpos($line, "protected") !== false)
				
				if($type != "")
				{
					$contents[$key] = str_replace('$', $type . ' $', $line);
					continue;
				}
			}
			
			// If there is a RETURN value
			if(strpos($line, "	)") !== false)
			{
				$type = self::varType($line, "RETURNS ");
				
				// Perform the update, if possible
				if($type != "")
				{
					$contents[$key] = str_replace("	)", "	): " . $type, $line);
					continue;
				}
			}
			
			// If the method line itself has a comment (for generic types)
			if(strpos($line, "function") !== false)
			{
				$type = self::varType($line, "// ");
				
				// Perform the update, if possible
				if($type == "T")
				{
					$contents[$key] = str_replace("// <T>", "<T> // <T>", $line);
					continue;
				}
			}
		}
		
		// Convert to String
		$fullContent = "";
		
		foreach($contents as $key => $line)
		{
			$fullContent .= ($key == 0 ? "" : "\n") . $line;
		}
		
		// Remove Documentation
		//$between = Data_Parse::through($fullContent, "/*", "*/");
		//$fullContent = str_replace($between, "", $fullContent);
		
		// Display Result
		// echo "<br /><pre>" . htmlspecialchars($fullContent) . "</pre>";
		
		// Save the File
		return File::write($classConfig->data['path'] . '/hhvm/' . $classConfig->pluginName . '.php', $fullContent);
	}
	
	
/****** Convert a group of plugins from PHP to Hack Language ******/
	public static function massConversion
	(
		$core = false	// <bool> TRUE to convert core plugins.
	,	$addon = false	// <bool> TRUE to convert plugin classes.
	,	$app = false	// <bool> TRUE to convert app plugins.
	)					// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Classes_Convert::massConversion([$core], [$addon], [$app]);
	{
		// Convert Core Classes
		if($core)
		{
			echo '<h2>Core Class HHVM Conversions</h2>';
			
			$classList = Classes_Meta::getClassList(CORE_PLUGIN_PATH);
			
			foreach($classList as $class)
			{
				echo '
				<span style="font-weight:bold;">' . $class . '</span>: HHVM Conversion Complete.<br />';
				
				self::convert($class, CORE_PLUGIN_PATH);
			}
		}
		
		// Convert Plugin Classes
		if($addon)
		{
			echo '<h2>Addon Class HHVM Conversions</h2>';
			
			$classList = Classes_Meta::getClassList(ADDON_PLUGIN_PATH);
			
			foreach($classList as $class)
			{
				echo '
				<span style="font-weight:bold;">' . $class . '</span>: HHVM Conversion Complete.<br />';
				
				self::convert($class, ADDON_PLUGIN_PATH);
			}
		}
		
		// Convert App Classes
		if($app)
		{
			echo '<h2>App Class HHVM Conversions</h2>';
			
			$classList = Classes_Meta::getClassList(PLUGIN_PATH);
			
			foreach($classList as $class)
			{
				echo '
				<span style="font-weight:bold;">' . $class . '</span>: HHVM Conversion Complete.<br />';
				
				self::convert($class, PLUGIN_PATH);
			}
		}
	}
	
	
/****** Delete an HHVM version of a plugin ******/
	public static function delete
	(
		$class		// <str> The name of the plugin to convert.
	,	$dir = ""	// <str> The directory that the plugin needs to be deleted from.
	)				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Classes_Convert::delete($class, [$dir]);
	{
		// Get the plugin configurations, so that we can identify the file's directory
		if(!$classConfig = Classes_Meta::getConfig($class, $dir))
		{
			return false;
		}
		
		// Make sure the file exists
		if(!File::exists($classConfig->data['path'] . '/hhvm/' . $classConfig->pluginName . '.php'))
		{
			return false;
		}
		
		// Delete the file
		return File::delete($classConfig->data['path'] . '/hhvm/' . $classConfig->pluginName . '.php');
	}
	
	
/****** Delete a group of HHVM plugins ******/
	public static function massDeletion
	(
		$core = false	// <bool> TRUE to convert core plugins.
	,	$addon = false	// <bool> TRUE to convert plugin classes.
	,	$app = false	// <bool> TRUE to convert app plugins.
	)					// RETURNS <void>
	
	// Classes_Convert::massDeletion([$core], [$addon], [$app]);
	{
		// Convert Core Classes
		if($core)
		{
			echo '<h2>Core Class HHVM Deletions</h2>';
			
			$classList = Classes_Meta::getClassList(CORE_PLUGIN_PATH);
			
			foreach($classList as $class)
			{
				echo '
				<span style="font-weight:bold;">' . $class . '</span>: HHVM Deletion Complete.<br />';
				
				self::delete($class, CORE_PLUGIN_PATH);
			}
		}
		
		// Convert Plugin Classes
		if($addon)
		{
			echo '<h2>Addon Class HHVM Deletions</h2>';
			
			$classList = Classes_Meta::getClassList(ADDON_PLUGIN_PATH);
			
			foreach($classList as $class)
			{
				echo '
				<span style="font-weight:bold;">' . $class . '</span>: HHVM Deletion Complete.<br />';
				
				self::delete($class, ADDON_PLUGIN_PATH);
			}
		}
		
		// Convert App Classes
		if($app)
		{
			echo '<h2>App Class HHVM Deletions</h2>';
			
			$classList = Classes_Meta::getClassList(PLUGIN_PATH);
			
			foreach($classList as $class)
			{
				echo '
				<span style="font-weight:bold;">' . $class . '</span>: HHVM Deletion Complete.<br />';
				
				self::delete($class, PLUGIN_PATH);
			}
		}
	}
	
	
/****** Check the variable type ******/
	public static function varType
	(
		$line			// <str> The line to retrieve the type from.
	,	$before = ""	// <str> The content prior to the variable type.
	)					// RETURNS <str>
	
	// $type = Classes_Convert::varType($line, [$before]);
	{
		// Prepare the content that should be found before and after the matching type key
		$before .= "<";
		
		// Cycle through the list of possible variables, and return the appropriate type
		// For example, if the line finds <str> in it, return "string"
		foreach(self::$typeList as $tKey => $tType)
		{
			if(strpos($line, $before . $tKey . ">") !== false)
			{
				return $tType;
			}
		}
		
		// If there is a more advanced situation, such as an array, we need to parse it differently
		if($check = Data_Parse::between($line, $before, ">"))
		{
			// Split the hypothetical array into first and second parts
			$exp = explode(":", $check, 2);
			
			// If the second part exists, we found a proper array
			if(isset($exp[1]))
			{
				// Check if the first section of the array is a proper type (will be int, str, or mixed)
				if(isset(self::$typeList[$exp[0]]))
				{
					// The second part may be a standard type. If so, we can return the type
					if(isset(self::$typeList[$exp[1]]))
					{
						return "array <" . $exp[0] . ", " . $exp[1] . ">";
					}
					
					// If we haven't returned, the second part is probably a nest (another array)
					// No more nests allowed beyond this.
					if(strpos($exp[1], "[") !== false)
					{
						// Repeat the same test again
						$nest = Data_Parse::between($exp[1], "[", "]");
						
						$nestExp = explode(":", $nest, 2);
						
						if(isset($nestExp[1]))
						{
							if(self::$typeList[$nestExp[0]] and self::$typeList[$nestExp[1]])
							{
								return "array <" . $exp[0] . ", " . "array<" . $nestExp[0] . ", " . $nestExp[1] . ">>";
							}
						}
					}
				}
			}
		}
		
		// If no variable was found, return no type found (empty string)
		return "";
	}
}
