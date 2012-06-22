<?php
    require('macros.php');
	
	$db = new SQLite3(DATABASE_MENU);
	if (!$db) {
	  die(ERR_CLOUD_NOT_CONECT_DB);
	}
	
	$categoryId = $_GET['CID'];
	if ($categoryId == NULL) {
		echo "[]";
		die(0);
	}
	
	$resultSet=$db->query("Select ".CATEGROY_TABLE_COLUM_TABLE_NAME
				 ." from ".CATEGROY_TABLE
				 ." where ".CATEGROY_TABLE_COLUM_ID."="
				 ."'".$categoryId."'");
				 
	if ($resultSet) {
		if ($row = $resultSet->fetchArray()) {
			$CategoryTableName = $row[0];
		} else {
			echo "[]";
			die(0);
		}
		//$resultSet->free();
	} else {
		die(ERR_DB_QUERY);
	}
	
	$resultSet = $db->query("Select ".DISHES_TABLE_COLUM_ID
				 ." from ".$CategoryTableName
				 ." where ".DISHES_TABLE_COLUM_STATUS."="
				 ."'".DISH_STATUS_SOLD_OUT."'");
	if ($resultSet) {
		echo "[";
		if ($row = $resultSet->fetchArray()) {
			$DishId = $row[0];
			echo "$DishId";
			while($row = $resultSet->fetchArray()) {
				$DishId = $row[0];
				echo ",$DishId";
			}
		}
		
		
		echo "]";
	} else {
		die(ERR_DB_QUERY);
	}
	
	$db->close();
?>