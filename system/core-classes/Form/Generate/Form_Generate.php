<?php /*

-------------------------------------------
------ About the Form_Generate Class ------
-------------------------------------------



-------------------------------
------ Methods Available ------
-------------------------------



*/

abstract class Form_Generate {
	
	
/****** Generate a creation form for this schema ******/
	public static function createForm
	(
		$schema			// <str:[str:mixed]> Schema column data for an object.
	,	$submittedData	// <str:mixed> The data submitted to the form.
	)					// RETURNS <str> HTML to insert into the form.
	
	// $formHTML = Form_Generate::createForm($schema, $submittedData)
	{
		// Prepare Values
		$formHTML = "";
		
		// Begin the Form
		$formHTML .= '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			// Identify and process tags that were listed for this column
			if(isset($schema['tags'][$columnName]))
			{
				$tags = is_array($schema['tags'][$columnName]) ? $schema['tags'][$columnName] : [$schema['tags'][$columnName]];
				
				// Check if there are any tags that the user needs to test
				foreach($tags as $tag)
				{
					switch($tag)
					{
						// If the tag cannot be modified, don't show it on the form
						case Model::CANNOT_MODIFY: continue 3;
					}
				}
			}
			
			// If data was submitted by the user, set the column's value to their input
			if(isset($submittedData[$columnName]))
			{
				$value = $submittedData[$columnName];
			}
			
			// If user input was not submitted, set a default value for the column
			else
			{
				$value = isset($schema['default'][$columnName]) ? $schema['default'][$columnName] : '';
			}
			
			$formHTML .= '
			<div>
				<label for="' . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . '</label>';
			
			// Determine how to display the column 
			switch($columnRules[0])
			{
				### Strings and Text ###
				case "string":
				case "text":
					
					// Identify all string-related form variables
					$minLength = isset($columnRules[1]) ? (int) $columnRules[1] : 0;
					$maxLength = isset($columnRules[2]) ? (int) $columnRules[2] : ($columnRules[0] == "text" ? 0 : 250);
					$sanitizeMethod = isset($columnRules[3]) ? $columnRules[3] : '';
					$extraChars = isset($columnRules[4]) ? (int) $columnRules[4] : '';
					
					// Display a textarea for strings of 101 characters or more
					if(!$maxLength or $maxLength > 100)
					{
						$formHTML .= '
						<textarea id="' . $columnName . '" name="' . $columnName . '"'
							. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '>' . htmlspecialchars($value) . '</textarea>';
					}
					
					// Display a text input for a string of 100 characters or less
					else
					{
						$formHTML .= '
						<input id="' . $columnName . '" type="text"
							name="' . $columnName . '"
							value="' . htmlspecialchars($value) . '"'
							. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '
							/>';
					}
					
					break;
					
				### Integers ###
				case "tinyint":			// 256
				case "smallint":		// 65k
				case "mediumint":
				case "int":
				case "bigint":
					
					// Identify all string-related form variables
					$minRange = isset($columnRules[1]) ? (int) $columnRules[1] : null;
					$maxRange = isset($columnRules[2]) ? (int) $columnRules[2] : null;
					$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
					
					// Display the form field for an integer
					$formHTML .= '
					<input id="' . $columnName . '" type="number"
						name="' . $columnName . '"
						value="' . ((int) $value) . '"'
						. ($maxLength ? 'maxlength="' . $maxLength . '"' : '')
						. ($minRange ? 'min="' . $minRange . '"' : '')
						. ($maxRange ? 'max="' . $maxRange . '"' : '') . '
						/>';
					
					break;
				
				### Floats ###
				case "float":
				case "double":
				
					// Identify all string-related form variables
					$minRange = isset($columnRules[1]) ? (int) $columnRules[1] : null;
					$maxRange = isset($columnRules[2]) ? (int) $columnRules[2] : null;
					$maxLength = self::getLengthOfNumberType($columnRules[0], $minRange, $maxRange);
					
					// Display the form field for an integer
					$formHTML .= '
					<input id="' . $columnName . '" type="text"
						name="' . $columnName . '"
						value="' . ((int) $value) . '"'
						. ($maxLength ? 'maxlength="' . ($maxLength + ceil($maxLength / 3)) . '"' : '') . '
						/>';
					
					break;
				
				### Booleans ###
				case "bool":
				case "boolean":
					
					// If the boolean types are not declared, set defaults
					$trueName = isset($columnRules[1]) ? $columnRules[1] : 'True';
					$falseName = isset($columnRules[2]) ? $columnRules[2] : 'False';
					
					// Display the form field for a boolean
					$formHTML .= str_replace('value="' . $value . '"', 'value="' . $value . '" selected', '
					<select id="' . $columnName . '" name="' . $columnName . '">
						<option value="1">' . htmlspecialchars($trueName) . '</option>
						<option value="0">' . htmlspecialchars($falseName) . '</option>
					</select>');
					break;
				
				### Enumerators ###
				case "enum-number":
				case "enum-string":
					
					// Get the available list of enumerators
					$enums = array_slice($columnRules, 1);
					
					// Display the form field for a boolean
					$formHTML .= '
					<select id="' . $columnName . '" name="' . $columnName . '">';
					
					// Handle numeric enumerators differently than string enumerators
					// These will have a numeric counter associated with each value
					if($columnRules[0] == "enum-number")
					{
						$enumCount = count($enums);
						
						for( $i = 0; $i < $enumCount; $i++ )
						{
							$formHTML .= '
							<option value="' . $i . '"' . ($value == $i ? ' selected' : '') . '>'  . htmlspecialchars($enums[$i]) . '</option>';
						}
					}
					
					// String Enumerators
					else
					{
						foreach($enums as $enum)
						{
							$formHTML .= '
							<option value="' . htmlspecialchars($enum) . '"' . ($value == $enum ? ' selected' : '') . '>' . htmlspecialchars($enum) . '</option>';
						}
					}
					
					$formHTML .= '
					</select>';
					
					break;
			}
			
			$formHTML .= '
			</div>';
		}
		
		// End the Form
		$formHTML .= '
		<div>
			<label for="submit">Submit</label>
			<input type="submit" name="submit" value="Submit" />
		</div>
		</form>';
		
		return $formHTML;
	}
	
	
/****** Generate a search table (with pagination) for this schema ******/
	public static function searchTable
	(
		$schema			// <str:[str:mixed]> Schema column data for an object.
	)					// RETURNS <str> HTML for the search table.
	
