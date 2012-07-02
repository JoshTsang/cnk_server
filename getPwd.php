<?php
	require('macros.php');
	
	$db = new SQLite3(DATABASE_MENU);
	if (!$db) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	$uName = $_GET['UNAME'];
	
	$sql=sprintf("select %s from %s where %s.%s = '%s'",
				 USER_PWD,USER_TABLE,USER_TABLE,USER_NAME,$uName);
	$resultSet = $db->query($sql);
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
	$db->close();
?>