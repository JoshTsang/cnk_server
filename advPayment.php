<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/CNK_DB.php');
    
    $db = new CNK_DB();
    
    $do = "get";
    if (isset($_GET['do'])){
        $do = $_GET['do'];
    }
    
    if (!isset($_GET['TID'])) {
        $ret = 0;
    }
    
    if ($do == 'get') {
        $ret = $db->getAdvancePayment($_GET['TID']);
        
        if ($ret < 0) {
            echo $db->error();
        } else {
            echo $ret;
        }
    } else {
        if (isset($_GET['payment'])) {
            $ret = $db->setAdvancePayment($_GET['TID'], $_GET['payment']);
        } else {
            $ret = 0;
        }
        
        if ($ret < 0) {
            echo $db->error();
        } else {
            echo $ret;
        }
    }
?>