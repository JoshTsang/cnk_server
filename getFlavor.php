<?php
    require("macros.php");
    require("classes/file.php");
	$file = new file(FLAVOR_CONF);
	$json = $file->getContent();
	echo "$json";
?>