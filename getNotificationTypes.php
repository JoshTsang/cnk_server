<?php
    require('macros.php');
	require('classes/CNK_DB.php');
	
	$db = new CNK_DB();
	
	$ret = $db->getNotificationTypes();
	if (!$ret) {
		echo $db->error();
	} else {
		echo $ret;
	}
?>