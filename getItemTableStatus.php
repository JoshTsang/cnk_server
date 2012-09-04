<?php
	require('macros.php');
	require('classes/CNK_DB.php');

	if (!isset($_GET['TSI'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$tableId = $_GET['TSI'];
	$db = new CNK_DB();
	echo $db->getTableStatus($tableId);
?>