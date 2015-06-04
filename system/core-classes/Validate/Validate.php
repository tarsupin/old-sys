<?php /*

--------------------------------------
------ About the Validate Class ------
--------------------------------------

This class provides common validations to a variety of user inputs. These validations will automatically apply the appropriate error Alerts on any invalid results, which can be used to identify if a form was successful or not.

This class works closely with the "Sanitize" class; it relies on the methods used in Sanitize to identify where several key validation conflicts occur.


-----------------------------------------
------ Example of using this class ------
-----------------------------------------

// Make sure the form was submitted correctly before processing the input
if(Form::submitted())
{
	// Check if all of the input you sent is valid: 
	Validate::number("Year of Birth", $_POST['birthYear'], 1900, 2012);
	
	Validate::number_float("Percent of Interest", $_POST['interest'], 0.00, 100.00);
	
	Validate::variable("Username", $_POST['username'], 1, 22);		// Length between 1 and 22 (variable)
	Validate::text("Display Name", $_POST['displayName'], 1, 22);	// Length between 1 and 22 (text)
	Validate::text("My Biography", $_POST['biography']);			// No length requirements
	
	Validate::confirmation("Terms of Service", isset($_POST['tos']));
	
	// You may have custom checks that aren't handled by Validate()
	if($_POST['myAge'] < 13)
	{
		Alert::error("Age", "You must be at least 13 to use this site.");
	}
	
	// Now check if the form has passed
	if(Validate::pass())
	{
		echo "Everything checks out. Update the database and redirect to the success page!";
		
		header("Location: /login-success"); exit;
	}
	
	// If the form fails, output the alerts:
	else
	{
		echo Alert::display();
	}
}


-------------------------------
------ Methods Available ------
-------------------------------

Validate::input($name, $value, $minLen, $maxLen = 0, $extra)	// Validates a simple text input field.
Validate::text($name, $value, $minLen, $maxLen = 0, $extra)		// Validates a text input (converts HTML entities)
Validate::variable($name, $value, $minLen, $maxLen, $extra)		// Validates a variable with $extra characters
Validate::url($name, $value, $minLen, $maxLen)					// Validates a url
Validate::number($name, $value, $minVal, $maxVal = 0)			// Validates a field that must be a number.
Validate::confirmation($name, $value)							// Validates a confirmation field.

Validate::pass()		// Returns true if there are no errors.

Validate::username($username);						// Validates a username.
Validate::email($email);							// Validates that your email is acceptable.
Validate::password($password, $confirm = false);	// Validates that your password is secure enough.

*/

abstract class Validate {
	
	
/****** Call Static ******/
	public static function __callStatic
	(
		$name		// <str> The method that will be called.
	,	$args		// <int:mixed> The arguments being passed.
	)				// RETURNS <bool> TRUE if the value is considered valid, FALSE otherwise.
	
	{
		/*
			The argument list is constructed as follows:
			
			$args[0] = $fieldName
			$args[1] = $dataToValidate
			$args[2] = $minimumRange		(length of string or minimum value of integer)
			$args[3] = $maximumRange		(length of string or minimum value of integer)
			$args[4] = $extraCharacters
		*/
		
		// Check if the minimum ranges are met
		if(isset($args[2]))
		{
			// If the value is a string, validate the minimum character length of the string
			if(is_string($args[1]) and strlen($args[1]) < $args[2])
			{
				Alert::error("Validate " . $args[0], $args[0] . " must be " . $args[2] . " characters or more.");
				
				return false;
			}
			
			// If the value is a number, validate the minimum numeric limit
			else if(is_numeric($args[1]) and (float) $args[1] < (float) $args[2])
			{
				Alert::error("Validate " . $args[0], $args[0] . " cannot be lower than " . $args[2] . ".");
				
				return false;
			}
		}
		
		// Check if maximum ranges are met
		if(isset($args[3]))
		{
			// If the value is a string, validate the maximum character length of the string
			if(is_string($args[1]) and strlen($args[1]) > $args[3])
			{
				Alert::error("Validate " . $args[0], $args[0] . " cannot exceed " . $args[3] . " characters in length.");
				
				return false;
			}
			
			// If the value is a number, validate the maximum numeric limit
			else if(is_numeric($args[1]) and (float) $args[1] > (float) $args[3])
			{
				Alert::error("Validate " . $args[0], $args[0] . " cannot be greater than " . $args[3] . ".");
				
				return false;
			}
		}
		
		// Check if there are any illegal characters detected
		if($illegalChars = call_user_func_array(array("Sanitize", $name), array($args[1], (isset($args[4]) ? $args[4] : ""), true)))
		{
			Alert::error("Validate " . $args[0], $args[0] . " does not allow: " . self::announceIllegalChars($illegalChars), 3);
			
			return false;
		}
		
		return true;
	}
	
	
/****** Check if a username is valid ******/
	public static function username
	(
		$username			// <str> The username to validate.
	)						// RETURNS <bool> TRUE if the username is valid, FALSE otherwise.
	
