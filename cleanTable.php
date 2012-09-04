<?php
    require('macros.php');
	require('classes/CNK_DB.php');
	
	if(!isset($_POST['json'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	$json_string = $_POST['json'];
	$obj = json_decode($json_string);
	$tableId = $obj->TID;
	$timestamp = $obj->timestamp;
	
	$db = new CNK_DB();
	$db->cleanTable($tableId, $timestamp);
?>