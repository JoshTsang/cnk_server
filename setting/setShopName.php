<?php
    require '../macros.php';
	require("../classes/file.php");
	
   if(!isset($_GET['shopname'])) {
    	die("parameter needed");
    }
   
	$file = new file("../".SHOPNAME_CONF);
	$config = $_GET['shopname'];
	$file->setContent($config);
?>