<?php
	require('macros.php');
	require('classes/CNK_DB.php');
    require('classes/lisence.php');
	
	$db = new CNK_DB();
    //TODO die when UUID not set 
    if (isset($_GET['UUID'])) {
        $lisence = new Lisence();
        $lisence->updatePadInfo($_GET['UUID']);
    }
	$ret = $db->getTableStatus();
	if (!$ret) {
		echo $db->error();
	} else {
		echo $ret;
	}
?>