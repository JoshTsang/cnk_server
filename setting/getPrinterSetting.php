<?php

	require("../classes/file.php");
	$file = new file("printerInfo.json");
	$json = $file->getContent();
	echo "$json";

?>