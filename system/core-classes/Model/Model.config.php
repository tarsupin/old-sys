<?php

class Model_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "Model";
	public $title = "Model Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides CRUD and REST behavior with data models.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `authentication`
		(
			`domain`				varchar(64)					NOT NULL	DEFAULT '',
			`shared_key`			varchar(120)				NOT NULL	DEFAULT '',
			
			`site_name`				varchar(32)					NOT NULL	DEFAULT '',
			`clearance`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`domain`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `authentication_tokens`
		(
			`public_salt`			varchar(64)					NOT NULL	DEFAULT '',
			`expires`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`public_salt`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = Database_Meta::columnsExist("authentication", array("domain", "shared_key"));
		$pass2 = Database_Meta::columnsExist("authentication_tokens", array("public_salt", "expires"));
		
		return ($pass1 and $pass2);
	}
	
}