<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['DID']) || !isset($_GET['TID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}

	$json_string = $_POST['json'];
	$obj = json_decode($json_string);
	$dishCount = count($obj -> order);
	$timestamp = $obj -> timestamp;
	
	if ($dishCount <= 0) {
		die("[MORE_PARAM_NEEDED:" . MORE_PARAM_NEEDED . "]");
	}
	
	$printer = new printer("setting/printerInfo.json");
	$printer -> printDel($json_string);
	
	$did = $_GET['DID'];
	$tid = $_GET['TID'];
	
	$db = new CNK_DB();
	$db->updateTableOrder($tid, $did);
	echo $db->error();
?>