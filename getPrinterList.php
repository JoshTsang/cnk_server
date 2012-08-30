<?php
    require 'macros.php';
	require 'print.php';
	require 'setting/defines.php';
	
	if (isset($_GET['for'])) {
		$for = $_GET['for'];
	} else {
		$for = 0;
	}
	$jsonObj = getPrinterInfo();
	$count = count($jsonObj);
	if ($for == 0) {
		$printerList = $jsonObj[0]->ip;
		for ($i=1; $i<$count; $i++) {
			$printerList = $printerList.",".$jsonObj[$i]->ip;
		}
	} else if ($for == PRINT_ORDER) {
		for ($i=0; $i<$count; $i++) {
			if($jsonObj[$i]->usefor <= PRINT_ORDER) {
				$printerList = $jsonObj[$i]->ip;
				break;
			}
		}
		for ($i += 1; $i<$count; $i++) {
			if($jsonObj[$i]->usefor <= PRINT_ORDER) {
				$printerList = $printerList.",".$jsonObj[$i]->ip;
			}
		}
	} else {
		for ($i=0; $i<$count; $i++) {
			if($jsonObj[$i]->usefor == $for) {
				$printerList = $jsonObj[$i]->ip;
				break;
			}
		}
		for ($i += 1; $i<$count; $i++) {
			if($jsonObj[$i]->usefor == $for) {
				$printerList = $printerList.",".$jsonObj[$i]->ip;
			}
		}
	}
	
	echo "["."$printerList"."]";
?>