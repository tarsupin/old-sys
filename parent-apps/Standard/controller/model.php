<?php /*

This page provides a generic search table for the model. It will also direct you to other available model forms.

*/

// Check the URL and make sure it exists
if(!class_exists($url[1]))
{
	die("Must provide a valid class to use this.");
}

// Get the type of form that we're loading - default to "search"
$formType = isset($url[2]) ? $url[2] : "search";

switch($formType)
{
	// Build a Create or Update Form
	case "create":
	case "update":
		
		// Make sure the class exists before calling it
		if(method_exists($url[1], 'buildForm'))
		{
			// Track any submitted data
			$submittedData = isset($_POST) ? $_POST : [];
			
			// Check if there is a record ID associated with an update
			$lookupID = ($formType == "update" && isset($_GET['lookupID'])) ? $_GET['lookupID'] : null;
			
			// Make sure the the submission is valid
			if(call_user_func([$url[1], 'verifyForm'], $submittedData, $lookupID))
			{
				// Remove the "submit" element from the data posted
				unset($submittedData['submit']);
				
				// If the submission is valid, process the form
				if(call_user_func([$url[1], 'update'], $lookupID, $submittedData))
				{
					Alert::saveSuccess("Updated Form", "The form was properly updated!");
					
					header("Location: /model/" . $url[1]); exit;
				}
			}
			
			// Display the Alerts
			echo Alert::display();
			
			// Load the appropriate CRUD form
			echo call_user_func([$url[1], 'buildForm'], $submittedData, $lookupID);
		}
		
		break;
	
	// Display Forms
	case "read":
	
		// Make sure the class exists before calling it
		if(method_exists($url[1], 'readForm'))
		{
			// Load the appropriate CRUD form
			$tableData = call_user_func([$url[1], 'readForm'], isset($_GET['lookupID']) ? $_GET['lookupID'] : null);
			
			echo UI_Table::buildTableFromArray($tableData);
		}
		
		break;
		
	case "delete":
		
		break;
		
	// Search Table
	case "search":
	default:
		
		// Display the Alerts
		echo Alert::display();
		
		// Make sure the class exists before calling it
		if(method_exists($url[1], 'searchForm'))
		{
			// Display the Model Name
			echo '<h2>Search the `' . ucfirst($url[1]) . '` Table</h2>';
			
			// Load the Search Form
			echo call_user_func([$url[1], 'searchForm']);
		}
}