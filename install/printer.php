<?php
    if (isset($_POST['printer'])) {
        echo $_POST['printer'];
        exec("cp ../config/printer/".$_POST['printer']." ../orderPad/conf/printer.conf");
    }
?>