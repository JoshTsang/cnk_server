<?php
	require('macros.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['UNAME'])) {
		die("[MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."]");
	}
	
	$db = new CNK_DB();
	echo $db->getPWD($_GET['UNAME']);
?>