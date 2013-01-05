<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/CNK_DB.php');
    
    $db = new CNK_DB();
    
    if (!isset($_GET['TID'])) {
        $ret = 0;
    } else {
        $ret = $db->getAdvancePayment($_GET['TID']);
    }
    
    if ($ret < 0) {
        echo $db->error();
    } else {
        echo $ret;
    }
?>