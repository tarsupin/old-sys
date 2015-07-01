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
table a:link {
	color: #666;
	font-weight: bold;
	text-decoration:none;
}
table a:visited {
	color: #999999;
	font-weight:bold;
	text-decoration:none;
}
table a:active, table a:hover { color: #bd5a35; text-decoration:underline; }
table {
	font-family:Arial, Helvetica, sans-serif;
	color:#666;
	font-size:12px;
	text-shadow: 1px 1px 0px #fff;
	background:#eaebec;
	margin:20px;
	border:#ccc 1px solid;

	border-radius:3px;
	box-shadow: 0 1px 2px #d1d1d1;
}
table th {
	padding:4px 4px 4px 4px;
	border-top:1px solid #fafafa;
	border-bottom:1px solid #e0e0e0;
	background: #ededed;
}
table td { padding:4px; background: #fafafa; }
table tr.even td { background: #f6f6f6; }
table tr:hover td { background: #f2f2f2; }
</style>';

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
			$lookupID = ($formType == "update" && isset($url[3]) ? $url[3] : null);
			
			// Make sure the the submission is valid
			if(call_user_func([$url[1], 'verifyForm'], $submittedData, $lookupID))
			{
				// Remove the "submit" element from the data posted
				unset($submittedData['submit']);
				
				// If the submission is valid, process the form
				if($formType == "create")
				{
					if(call_user_func([$url[1], $formType], $submittedData))
					{
						Alert::saveSuccess("Form Created", "The form data was properly submitted!");
						
						header("Location: /model/" . $url[1]); exit;
					}
				}
				
				else if(call_user_func([$url[1], $formType], $lookupID, $submittedData))
				{
					Alert::saveSuccess("Form Updated", "The form was properly updated!");
					
					header("Location: /model/" . $url[1]); exit;
				}
			}
			
			// Display the Alerts
			echo Alert::display();
			
			// Display appropriate header for this form
			if($formType == "create")
			{
				echo '<h2>Create New `' . ucfirst($url[1]) . '` Record</h2>';
			}
			else
			{
				echo '<h2>Update `' . ucfirst($url[1]) . '` Record: ' . $lookupID . '</h2>';
			}
			
			// Load the appropriate CRUD form
			$tableData = call_user_func([$url[1], 'buildForm'], $submittedData, $lookupID);
			
			// Begin the Form
			echo '
			<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';
			
			echo UI_Table::draw($tableData);
			
			echo '
			</form>';
		}
		
		echo '<div><a href="/model/' . $url[1] . '">Search Records</a></div>';
		
		break;
	
	// Display Forms
	case "view":
		
		$lookupID = isset($url[3]) ? $url[3] : null;
		
		// Check if deletion is allowed and handle permissions
		
		// Make sure the class exists before calling it
		if(method_exists($url[1], 'readForm'))
		{
			// If the link to delete the record was submitted, run the deletion sequence
			if(Link::clicked("DeletedRecord"))
			{
				if(call_user_func([$url[1], 'delete'], $lookupID))
				{
					Alert::saveSuccess("Record Deleted", "The record was successfully deleted.");
					
					header("Location: /model/" . $url[1]); exit;
				}
			}
			
			// Load the appropriate CRUD form
			$tableData = call_user_func([$url[1], 'readForm'], $lookupID);
			
			// If this is the delete page, show an option to delete it
			$tableData['footer'] = ['' => '', 'Option Next' => '<a href="/model/' . $url[1] . '/view/' . $lookupID . '&' . Link::prepare("DeletedRecord") . '" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete this Record</a>'];
			
			echo UI_Table::draw($tableData);
		}
		
		echo '<div><a href="/model/' . $url[1] . '">Search Records</a></div>';
		
		break;
		
	// Generation of this model
	case "generate":
		// Classes_Generator::generate($url[1]);
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
			$tableData = call_user_func([$url[1], 'searchForm']);
			
			echo UI_Table::draw($tableData);
			
			echo '<a href="/model/' . $url[1] . '/create">Create New ' . ucfirst($url[1]) . ' Record</a>';
		}
}