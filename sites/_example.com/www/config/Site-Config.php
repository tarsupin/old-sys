<?php

// Site Configurations
return [
	
	
	/****** Required Configurations ******/
	# Each of the entries below must be configured manually.
	
	// Set to true when this file has been properly edited
	"Is Configured"		=> false
	
	// The name of the site
,	"Site Name"			=> "Example Site"
	
	// The application to use (see /apps/ for a listing)
	// Note: If you need to list a specific directory, see "Application Path" in the option configurations
,	"Application"		=> "example"
	
	// The site's unique handle that other sites can use to recognize this site
,	"Site Handle"		=> "example"
	
	// A salt used for generic site-wide purposes
	// Try to keep this value between 60 - 70 characters long
,	"Site Salt"			=>	"MAKE_SURE_YOU_CHANGE_THIS_VALUE_AS_IT_IS_NOT_CURRENTLY_SECURE"
	//						|    5   10   15   20   25   30   35   40   45   50   55   60   65   |
	
	
	/****** Optional Configurations ******/
	# The entries below can be left empty to assume default values.
	# If they are set, they will be overwritten with your changes.
	
	// The name of the database to call, if applicable
	// By default, this will use the same value as your "Site Handle"
,	"Database Name"		=> ""
	
	// The path to the application file
	// By default, this will direct to: ROOT_PATH . '/apps/' . {Application}
,	"Application Path"	=> ""
	
	// The CDN (Content Delivery Network) for this site
	// If there is no CDN, it will be set to the site's domain itself: "http://" . FULL_DOMAIN . URL_SUFFIX
,	"CDN"				=> ""
	
	// The timezone that you're in, using PHP standards
	// The default is set to "America/Los_Angeles"
	// You can refer to http://www.w3schools.com/php/php_ref_timezones.asp for a full list of valid timezones
,	"Timezone"			=> ""
];