	// Validate::username($username);
	{
		// Retrieve configuration for the user
		if(!Config::$extraData = Config::get("Username"))
		{
			Alert::error("Username Config", "Unable to retrieve configurations for username.");
			
			return false;
		}
		
		// Prepare Values
		$lenReduce = Config::$extraData['Use Letter First'] ? 1 : 0;
		$regexChars = preg_quote(Config::$extraData['Allow Special']);
		
		// Make sure the username is long enough
		if(strlen($username) < Config::$extraData['Min Length'])
		{
			Alert::error("Username Min Length", "Username requires " . Config::$extraData['Min Length'] . " characters.");
			
			return false;
		}
		
		// Make sure the username isn't too long
		if(strlen($username) > Config::$extraData['Max Length'])
		{
			Alert::error("Username Max Length", "Username cannot exceed " . Config::$extraData['Max Length'] . " characters.");
			
			return false;
		}
		
		// Set username to lowercase (if applicable)
		if(Config::$extraData['Use Lowercase'])
		{
			$username = strtolower($username);
		}
		
		// If the username requires a letter to be first
		if(Config::$extraData['Use Letter First'])
		{
			// Test if the username is valid
			if(!preg_match('/^' . (Config::$extraData['Use Letter First'] ? '[a-zA-Z]{1}' : '') . '$/', $username[0]))
			{
				Alert::error("Username FirstChar", "Username must start with a letter.");
				
				return false;
			}
		}
		
		// Prepare values for the Regex Expression
		$regexAppend = (Config::$extraData['Use Lowercase'] ? "" : "A-Z") . (Config::$extraData['Allow Special'] ? preg_quote(Config::$extraData['Allow Special']) : "");
		
		$regex = '/^' . (Config::$extraData['Use Letter First'] ? '[a-z' . $regexAppend . ']{1}' : '') . '[a-z' . $regexAppend . (Config::$extraData['Allow Numbers'] ? "0-9" : '') . ']{' . (Config::$extraData['Min Length'] - $lenReduce) . ',' . (Config::$extraData['Max Length'] - $lenReduce) . '}$/';
		
		// Test if the username is valid
		if(!preg_match($regex, $username, $matches))
		{
			// If we're using special characters, error message must include them
			if(Config::$extraData['Allow Special'])
			{
				Alert::error("Username Special", "Username can only use letters" . (Config::$extraData['Allow Numbers'] ? ", numbers," : "") . " and: " . Config::$extraData['Allow Special']);
			}
			else
			{
				Alert::error("Username Special", "Username can only use letters" . (Config::$extraData['Allow Numbers'] ? " and numbers" : "") . ".");
			}
			
			return false;
		}
		
		// The username is valid - return true
		return true;
	}
	
	
/****** Check if a password is strong enough for this system ******/
	public static function password
	(
		$password 			// <str> The plaintext password that you want to validate the strength of.
	)						// RETURNS <bool> TRUE if the password is valid (strong enough).
	
	// Validate::password($password);
	{
		// Retrieve configuration for the password
		if(!Config::$extraData = Config::get("Password"))
		{
			Alert::error("Password Config", "Unable to retrieve configurations for passwords.");
			
			return false;
		}
		
		// Make sure the password is long enough
		if(strlen($password) < Config::$extraData['Min Length'])
		{
			Alert::error("Password Min Length", "Password requires " . Config::$extraData['Min Length'] . " characters.");
			
			return false;
		}
		
		// Make sure there are a sufficient number of upper-case characters
		if(Config::$extraData['Min UpperCase'] > 0)
		{
			preg_match_all('/[A-Z]/', $password, $matches);
			
			if(count($matches[0]) < Config::$extraData['Min UpperCase'])
			{
				Alert::error("Password Min Upper", "Password requires " . Config::$extraData['Min UpperCase'] . " uppercase characters.");
				
				return false;
			}
		}
		
		// Make sure there are a sufficient number of lower-case characters
		if(Config::$extraData['Min LowerCase'] > 0)
		{
			preg_match_all('/[a-z]/', $password, $matches);
			
			if(count($matches[0]) < Config::$extraData['Min LowerCase'])
			{
				Alert::error("Password Min Lower", "Password requires " . Config::$extraData['Min LowerCase'] . " lowercase characters.");
				
				return false;
			}
		}
		
		// Make sure there are a sufficient number of digits
		if(Config::$extraData['Min Numbers'] > 0)
		{
			preg_match_all('/[0-9]/', $password, $matches);
			
			if(count($matches[0]) < Config::$extraData['Min Numbers'])
			{
				Alert::error("Password Min Numbers", "Password requires " . Config::$extraData['Min Numbers'] . " numbers.");
				
				return false;
			}
		}
		
		// Make sure there are a sufficient number of special characters
		if(Config::$extraData['Min Special'] > 0)
		{
			preg_match_all('/[!@#$%^&*() _+=\-\[\]\';,.\/{}|":<>?`~\\\\]/', $password, $matches);
			
			if(count($matches[0]) < Config::$extraData['Min Special'])
			{
				Alert::error("Password Min Special", "Password requires " . Config::$extraData['Min Special'] . " special characters.");
				
				return false;
			}
		}
		
		return true;
	}
	
	
/****** Parses through an email string ******/
	public static function email
	(
		$email		// <str> The email string to parse.
	)				// RETURNS <str:str> data about the email, or array() on failure.
	
