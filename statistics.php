<?php
	require('macros.php');
	require('print.php');
	
	if(!isset($_POST['json'])) {
		exit();
	}
	
	printSalesReceipt($_POST['json'], PRINTER_FOR_KITCHEN, PRINTER_TYPE_58);
?>