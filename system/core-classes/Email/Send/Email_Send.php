<?php /*

----------------------------------------
------ About the Email_Send Class ------
----------------------------------------

This class allows you to send emails, as well as provides handling for attachments.


-----------------------------------------
------ Example of using the plugin ------
-----------------------------------------

// Emails "someone@hotmail.com" with an important update.
Email_Send::standard("someone@hotmail.com", "Super Important", "Dude, this is incredibly important!!!");


-------------------------------
------ Methods Available ------
-------------------------------

// Sends a standard email
Email_Send::standard($emailTo, $subject, $message, [$emailFrom], [$headers])

// Sends an email with an attachment
Email_Send::withAttachment($emailTo, $subject, $message, $filepath, $filename, [$emailFrom])

*/

abstract class Email_Send {
	
	
/****** Sends a Simple Email ******/
	public static function send
	(
		$emailTo			// <mixed> The email(s) you're sending a message to.
	,	$subject			// <str> The subject of the message.
	,	$message			// <str> The content of your message.
	,	$emailFrom = ""		// <str> An email that you would like to send from.
	,	$headers = ""		// <str> A set of headers in a single string (use cautiously).
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Email_Send::standard(array("somebody@email.com"), "Greetings!", "This welcome message will make you feel welcome!")
	{
		// Determine the Email being sent from
		if(!$emailFrom)
		{
			$emailFrom = "admin@" . BASE_DOMAIN . URL_SUFFIX;
		}
		
		// Handle Email Recipients
		if(is_array($emailTo))
		{
			foreach($emailTo as $next)
			{
				if(!Email::isValid($next))
				{
					Alert::error("Email", "Illegal email used, cannot send email.", 3);
					return false;
				}
			}
			
			$emailTo = implode(", ", $emailTo);
		}
		else if(!Email::isValid($emailTo))
		{
			Alert::error("Email", "Illegal email used, cannot send email.", 3);
			return false;
		}
		
		// Handle the Email Headers
		if($headers == "")
		{
			$headers = 'From: ' . $emailFrom . "\r\n" .
			'Reply-To: ' . $emailFrom . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		}
		
		// Record this email in the database
		$primeRecipient = is_array($emailTo) ? $emailTo[0] : $emailTo;
		
		$details = array(
			"recipients"	=> $emailTo
		,	"sender"		=> $emailFrom
		);
		
		Database::query("INSERT INTO log_email (recipient, subject, message, details, date_sent) VALUES (?, ?, ?, ?, ?)", array($primeRecipient, $subject, $message, json_encode($details), time()));
		
		// Localhost Versions, just edit email.html with the message
		if(ENVIRONMENT == "local")
		{
			return File::write(APP_PATH . "/email.html", "To: " . $emailTo . "
From: " . $emailFrom . "
Subject: " . $subject . "

" . $message);
		}
		
		// Send the Mail
		if(!mail($emailTo, $subject, $message, $headers))
		{
			Alert::error("Email", "Email was not sent properly", 4);
			return false;
		}
		
		return true;
	}
	
	
/****** Sends an Email with an Attachment ******/
# dqhendricks on Stack Overflow provided the "$header" code of this section.
	public static function sendAttachment
	(
		$emailTo			// <mixed> The email(s) you're sending a message to.
	,	$subject			// <str> The subject of the message.
	,	$message			// <str> The content of your message.
	,	$filePath			// <str> The file path to the attachment you're sending.
	,	$filename			// <str> The name of the file as you'd like it to appear.
	,	$emailFrom = ""		// <str> An email that you would like to send from.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Email_Send::withAttachment("joe@email.com", "Hi!", "Sup!?", "./assets/file.csv", "excelPage.csv"])
	// May use: $_FILES["file"]["tmp_name"] and $_FILES["file"]["name"]
	{
		// Determine the Email being sent from
		if(!$emailFrom)
		{
			$emailFrom = "admin@" . BASE_DOMAIN . URL_SUFFIX;
		}
		
		// Handle Email Recipients
		if(is_array($emailTo))
		{
			foreach($emailTo as $next)
			{
				if(!Email::isValid($next))
				{
					Alert::error("Email", "Illegal email used, cannot send email.", 3);
					return false;
				}
			}
			
			$emailTo = implode(", ", $emailTo);
		}
		else if(!Email::isValid($emailTo))
		{
			Alert::error("Email", "Illegal email used, cannot send email.", 3);
			return false;
		}
		
		// $filePath should include path and filename
		$filename = basename($filename);
		$file_size = filesize($filePath);
		
		$content = chunk_split(base64_encode(file_get_contents($filePath))); 
		
		$uid = md5(uniqid(time()));
		
		// Designed to prevent email injection, although we should run stricter validation if we're going to allow
		// other people to insert emails into the email.
		$emailFrom = str_replace(array("\r", "\n"), '', $emailFrom);
		
		// Prepare header
		$header = "From: ".$emailFrom."\r\n"
			."MIME-Version: 1.0\r\n"
			."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
			."This is a multi-part message in MIME format.\r\n" 
			."--".$uid."\r\n"
			."Content-type:text/plain; charset=iso-8859-1\r\n"
			."Content-Transfer-Encoding: 7bit\r\n\r\n"
			.$message."\r\n\r\n"
			."--".$uid."\r\n"
			."Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"
			."Content-Transfer-Encoding: base64\r\n"
			."Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n"
			.$content."\r\n\r\n"
			."--".$uid."--";
		
		// Record this email in the database
		$primeRecipient = is_array($emailTo) ? $emailTo[0] : $emailTo;
		
		$details = array(
			"recipients"	=> $emailTo
		,	"sender"		=> $emailFrom
		,	"file"			=> $filename
		);
		
		Database::query("INSERT INTO log_email (recipient, subject, message, details, date_sent) VALUES (?, ?, ?, ?, ?)", array($primeRecipient, $subject, $message, json_encode($details), time()));
		
		// Localhost Versions, just edit email.html with the message
		if(ENVIRONMENT == "local")
		{
			return File::write(APP_PATH . "/email.html", "To: " . $emailTo . "
From: " . $emailFrom . "
Subject: " . $subject . "
Attachment: " . $filename . "

" . $message);
		}
		
		// Send the email
		if(!mail($emailTo, $subject, "", $header))
		{
			Alert::error("Email", "Email was not sent properly.", 4);
			return false;
		}
		
		return true;
	}
	
}
