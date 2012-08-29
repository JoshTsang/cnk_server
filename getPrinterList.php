<?php
    require 'macros.php';
	require 'print.php';
	$jsonObj = getPrinterInfo();
	$count = count($jsonObj);
	$printerList = $jsonObj[0]->ip;
	for ($i=1; $i<$count; $i++) {
		$printerList = $printerList.",".$jsonObj[$i]->ip;
	}
	echo "["."$printerList"."]";
?>