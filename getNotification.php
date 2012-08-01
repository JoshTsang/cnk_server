<?php
    require('macros.php');
	
	$db = new SQLite3(DATABASE_PHONE);
	if (!$db) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	
	$sql=sprintf("select %s from %s group by %s", NOTIFICATION_COLUM_TID, TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID);
				 
	$resultSet = $db->query($sql);
	if ($resultSet) {
		$j = 0;
		while($row = $resultSet->fetchArray()) {
			$sql=sprintf("select * from %s where %s=%s", TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID, $row[0]);
			$resultSet2 = $db->query($sql);
			if ($resultSet2) {
				$i = 0;
				while($rowNotification = $resultSet2->fetchArray()) {
					$notifications[$i] = $rowNotification[2];
					$i++;
				}
			} else {
				die(ERR_DB_QUERY);
			}
			
			
			$item = array('tid' => $row[0],
						  'notifications' => $notifications);
			$table[$j] = $item;	
			$j++;
		}
	} else {
		die(ERR_DB_QUERY);
	}
	$jsonString = json_encode($table);
	echo $jsonString;
	$db->close();
?>