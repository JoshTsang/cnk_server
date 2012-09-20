<?php
	require("../classes/file.php");
	
    if(!isset($_POST['config'])) {
    	die("parameter needed");
    }
	
	$file = new file("flavor.json");
	$config = $_POST['config'];
	$obj = json_decode($config);
	
	$file->setContent(json_encode($obj));
?>