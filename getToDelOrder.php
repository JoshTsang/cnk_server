<?php	require('macros.php');		$dbTable = new SQLite3(DATABASE_ORDER);	if (!$dbTable) {		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");	  	die(ERR_COULD_NOT_CONECT_DB);	}	$tableId = $_GET['TID'];		$sql=sprintf("select %s,%s,%s,%s,%s,%s,%s.%s from %s,%s where %s.%s = %s.%s and %s.%s = %s",				 ORDER_DETAIL_TABLE_COLUM_DISH_ID ,ORDER_DETAIL_TABLE_COLUM_PRICE,				 ORDER_DETAIL_TABLE_COLUM_ORDER_ID,ORDER_DETAIL_TABLE_COLUM_QUANTITY,				 TABLE_ORDER_TABLE_COLUM_TABLE_ID,TABLE_ORDER_TABLE_COLUM_TIMESTAMP,				ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ID,				 ORDER_DETAIL_TABLE,TABLE_ORDER_TABLE,				 ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,				 TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_ID,				 TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID,"$tableId");				 	$resultSet = $dbTable->query($sql);	if ($resultSet) {		$i = 0;		while($row = $resultSet->fetchArray()) {			$item = array('dish_id' => $row[0],			 			  'price' => $row[1],						  'order_id' => $row[2],						  'quantity' => $row[3]);			$Table[$i] = $item;			$i++;			// echo "-----------------------<br/>";			// echo "id:$row[0]<br/>";			// echo "dish_id:$row[1]<br/>";			// echo "price:$row[2]<br/>";			// echo "quantity:$row[3]<br/>";			// echo "order_id:$row[4]<br/>";		}		$jsonString = json_encode($Table);		echo "$jsonString";	} else {		die(ERR_DB_QUERY);	}	$dbTable->close();?>