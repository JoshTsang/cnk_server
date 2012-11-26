<?php
    require 'macros.php';
    require('setting/defines.php');
    require 'classes/printer.php';
    require 'classes/CNK_DB.php';
    
    if (!isset($_GET['action']) || !isset($_POST['print'])) {
        if (!isset($_POST['json'])) {
            die("{\"succ\":\"false\", \"err\":\"more param needed\"}");
        }
    }
    
    $printer = new printer(PRINTER_CONF);
    $action = strtoupper($_GET['action']);
    switch ($action) {
        case 'SALES':
            $printer->printSales($_POST['print']);
            break;
        case 'CHECKOUT':
            $db = new CNK_DB();
            $checkoutNo = $db->getCheckoutNo();
            $printer->printCheckout($_POST['print'], $checkoutNo);
            break;
        case 'ORDER':
            $printer->savePrintOrder($_POST['json'], "0", FALSE);
            break;
        default:
            die("{\"succ\":\"false\", \"err\":\"unsupporrtted action:$action\"}");
            break;
    }
    echo "{\"succ\":\"true\"}";
?>