<?php
    if (isset($_POST['menu'])) {
        echo $_POST['menu'];
        exec("cp ../data/".$_POST['menu']."/* ../db/");
    }
?>