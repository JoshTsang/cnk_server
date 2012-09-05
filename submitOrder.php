<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_POST['json'])) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$printer = new printer("setting/printerInfo.json");
	$db = new CNK_DB();
	
	$json_string = $_POST['json'];
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	
	if ($dishCount <= 0) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$printer->printOrder($json_string);
	$db->submitOrder($obj);
	echo $db->error();
?>