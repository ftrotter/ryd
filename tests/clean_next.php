<?php

	require_once("../config.php");

	//this test script will clean the database from anything to do with Fred Trotters personal data
	//so that fred.trotter@gmail.com etc will be useful for initial signup again...

if(
	strcmp($GLOBALS['base_url'],'http://next.yourdoctorsadvice.org/') == 0)
{//then this is the test instance, and it is OK to blow it away...


$sql = "TRUNCATE TABLE  `phones`";	
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted phones\n";

$sql = "TRUNCATE TABLE  `invites`";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted invites\n";

$sql = "TRUNCATE TABLE  `users`";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted users\n";

$sql = "TRUNCATE TABLE  `hybrid_user_instance`";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted hybrids\n";

$sql = "TRUNCATE TABLE  `openid_email_users`";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted openid users\n";

$sql = "TRUNCATE TABLE  `openid_email_users`";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted openid users\n";


}else{
	echo "My god man, you just tried to run the death script against a live instance! You should be ashamed! \n";
}

?>
