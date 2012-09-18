<?php
    require("classes/file.php");
	$file = new file("flavor.json");
	$json = $file->getContent();
	echo "$json";
?>