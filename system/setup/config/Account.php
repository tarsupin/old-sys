<?php

/*
	Note: Each site can overwrite these configurations by creating them at the following path:
	
		{$SITE_PATH}/config/Account.php
*/

// Account Configurations
return [
	
	/*
		Determine how your site handles your account. Some sites are self-contained (the user is registered only on this site), while others may want to use accounts that work across multiple sites.
		
		Options include:
			"Self-Contained"		// The user accounts only exists on this site.
			"Distributed"			// A distributed system - so you can log in socially with this site.
			"UniFaction"			// This site uses UniFaction accounts; shares login / registration with UniFaction.
			
	*/
	"Account Type" =>		"Self-Contained"
	
	
];
