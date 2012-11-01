<?php
    require('macros.php');
    require('classes/CNK_DB.php');

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
?>