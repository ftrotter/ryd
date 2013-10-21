<?php
echo "<html><head><title>Icons</title></head><body>";
$files = glob("*.*");
for ($i=1; $i<count($files); $i++)
{
	$num = $files[$i];
	echo '<img src="'.$num.'" alt="random image">'."&nbsp;&nbsp;";
	}
?>


echo "</body></html>";
