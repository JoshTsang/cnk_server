<?php
    require("classes/file.php");
	$file = new file("setting/flavor.json");
	$json = $file->getContent();
	echo "$json";
?>