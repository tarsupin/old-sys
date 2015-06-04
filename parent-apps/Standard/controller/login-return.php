<?php /*

-------------------------------------------
------ About the Login Return Script ------
-------------------------------------------

This script will return the user to the last known "Return URL" after a failed auto-login attempt.
	
*/

// Prepare the default URL
$returnURL = (isset($_SESSION['login']['return_url']) ? $_SESSION['login']['return_url'] : "/");

// Make sure the Return URL exists
unset($_SESSION['login']);

// Redirect to the Return URL
header("Location: /" . $returnURL); exit;