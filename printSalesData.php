<?php
	require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	
	if(!isset($_POST['json'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	$printer = new printer("setting/printerInfo.json");
	$printer->printSales($_POST['json']);
?>