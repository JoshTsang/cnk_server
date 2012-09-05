<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	
	$dbTable = new SQLite3(DATABASE_ORDER);
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
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
	
	$DishId = $_GET['DID'];
	$TableId = $_GET['TID'];
	echo "$DishId ";
	echo "$TableId ";
	$sql = sprintf("select %s,%s from %s,%s where %s.%s = %s.%s and %s.%s = %s
					and %s.%s = %s",
					ORDER_DETAIL_TABLE_COLUM_QUANTITY,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
					ORDER_DETAIL_TABLE,TABLE_ORDER_TABLE,
					ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
				 	TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_ID,
				 	TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID,$TableId,
					ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_DISH_ID,$DishId);
	$resultSet = $dbTable->query($sql);
	if ($resultSet) {
		while($row = $resultSet->fetchArray()) {
			echo "$row[0] ";
			echo "$row[1] ";
			if($row[0] > 1){
				$sql = sprintf("update %s set %s = %s where %s = %s and %s = %s",
								ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
								($row[0]-1),ORDER_DETAIL_TABLE_COLUM_ORDER_ID,$row[1],
								ORDER_DETAIL_TABLE_COLUM_DISH_ID,$DishId);
				if (!$dbTable->exec($sql)) {
					echo "[ERR_DB_EXEC:";
					die(ERR_DB_EXEC."]");
				}else{
					break;
				}
				
			}else if($row[0] == 1){
				$sql = sprintf("DELETE from %s where %s.%s = %s and %s.%s = %s",
								ORDER_DETAIL_TABLE,
								ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,$row[1],
								ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_DISH_ID,$DishId);
				if (!$dbTable->exec($sql)) {
					echo "[ERR_DB_EXEC:";
					die(ERR_DB_EXEC."]");
				}else{
					break;
				}
			}
		}
		
	} else {
		die(ERR_DB_QUERY);
	}	 
					 
	$dbTable->close();
?>