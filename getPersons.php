<?php
    require('macros.php');
	require('setting/defines.php');
	require('classes/CNK_DB.php');
	
	$db = new CNK_DB();
	
	if (!isset($_GET['TID'])) {
		$ret = $db->getCurrentPersons();
	} else {
		$ret = $db->getPersons($_GET['TID']);
	}
	
	if (!$ret) {
		echo $db->error();
	} else {
		echo $ret;
	}
?>