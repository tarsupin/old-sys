<?php /*

-----------------------------------------------
------ About the Classes_Generator Class ------
-----------------------------------------------

This class, when run, will automatically generate controllers and views for the designated class.

NOTE: This will OVERWRITE existing files within the /controller/{ClassName} directory

------------------------------------------
------ Example of using this class ------
------------------------------------------



-------------------------------
------ Methods Available ------
-------------------------------

// Generates a generic set of controllers and views for a class
Classes_Generator::generate($class);

*/

abstract class Classes_Generator {
	
	
/****** Run the full generation script ******/
	public static function generate
	(
		$class		// <str> The name of the class to generate content for.
	)				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Classes_Generator::generate($class);
	{
		// If the class doesn't have a ::search() method, it's not a model - return early.
		if(!method_exists($class, "search"))
		{
			return false;
		}
		
		//self::generateSearchPage($class);
		
		// Generate the "Create" Form
		self::generateCreateForm($class);
	}
	
	
/****** Generate a generic set of controllers, forms, and views for this class ******/
	public static function generateSearchPage
	(
		$class		// <str> The name of the class to generate content for.
	)				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Classes_Generator::generate($class);
	{
		$schema = $class::$schema;
		
		$pageHTML = <<<'Something'
<?php

// Display the Alerts
echo Alert::display();

// Make sure the class exists before calling it
if(method_exists($url[0], 'searchForm'))
{
	// Display the Model Name
	echo '<h2>Search the `' . ucfirst($url[0]) . '` Table</h2>';
	
	// Load the Search Form
	$schema = $url[0]::$schema;
	
	$columnSorted = isset($_GET['sort']) ? Sanitize::variable($_GET['sort']) : null;
	
	// Prepare the SQL Statement
	$fetchRows = $url[0]::search($_GET, $rowCount);
	
	// Begin the Table
	$tableData = ['head' => ["~opts~" => "Options"], 'data' => []];
	
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
					// If the tag is hidden, don't show it
					case Model::HIDE:
						continue 3;
				}
			}
		}
		
		// If we're currently sorting by this column, provide special behavior to show this
		if($columnSorted == $columnName)
		{
			if($_GET['sort'][0] == '-')
			{
				$tableData['head'][$columnName] = '<a href="/' . $url_relative . '?' . Link::queryHold("columns", "limit", "page") . "&sort=" . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . ' ^</a>';
			}
			else
			{
				$tableData['head'][$columnName] = '<a href="/' . $url_relative . '?' . Link::queryHold("columns", "limit", "page") . "&sort=-" . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . ' v</a>';
			}
		}
		else
		{
			$tableData['head'][$columnName] = '<a href="/' . $url_relative . '?' . Link::queryHold("columns", "limit", "page") . "&sort=" . $columnName . '">' . ucwords(str_replace("_", " ", $columnName)) . '</a>';
		}
	}
	
	// Loop through each row
	$totalRows = count($fetchRows);
	
	for( $i = 0; $i < $totalRows; $i++ )
	{
		$row = $fetchRows[$i];
		$lookupID = $row[$url[0]::$lookupKey];
		
		$tableData['data'][$i][] = '<a href="/' . $url_relative . '/view?lookupID=' . $lookupID . '">V</a> <a href="/' . $url_relative . '/update?lookupID=' . $lookupID . '">U</a>';
		
		// Loop through each schema column, and use that to place values appropriately
		foreach($tableData['head'] as $columnName => $_ignore)
		{
			// Make sure the value exists in the schema
			if(!isset($schema['columns'][$columnName])) { continue; }
			
			// Add a Standard Row to the table
			if(isset($row[$columnName]))
			{
				$tableData['data'][$i][] = $row[$columnName];
			}
			
			// If there is no row entry, it must be a child table of some sort
			else
			{
				// Prepare Values
				$inputHTML = "";
				$columnRules = $schema['columns'][$columnName];
				
				//
				//	In order to handle related tables, the class needs to provide the following method:
				//		::getFormatted($lookupID)
				//	
				//	This will allow the table to return proper human-readable data entry.
				//
				
				// One-to-One relationships
				if($columnRules[0] == 'has-one')
				{
					$inputHTML = "ONE";
				}
				
				// One-to-Many relationships
				else if($columnRules[0] == 'has-many')
				{
					$inputHTML = "MANY";
				}
				
				$tableData['data'][$i][] = $inputHTML;
			}
		}
	}
	
	// Prepare Pagination
	$resultsPerPage = isset($_GET['limit']) ? (int) $searchArgs['limit'] : 25;
	$currentPage = (isset($_GET['page']) ? (int) $searchArgs['page'] : 1);
	
	// Construct the pagination object
	$paginate = new Pagination($rowCount, $resultsPerPage, $currentPage);
	
	// Display the Pagination
	$tableData['footer'] = '
	<div>Pages:';
	
	foreach($paginate->pages as $page)
	{
		if($paginate->currentPage == $page)
		{
			$tableData['footer'] .= ' [' . $page . ']';
		}
		else
		{
			$tableData['footer'] .= ' <a href="/' . $url_relative . '?' . Link::queryHold("columns", "sort", "limit") . "&page=" . $page . '">' . $page . '</a>';
		}
	}
	
	$tableData['footer'] .= '
	</div>';
	
	echo UI_Table::draw($tableData);
	
	echo '<a href="/' . $url_relative . '/create">Create New ' . ucfirst($url[0]) . ' Record</a>';
}
<?php
Something;
		
		//$pageHTML = self::generatePage($pageHTML, "");
		//var_dump($pageHTML);
		File::write(APP_PATH . "/controller/" . $class . "/search.php", $pageHTML);
		
		echo "Finished " . mt_rand(0, 99999);
	}
	
	
