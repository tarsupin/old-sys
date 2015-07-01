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
		
		self::generateSearchPage($class);
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
Something;
		
		//$pageHTML = self::generatePage($pageHTML, "");
		//var_dump($pageHTML);
		File::write(APP_PATH . "/controller/" . $class . "/search.php", $pageHTML);
		
		echo "Finished " . mt_rand(0, 99999);
	}
	
	
/****** Generate a generic set of controllers, forms, and views for this class ******/
	public static function generateUpdateForm
	(
		$class		// <str> The name of the class to generate an update form for.
	)				// RETURNS <str> HTML for an update form.
	
	// Classes_Generator::generateUpdateForm($class);
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
		return <<<PageContent
<?php

// Display the Header
require(HEADER_PATH);

// Display the Footer
require(FOOTER_PATH);

PageContent;
	}
}
