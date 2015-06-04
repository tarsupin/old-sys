<?php

/*
	Note: Each site can overwrite these configurations by creating them at the following path:
	
		{$SITE_PATH}/config/Password.php
*/

// Password Configurations
return [

	"Min Length"			=> 8			// The minimum length for the password.

,	"Min LowerCase"			=> 0			// The minimum number of lower-case characters required.
,	"Min UpperCase"			=> 0			// The minimum number of upper-case characters required.
,	"Min Numbers"			=> 0			// The minimum number of digits required.
,	"Min Special"			=> 0			// The minimum number of non-alphanumeric characters required.

];
