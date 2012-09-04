<?php
	require("../classes/file.php");
	
   if(!isset($_GET['shopname'])) {
    	die("parameter needed");
    }
   
	$file = new file("shopname");
	$config = $_GET['shopname'];
	$file->setContent($config);
?>