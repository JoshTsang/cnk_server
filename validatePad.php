<?php
    require 'macros.php';
    require 'classes/lisence.php';
    
    if (!isset($_GET['UUID'])) {
        die("UUID NEEDED");
    }
    
    $UUID = $_GET['UUID'];
    $lisence = new Lisence();
    
    $ret = $lisence->validatePad($UUID);
    echo "[$ret]";
?>