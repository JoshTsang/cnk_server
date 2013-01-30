<?php
    require 'macros.php';
    require('setting/defines.php');
    require 'classes/printer.php';
    require 'classes/CNK_DB.php';
    
    if (!isset($_GET['action']) || !isset($_POST['print'])) {
        if (!isset($_POST['json'])) {
            die("{\"succ\":\"false\", \"err\":\"more param needed\"}");
        }  else {
            $print = $_POST['json'];
        }
    }
    
    if (isset($_POST['print'])) {
        $print = $_POST['print'];
    }
    $printer = new printer(PRINTER_CONF);
    $action = strtoupper($_GET['action']);
    switch ($action) {
        case 'SALES':
            $printer->printSales($print);
            break;
        case 'CHECKOUT':
            $db = new CNK_DB();
            $checkoutNo = $db->getCheckoutNo();
            $printer->printCheckout($print, $checkoutNo);
            $receipt = json_decode($print);
            $history = array('type' => HISTORY_CHECKOUT,
                             'table' => $receipt->tableName,
                             'timestamp' => $receipt->timestamp,
                             'receipt' => $print,
                             'extra' => $checkoutNo);
            $printer->saveHistory((object)$history);
            break;
        case 'ORDER':
            $printer->savePrintOrder($print, "0", FALSE);
            break;
        case 'RESERVATION':
            $printer->printReservation($print);
            break;
        default:
            die("{\"succ\":\"false\", \"err\":\"unsupporrtted action:$action\"}");
            break;
    }
    echo "{\"succ\":\"true\"}";
?>