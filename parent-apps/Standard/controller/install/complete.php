<?php

// Set the Installation Value to complete
SiteVariable::save("site-configs", "install-complete", 1);


// Run Global Script
require(PARENT_APP_PATH . "/includes/install_global.php");

// Display the Header
require(HEADER_PATH);

// Display the Page
echo '
<h1>Installation Complete!</h1>

<h3>Your site is ready!</h3>
<p>Include more information here, such as information on what you should do now that the site is installed: a test to see if your code is up to date, access to our plugin and theme pages, recent news and updates, etc.</p>

<p><a class="button" href="/">Finish Installation</a></p>';

echo '
</form>';

// Display the Footer
require(FOOTER_PATH);