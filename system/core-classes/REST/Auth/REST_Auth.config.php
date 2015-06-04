<?php

class REST_Auth_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "REST_Auth";
	public $title = "API Authentication Handler";
	public $version = 1.00;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows other sites to connect to your REST APIs.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `api_authentication`
		(
			`domain`				varchar(48)					NOT NULL	DEFAULT '',
			`site_name`				varchar(48)					NOT NULL	DEFAULT '',
			`clearance`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`auth_key`				varchar(100)				NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`domain`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		return Database_Meta::columnsExist("api_authentication", array("domain", "auth_key"));
	}
	
}