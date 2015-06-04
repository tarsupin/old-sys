<?php

class UnityAccount_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "UnityAccount";
	public $title = "Unity Account Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the core functionality for dealing with Unity Accounts.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `unity_accounts`
		(
			`username`				varchar(22)					NOT NULL	DEFAULT '',
			`password`				varchar(128)				NOT NULL	DEFAULT '',
			
			UNIQUE (`username`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(username) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = Database_Meta::columnsExist("unity_accounts", array("username", "password"));
		
		return ($pass1);
	}
	
}