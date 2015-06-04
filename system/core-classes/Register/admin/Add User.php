<?php

// Form Submission
if(Form::submitted("add-user-uni6"))
{
	// Check if all of the input you sent is valid: 
	Validate::variable("Handle", $_POST['handle'], 1, 22);
	Validate::text("Display Name", $_POST['display_name'], 3, 22);
	Validate::password($_POST['password']);
	Validate::email($_POST['email']);
	
	// Check if the handle has already been taken
	if(AppAccount::handleTaken($_POST['handle']))
	{
		Alert::error("Handle Taken", "That handle has already been taken", 1);
	}
	
	if(Database::selectOne("SELECT email FROM users WHERE email=? LIMIT 1", array($_POST['email'])))
	{
		Alert::error("Email", "That email already exists.", 1);
	}
	
	// Final Validation Test
	if(Validate::pass())
	{
		Database::startTransaction();
		
		$uniID = 0;
		
		// Check if the account already exists
		if($checkAuth = Database::selectValue("SELECT uni_id FROM users WHERE handle=? LIMIT 1", array($_POST['handle'])))
		{
			$uniID = (int) $checkAuth;
		}
		
		// If the account doesn't already exist, try to create it
		else if($regSuccess = Database::query("INSERT INTO users (handle, display_name, email, password, date_joined, auth_token, verified) VALUES (?, ?, ?, ?, ?, ?, ?)", array($_POST['handle'], $_POST['display_name'], $_POST['email'], Security_HashPassword::set($_POST['password']), time(), Security_Hash::random(22, 72), 1)))
		{
			$uniID = (int) Database::$lastID;
			
			if(isset($_POST['send_email']))
			{
				// Email a verification letter
				AppVerification::sendVerification($uniID);
				
				Alert::success("Email Sent", "The account was created successfully! A verification email has been sent to " . $_POST['email'] . "!");
			}
			else
			{
				Alert::success("User Added", "The account was created successfully!");
			}
		}
		
		// Create the account
		if($uniID)
		{
			$pass = Database::query("INSERT INTO users_handles (handle, uni_id) VALUES (?, ?)", array($_POST['handle'], $uniID));
			
			if(Database::endTransaction($pass))
			{
				// Create the ProfilePic for this Account
				$packet = array(
					"uni_id"		=> $uniID
				,	"title"			=> $_POST['display_name']
				);
				
				$response = API_Connect::to("profile_picture", "SetDefaultPic", $packet);
				
				// Reset Values
				$_POST['handle'] = "";
				$_POST['display_name'] = "";
				$_POST['email'] = "";
				$_POST['password'] = "";
			}
		}
		else
		{
			Database::endTransaction(false);
			
			Alert::error("Process Error", "An error has occurred while processing this registration.", 1);
		}
	}
}

// Sanitize Post Values
else
{
	$_POST['email'] = isset($_POST['email']) ? Sanitize::email($_POST['email']) : "";
	$_POST['password'] = isset($_POST['password']) ? Sanitize::safeword($_POST['password']) : "";
	$_POST['handle'] = isset($_POST['handle']) ? Sanitize::variable($_POST['handle']) : "";
	$_POST['display_name'] = isset($_POST['display_name']) ? Sanitize::safeword($_POST['display_name'], ' ') : "";
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the Editing Form
echo '
<h3>Add a New User</h3>
<form class="uniform" action="/admin/AppAccount/Add User" method="post">' . Form::prepare("add-user-uni6") . '

<p>
	<strong>Profile Handle:</strong><br />
	<input type="text" name="handle" value="' . $_POST['handle'] . '" style="width:200px;" maxlength="22" />
</p>

<p>
	<strong>Profile Display Name:</strong><br />
	<input type="text" name="display_name" value="' . $_POST['display_name'] . '" style="width:200px;" maxlength="32" />
</p>

<p>
	<strong>Password:</strong><br />
	<input type="password" name="password" value="' . $_POST['password'] . '" style="width:95;" maxlength="100" />
</p>

<p>
	<strong>Email:</strong><br />
	<input type="text" name="email" value="' . $_POST['email'] . '" style="width:95%;" maxlength="80" />
</p>

<p>
	<strong>Send Verification Email:</strong><br />
	<input type="checkbox" name="send_email" checked /> Send the user a verification email.
</p>

<p><input type="submit" name="submit" value="Add User" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
