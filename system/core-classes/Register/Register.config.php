<?php

class Register_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "Register";
	public $title = "User Registration System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows users to register through any site, and authenticates them through Auth.";
	public $dependencies = array("User");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		// If already installed, don't run this section again
		if($this->isInstalled()) { return true; }
		
		# Remove the uni_id, partitions, and indexes that interfere with the updates
		Database_Meta::dropIndex("users", "uni_id");
		Database_Meta::removePartitions("users");
		Database_Meta::dropColumn("users", "uni_id");
		
		# Prepare new columns for the users table
		Database_Meta::addColumn("users", "uni_id", "int(10) unsigned not null auto_increment primary key first", "");
		Database_Meta::addColumn("users", "email", "varchar(80) not null", "");
		Database_Meta::addColumn("users", "password", "varchar(128) not null", "");
		Database_Meta::addColumn("users", "verified", "tinyint(1) unsigned not null", "0");
		Database_Meta::addColumn("users", "referred_by", "int(10) unsigned not null", "0");
		
		# Set the partition on the users table
		Database_Meta::setPartitions("users", "key", "uni_id", 13);
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		return Database_Meta::columnsExist("users", array("uni_id", "handle", "password"));
	}
	
}
