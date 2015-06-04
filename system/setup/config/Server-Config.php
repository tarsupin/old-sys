<?php

// Server Configurations
return [
	
	/*
		Choose the appropriate environment for your server.
		
		By default, phpTesla provides three available environments:
			
			1. The "production" environment is your live server, where your final product is visible.
			2. The "staging" environment is your staging server, where you're testing it to be production-ready.
			3. The "local" environment is on your own personal computer.
	*/
	"Environment"		=> "local"		// Defaults to empty to force you to set it
	
	// Set the handle that this server is recognized by (i.e. the name of the server)
,	"Server Handle"		=> "ExampleName"
	
	// Set a global salt used on this server
	// Note: This is only one part of the salts used on your applications.
	// It will be used for Cookies, Forms, etc - it does not permanently fix to anything (such as for passwords)
	// Try to keep this value between 60 - 70 characters long
,	"Server Salt"		=>	"CHANGE_THIS_VALUE_TO_A_VALUE_OF_YOUR_CHOOSING"
	//						|    5   10   15   20   25   30   35   40   45   50   55   60   65   |
	
	// Does this server use HHVM as it's web server? If so, it can take advantage of the HACK language.
	// Setting this to true will use the HHVM files rather than PHP where applicable
,	"Use HHVM"			=> false
	
];

