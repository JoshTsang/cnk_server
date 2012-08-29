<?php
	require('macros.php');
	require('print.php');
	require('setting/defines.php');
	
	if(!isset($_POST['json'])) {
		exit();
	}
	$jsonObj = getPrinterInfo();
	$count = count($jsonObj);
	for ($i=0; $i<$count; $i++) {
		if($jsonObj[$i]->usefor == PRINT_STATISTICS) {
			printSalesReceipt($_POST['json'], $jsonObj[$i]->ip, $jsonObj[$i]->type);
		}
	}
	
?>