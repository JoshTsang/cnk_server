<?php
    require('macros.php');
	require('classes/CNK_DB.php');
	
	$db = new CNK_DB();
	
	$jsonString = $db->getNotifications();
	echo $jsonString;
?>