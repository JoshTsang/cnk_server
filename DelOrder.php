<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	if (!isset($_GET['TYPE'])) {
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
	$type = $_GET['TYPE'];
	$printer = new printer(PRINTER_CONF);
	$db = new CNK_DB();
	for ($i = 0; $i < $dishCount; $i++) {
		$dishId = $obj -> order[$i] -> dishId;
		$quan = $order[$i] ->quan;
		if($type == DEL_ORDER){
			$ret = $db->updateTableOrder($tableId, $dishId, $type);
			if(!isset($orderID))
			$orderID = $ret;		
		}else{
			for($k = 0;$k < $quan;$k++){
				$ret = $db->updateTableOrder($tableId, $dishId, $type);
				if(!isset($orderID))
				$orderID = $ret;
				for($n = 0;$n<count($ret);$n++){
					$equal = TRUE;
					$id = 0;
					for(;$id <count($orderID);$id++){
							if($orderID[$id] == $ret[$n]){
								$equal = FALSE;
							}						
					}
					if($equal)
						$orderID[$id] = $ret[$n];
				}
			}
		}
		$item = array('timestamp' => $timestamp,
				  'order' => $order,
				  'waiter' => $waiter,
				  'tableName' => $tableName,
				  'tableId' => $tableId,
				  'orderId' => $orderID);
	}
	$jsonString = json_encode($item);
	$printer -> printDel($jsonString);
	echo $db->error();
?>