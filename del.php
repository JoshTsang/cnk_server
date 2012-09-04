<?php
	require('macros.php');
	
	$dbTable = new SQLite3(DATABASE_ORDER);
	
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	
	
	$json_string = $_GET['json'];
	
	$sql=sprintf("DELETE from %s where %s = %d",
				 ORDER_DETAIL_TABLE,
				 ORDER_DETAIL_TABLE_COLUM_ID,$json_string);
	
	$resultSet = $dbTable->query($sql);
	if ($resultSet) {
		if ($row = $resultSet->fetchArray()) {
			$Pwd = $row[0];
			echo "$Pwd";
		} else {
			die(0);
		}
	} else {
		die(ERR_DB_QUERY);
	}
	echo $json_string;
	$dbTable->close();


?>