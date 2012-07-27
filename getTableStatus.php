<?php
	require('macros.php');
	

	$dbTable = new SQLite3(DATABASE_PHONE);
	jone
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	$sql=sprintf("select %s,%s,%s from %s",
				 TABLE_ID ,TABLE_STATUS,TABLE_NAME,TABLE_INFO);
	$resultSet = $dbTable->query($sql);
	if ($resultSet) {
		$i = 0;
		while($row = $resultSet->fetchArray()) {
			$item = array('id' => $row[0],
			 			  'status' => $row[1],
						  'name' => $row[2]);
			$Table[$i] = $item;
			$i++;
			// echo "-----------------------<br/>";
			// echo "id:$row[0]<br/>";
			// echo "dish_id:$row[1]<br/>";
			// echo "price:$row[2]<br/>";
			// echo "quantity:$row[3]<br/>";
			// echo "order_id:$row[4]<br/>";
		}
		$jsonString = json_encode($Table);
		echo "$jsonString";
	} else {
		die(ERR_DB_QUERY);
	}
	$dbTable->close();
?>