<?php
    require('macros.php');
	
	$db = new SQLite3(DATABASE_PHONE);
	if (!$db) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	$tableId = $_GET['TID'];
	
	$sql=sprintf("delete from %s where %s=%s", TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, "$tableId");
	
	$db->exec($sql);			 
	$db->close();
?>