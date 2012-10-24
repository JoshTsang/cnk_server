<?php
    require '../macros.php';
    
    if (!isset($_POST['padNum'])) {
        die();
    }
    
    file_put_contents("../".LISENCE_CONF, $_POST['padNum']);
?>