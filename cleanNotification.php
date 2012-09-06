<?php
    require('macros.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['TID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$tableId = $_GET['TID'];
	$db = new CNK_DB();
	$db->cleanNotification($tableId);
	echo $db->error();
?>