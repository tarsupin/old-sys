<?php

// Make sure the user is root
CLI_User::verify(CLI_User::SCRIPT_DIES_ON_FAILURE, "root");

$files = File_Scan::scanRecursive(APP_PATH, "*.php");

var_dump($files);
