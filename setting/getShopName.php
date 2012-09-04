<?php
	require("../classes/file.php");
	$file = new file("shopname");
	$shopname = $file->getContent();
	echo $shopname;
?>