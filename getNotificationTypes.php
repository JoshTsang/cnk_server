<?php
    require('macros.php');
	
	$db = new SQLite3(DATABASE_MENU);
	if (!$db) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	
	$sql=sprintf("select * from %s", TABLE_NOTIFICATION_TYPES);
	$resultSet = $db->query($sql);
	if ($resultSet) {
		$j = 0;
		while($row = $resultSet->fetchArray()) {
			$item = array('nid' => $row[0],
						  'value' => $row[1]);
			$table[$j] = $item;	
			$j++;
		}
	} else {
		die(ERR_DB_QUERY);
	}
	$jsonString = json_encode($table);
	echo "$jsonString";
	$db->close();
?>