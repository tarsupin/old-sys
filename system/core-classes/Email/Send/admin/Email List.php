<?php 

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the Report List
echo '
<a href="/admin/Email/Send Email">Send Email</a>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
