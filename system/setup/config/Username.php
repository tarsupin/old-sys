<?php

/*
	Note: Each site can overwrite these configurations by creating them at the following path:
	
		{$SITE_PATH}/config/Username.php
*/

// Username Configurations
return [

	"Min Length"			=> 3			// The minimum length for the username.
,	"Max Length"			=> 32			// The maximum length for the username.

,	"Use Lowercase"			=> false		// TRUE if you're only allowing usernames with lower case.
,	"Use Letter First"		=> true			// TRUE if the first character has to be a letter.

,	"Allow Numbers"			=> true			// TRUE if numbers are allowed in the username.
,	"Allow Special"			=> "_-"			// A string of special characters to allow in the username.

];
