<?php
    require('macros.php');
    require('classes/CNK_DB.php');
    
    if (isset($_POST['json'])) {
        $obj = json_decode($_POST['json']);
        $start_datetime = $obj->start;
        $end_datetime = $obj->end;
        $type = $obj->type;
    } else {
        die("More Param Needed");
    }
    
    
    $db = new CNK_DB();
    switch ($type) {
        case 0:
            $ret = $db->statisticsByDish($start_datetime, $end_datetime);
            break;
        case 1:
            $ret = $db->statisticsByStuff($start_datetime, $end_datetime);
            break;
        case 2:
            $ret = $db->statisticsByCategory($start_datetime, $end_datetime);
            break;
        case 3:
            $ret = $db->statisticsByPrinter($start_datetime, $end_datetime);
            break;
        default:
            $ret = "";
            break;
    }

    echo $ret;
?>