<?php
    require 'macros.php';
	
	if (!isset($_GET['srcTID']) || !isset($_GET['destTID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
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