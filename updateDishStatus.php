<?php
	require('macros.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['TID']) || !isset($_GET['DID']) || !isset($_GET['STATUS'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$db = new CNK_DB();
	$db->updateDishStatus($_GET['TID'], $_GET['DID'], $_GET['STATUS']);
	
	echo $db->error();
?>