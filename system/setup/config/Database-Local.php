<?php

/*
	Note: Each site can overwrite these configurations by creating them at the following path:
	
		{$SITE_PATH}/config/Database-Local.php
*/

// Local Environment Configurations
return [
	
		// Standard Database User
		"Username"			=> "USERNAME_GOES_HERE"
	,	"Password"			=> "PASSWORD_GOES_HERE"
		
		// Admin User
	,	"Admin Username"	=> "ADMIN_USERNAME_GOES_HERE"
	,	"Admin Password"	=> "ADMIN_PASSWORD_GOES_HERE"
		
		// Database Connection
	,	"Host"				=> '127.0.0.1'
	,	"Engine"			=> 'mysql'
	
];
