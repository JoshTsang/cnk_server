<?php
	require("../classes/file.php");
    require("../macros.php");
	
    if(!isset($_POST['config'])) {
    	die("parameter needed");
    }
	
	$file = new file("../".FLAVOR_CONF);
	$config = $_POST['config'];
	$obj = json_decode($config);
	
	$file->setContent(json_encode($obj));
?>