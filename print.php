<?php
    require 'macros.php';
    require 'classes/printer.php';
    
    if (!isset($_GET['action']) || !isset($_POST['print'])) {
        die("{\"succ\":\"false\", \"err\":\"more param needed\"}");
    }
    $action = mb_strtoupper($_GET['action']);
    switch ($action) {
        case 'SALES':
            $printer->printSales($_POST['print']);
            break;
        
        default:
            die("{\"succ\":\"false\", \"err\":\"unsupporrtted action\"}");
            break;
    }
    echo "{\"succ\":\"true\"}";
?>