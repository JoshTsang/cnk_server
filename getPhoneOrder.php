<?php
	require('macros.php');
	
	$db = new SQLite3(DATABASE_PHONE);
	if (!$db) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	$tableId = $_GET['TID'];
	
	$sql=sprintf("select * from %s where %s=%s", TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, "$tableId");
				 
	$resultSet = $db->query($sql);
	if ($resultSet) {
		$i = 0;
		while($row = $resultSet->fetchArray()) {
			$item = array('dish_id' => $row[1],
						  'quantity' => $row[2]);
			$table[$i] = $item;
			$i++;
		}
		$jsonString = json_encode($table);
		echo $jsonString;
	} else {
		die(ERR_DB_QUERY);
	}
	$db->close();
?>