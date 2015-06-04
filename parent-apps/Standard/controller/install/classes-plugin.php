<?php

// Increase the amount of time allowed for this page to run
set_time_limit(180);	// Three minutes

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Connect to database with admin user
Database::initRoot();

// Prepare Values
$madeSelection = (Form::submitted("install-addon-plugins") ? true : false);

// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

// Display the Page
echo '
<h1>Installation: Classes</h1>

<h3>Step #1 - Install Plugin Classes</h3>

<p>The plugin classes are not required plugins, but are added to phpTesla system (as opposed to the phpTesla application) so that they can be used across all phpTesla applications within your environment. This step will allow you to install them.</p>

<p>Note: Not all plugins require installation. For plugins that do require installation, you can choose whether or not you wish to install them for this application.</p>';

// Loop through each class and install it
$configPaths = File_Scan::scanRecursive(ROOT_PATH . "/plugin-classes", "*.config.php");

if($madeSelection)
{
	foreach($configPaths as $configPath)
	{
		// Extract the name of this class
		$class = str_replace(".config.php", "", basename($configPath));
		
		// Load the Class's Config Class
		if(!$classConfig = Classes_Meta::getConfig($class, ROOT_PATH . "/plugin-classes"))
		{
			echo '<h4 style="color:red;">' . $class . '</h4>
			<p><span style="color:red;">The plugin\'s config class was inaccessible.</span></p>';
			
			continue;
		}
		
		$details = "";
		
		// If the plugin was intentionally installed
		if(isset($_POST['addon'][$class]))
		{
			// Install the Class
			$installed = Classes_Meta::install($class);
			
			switch($installed)
			{
				case Classes_Meta::DEPENDENCIES_MISSING:
					$details = '<span style="color:red; font-weight:700;">This installation requires dependencies that were not installed properly.</span>';
					break;
				
				case Classes_Meta::INSTALL_FAILED:
					$details = '<span style="color:red; font-weight:700;">Installation failed. Core functionality may be broken.</span>';
					break;
				
				case Classes_Meta::INSTALL_SUCCEEDED:
					$details = '<span style="color:green; font-weight:700;">Installation was completed successfully.</span>';
					break;
				
				case Classes_Meta::NO_INSTALL_NEEDED:
					$details = '<span style="color:blue;">No installation was necessary for this plugin.</span>';
					break;
			}
		}
		
		// If the plugin doesn't require installation
		else if(!Classes_Meta::hasInstaller($class, $classConfig))
		{
			$details = '<span style="color:blue;">No installation was necessary for this plugin.</span>';
		}
		else
		{
			$details = '<span style="color:blue; font-weight:700;">Was not installed.</span>';
		}
		
		// Display the Class
		echo '<h4>' . $class . ' - v' . number_format($classConfig->version, 2) . '</h4>
		<p>
			Author: ' . $classConfig->author . '
			<br />Description: ' . $classConfig->description . '
			<br />' . $details . '
		</p>';
	}
	
	echo '
	<a class="button" href="/install/classes-app">Continue with Installation</a>';
}
else
{
	echo '
	<style>
		.plugin-table tr:nth-child(2n-1) { background-color:#cceeff; }
		.plugin-table tr:hover { background-color:#aaddaa; }
		.plugin-table td { padding:3px; border:solid black 1px; }
	</style>
	
	<form class="uniform" action="/install/classes-plugin" method="post">' . Form::prepare("install-addon-plugins") . '
	<table class="plugin-table">';
	
	foreach($configPaths as $configPath)
	{
		// Extract the name of this class
		$class = str_replace(".config.php", "", basename($configPath));
		
		// Load the Class's Admin Class
		if($classConfig = Classes_Meta::getConfig($class, ROOT_PATH . "/plugin-classes"))
		{
			// Display the Classes
			echo '
			<tr>
				<td>';
			
			if(Classes_Meta::hasInstaller($class, $classConfig))
			{
				echo '<input type="checkbox" name="addon[' . $class . ']" ' . (isset(Install::$pluginClasses[$class]) ? 'checked onchange="this.checked=true"' : '') . ' />';
			}
			else
			{
				echo '&nbsp;';
			}
			
			echo '</td>
				<td style="max-width:100px; overflow:hidden;"><a href="/admin/classes/' . $class . '">' . $class . '</a></td>
				<td>' . $classConfig->description . '</td>
			</tr>';
		}
	}
	
	echo '
	</table>
	
	<br />
	<input type="submit" name="submit" value="Install Selected Classes" />
	</form>';
}

// Display the Footer
require(FOOTER_PATH);