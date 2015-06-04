<?php

$installPage = (isset($url[1]) ? $url[1] : "");

// Main Installation Navigation
WidgetLoader::add("SidePanel", 10, '
<div class="panel-box">
	<ul class="panel-slots">
		<li class="nav-slot"><a href="/">Return Home<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "" ? " nav-active" : "") . '"><a href="/install">Welcome<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "config-server" ? " nav-active" : "") . '"><a href="/install/config-server">Server Configuration<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "config-site" ? " nav-active" : "") . '"><a href="/install/config-site">Site Configuration<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "config-database" ? " nav-active" : "") . '"><a href="/install/setup-database">Database Configuration<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "classes-core" ? " nav-active" : "") . '"><a href="/install/classes-core">Install Core Classes<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "classes-plugin" ? " nav-active" : "") . '"><a href="/install/classes-plugin">Install Plugin Classes<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "classes-app" ? " nav-active" : "") . '"><a href="/install/classes-app">Install App Classes<span class="icon-circle-right nav-arrow"></span></a></li>
		
		<li class="nav-slot' . ($installPage == "app-custom" ? " nav-active" : "") . '"><a href="/install/app-custom">Custom App Install<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "complete" ? " nav-active" : "") . '"><a href="/install/complete">Installation Complete!<span class="icon-circle-right nav-arrow"></span></a></li>
	</ul>
</div>');

/*
		<li class="nav-slot' . ($installPage == "connect-handle" ? " nav-active" : "") . '"><a href="/install/connect-handle">Site Admin<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "connect-auth" ? " nav-active" : "") . '"><a href="/install/connect-auth">Confirm Auth Key<span class="icon-circle-right nav-arrow"></span></a></li>
*/