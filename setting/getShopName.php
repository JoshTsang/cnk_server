<?php
    require("../macros.php");
	require("../classes/file.php");
	$file = new file("../".SHOPNAME_CONF);
	$shopname = $file->getContent();
	echo $shopname;
?>