<?php

    require("../macros.php");
	require("../classes/file.php");
	$file = new file("../".PRINTER_CONF);
	$json = $file->getContent();
	echo "$json";
?>