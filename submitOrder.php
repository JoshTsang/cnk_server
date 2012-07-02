<?php
	require('macros.php');
	require('print.php');
	
	$json_string = $_POST['json'];
	//$json_string =  " {\"order\":[{\"quan\":1,\"id\":2,\"price\":12},{\"quan\":1,\"id\":3,\"price\":12},{\"quan\":1,\"id\":5,\"price\":34}],\"timestamp\":\"2012-06-23 20:22:38\",\"tableId\":0}";
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	if ($dishCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	
	$dbOrder = new SQLite3(DATABASE_ORDER);
	if (!$dbOrder) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	
	if (!$dbOrder->exec("INSERT INTO ".TABLE_ORDER_TABLE."(".TABLE_ORDER_TABLE_COLUM_TABLE_ID.",". 
									 TABLE_ORDER_TABLE_COLUM_TIMESTAMP.")".
						"values('$tableId', '$timestamp')")){
		header("HTTP/1.1 ERR_DB_QUERY 'ERR_DB_QUERY'");
		die(ERR_COULD_NOT_CONECT_DB);
	}
	
	$resultSet = $dbOrder->query("SELECT MAX(".TABLE_ORDER_TABLE_COLUM_ID.") from ".
								  TABLE_ORDER_TABLE." WHERE ".TABLE_ORDER_TABLE_COLUM_TABLE_ID."=$tableId");
	if ($resultSet) {
		if ($row = $resultSet->fetchArray()) {
			$orderId = $row[0];
		} else {
			header("HTTP/1.1 ERR_DB_QUERY 'ERR_DB_QUERY'");
			die(0);
		}
	} else {
		header("HTTP/1.1 ERR_DB_QUERY 'ERR_DB_QUERY'");
		die(ERR_DB_QUERY);
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
			header("HTTP/1.1 ERR_DB_EXEC 'ERR_DB_QUERY'");
			die(0);
		}
	}
	
	printJson($json_string);
?>