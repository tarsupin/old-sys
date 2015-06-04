<?php

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(HEADER_PATH);

// Breadcrumb List
$bcrumb = '<a href="/admin">Admin Home</a>';
$bbase = "/admin";
$blength = count($url) - 1;

for($a = 1;$a < $blength;$a++)
{
	$bbase .= '/' . $url[$a];
	
	$bcrumb .= ' &gt; <a href="' . $bbase . '">' . ucfirst($url[$a]) . '</a>';
}

if($blength > 0)
{
	$bcrumb .= ' &gt; ' . ucfirst($url[$blength]);
}

echo '
<h3>' . $bcrumb . '</h3>';

