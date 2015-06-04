<?php

// Display the Navigation Bar
echo '
<!DOCTYPE HTML>
<html>
<head>
	<base href="' . SITE_URL . '">
	<title>' . (isset(Config::$siteConfig['pageTitle']) ? Config::$siteConfig['pageTitle'] : Config::$siteConfig['Site Name']) . '</title>
	
	<!-- Meta Data -->
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />' .
	(isset(Config::$siteConfig['active-hashtag']) ? '<meta id="activeHashtag" name="activeHashtag" content="' . Config::$siteConfig['active-hashtag'] . '" />' : '') . '
	<link rel="icon" type="image/gif" href="/favicon.gif">
	<link rel="canonical" href="' . (isset(Config::$siteConfig['canonical']) ? Config::$siteConfig['canonical'] : '/' . $url_relative) . '" />
	
	<!-- Primary Stylesheet -->
	<link rel="stylesheet" href="' . CDN . '/css/unifaction-base.css" />
	<link rel="stylesheet" href="/assets/css/style.css" />
	<link rel="stylesheet" href="/assets/css/icomoon.css" />
	
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	' . Metadata::header() . '
</head>

<body>

<!-- Content here gets displayed in the right panel, even with a dynamic AJAX loader -->
<div id="move-content-wrapper" style="display:none;">' . (isset(Config::$siteConfig['load-ad']) ? Config::$siteConfig['load-ad'] : '') . '</div>

<div id="container">
<div id="header-wrap">
	<a href="' . URL::unifaction_com() . '"><img id="nav-logo" src="' . CDN . '/images/unifaction-logo.png" /></a>
	<ul id="header-right">' .
		Search::searchEngineBar();
	
	// See the person that you're viewing
	if(You::$id && You::$id != Me::$id)
	{
		echo '
		<li id="viewing-user">
			<img class="circimg-small" src="' . ProfilePic::image(You::$id, "small") . '" /> <div><span style="font-size:13px;">Viewing</span><br /><span style="font-size:13px;">' . You::$name . '</span></div>
		</li>';
	}
	
	// If you're logged in
	if(Me::$id)
	{
		echo '
		<li id="login-menu"><a href="#"><img id="nav-propic" class="circimg-small" src="' . ProfilePic::image(Me::$id, "small") . '" /></a>
			<ul style="line-height:22px; min-width:180px;">
				<li><a href="' . URL::unifaction_social() . '/' . Me::$vals['handle'] . '">My Unity Wall</a></li>
				<li><a href="' . URL::blogfrog_social() . '/' . Me::$vals['handle'] . '">My BlogFrog</a></li>
				<li><a href="' . URL::unifaction_social() . '/friends">My Friends</a></li>
				<li><a href="' . URL::unijoule_com() . '">My UniJoule</a></li>
				<li><a href="' . URL::inbox_unifaction_com() . '">My Inbox</a></li>
				<li><a href="' . URL::profilepic_unifaction_com() . '/">Update Profile Pic</a></li>
				<li><a href="' . URL::unifaction_com() . '/user-panel">My Settings</a></li>
				<li><a href="' . URL::unifaction_com() . '/multi-accounts">Switch User</a></li>
				<li><a href="/logout">Log Out</a></li>
			</ul>
		</li>';
	}
	
	// If you're a guest
	else
	{
		echo '
		<li id="login-menu"><a href="#"><img id="nav-propic" class="circimg-small" src="' . ProfilePic::image(0, "small") . '" /></a>
			<ul style="line-height:22px; min-width:150px;">
				<li><a href="/login">Log In</a></li>
				<li><a href="/register">Sign Up</a></li>
			</ul>
		</li>';
	}
	
	echo '
	</ul>
</div>';

// Load the Core Navigation Panel
require(ROOT_PATH . "/parent-apps/Standard/includes/core_panel_" . ENVIRONMENT . ".php");

echo '
<div id="content-wrap">
	<div style="padding-top:60px;"></div>';

// Load the widgets contained in the "UniFactionMenu" container, if applicable
$widgetList = WidgetLoader::get("UniFactionMenu");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
	<div id="viewport-wrap">';

// Draw the Left Panel
echo '
<!-- Side Panel -->
<div id="panel">';

echo '
<div id="panel-left">';

// Load the widgets contained in the "SidePanel" container
$widgetList = WidgetLoader::get("SidePanel");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
</div> <!-- Panel Nav -->
</div>';

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display();