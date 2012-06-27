<?php
    require('macros.php');
	
	$dbSales = new SQLite3(DATABASE_SALES);
	if (!$dbSales) {
	  die(ERR_CLOUD_NOT_CONECT_DB);
	}
	
	$dbOrder = new SQLite3(DATABASE_ORDER);
	if (!$dbSales) {
	  die(ERR_CLOUD_NOT_CONECT_DB);
	}
	
	$tableId = $_GET['TID'];
	if ($tableId == NULL) {
		echo "[]";
		die(0);
	}
	
	/* save sales data to sales db */
	$sql=sprintf("select %s.%s,%s.%s,%s.%s,%s.%s from %s,%s where %s.%s=%s.%s and %s=%s",
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_DISH_ID,
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_PRICE,
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
					  TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_TIMESTAMP,
					  TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE,
					  TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
					  TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tableId);
	$resultSet = $dbOrder->query($sql);
	if ($resultSet) {
		while($row = $resultSet->fetchArray()) {
			$sqlInsert=sprintf("insert into [sales_data] values(null, %s, %s, %s, '%s');", $row[0],$row[1],$row[2],$row[3]);
			$dbSales->exec($sqlInsert);
		}
		$dbSales->close();
	} else {
		die(ERR_DB_QUERY);
	}
	
	/* clean table and refrence order */
	$sql=sprintf("select %s from %s where %s=%s",
					  ORDER_DETAIL_TABLE_COLUM_ID, TABLE_ORDER_TABLE,
					  TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tableId);
	$resultSet = $dbOrder->query($sql);
	if ($resultSet) {
		while($row = $resultSet->fetchArray()) {
			$sqlDelete=sprintf("DELETE FROM %s where %s=%s;", ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,$row[0]);
			$dbOrder->exec($sqlDelete);
		}
	} else {
		die(ERR_DB_QUERY);
	}
	
	$sqlDelete=sprintf("DELETE FROM %s where %s=%s;", TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID,$tableId);
	$dbOrder->exec($sqlDelete);
	$dbOrder->close();
?>