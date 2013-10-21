<?php

	require_once("../config.php");

	//this test script will clean the database from anything to do with Fred Trotters personal data
	//so that fred.trotter@gmail.com etc will be useful for initial signup again...

$sql = "DELETE FROM `users` WHERE `users`.`phone` = '7134099506'";	
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted from main record\n";

$sql = "DELETE FROM `openid_email_users` WHERE `openid_email_users`.`email` = 'fred.trotter@gmail.com'";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted from openid\n";

$sql = "DELETE FROM `yourdoctorsadvice`.`hybrid_user_instance` WHERE `hybrid_user_instance`.`email` = 'fred.trotter@gmail.com'";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted hybrid instance\n";

$fred_phone_hash = hash("sha512", '7134099506');
$sql = "DELETE FROM `phones` WHERE `phone_hash` LIKE '$fred_phone_hash'";
mysql_query($sql) or die("Could not $sql ".mysql_error());
echo "deleted hashed phone record\n";



?>
