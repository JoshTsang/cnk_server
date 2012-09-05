<?php
	require('macros.php');
	require('classes/CNK_DB.php');

	if (!isset($_GET['DID']) || !isset($_GET['DNUM']) || !isset($_GET['TID'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	$did = $_GET['DID'];
	$quantity = $_GET['DNUM'];
	$tid = $_GET['TID'];
	
	$db = new CNK_DB();
	$db->updatePhoneOrder($tid, $did, $quantity);
	echo $db->error();
?>

