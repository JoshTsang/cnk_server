<?php
    require('macros.php');
	require('setting/defines.php');
	require('classes/CNK_DB.php');
	
	if (!isset($_GET['TID'])) {
		die("{MORE_PARAM_NEEDED:".MORE_PARAM_NEEDED."}");
	}
	
	$db = new CNK_DB();
	
	$ret = $db->getPersons($_GET['TID']);
	if (!$ret) {
		echo $db->error();
	} else {
		echo $ret;
	}
?>