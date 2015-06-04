<?php

class VersionControl_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "Version Control Class";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Handles version control for database tables.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `version_control_commits`
		(
			`branch_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`commit_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`author`				varchar(64)					NOT NULL	DEFAULT '',
			`description`			varchar(200)				NOT NULL	DEFAULT '',
			`timestamp`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`instruction_set`		longtext					NOT NULL	DEFAULT '',
			
			UNIQUE (`branch_id`, `commit_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(branch_id, commit_id) PARTITIONS 31;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = Database_Meta::columnsExist("version_control_commits", array("branch_id", "commit_id"));
		
		return ($pass1);
	}
	
}