<?php
require ('macros.php');
require ('setting/defines.php');
require ('classes/printer.php');

$json_string = $_POST['json'];
$obj = json_decode($json_string);
$dishCount = count($obj -> order);
$timestamp = $obj -> timestamp;
if ($dishCount <= 0) {
	die("[MORE_PARAM_NEEDED:" . MORE_PARAM_NEEDED . "]");
}

$dbOrder = new SQLite3(DATABASE_ORDER);
if (!$dbOrder) {
	echo "[ERR_COULD_NOT_CONECT_DB:";
	die(ERR_COULD_NOT_CONECT_DB . "]");
}

$printer = new printer("setting/printerInfo.json");
$printer -> printDel($json_string);
for ($i = 0; $i < $dishCount; $i++) {
	$dishId = $obj -> order[$i] -> dishId;
	if (!$dbOrder -> exec("DELETE from " . ORDER_DETAIL_TABLE . " where " . ORDER_DETAIL_TABLE_COLUM_DISH_ID . "=$dishId")) {
		echo "[ERR_DB_EXEC:";
		die(ERR_DB_EXEC . "]");
	}
}

$dbOrder -> close();
?>