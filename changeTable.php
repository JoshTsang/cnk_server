<?php
    require 'macros.php';
	
	if (!isset($_GET['srcTID']) || !isset($_GET['destTID']) || !isset($_POST['json'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$json_string = $_POST['json'];
	//$json_string =  " {\"order\":[{\"quan\":1,\"id\":1,\"price\":7,\"name\":\"柠檬汁\"}],\"timestamp\":\"2012-07-03 23:31:21\",\"tableId\":1}";
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	$datetime = split(" ", $timestamp);
	$printer = new printer("setting/printerInfo.json");
	
	if ($dishCount <= 0) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$printer->printChangeTable($json_string);
	
	$srcTID = $_GET['srcTID'];
	$destTID = $_GET['destTID'];
	$dbTable = new SQLite3(DATABASE_ORDER);
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	$sql=sprintf("update %s set %s=%d where %s = %d",
				 TABLE_ORDER_TABLE, /*update*/
				 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
				 $destTID,/*set*/
				 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
				 $srcTID);	 
	$dbTable->query($sql);
	$dbTable->close();
	
	/* clean phone order */
	$db = new SQLite3(DATABASE_PHONE);
	if (!$db) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	$sql=sprintf("delete from %s where %s=%s", 
		TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $srcTID);
	
	if (!$db->exec($sql)) {
			echo "[ERR_DB_EXEC:";
			die(ERR_DB_EXEC."]");
	}	

	$db->close();
	
	/* change table status */
	$dbTable = new SQLite3(DATABASE_PHONE);
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	
	$sql=sprintf("UPDATE %s SET %s = %s where %s = %d",
				 TABLE_INFO,
				 TABLE_STATUS,1,
				 TABLE_ID,$destTID); 
	$dbTable->query($sql);
	
	$sql=sprintf("UPDATE %s SET %s = %s where %s = %d",
				 TABLE_INFO,
				 TABLE_STATUS, 0,
				 TABLE_ID,$srcTID); 
	$dbTable->query($sql);
	$dbTable->close();
?>