<?php
    require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['srcTID']) || !isset($_GET['destTID']) || !isset($_POST['json'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$json_string = $_POST['json'];

	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	$persons = $obj->persons;
	$datetime = split(" ", $timestamp);
	$printer = new printer("setting/printerInfo.json");
	
	$db = new CNK_DB();
	$db->changeTable($_GET['srcTID'], $_GET['destTID'], $persons);
	
	
	if ($dishCount > 0) {
	  	$printer->printCombine($json_string);
	}
	echo $db->error();
?>