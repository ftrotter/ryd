<?php
// Sometimes the user table will be modified and the phone table
//will still have a record of that user.
//then when we add new phones, it cannot find the user.
//Frustrating and wrong...

	require_once("../config.php");

$sql = "
SELECT phones.id
FROM `phones`
LEFT JOIN users ON phones.user_id = users.id
WHERE ISNULL(users.id)
";

$result = mysql_query($sql) or die("Could not $sql ".mysql_error());

while($row = mysql_fetch_assoc($result)){

	$id = $row['id'];
ELECT phones.id
FROM `phones`
LEFT JOIN users ON phones.user_id = users.id
WHERE ISNULL(users.id)

	$sql = "DELETE FROM phones WHERE phones.id = $id";
	mysql_query($sql) or die("could not delete with $sql".mysql_error());
}

?>
