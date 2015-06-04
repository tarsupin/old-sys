<?php

class API_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "API";
	public $title = "API System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to create APIs that other phpTesla sites can interact with.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `api_conf_hash`
		(
			`conf`					varchar(22)					NOT NULL	DEFAULT '',
			`date_run`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`conf`),
			INDEX (`date_run`)
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
		return Database_Meta::columnsExist("api_conf_hash", array("conf", "date_run"));
	}
	
}