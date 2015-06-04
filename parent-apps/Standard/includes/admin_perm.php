<?php

if(ENVIRONMENT == "local")
{
	Me::$clearance = 10;
}

// If you don't have the appropriate permissions
if(Me::$clearance < 5)
{
	die("You do not have permissions to access this page.");
}