	// Validate::email($email)
	{
		// Make sure the email doesn't contain illegal characters
		$illegalChars = Sanitize::email($email, "", true);
		
		if($illegalChars != array())
		{
			Alert::error("Validate Email", "The email does not allow: " . self::announceIllegalChars($illegalChars), 3);
			return false;
		}
		
		// Make sure the email has an "@"
		if(strpos($email, "@") === false)
		{
			Alert::error("Validate Email", "Email improperly formatted: doesn't include an @ character.", 3);
			return false;
		}
		
		// Prepare Values
		$emailData = array();
		$exp = explode("@", $email);
		
		$emailData['full'] = $email;
		$emailData['username'] = $exp[0];
		$emailData['domain'] = $exp[1];
		
		$lenEmail = strlen($email);
		$lenUser = strlen($emailData['username']);
		$lenDomain = strlen($emailData['domain']);
		
		// Check if the email is too long
		if($lenEmail > 72)
		{
			Alert::error("Validate Email", "Email is over 72 characters long.", 1);
			return false;
		}
		
		// Check if the username is too long
		if($lenUser < 1 or $lenUser > 50)
		{
			Alert::error("Validate Email", "Email username must be between 1 and 50 characters.", 2);
			return false;
		}
		
		// Check if the domain is too long
		if($lenDomain < 1 or $lenDomain > 50)
		{
			Alert::error("Validate Email", "Email domain must be between 1 and 50 characters.", 2);
			return false;
		}
		
		// Check for valid emails with the username
		if($emailData['username'][0] == '.' or $emailData['username'][($lenUser - 1)] == '.')
		{
			Alert::error("Validate Email", "Email username cannot start or end with a period.", 5);
			return false;
		}
		
		// Username cannot have two consecutive dots
		if(strpos($emailData['username'], "..") !== false)
		{
			Alert::error("Validate Email", "Email username cannot contain two consecutive periods.", 5);
			return false;
		}
		
		// Check the domain for valid characters
		if(!IsSanitized::variable($emailData['domain'], "-."))
		{
			Alert::error("Validate Email", "Email domain was not properly sanitized.", 3);
			return false;
		}
		
		// The email was successfully validated
		return true;
	}
	
	
/****** Validate a Confirmation Field (such as checkbox) ******/
	public static function confirmation
	(
		$name		// <str> The name of the checkbox.
	,	$bool		// <bool> Whether or not the value is set
	)				// RETURNS <void>
	
	// Validate::confirmation("Terms of Service", isset($_POST['tos']));
	{
		if(!$bool)
		{
			Alert::error($name, "You must confirm " . $name);
		}
	}
	
	
/****** Check if Form Validation passed ******/
	public static function pass
	(
		$key = ""		// <str> If specified, it checks the particular error key only.
	)					// RETURNS <bool> TRUE if validation passed, FALSE if not.
	
	// Validate::pass([$key]);
	{
		return (Alert::hasErrors($key) ? false : true);
	}
	
	
/****** Announce Illegal Characters ******/
	public static function announceIllegalChars
	(
		$illegalChars		// <int:str> The illegal characters that were identified.
	)						// RETURNS <str> the illegal characters identified
	
	// self::announceIllegalChars($illegalChars);
	{
		// Prepare Values
		$announce = "";
		$maxShow = 6;
		
		// Some characters need to be specifically called out for readability
		$search = array(" ", "	", chr(13), "
" => "(newlines)");
		$replacements = array("spaces", "tabs", "newlines", "newlines");
		
		// Loop through each illegal character and add it to the announcement
		foreach($illegalChars as $char)
		{
			$announce .= ($announce === "" ? "" : ", ") . str_replace($search, $replacements, $char);;
			
			if($maxShow == 0) { $announce .= ", and others."; break; }
			$maxShow--;
		}
		
		return htmlspecialchars($announce);
	}
	
}
