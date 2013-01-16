<?php
    require '../macros.php';
    $sql = file_get_contents("../db/order.sql");
    $db = new SQLite3("../../db/temp/order.db");
    $db->exec($sql);
    $db->close();
    
    $sql = file_get_contents("../db/sales.sql");
    $db = new SQLite3("../".DATABASE_SALES);
    $db->exec($sql);
    $db->close();
    
    $sql = file_get_contents("../db/receipt.sql");
    $db = new SQLite3("../../db/temp/receipt.db");
    $db->exec($sql);
    $db->close();
     
?>