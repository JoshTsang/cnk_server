<?php
	require('macros.php');
	
	$dbPhone = new SQLite3(DATABASE_TEMP);
	if (!$dbPhone) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}


	$DishId = $_GET['DID'];
	$DishNum = $_GET['DNUM'];
	$TableId = $_GET['TID'];
	$sql=sprintf("UPDATE %s SET %s = %s where %s = %s and %s = %s",
				 TABLE_PHONE_ORDERED_DISH,
				 TABLE_PHONE_ORDERED_DNUM,$DishNum,
				 TABLE_PHONE_ORDERED_DID,$DishId,
				 PHONE_COLUM_TID,$TableId);

	if (!$dbPhone->exec($sql)) {
			echo "[ERR_DB_EXEC:";
			die(ERR_DB_EXEC."]");
	}
	$dbPhone->close();
?>

