<?php

class Security_ThreatTracker_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "Security_ThreatTracker";
	public $title = "Threat Tracking System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides simple tools to warn you about unsanitized input that may be potentially dangerous.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_threat_tracker` (
			
			`severity`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`date_logged`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`threat_type`			varchar(12)					NOT NULL	DEFAULT '',
			`threat_text`			varchar(250)				NOT NULL	DEFAULT '',
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`ip`					varchar(45)					NOT NULL	DEFAULT '',
			
			`function_call`			varchar(100)				NOT NULL	DEFAULT '',
			`file_path`				varchar(32)					NOT NULL	DEFAULT '',
			`file_line`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`url_path`				varchar(64)					NOT NULL	DEFAULT '',
			
			`data_captured`			text						NOT NULL	DEFAULT '',
			
			INDEX (`severity`, `date_logged`),
			INDEX (`uni_id`, `ip`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		return Database_Meta::columnsExist("log_threat_tracker", array("severity", "date_logged", "threat_type", "uni_id", "ip"));
	}
	
}