<?php /*

This page provides a generic search table for the model. It will also direct you to other available model forms.

*/

// Check the URL and make sure it exists
if(!class_exists($url[1]))
{
	die("Must provide a valid class to use this.");
}

// Provide a simple CSS styling
echo '
<style>
.genTable a:link {
	color: #777;
	font-weight: bold;
	text-decoration:none;
}
.genTable a:visited {
	color: #999999;
	font-weight:bold;
	text-decoration:none;
}
.genTable a:active, .genTable a:hover { color: #bd5a35; text-decoration:underline; }
.genTable {
	font-family:Arial, Helvetica, sans-serif;
	color:#777;
	font-size:12px;
	text-shadow: 1px 1px 0px #fff;
	background:#eaebec;
	margin:20px;
	border:#ccc 1px solid;

	border-radius:3px;
	box-shadow: 0 1px 2px #d1d1d1;
}
.genTable th {
	padding:4px 4px 4px 4px;
	border-top:1px solid #fafafa;
	border-bottom:1px solid #e0e0e0;
	background: #ededed;
}
.genTable td { padding:4px; background: #fafafa; font-family:Arial, Helvetica; font-size:12px; color:#777; }
.genTable tr.even td { background: #f6f6f6; }
.genTable tr:hover td { background: #f2f2f2; }
</style>';

// Prepare Values
$class = Sanitize::variable($url[1]);
$formType = isset($url[2]) ? $url[2] : "search";	// Type of form we're loading ("create", "update", etc)

switch($formType)
{
	// Build a Create or Update Form
	case "create":
	case "update":
	
		// Make sure the class exists before calling it
		if(method_exists($class, 'buildForm'))
		{
			// Track any submitted data
			$submittedData = isset($_POST) ? $_POST : [];
			
			// Check if there is a record ID associated with an update
			$lookupID = ($formType == "update" && isset($url[3]) ? $url[3] : null);
			
			// Process the Form
			$class::processForm($submittedData, $lookupID = null);
			
			// Make sure the the submission is valid
			if($class::verifyForm($submittedData, $lookupID))
			{
				/*
				// Remove the "submit" element from the data posted
				unset($submittedData['submit']);
				
				// If the submission is valid, process the form
				if($formType == "create")
				{
					if($class::$formType($submittedData))
					{
						Alert::saveSuccess("Form Created", "The form data was properly submitted!");
						
						header("Location: /model/" . $class); exit;
					}
				}
				
				else if($class::$formType($lookupID, $submittedData))
				{
					Alert::saveSuccess("Form Updated", "The form was properly updated!");
					
					header("Location: /model/" . $class); exit;
				}
				*/
			}
			
			// Display the Alerts
			echo Alert::display();
			
			// Display appropriate header for this form
			if($formType == "create")
			{
				echo '<h2>Create New `' . ucfirst($class) . '` Record</h2>';
			}
			else
			{
				echo '<h2>Update `' . ucfirst($class) . '` Record: ' . $lookupID . '</h2>';
			}
			
			// Load the appropriate CRUD form
			$tableData = $class::buildForm($submittedData, $lookupID);
			
			// Begin the Form
			echo '
			<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
			
			echo UI_Table::draw($tableData);
			
			echo '
			</form>';
		}
		
		echo '<div><a href="/model/' . $class . '">Search Records</a></div>';
		
		break;
	
	// Display Forms
	case "view":
		
		$lookupID = isset($url[3]) ? $url[3] : null;
		
		// Check if deletion is allowed and handle permissions
		
		// Make sure the class exists before calling it
		if(method_exists($class, 'readForm'))
		{
			// If the link to delete the record was submitted, run the deletion sequence
			if(Link::clicked("DeletedRecord"))
			{
				if($class::delete($lookupID))
				{
					Alert::saveSuccess("Record Deleted", "The record was successfully deleted.");
					
					header("Location: /model/" . $class); exit;
				}
			}
			
			// Load the appropriate CRUD form
			$tableData = $class::readForm($lookupID);
			
			// If this is the delete page, show an option to delete it
			$tableData['footer'] = ['' => '', 'Option Next' => '<a href="/model/' . $class . '/view/' . $lookupID . '&' . Link::prepare("DeletedRecord") . '" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete this Record</a>'];
			
			echo UI_Table::draw($tableData);
		}
		
		echo '<div><a href="/model/' . $class . '">Search Records</a></div>';
		
		break;
		
	// Generation of this model
	case "generate":
		Classes_Generator::generate($class);
		break;
		
	// Search Table
	case "search":
	default:
		
		// Display the Alerts
		echo Alert::display();
		
		// Make sure the class exists before calling it
		if(method_exists($class, 'searchForm'))
		{
			echo '<a href="/model/' . $class . '/create">Create New ' . ucfirst($class) . ' Record</a>';
			echo ' <a href="/model/' . $class . '/generate" onclick="return confirm(\'This will overwrite files in the /controller/' . $class . '/ directory. Are you sure you want to generate default pages?\')">Generate Default Pages For ' . ucfirst($class) . '</a>';
			
			// Display the Model Name
			echo '<h2>Search the `' . ucfirst($class) . '` Table</h2>';
			
			// Load the Search Form
			$tableData = $class::searchForm();
			
			echo UI_Table::draw($tableData);
			
			echo '<a href="/model/' . $class . '/create">Create New ' . ucfirst($class) . ' Record</a>';
		}
}