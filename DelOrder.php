<?php
	require('macros.php');
	
	$dbTable = new SQLite3(DATABASE_ORDER);
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}


	$DishId = $_GET['DID'];
	$sql=sprintf("DELETE from %s where %s = %d",
				 ORDER_DETAIL_TABLE,
				 ORDER_DETAIL_TABLE_COLUM_DISH_ID,$DishId);
				 
	$dbTable->query($sql);
	
?>