/****** Generate a creation form for this class ******/
	public static function generateCreateForm
	(
		$class		// <str> The name of the class to generate a create form for.
	)				// RETURNS <str> HTML for a create form.
	
	// $formHTML = Classes_Generator::generateCreateForm($class);
	{

// Prepare Values
$schema = $class::$schema;
$currentRow = 0;

$controller = '
// Handle a Submission
if(Form::submitted("' . $class . '-protect-form"))
{
	// Make sure the the submission is valid
	if($result = ' . $class . '::verifyForm($_POST))
	{
		// If the submission is valid, process the form
		if(' . $class . '::create($_POST))
		{
			Alert::saveSuccess("Form Created", "The form data was properly submitted!");
			
			// Clear the existing POST data
			unset($_POST);
		}
	}
}

// Display the Alerts
echo Alert::display();';

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
	if(isset($_POST[$columnName]))
	{
		$value = $_POST[$columnName];
	}
	
	// If user input was not submitted, set a default value for the column
	else
	{
		$value = isset($schema['default'][$columnName]) ? $schema['default'][$columnName] : '';
	}
	
	$currentRow++;
	$columnTitle = ucwords(str_replace("_", " ", $columnName));
	$table[$currentRow][0] = $columnTitle;
	
	// Determine how to display the column 
	switch($columnRules[0])
	{
		### Strings and Text ###
		case "string":
		case "text":
			
			// Identify all string-related form variables
			$minLength = isset($columnRules[1]) ? (int) $columnRules[1] : 0;
			$maxLength = isset($columnRules[2]) ? (int) $columnRules[2] : ($columnRules[0] == "text" ? 0 : 250);
			
			// Display a textarea for strings of 101 characters or more
			if(!$maxLength or $maxLength > 100)
			{
				$table[$currentRow][1] = '
				<textarea id="' . $columnName . '" name="' . $columnName . '"'
					. ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '><?php htmlspecialchars($value); ?></textarea>';
			}
			
			// Display a text input for a string of 100 characters or less
			else
			{
				$table[$currentRow][1] = '
				<input id="' . $columnName . '" type="text"
					name="' . $columnName . '"
					value="<?php htmlspecialchars($value); ?>"
					' . ($maxLength ? 'maxlength="' . $maxLength . '"' : '') . '
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
			$table[$currentRow][1] = '
			<input id="' . $columnName . '" type="number"
				name="' . $columnName . '"
				value="<?php echo ((int) $value); ?>"'
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
				value="<?php echo ((int) $value); ?>"'
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
			$table[$currentRow][1] = '
			<select id="' . $columnName . '" name="' . $columnName . '"><?php echo str_replace(\'value="' . $value . '"\', \'value="' . $value . '" selected\', \'
				<option value="1">' . htmlspecialchars($trueName) . '</option>
				<option value="0">' . htmlspecialchars($falseName) . '</option>\'); ?>
			</select>';
			
			break;
		
		### Enumerators ###
		case "enum-number":
		case "enum-string":
			
			// Get the available list of enumerators
			$enums = array_slice($columnRules, 1);
			
			// Display the form field for a boolean
			$table[$currentRow][1] = '
			<select id="' . $columnName . '" name="' . $columnName . '"><?php echo str_replace(\'value="' . $value . '"\', \'value="' . $value . '" selected\', \'';
			
			// Handle numeric enumerators differently than string enumerators
			// These will have a numeric counter associated with each value
			if($columnRules[0] == "enum-number")
			{
				$enumCount = count($enums);
				
				for( $i = 0; $i < $enumCount; $i++ )
				{
					$table[$currentRow][1] .= '
					<option value="' . $i . '">'  . htmlspecialchars($enums[$i]) . '</option>';
				}
			}
			
			// String Enumerators
			else
			{
				foreach($enums as $enum)
				{
					$table[$currentRow][1] .= '
					<option value="' . htmlspecialchars($enum) . '">' . htmlspecialchars($enum) . '</option>';
				}
			}
			
			$table[$currentRow][1] .= '\'); ?>
			</select>';
			
			break;
	}
}

// Convert the data into an HTML table
$table[$currentRow + 1] = ['Submit', '<input type="submit" name="submit" value="Submit" />'];

$view = '?>

<form action="/' . $class . '/create" method="post"><?php echo Form::prepare("' . $class . '-protect-form"); ?>
' . UI_Table::draw($table) . '
</form>

<?php';

// Save the page / form to the appropriate file
$pageHTML = self::generatePage($controller, $view);
File::write(APP_PATH . "/controller/" . $class . "/create.php", $pageHTML);

	}
	
	
/****** Generate a generic set of controllers, forms, and views for this class ******/
	public static function generateUpdateForm
	(
		$class		// <str> The name of the class to generate an update form for.
	)				// RETURNS <str> HTML for an update form.
	
	// $formHTML = Classes_Generator::generateUpdateForm($class);
	{
		
	}
	
	
/****** Generate a page ******/
	private static function generatePage
	(
		$controllerHTML		// <str> The content to put above the header.
	,	$viewHTML			// <str> The content to put below the header, but above the footer.
	)						// RETURNS <str> HTML for the page.
	
	// $html = self::generatePage();
	{
		return '<?php
' . $controllerHTML . '

// Display the Header
require(HEADER_PATH);

' . $viewHTML . '

// Display the Footer
require(FOOTER_PATH);
';
	}
}
