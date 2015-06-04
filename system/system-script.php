<?php if(!defined("SITE_PATH") or !defined("PROTECTED")) { die("No direct script access allowed."); }  /*

-------------------------------------
------ About the System Script ------
-------------------------------------

File Path: {SYS_PATH}/system-script.php

This script is designed to hold instructions that ALL SITES on this server should be updating. Though each site has to have this action triggered independently, the script exists for the purpose of re-usability across multiple sites.

This page can be activated from the site's admin panel, or can be triggered by a command from Auth. This script is designed to be particularly helpful for localhost development, but can also serve practical purposes for production environments when used for appropriate tasks.

Due to the increased need for security of this page, it cannot be accessed directly, and can only be accessed through pages that also define the "PROTECTED" constant, specifically to enable this script.

*/

// Load administrative database privileges
Database::initRoot();

// Prepare your script(s) below:
/*
if(Database_Meta::tableExists("notifications"))
{
	Database_Meta::renameColumn("notifications", "category", "note_type");
	Database_Meta::addColumn("users", "date_notes", "int(10) unsigned NOT NULL", 0);
}

if(Database_Meta::tableExists("content_block_video"))
{
	Database_Meta::renameColumn("content_block_video", "class", "video_class");
	Database_Meta::renameColumn("content_block_video", "caption", "video_caption");
}

if(Database_Meta::tableExists("content_block_image"))
{
	Database_Meta::renameColumn("content_block_image", "class", "img_class");
}

if(Database_Meta::tableExists("users_friends"))
{
	Database_Meta::dropTable("users_friends");
}
*/

if(Database_Meta::tableExists("content_block_image"))
{
	// Database_Meta::editColumn("content_block_image", "caption", "varchar(180) not null", 0);
}
