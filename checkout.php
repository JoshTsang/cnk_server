<?php
    require('macros.php');
	require('setting/defines.php');
	require('classes/printer.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_POST['json'])) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$printer = new printer(PRINTER_CONF);
    $db = new CNK_DB();
    
    $checkoutNo = $db->getCheckoutNo();
	$json_string = $_POST['json'];
	
	$printer->printCheckout($json_string, $checkoutNo);
	echo "{\"succ\":true}";
?>