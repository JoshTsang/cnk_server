<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/mysql.php');
    require('classes/printer.php');
    
    if (isset($_GET['do'])) {
        $do = $_GET['do'];
    } else {
        $do = 'get';
    }
    
    $printer = new printer(PRINTER_CONF);
    $ret = '{"succ":false, "err":"unknown"}';
    if ($do == 'print') {
        if (isset($_GET['id'])) {
            $ret = $printer->printHistory($_GET['id']);
        } else {
            $ret = '{"succ":false, "err":"id?"}';
        }
    }else {
        $ret = $printer->getHistory();
    }

    echo $ret;
    
?>