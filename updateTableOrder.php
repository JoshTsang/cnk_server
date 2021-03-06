<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['DID']) || !isset($_GET['TID']) || !isset($_GET['TYPE'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}

	$json_string = $_POST['json'];
	$obj = json_decode($json_string);
	$dishCount = count($obj -> order);
	$timestamp = $obj -> timestamp;
	$order = $obj -> order;
	$waiter = $obj ->waiter;
	$tableName = $obj -> tableName;
	$tableId = $obj -> tableId;
	if ($dishCount <= 0) {
		die("[MORE_PARAM_NEEDED:" . MORE_PARAM_NEEDED . "]");
	}
	
	$printer = new printer(PRINTER_CONF);
	
	
	$did = $_GET['DID'];
	$tid = $_GET['TID'];
	$type = $_GET['TYPE'];
	$db = new CNK_DB();
	$ret = $db->updateTableOrder($tid, $did, $type);
	$item = array('timestamp' => $timestamp,
					  'order' => $order,
					  'waiter' => $waiter,
					  'tableName' => $tableName,
					  'tableId' => $tableId,
					  'orderId' => $ret);
	$jsonString = json_encode($item);
	$printer -> printDel($jsonString);
	echo $db->error();
?>