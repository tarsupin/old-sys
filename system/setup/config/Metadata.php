<?php

/*
	Note: Each site can overwrite these configurations by creating them at the following path:
	
		{$SITE_PATH}/config/Metadata.php
*/

// Metadata Configurations
return [
	
	/*
		[[ Header Metadata ]]
		
		Every item of this array will be inserted as a new line when Metadata::header() is called.
		
		This is useful for adding stylesheets and scripts to the <meta> tag.
	*/
	"Header"			=> array(
	//	'<link rel="stylesheet" href="/css/example.css" />'
	//,	'<link rel="stylesheet" href="/css/another_example.css" />'
	)
	
	
	/*
		[[ Footer Metadata ]]
		
		Every item of this array will be inserted as a new line when Metadata::footer() is called.
		
		This is useful for adding scripts in the HTML footer for asynchronous behavior.
	*/
,	"Footer"			=> array(
		
	)

];
