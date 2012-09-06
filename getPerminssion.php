<?php
	require('macros.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['UNAME'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$db = new CNK_DB();
	$ret = $db->getPermission($_GET['UNAME']);
	if (!$ret) {
		echo $db->error();
	} else {
		echo $ret;
	}
?>