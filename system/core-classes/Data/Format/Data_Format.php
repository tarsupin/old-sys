<?php /*

-----------------------------------------
------ About the Data_Format Class ------
-----------------------------------------

This allows you to manipulate text in useful ways, such as to secure dangerous characters.


-------------------------------
------ Methods Available ------
-------------------------------

Data_Format::safeText($text)				// Changes illegal characters to safe HTML equivalents
Data_Format::overProtectiveText($text);			// Changes basically everything to safe HTML equivalents
Data_Format::convertWindowsText($text);

*/

abstract class Data_Format {
	
	
/****** Safe Conversion of Text into HTML ******/
# Special thanks to anonymous "info" poster on PHP.net
	public static function safeText
	(
		$text		// <str> The text that you want to convert to safe HTML entities.
	)				// RETURNS <str> The safe HTML-ready text.
	
	// $safeText = Data_Format::safeText("<àáâãäåæ¼½> and other such characters.");
	{
		$newText = "";
		$textLen = strlen($text);
		
		// Allows: !#$()*+,:;=?@[]^`{|}
		$allowed = array(33, 35, 36, 39, 40, 41, 42, 43, 44, 47, 58, 59, 61, 63, 64, 91, 93, 94, 96, 123, 124, 125);
		
		for($i = 0;$i < $textLen;$i++)
		{
			$chrNum = hexdec(rawurlencode(substr($text, $i, 1)));
			
			if($chrNum < 33 || $chrNum > 1114111)
			{
				$newText .= substr($text, $i, 1);
			}
			else
			{
				if(in_array($chrNum, $allowed))
				{
					$newText .= substr($text, $i, 1);
				}
				else
				{
					$newText .= "&#" . $chrNum . ";";
				}
			}
		}
		
		return $newText;
	}
	
	
/****** Completely Safe Conversion of Text into HTML ******/
# Special thanks to anonymous "info" poster on PHP.net.
# This system prevents basically every type of character, including things like ! and #
	public static function overProtectiveText
	(
		$text		// <str> The text that you want to convert to safe HTML entities.
	)				// RETURNS <str> The safe HTML-ready text.
	
	// List of Example Characters Protected: ~-_. !\"#$%&'()*+,/:;<=>?@[\]^`{|}
	// $safeText = Data_Format::overProtectiveText("<àáâãäåæ¼½> and other such characters.");
	{
		$newText = "";
		$textLen = strlen($text);
		
		for($i = 0;$i < $textLen;$i++)
		{
			$chrNum = hexdec(rawurlencode(substr($text, $i, 1)));
			
			if($chrNum < 33 || $chrNum > 1114111)
			{
				$newText .= substr($text, $i, 1);
			}
			else
			{
				$newText .= "&#" . $chrNum . ";";
			}
		}
		
		return $newText;
	}
	
	
/****** Convert Windows Text ******/
	public static function convertWindowsText
	(
		$text		// <str> The text that you want to convert windows text from.
	)				// RETURNS <str> The converted text.
	
	// $fixText = Data_Format::convertWindowsText($text);
	{
		$text = htmlentities($text, ENT_IGNORE, 'ISO-8859-1');
		
		$map = array(
			"&lsquo;" => "'"
		,	"&rsquo;" => "'"
		,	"&sbquo;" => ","
		,	"&ldquo;" => '"'
		,	"&rdquo;" => '"'
		,	"&ndash;" => '-'
		,	"–" => '-'
		,	"“" => '"'
		,	"”" => '"'
		,	"‘" => "'"
		,	"’" => "'"
		,	"‚" => ','
		,	"„" => '"'
		);
		
		return strtr($text, $map);
	}
	
}
