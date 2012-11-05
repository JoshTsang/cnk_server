<?php
    require('macros.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['TID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}

	$tableId = $_GET['TID'];
	
	if (!isset($_GET['DID'])) {
		$tableDishId = $_GET['DID'];
	}
	
	$db = new CNK_DB();
	if(isset($tableDishId)){
		$db->deletePhoneOrder($tableId, $tableDishId);
	} else {
		$db->cleanPhoneOrder($tableId);
	}

	echo $db->error();
?>
