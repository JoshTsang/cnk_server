<?php
	require("setting/defines.php");
	require("macros.php");
	require("classes/file.php");
	require("classes/CNK_DB.php");
	
    if(!isset($_POST['config'])) {
    	die("parameter needed");
    }
	
	$db = new CNK_DB();
	$file = new file("./setting/printerInfo.json");
	$config = $_POST['config'];
	$jsonObj = json_decode($config);
	
	$jsonObj = $db->updatePrinterSetting($jsonObj);
	echo json_encode($jsonObj);
	$file->setContent(json_encode($jsonObj));
?>