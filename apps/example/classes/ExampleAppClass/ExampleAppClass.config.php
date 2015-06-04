<?php

class ExampleAppClass_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "ExampleAppClass";
	public $title = "An example class";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a simple example.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		/*
			The "Example Table" below will be automatically built during the installation process.
			
			You can include other tables here as well.
		*/
		
		// Example Table
		Database::exec("
		CREATE TABLE IF NOT EXISTS `example_table`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`words`					varchar(72)					NOT NULL	DEFAULT '',
			`description`			varchar(250)				NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`id`) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = Database_Meta::columnsExist("example_table", array("id"));
		
		return ($pass1);
	}
	
}