<?php
    require('macros.php');
	require('classes/CNK_DB.php');
	
	if(!isset($_POST['json'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	$json_string = $_POST['json'];
	$obj = json_decode($json_string);
	$db = new CNK_DB();
	
	
	$order = $obj->order;
	$timestamp = $obj->timestamp;
	$tableIdCount = count($obj->order);
	if ($tableIdCount <= 0) {
	  	die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	for($i = 0;$i < $dishCount;$i++){
		$tableId = $order[$i] -> TID;
		$db->cleanTable($tableId, $timestamp);
	}
	echo $db->error();
	
?>