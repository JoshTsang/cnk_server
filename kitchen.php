<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/CNK_DB.php');
    
    $db = new CNK_DB();
    if (isset($_GET['id']) && isset($_GET['flag'])) {
        $db->markCookedDish($_GET['id'], $_GET['flag']);
    }
    $ret = $db->getKitchenTodo();
    echo $ret;
?>