<?php
    require('macros.php');
    require('setting/defines.php');
    require('classes/CNK_DB.php');
    
    $db = new CNK_DB();
    
    $db->install();
?>