<?php

// Display the Navigation Bar
echo '
<!DOCTYPE HTML>
<html>
<head>
	<base href="' . SITE_URL . '">
	<title>' . (isset(Config::$siteConfig['pageTitle']) ? Config::$siteConfig['pageTitle'] : Config::$siteConfig['Site Name']) . '</title>
	
	<!-- Meta Data -->
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/gif" href="/favicon.gif">
	<link rel="canonical" href="' . (isset(Config::$siteConfig['canonical']) ? Config::$siteConfig['canonical'] : '/' . $url_relative) . '" />
	
	<!-- Primary Stylesheet -->
	<link rel="stylesheet" href="/assets/css/style.css" />
	<link rel="stylesheet" href="/assets/css/icomoon.css" />
	
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	' . Metadata::header() . '
</head>

<body>

<div id="content">' . Alert::display();