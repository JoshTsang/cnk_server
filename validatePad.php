<?php
    require 'macros.php';
    require 'classes/CNK_DB.php';
    require 'classes/lisence.php';
    
    if (!isset($_GET['UUID'])) {
        die("UUID NEEDED");
    }
    
    $UUID = $_GET['UUID'];
    $db = new CNK_DB();
    if ($db->validate()) {
        $lisence = new Lisence();
        
        $ret = $lisence->validatePad($UUID);
        echo "[$ret]";
    } else {
        echo "[1]";
    }
?>