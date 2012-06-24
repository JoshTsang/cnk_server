<?php
	require('macros.php');
	
	$dbOrder = new SQLite3(DATABASE_ORDER);
	if (!$dbOrder) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	$sql=sprintf("select %s.%s,%s.%s,%s.%s,%s.%s from %s,%s where %s.%s=%s.%s",
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_DISH_ID,
				  TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_TABLE_ID,
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
				  TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE,
				  TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_ID,
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID);
	$resultSet = $dbOrder->query($sql);
	if ($resultSet) {
		$i = 0;
		while($row = $resultSet->fetchArray()) {
			$item = array('id' => $row[0],
			 			  'did' => $row[1],
						  'tid' => $row[2],
						  'quan' => $row[3]);
			$dishes[$i] = $item;
			$i++;
			// echo "-----------------------<br/>";
			// echo "id:$row[0]<br/>";
			// echo "dish_id:$row[1]<br/>";
			// echo "price:$row[2]<br/>";
			// echo "quantity:$row[3]<br/>";
			// echo "order_id:$row[4]<br/>";
		}
		$jsonString = json_encode($dishes);
		echo "$jsonString";
	} else {
		die(ERR_DB_QUERY);
	}
?>