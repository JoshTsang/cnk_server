<?php
	require("../classes/file.php");
	
    if(!isset($_POST['config'])) {
    	die("parameter needed");
    }
	
	$file = new file("printerInfo.json");
	$config = $_POST['config'];
	$file->setContent($config);
?>