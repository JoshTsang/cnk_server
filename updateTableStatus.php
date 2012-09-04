<?php	require('macros.php');	require('');		if (!isset($_GET['TID']) || !isset($_GET['TST'])) {		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");	}	$TableId = $_GET['TID'];	$TableStatus = $_GET['TST'];	$db = new CNK_DB();	$db->updateTableStatus($TableId, $TableStatus);?>

