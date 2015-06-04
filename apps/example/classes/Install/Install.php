<?php

// Installation
abstract class Install extends Installation {
	
	
/****** Class Variables ******/
	
	// These plugin classes will be selected for installation during the "addon" installation process:
	public static $pluginClasses = array(	// <str:bool>
	//	"Email"			=> true
	//,	"Confirm"		=> true
	);
	
	
/****** App-Specific Installation Processes ******/
	public static function setup(
	)					// RETURNS <void>
	
	{
		return true;
	}
}
