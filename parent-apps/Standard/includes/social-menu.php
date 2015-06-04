<?php 

// Display the viewed user's social menu
if(You::$handle)
{
	$uniMenu = '
	<li class="menu-slot social-menu"><a href="' . URL::unifaction_social() . '/' . You::$handle . '">@' . You::$handle . '</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . You::$handle . '">Unity Wall</a></li><li class="dropdown-slot"><a href="' . URL::inbox_unifaction_com() . '/to/' . You::$handle . '">Send Message</a></li><li class="dropdown-slot"><a href="' . URL::blogfrog_social() . '/' . You::$handle . '">BlogFrog</a></li></ul>';
}