	// $tableHTML = Form_Generate::searchTable($schema)
	{
		// Prepare Values
		$offset = 0;
		$limit = 10;
		
		// Prepare the SQL Statement
		$fetchRows = Database::selectMultiple("SELECT * FROM powers", []);
		
		// Begin the Table
		$tableHTML = '
		<table border="1" cellpadding="4" cellspacing="0">
			<tr>';
		
		// Loop through each column in the schema
		foreach($schema['columns'] as $columnName => $columnRules)
		{
			// Identify and process tags that were listed for this column
			if(isset($schema['tags'][$columnName]))
			{
				$tags = is_array($schema['tags'][$columnName]) ? $schema['tags'][$columnName] : [$schema['tags'][$columnName]];
				
				// Check if there are any tags that the user needs to test
				foreach($tags as $tag)
				{
					switch($tag)
					{
						// If the tag cannot be modified, don't show it on the form
						//case Model::CANNOT_MODIFY: continue 3;
					}
				}
			}
			
			$tableHTML .= '<td>' . ucwords(str_replace("_", " ", $columnName)) . '</td>';
		}
		
		$tableHTML .= '
			</tr>';
		
		// Loop through each row
		$totalRows = count($fetchRows);
		
		for( $i = 0; $i < $totalRows; $i++ )
		{
			$row = $fetchRows[$i];
			
			$tableHTML .= '
			<tr>';
			
			// Loop through each schema column, and use that to place values appropriately
			foreach($schema['columns'] as $columnName => $columnData)
			{
				// Retrieve the appropriate value from the row and display it
				if(isset($row[$columnName]))
				{
					$tableHTML .= '
					<td>' . $row[$columnName] . '</td>';
				}
				else
				{
					// Show a default value for the column, if applicable
					$value = isset($schema['default'][$columnName]) ? $schema['default'][$columnName] : '';
					
					$tableHTML .= '
					<td>' . $value . '</td>';
				}
			}
			
			$tableHTML .= '
			</tr>';
		}
		
		$tableHTML .= '
		</table>';
		
		return $tableHTML;
	}
	
	
/****** Determine the maximum length of a number based on the variable type ******/
	private static function getLengthOfNumberType
	(
		$numericType		// <str> The type of number: tinyint, mediumint, int, long, etc.
	,	$minRange = null	// <int> 
	,	$maxRange = null	// <int> 
	)						// RETURNS <str> HTML to insert into the form.
	
	// $lengthOfNumType = self::getLengthOfNumberType('int');
	{
		$baseSize = 0;
		$extraSize = 0;
		
		switch($numericType)
		{
			case "tinyint":		$baseSize = 3;
			case "smallint":	$baseSize = 5;
			case "mediumint":	$baseSize = 8;
			case "int":			$baseSize = 11;
			case "integer":		$baseSize = 11;
			case "long":		$baseSize = 16;
			
			// These values need to account for an additional "."
			case "float":		$baseSize = 10;   $extraSize = 1;
			case "double":		$baseSize = 20;   $extraSize = 1;
		}
		
		// If there is a minimum or maximum range provided, check custom behavior
		if($minRange or $maxRange)
		{
			// Set default ranges to 0
			if(!$minRange) { $minRange = 0; }
			if(!$maxRange) { $maxRange = 0; }
			
			// If negative values are allowed, the "-" can increase the field size by 1
			if($minRange < 0 or $maxRange < 0)
			{
				$extraSize += 1;
			}
			
			// Determine what the maximum length of the range allows
			$sizeOfRange = strlen((string) max(abs($minRange), abs($maxRange)));
			
			// Shrink the maximum size to the highest range allowed
			$baseSize = min($baseSize, $sizeOfRange);
		}
		
		// Return the maximum size of the field allowed
		return ($baseSize + $extraSize);
	}
}
