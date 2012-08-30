<?php
	require('macros.php');
	
	if (!isset($_GET['TID']) || !isset($_GET['DID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$dbTable = new SQLite3(DATABASE_ORDER);
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	$tableId = $_GET['TID'];
	$dishId = $_GET['DID'];
	$sql=sprintf("update %s set %s=2 where %s in (select %s from %s where %s = %s) and %s = %s",
				 ORDER_DETAIL_TABLE, /*update*/
				 ORDER_DETAIL_TABLE_COLUM_STATUS,/*set*/
				 ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
				 TABLE_ORDER_TABLE_COLUM_ID,TABLE_ORDER_TABLE,
				 TABLE_ORDER_TABLE_COLUM_TABLE_ID,"$tableId",
				 ORDER_DETAIL_TABLE_COLUM_DISH_ID, $dishId);	 
	// echo "$sql</br>";
	// echo "-----------------------------------------</br>";
	$dbTable->query($sql);
	$dbTable->close();
?>