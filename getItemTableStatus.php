<?php
	require('macros.php');
	

	$dbTable = new SQLite3(DATABASE_TEMP);
	$tableStatus = $_GET['TSI'];
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	$sql=sprintf("select %s from %s where id = %s",
				 TABLE_STATUS,TABLE_INFO,$tableStatus);
	$resultSet = $dbTable->query($sql);
	if ($resultSet) {
		if ($row = $resultSet->fetchArray()) {
			$status = $row[0];
			echo "$status";
		} else {
			die(0);
		}
	} else {
		die(ERR_DB_QUERY);
	}
	$dbTable->close();
?>