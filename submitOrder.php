<?php
	require('macros.php');
	require('print.php');
	
	// if (!isset($_POST['json'])) {
	  	// die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	// }
	$json_string = $_POST['json'];
	//$json_string =  " {\"order\":[{\"quan\":1,\"id\":1,\"price\":7,\"name\":\"柠檬汁\"}],\"timestamp\":\"2012-07-03 23:31:21\",\"tableId\":1}";
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	if ($dishCount <= 0) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$dbOrder = new SQLite3(DATABASE_ORDER);
	if (!$dbOrder) {
		echo "[ERR_COULD_NOT_CONECT_DB:";
	  	die(ERR_COULD_NOT_CONECT_DB."]");
	}
	$dbOrder->busyTimeout(0);
	if (!$dbOrder->exec("INSERT INTO ".TABLE_ORDER_TABLE."(".TABLE_ORDER_TABLE_COLUM_TABLE_ID.",". 
									 TABLE_ORDER_TABLE_COLUM_TIMESTAMP.")".
						"values('$tableId', '$timestamp')")){
		echo "[ERR_COULD_NOT_CONECT_DB:";
		die(ERR_COULD_NOT_CONECT_DB."]");
	}
	
	$resultSet = $dbOrder->query("SELECT MAX(".TABLE_ORDER_TABLE_COLUM_ID.") from ".
								  TABLE_ORDER_TABLE." WHERE ".TABLE_ORDER_TABLE_COLUM_TABLE_ID."=$tableId");
	if ($resultSet) {
		if ($row = $resultSet->fetchArray()) {
			$orderId = $row[0];
		} else {
			echo "[ERR_DB_QUERY:";
			die(ERR_DB_QUERY."]");
		}
	} else {
		echo "[ERR_DB_QUERY:";
		die(ERR_DB_QUERY."]");
	}
	
	for ($i=0; $i<$dishCount; $i++) {
		$dishId = $obj->order[$i]->id;
		$price = $obj->order[$i]->price;
		$dishQuantity = $obj->order[$i]->quan;
		$dishName = $obj->order[$i]->name;
		if (!$dbOrder->exec("INSERT INTO ".ORDER_DETAIL_TABLE."(".ORDER_DETAIL_TABLE_COLUM_DISH_ID.",".
															ORDER_DETAIL_TABLE_COLUM_PRICE.",".
															ORDER_DETAIL_TABLE_COLUM_QUANTITY.",".
															ORDER_DETAIL_TABLE_COLUM_ORDER_ID.")".
							 "values($dishId, $price, $dishQuantity, $orderId)")) {
			echo "[ERR_DB_EXEC:";
			die(ERR_DB_EXEC."]");
		}
	}
	$dbOrder->close();
	
	printJson($json_string);
?>