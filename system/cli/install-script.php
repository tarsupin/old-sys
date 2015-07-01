<?php

// Make sure the user is root
CLI_User::verify(CLI_User::SCRIPT_DIES_ON_FAILURE, "root");


/*******************************
****** Update Environment ******
*******************************/

// Make sure the global config file is in the appropriate location
if(!file_exists($phpTeslaDirectory . "/global-config.php"))
{
	echo "Unable to locate global-config.php. Please set the phpTeslaDirectory value to the proper directory.\n";
	exit;
}

$environment = "production";

echo "What environment is being used? (l = local, s = staging, p = production) : ";

$char = getInputChar();

if($char == "s")
{
	echo "\nEnvironment: Staging\n";
	$environment = "staging";
}
else if($char == "l")
{
	echo "\nEnvironment: Local\n";
	$environment = "local";
}
else
{
	echo "\nEnvironment: Production\n";
}

// Get the contents of the global config file
$content = file_get_contents($phpTeslaDirectory . "/global-config.php");

// Change to the new environment
$content = str_replace('define("ENVIRONMENT", "local")', 'define("ENVIRONMENT", "' . $environment . '")', $content);
$content = str_replace('define("ENVIRONMENT", "staging")', 'define("ENVIRONMENT", "' . $environment . '")', $content);
$content = str_replace('define("ENVIRONMENT", "production")', 'define("ENVIRONMENT", "' . $environment . '")', $content);

passthru('echo "' . str_replace(array('"', '$'), array('\"', '\$'), $content) . '" > "' . $phpTeslaDirectory . "/global-config.php" . '"');








exit; 

/************************
****** Create User ******
************************/

$username = "uni6user";

$contents = file_get_contents("/etc/passwd");

if(strpos($contents, $username . ":") !== false)
{
	echo 'The user "' . $username . '" already exists.\n';
}
else
{
	// Add the user
	passthru('useradd ' . $username);
	
	// Create the user's home
	passthru('mkdir /home/' . $username);
	
	// Set the user's empty SSH authorization keys
	passthru('mkdir /home/' . $username . '/.ssh');
	passthru('echo "" > /home/' . $username . '/.ssh/authorized_keys');
	
	echo 'The user "' . $username . '" has been created.\n';
}

// Add an SSH key to the user
// passthru('echo "' . $sshKey . '" >> /home/' . $username . '/.ssh/authorized_keys');


/*****************************
****** SSH Key Handling ******
*****************************/

$username = "uni6user";
$sshKey = "";

// Check if the SSH Key is already set
if(!$contents = file_get_contents('/home/' . $username . '/.ssh/authorized_keys'))
{
	passthru('echo "" > /home/' . $username . '/.ssh/authorized_keys');
}
else if(strpos($contents, $sshKey) !== false)
{
	return true;
}
