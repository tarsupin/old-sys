<?php /*

-----------------------------------
------ About the Admin Panel ------
-----------------------------------

This control panel is for administrators of the site, which include all staff members.

This panel will list all of the available functionality and administrative pages available to the system, but some of them may be locked to staff members that do not have high enough clearance levels.

This page will pull all of the functionality from the plugins available to the site in two ways:

	1. Any .php file saved in the /admin directory of a plugin will be loaded as an admin page here.
	
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Retrieve the URL segments to determine what to load
$class = isset($url[1]) ? Sanitize::variable($url[1]) : '';
$page = isset($url[2]) ? Sanitize::variable($url[2], " -") : '';

// Attempt to load the Admin Pages
if($class and $page)
{
	// Load the Class Config
	$classConfig = Classes_Meta::getConfig($class);
	
	// Attempt to load an admin file
	$adminFile = $classConfig->data['path'] . "/admin/" . $page . ".php";
	
	if(is_file($adminFile))
	{
		require($adminFile); exit;
	}
}

// Scan through the plugins directory
$classList = Classes_Meta::getClassList();

// Prepare Values
$linkList = array();

// Cycle through the plugins to find any admin pages available.
foreach($classList as $class)
{
	// Reject class names that aren't valid
	if(!ctype_alnum($class)) { continue; }
	
	if($classConfig = Classes_Meta::getConfig($class))
	{
		// If there is no "isInstalled" method, don't show the entry
		if(!method_exists($classConfig->pluginName . "_config", "isInstalled"))
		{
			continue;
		}
		
		// If the plugin isn't installed, don't show it
		if(!$installed = call_user_func(array($classConfig->pluginName . "_config", "isInstalled")))
		{
			continue;
		}
		
		// Get list of controllers
		if($controllerList = Classes_Meta::getAdminPages($classConfig->data['path']))
		{
			foreach($controllerList as $controller)
			{
				$linkList[$classConfig->pluginName][$controller] = $controller;
			}
		}
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Cycle through all of the available admin components
ksort($linkList);

echo '
<style>
	.admin-table tr:nth-child(2n-1) { background-color:#cceeff; }
	.admin-table td { padding:3px; border:solid black 1px; }
</style>

<table class="admin-table">';

foreach($linkList as $class => $linkData)
{
	echo '
	<tr>	
		<td>' . $class . '</td>
		<td>';
	
	$comma = "";
	
	foreach($linkData as $title => $link)
	{
		echo $comma . '<a href="/admin/' . $class . '/' . $link . '">' . $title . '</a>';
		$comma = "<br />";
	}
	
	echo '
		</td>
	</tr>';
}

echo '
</table>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
