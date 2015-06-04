<?php

// Installation Header
require(PARENT_APP_PATH . "/includes/install_header.php");

// Connect to database with admin user
Database::initRoot();

// Load the appropriate installation page, if necessary:


// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

// Display the Page
echo '
<h1>Installation: Classes</h1>

<h3>Step #1 - Custom Installation</h3>

<p>This is the custom installation page for the `' . Config::$siteConfig['Site Name'] . '` application you are setting up.</p>

<br /><br /><a class="button" href="/install/complete">Continue with Installation</a>';

// Display the Footer
require(FOOTER_PATH);