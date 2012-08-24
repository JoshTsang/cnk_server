<?php
	require('macros.php');
	

	$dbTable = new SQLite3(DATABASE_TEMP);

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
		}
		$jsonString = json_encode($Table);
	} else {
		die(ERR_DB_QUERY);
	}
	$dbTable->close();
	echo "$jsonString";
?>