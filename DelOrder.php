<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');

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
	$db = new CNK_DB();
	for ($i = 0; $i < $dishCount; $i++) {
		$dishId = $obj -> order[$i] -> dishId;
		$ret = $db->updateTableOrder($tableId, $dishId, DEL_ORDER);
		$item = array('timestamp' => $timestamp,
				  'order' => $order,
				  'waiter' => $waiter,
				  'tableName' => $tableName,
				  'tableId' => $tableId,
				  'orderId' => $ret);
		$table[$i] = $item;
	}
	$jsonString = json_encode($item);
	$printer -> printDel($jsonString);
	echo $db->error();
?>