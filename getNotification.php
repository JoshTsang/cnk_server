<?php
    require('macros.php');
    require('classes/CNK_DB.php');
    require('classes/printer.php');
    require('setting/defines.php');

    $db = new CNK_DB();

    $ret = $db->getNotifications();
    if (!$ret) {
        echo $db->error();
    } else {
        if ($ret == "[]") {
            echo "null";
        } else {
          echo $ret;
        }
    }
    
    $fp = fopen("../db/temporary/printer", "r+");

    if (flock($fp, LOCK_EX | LOCK_NB)) {  // acquire an exclusive lock
        $printer = new printer(PRINTER_CONF);
        $printer->printSavedOrder();
        flock($fp, LOCK_UN);    // release the lock
    } 

    fclose($fp);
?>