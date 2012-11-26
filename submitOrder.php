<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_POST['json'])) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED.",json]");
	}
	
	$printer = new printer(PRINTER_CONF);
	$db = new CNK_DB(); 
	
	$json_string = $_POST['json'];
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	
	if ($dishCount <= 0) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED.",dishes]");
	}
	
	$orderId = $db->submitOrder($obj, $_GET['MD5']);
	if ($orderId > 0) {
		$printer->savePrintOrder($json_string, $orderId, isset($_GET['action']));
	}
	
	echo $db->error();
?>