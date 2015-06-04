<?php

class Database_UniqueID_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "Database_UniqueID";
	public $title = "Unique Numerical ID Generator";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Creates numerically unique ID counters that generate new, unique IDs when called.";
	public $dependencies = array("SiteVariable");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		// Create an UniqueID Tracker
		return Database_UniqueID::newCounter("unique");
	}
	
}