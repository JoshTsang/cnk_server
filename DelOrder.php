<?php
	require('macros.php');
	require('print.php');
	
	$dbTable = new SQLite3(DATABASE_ORDER);
	
	if (!$dbTable) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}

	
	
	$json_string = $_GET['JSON'];
	$obj = json_decode($json_string); 
	$DishId = $obj->id;
	$sql=sprintf("DELETE from %s where %s = %d",
				 ORDER_DETAIL_TABLE,
				 ORDER_DETAIL_TABLE_COLUM_ID,$DishId);
				 
	$dbTable->query($sql);
	$dbTable->close();
	printJsonDel($json_string);
?>