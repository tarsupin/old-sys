<?php

/*
	This page runs the API system.
	
	The API that gets loaded is equal to $url[1].
	
	http://example.com/api/API_NAME
*/

// The name of the API to run
$api = Sanitize::variable($url[1]);

// Make sure the runAPI method exists.
if(!method_exists($api, "processRequest")) { exit; }


// Get the API Data
$jsonData = $api::processRequest();

// Format the API Data
echo '<pre>';
echo json_encode(json_decode($jsonData, true), JSON_PRETTY_PRINT);
echo '</pre>';

/*
$jsonData = json_decode($result, true);

foreach($jsonData as $res)
{
	$d = trim($res['description']);
	
	$last = substr($d, -1);
	
	if($last != '.')
	{
		echo json_encode($res, JSON_PRETTY_PRINT);
	}
}
*/