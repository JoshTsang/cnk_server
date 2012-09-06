<?php
	require('macros.php');
	require('classes/CNK_DB.php');

	if (!isset($_GET['TID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	$tableId = $_GET['TID'];
	
	$db = new CNK_DB();
	
	$ret = $db->getPhoneOrder($tableId); 
	if (!$ret) {
		echo $db->error();
	} else {
		echo $ret;
	}
?>