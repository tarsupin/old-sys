<?php

class Cache_Memory_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "Cache_Memory";
	public $title = "Data Caching System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Caches data in memory, and reduces the number of calls to expensive operations.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		return Cache_Memory::sql();
	}
	
}