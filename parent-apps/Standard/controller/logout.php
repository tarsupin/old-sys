<?php 

// End all user sessions and cookies for the server
Logout::server();

// Log out of the UniFaction's Auth system
header("Location: " . URL::auth_unifaction_com() . "/logout?ret=" . urlencode(FULL_DOMAIN . URL_SUFFIX)); exit;