<?php
    require 'macros.php';
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['srcTID']) || !isset($_GET['destTID']) || !isset($_POST['json'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$json_string = $_POST['json'];
	//$json_string =  " {\"order\":[{\"quan\":1,\"id\":1,\"price\":7,\"name\":\"柠檬汁\"}],\"timestamp\":\"2012-07-03 23:31:21\",\"tableId\":1}";
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	$datetime = split(" ", $timestamp);
	$printer = new printer("setting/printerInfo.json");
	
	if ($dishCount > 0) {
	  	$printer->printChangeTable($json_string);
	}
	
	$db = new CNK_DB();
	$db->changeTable($_GET['srcTID'], $_GET['destTID']);
	echo $db->error();
?>