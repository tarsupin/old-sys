<?php

// Form Submission
if(Form::submitted("ban-user-uni6"))
{
	// Check if all of the input you sent is valid: 
	Validate::variable("Handle", $_POST['handle'], 1, 22);
	
	// Final Validation Test
	if(Validate::pass())
	{
		$uniID = User::getIDByHandle($_POST['handle']);
		
		if(Database::query("UPDATE users SET clearance=? WHERE uni_id=? LIMIT 1", array(-3, $uniID)))
		{
			Alert::success("Ban Success", "You have successfully banned " . $_POST['handle']);
		}
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the Editing Form
echo '
<h3>Permanently Ban Which User?</h3>
<form class="uniform" action="/admin/User/Ban User" method="post">' . Form::prepare("ban-user-uni6") . '

<p>
	<strong>Handle:</strong><br />
	<input type="text" name="handle" value="" style="width:200px;" maxlength="22" />
</p>

<p><input type="submit" name="submit" value="Ban User" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
